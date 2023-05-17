<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\RolePermissions;
use Exception;

use App\Traits\StandardRepo;

class RolePermissionsRepository
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
        $model = new RolePermissions;
        if (!empty($id)) $model = $this->model->where($this->model->getKeyName(), $id)->first();
        return $model;
    }

    public function store ($id = null, $customPayload = null) {
        try {
            $payload = $this->request->all();
            if ($customPayload) $payload = $customPayload;
            $data = $this->initModel($id);

            $data->permission_id = H_hasProps($payload, 'permission_id'); 
            $data->role_id = H_hasProps($payload, 'role_id'); 

            $this->setLogHistory($data);

            $data->save();
            return $data;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '['.H_getRepositoryName(get_class()).'::store] '));
        }
    }

    // Fetching, Delete & Restore functions there are in Traits/StandardRepo

    public function saveRolePermissions ($role, $details) {
        $saved = [];
        foreach ($details as $key => $permission) {
            if ($permission['current_allow'] != $permission['allow']) {
                $data = $this->prepareAndStore($role, $permission);
                $saved[] = $data;
            }
        }
        return $saved;
    }

    function prepareAndStore($role, $permission) {
        $data = [
            "id" => $permission['id'],
            "permission_id" => $permission['permission_id'],
            "role_id" => $role['id'],
        ];
        if ($permission['allow']) {
            $check = $this->model;
            $check = $check->where('permission_id', $permission['permission_id']);
            $check = $check->where('role_id', $role['id']);
            $check = $check->onlyTrashed()->first();
            if ($check) {
                $save = $this->deleteRestoreBatch([$check->id], 'restore'); // jika pernah dibuat namun pernah di disallow
                $save = $check;
            }
            else $save = $this->store($permission['id'], $data); // belum ada sama sekali
        }
        else  $save = $this->deleteRestoreBatch([$permission['id']], 'delete');
        return json_decode($save);
    }
    
}
