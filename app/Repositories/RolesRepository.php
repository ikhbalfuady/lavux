<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Roles;
use App\Models\Permissions;
use App\Models\RolePermissions;
use Exception;

use App\Traits\StandardRepo;

class RolesRepository
{
    use StandardRepo;

    public $request;
    public $model;
    public $RolePermissionsRepository;
    
    public function __construct(
        Request $request,
        RolePermissionsRepository $RolePermissionsRepository
    ) {
        $this->request = $request;
        $this->model = $this->initModel();
        $this->RolePermissionsRepository = $RolePermissionsRepository;
    }

    /**
     * Model initiate
     * @return object
     */
    public function initModel($id = null) {
        $model = new Roles;
        if (!empty($id)) $model = $this->model->where($this->model->getKeyName(), $id)->first();
        return $model;
    }

    public function store ($id = null, $customPayload = null) {
        try {
            $payload = $this->request->all();
            if ($customPayload) $payload = $customPayload;
            $data = $this->initModel($id);

            $data->role_group_id = H_hasProps($payload, 'role_group_id'); 
            $data->name = H_hasProps($payload, 'name'); 
            $data->slug = H_hasProps($payload, 'slug'); 

            $this->setLogHistory($data);

            $data->save();

            if ($id) { // only for update mode
                if (isset($payload['detail']) && count($payload['detail']) != 0) {
                    
                    $details = [];
                    foreach ($payload['detail'] as $k => $perms) {
                        $details = [
                            ...$details,
                            ...$perms['permissions'],
                        ];
                    }

                    $data->detail = $this->RolePermissionsRepository->saveRolePermissions($data, $details);
                } else {
                    throw new Exception('Permissions not defined for this role!');
                }
            }

            return $data;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '['.H_getRepositoryName(get_class()).'::store] '));
        }
    }

    // Fetching, Delete & Restore functions there are in Traits/StandardRepo

    public function getRole($id) {
        try {
            $data = $this->model;
            $data = $data->where($this->model->getKeyName(), $id)->first();
            if (empty($data)) throw new Exception("Data with id $id not found");
            else {
                $permissions = Permissions::whereRaw('deleted_at IS NULL')->get();
                $send = [];
                foreach ($permissions as $key => $value) {
                    $obj = [
                        "id" => null,
                        "permission_id" => $value->id,
                        "role_id" => null,
                        "name" => $value->name,
                        "module" => $value->module,
                        "current_allow" => false,
                        "allow" => false,
                    ];
                    $hasRole = RolePermissions::whereRoleId($id)->wherePermissionId($value->id)->first();
                    if ($hasRole) {
                        $obj['id'] = $hasRole->id;
                        $obj['role_id'] = $hasRole->role_id;
                        $obj['current_allow'] = true;
                        $obj['allow'] = true;
                    }
                    $send[] = $obj;
                }
                $send = collect($send)->groupBy('module');

                $fix = [];
                foreach ($send as $k => $perms) {
                    $obj = [
                        "name" => $k,
                        "permissions" => $perms,
                    ];
                    $fix[] = $obj;
                }

                $data->detail = $fix;
            }

            return $data;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '['.H_getRepositoryName(get_class()).'::getRole] '));
        }
    }


}
