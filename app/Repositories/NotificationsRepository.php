<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Notifications;
use Exception;

use App\Traits\StandardRepo;

class NotificationsRepository
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
        $model = new Notifications;
        if (!empty($id)) $model = $this->model->where($this->model->getKeyName(), $id)->first();
        return $model;
    }

    public function store ($id = null, $customPayload = null) {
        try {
            $payload = $this->request->all();
            if ($customPayload) $payload = $customPayload;
            $data = $this->initModel($id);

            $data->title = H_hasProps($payload, 'title'); 
            $data->content = H_hasProps($payload, 'content'); 
            $data->type = H_hasProps($payload, 'type', 'system'); 
            $data->category = H_hasProps($payload, 'category'); 
            $data->link_source = H_hasProps($payload, 'link_source', 'frontend'); 
            $data->link_url = H_hasProps($payload, 'link_url'); 
            $data->date = H_hasProps($payload, 'date'); 

            $this->setLogHistory($data);

            $data->save();
            return $data;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '['.H_getRepositoryName(get_class()).'::store] '));
        }
    }

    // Fetching, Delete & Restore functions there are in Traits/StandardRepo

}
