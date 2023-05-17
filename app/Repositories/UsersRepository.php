<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Users;
use Exception;

use App\Traits\StandardRepo;

class UsersRepository
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
        $model = new Users;
        if (!empty($id)) $model = $this->model->where($this->model->getKeyName(), $id)->first();
        return $model;
    }

    public function store ($id = null, $customPayload = null) {
        try {
            $payload = $this->request->all();
            if ($customPayload) $payload = $customPayload;
            $data = $this->initModel($id);

            $data->name = H_hasProps($payload, 'name');
            $data->email = H_hasProps($payload, 'email');
            // $data->password = H_hasProps($payload, 'password');

            $this->setLogHistory($data);

            $data->save();
            return $data;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '['.H_getRepositoryName(get_class()).'::store] '));
        }
    }

    // Fetching, Delete & Restore functions there are in Traits/StandardRepo

    public function userProfile () {
        try {
            $payload = $this->request->all();
            $id = H_hasProps($payload, 'id');
            $username = H_hasProps($payload, 'username');
            if (!$id && !$username) throw new Exception("Set params ?id=value OR ?username=value for get the profile");

            $data = DB::table($this->tableName() . " as us")->select([
                'us.id',
                'us.name',
                'us.username',
                'us.email',
                'us.picture',
                'us.is_ban',
                'is_ban',
                'rl.slug as role_slug',
                'rl.name as role_name',
            ])
            ->join('roles as rl', 'rl.id', '=', 'us.role_id');
            if ($id) $data = $data->whereId($id);
            elseif ($username) $data = $data->whereUsername($username);

            return $data->first();
        } catch (Exception $e){
            throw new Exception(H_throw($e, '['.H_getRepositoryName(get_class()).'::userProfile] '));
        }
    }
    

}
