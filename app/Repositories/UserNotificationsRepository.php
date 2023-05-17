<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\UserNotifications;
use App\Models\Notifications;
use Exception;

use App\Traits\StandardRepo;

class UserNotificationsRepository
{
    use StandardRepo;

    public $request;
    public $model;
    
    public function __construct(
        Request $request
    ) {
        $this->request = $request;
        $this->model = $this->initModel();
    }

    /**
     * Model initiate
     * @return object
     */
    public function initModel($id = null) {
        $model = new UserNotifications;
        if (!empty($id)) $model = $this->model->where($this->model->getKeyName(), $id)->first();
        return $model;
    }

    public function store ($id = null, $customPayload = null) {
        try {
            $payload = $this->request->all();
            if ($customPayload) $payload = $customPayload;
            $data = $this->initModel($id);

            $data->user_id = H_hasProps($payload, 'user_id', 0); 
            $data->is_read = H_hasProps($payload, 'is_read', false); 
            $data->notification_id = H_hasProps($payload, 'notification_id', 0); 

            $this->setLogHistory($data);

            $data->save();
            return $data;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '['.H_getRepositoryName(get_class()).'::store] '));
        }
    }

    // Fetching, Delete & Restore functions there are in Traits/StandardRepo


    /**
     * 
     * Base Query for get notification
     * 
     * notifications table as 'nf'
     * user_notifications table as 'un'
     * users table as 'us'
     * 
     * @param Int $userId
     * @param Array $select
     * @param Boolean $joinUser
     */
    public function getBaseQuery ($userId, $select = [], $joinUser = true) {
        try {
            $data = DB::table('notifications as nf')
            ->select($select)
            ->join('user_notifications as un', 'nf.id', '=', 'un.notification_id');

            if ($joinUser) $data = $data->join('users as us', 'nf.created_by', '=', 'us.id');
            $data = $data->where('un.user_id', $userId);

            return $data;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '['.H_getRepositoryName(get_class()).'::getBaseQuery] '));
        }
    }

    public function getDataByUser ($userId) {
        try {
            $payload = $this->request->all();

            // filter
            $unRead = H_keyExist($payload, 'unread');
            $title = H_hasProps($payload, 'title');
            $content = H_hasProps($payload, 'content');
            $type = H_hasProps($payload, 'type');
            $category = H_hasProps($payload, 'category');
            $from = H_hasProps($payload, 'from');
            $to = H_hasProps($payload, 'to');
            $creator = H_hasProps($payload, 'creator');

            $select = [
                'un.id',
                'nf.title',
                'nf.content',
                'nf.type',
                'nf.category',
                'nf.link_source',
                'nf.link_url',
                'nf.date',
                'un.user_id',
                'un.is_read',
                'us.name as creator',
                'us.picture as creator_pic',
            ];

            $data = $this->getBaseQuery($userId, $select);

            if ($unRead) $data = $data->where('un.is_read', false);
            if ($title) $data = $data->whereRaw('nf.title LIKE ?', ['%'.$title.'%']);
            if ($content) $data = $data->whereRaw('nf.content LIKE ?', ['%'.$content.'%']);
            if ($type) $data = $data->whereRaw('nf.type LIKE ?', ['%'.$type.'%']);
            if ($category) $data = $data->whereRaw('nf.category LIKE ?', ['%'.$category.'%']);
            
            if ($from) $data = $data->where('nf.date', $from);
            elseif ($from && $to) $data = $data->whereBetween('nf.date', [$from, $to]);

            if ($creator) $data = $data->whereRaw('us.name LIKE ?', ['%'.$creator.'%']);

            $data = $data->orderBy('nf.date', 'desc');

            $data = $this->paging($data, $payload);

            return $data;

        } catch (Exception $e){
            throw new Exception(H_throw($e, '['.H_getRepositoryName(get_class()).'::getDataByUser] '));
        }
    }

    public function getByUser ($userId) {
        try {
            $payload = $this->request->all();

            // fetch only get total unread
            $max_group = (int) H_hasProps($payload, 'max_group', 0);
            $total_unread = H_keyExist($payload, 'total_unread');
            if ($total_unread) return $this->getBaseQuery($userId, [DB::raw('count(nf.id) as total')])->where('un.is_read', false)->first();

            // fetch list
            $list = $this->getDataByUser($userId);

            $all = $this->getBaseQuery($userId, [DB::raw('count(nf.id) as total')])->first();
            $totalUnRead = $this->getBaseQuery($userId, [DB::raw('count(nf.id) as total')])->where('un.is_read', false)->first();

            $grouped = null;
            $totalByType = null;
            $types = (new Notifications)->EnumType();
            foreach ($types  as $type) {
                $get = $this->getBaseQuery($userId, [DB::raw('count(nf.id) as total')])->where('nf.type', $type)->where('un.is_read', false)->first();
                $totalByType['total_unread_'.$type] =  $get->total ?? 0;

                if (H_keyExist($payload, 'grouping')) {
                    if ($max_group > 0) $grouped[$type] = $this->formatTimeAgo($list->where('type', $type)->take($max_group)->values());
                    else $grouped[$type] = $this->formatTimeAgo($list->where('type', $type)->values());
                }
            }

            $data = [
                'total' => $all->total ?? 0 ,
                'total_unread' => $totalUnRead->total ?? 0,
                ...$totalByType,
            ];

            if (H_keyExist($payload, 'grouping')) {
                $data = [
                    ...$data,
                    ...$grouped,
                ];
            } else {
                $data = [
                    ...$data,
                    'list' => $this->formatTimeAgo($list),
                ];
            }

            return $data;

        } catch (Exception $e){
            throw new Exception(H_throw($e, '['.H_getRepositoryName(get_class()).'::getByUser] '));
        }
    }

    public function getListByUser ($userId) {
        try {

            $rawRequest = $this->request;
            $rawRequest['user_id'] = $userId; // inject user id 
            $payload = $this->request->all();

            $params = [
                'date'  => H_hasProps($payload, 'date'),
                'title'  => H_hasProps($payload, 'title'),
                'content'  => H_hasProps($payload, 'content'),
                'type'  => H_hasProps($payload, 'type'),
                'category'  => H_hasProps($payload, 'category'),
                'link_url'  => H_hasProps($payload, 'link'),
            ];

            $creator  = H_hasProps($payload, 'creator');
            $unread  = H_toBoolean(H_hasProps($payload, 'unread', 'true'));

            // $data = DB::table('notifications as nf')
            $nf = (new Notifications)->getTable();
            $data = Notifications::select([
                "un.id",
                "$nf.id as notification_id",
                "$nf.title",
                "$nf.content",
                "$nf.type",
                "$nf.category",
                "$nf.link_source",
                "$nf.link_url",
                "$nf.date",
                DB::raw("(SELECT username FROM users WHERE id = $nf.created_by LIMIT 1) as creator")
            ])
            ->join("user_notifications as un", "$nf.id", "=", "un.notification_id")
            ->join("users as us", "$nf.created_by", "=", "us.id");

            foreach ($params as $col => $value ) {
                if ($value) $data = $this->makeWhereQuery($data, $col, $value);
            }

            if ($creator) $data->whereRaw('us.username' .' LIKE ?', ['%'.$creator.'%']);

            if ($unread) $data->where('un.is_read', false);
            else $data->where('un.is_read', true);
 
            $data = $data->where("un.user_id", $userId);

            // dd($data->toSql());
  
            $data = $this->dynamicList($data, $payload);

            return $data;

        } catch (Exception $e){
            throw new Exception(H_throw($e, '['.H_getRepositoryName(get_class()).'::getListByUser] '));
        }
    }

    public function readNotification ($userId) {
        try {

            $payload = $this->request->all();
            $notif = H_hasProps($payload, 'notif');
            $data = $this->model->whereUserId($userId)->whereIn('id', $notif);
            return $data->update(['is_read' => 1]);
        }  catch (Exception $e){
            throw new Exception(H_throw($e, '['.H_getRepositoryName(get_class()).'::readNotification] '));
        }
    }

    function formatTimeAgo ($data) {
        $fix = [];
        foreach ($data as $row) {
            if (isset($row->date)) {
                $row->time_ago = H_timeAgo($row->date); 
                $fix[] = $row;
            }
        }
        return $fix;
    }
}
