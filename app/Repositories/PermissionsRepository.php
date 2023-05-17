<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Permissions;
use Exception;

use App\Traits\StandardRepo;

class PermissionsRepository
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
        $model = new Permissions;
        if (!empty($id)) $model = $this->model->where($this->model->getKeyName(), $id)->first();
        return $model;
    }

    public function store ($id = null, $customPayload = null) {
        try {
            $payload = $this->request->all();
            if ($customPayload) $payload = $customPayload;
            $data = $this->initModel($id);

            $data->module = H_hasProps($payload, 'module'); 
            $data->name = H_hasProps($payload, 'name'); 

            $this->setLogHistory($data);

            $data->save();
            return $data;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '['.H_getRepositoryName(get_class()).'::store] '));
        }
    }

    // Fetching, Delete & Restore functions there are in Traits/StandardRepo

}
