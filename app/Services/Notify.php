<?php
namespace App\Services;
use App\Models\Notifications;
use App\Models\UserNotifications;
use Exception;

class Notify {


    public static function template ($body = null) : array {
        return [
            'title'       => H_hasProps($body, 'title'),
            'content'     => H_hasProps($body, 'content'),
            'type'        => H_hasProps($body, 'type', 'system'),
            'category'    => H_hasProps($body, 'category'),
            'link_source' => H_hasProps($body, 'link_source', 'external'),
            'link_url'    => H_hasProps($body, 'link_url'),
            'date'        => H_hasProps($body, 'date', H_today()),
            'created_by'  => H_hasProps($body, 'created_by'),
            'created_at'  => H_today(),
            'created_ip'  => H_getIpClient(),
        ];
    }

    /**
     * Basic make raw notification
     * 
     * @param Object $body : { title, content, category, type, link_source, link_url, date }
     * @param Integer $fromUserId : when null, set by default original name
     * @param Array $toUsers : array value of user id
     * 
     */
    public static function make($body, $fromUserId, $toUsers = []) : bool {
        try {

            if (!count($toUsers)) return false; // abort when there's no receiver
            if (!$fromUserId) return false; // abort when there's no sender
            
            $template = Notify::template($body);
            $template['created_by'] = $fromUserId;

            $notification = Notifications::create($template);
            
            if ($notification) {
                $receiver = [];
                foreach ($toUsers as $userId) {
                    $receiver[] = [
                        'user_id' => $userId,
                        'notification_id' => $notification->id,
                        'created_by' => $fromUserId,
                        'created_at' => H_today(),
                        'created_ip' => H_getIpClient(),
                    ];
                }
                
                return UserNotifications::insert($receiver);
            } else return false;

        } catch (Exception $e){
            throw new Exception(H_throw($e, '['.get_class().'::make]'));
        }

    }



}