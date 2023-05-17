<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Metas;
use Exception;

use App\Traits\StandardRepo;

class MetasRepository
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
        $model = new Metas;
        if (!empty($id)) $model = $this->model->where($this->model->getKeyName(), $id)->first();
        return $model;
    }

    public function store ($id = null, $customPayload = null) {
        try {
            $payload = $this->request->all();
            if ($customPayload) $payload = $customPayload;
            $data = $this->initModel($id);

            $data->type = H_hasProps($payload, 'type', 'string'); 
            $data->slug = H_hasProps($payload, 'slug'); 
            $data->name = H_hasProps($payload, 'name'); 
            $data->value = H_hasProps($payload, 'value'); 
            $data->description = H_hasProps($payload, 'description'); 
            $data->remarks = H_hasProps($payload, 'remarks'); 

            $this->setLogHistory($data);

            $data->save();
            return $data;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '['.H_getRepositoryName(get_class()).'::store] '));
        }
    }

    // Fetching, Delete & Restore functions there are in Traits/StandardRepo

}
