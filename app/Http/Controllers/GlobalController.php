<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\MetasRepository;
use App\Services\ModuleGenerator;
use App\Services\Uploader;
use App\Services\ApplicationSettings;
use Exception;

class GlobalController extends Controller
{
    protected $MetasRepository;
    protected $request;
    protected $ApplicationSettings;

    public function __construct(
        Request $request,
        MetasRepository $MetasRepository,
        ApplicationSettings $ApplicationSettings
    ){
        $this->request = $request;
        $this->MetasRepository = $MetasRepository;
        $this->ApplicationSettings = $ApplicationSettings;
    }


    /* GENERATOR */
    
    public function listScope() {
        try {

            $data = ModuleGenerator::getScopeListDetail();
            return H_apiResponse($data);
        } catch (Exception $e){
            return H_apiResError($e);
        }

    }

    public function insertScope() {
        try {
            $payload = $this->request->all();
            $check = ModuleGenerator::getScope($payload['name']);
            if (!$check->error) throw new Exception("scope name \"<b>{$payload['name']}</b>\" already exist, please use another name! ");

            $data = ModuleGenerator::storeScope($payload['name'], $payload['modules']);
            return H_apiResponse($data->result, $data->message);
        } catch (Exception $e){
            return H_apiResError($e);
        }

    }

    public function updateScope($scope) {
        try {
            $payload = $this->request->all();
            $data = ModuleGenerator::storeScope($scope, $payload['modules']);
            return H_apiResponse($data);
        } catch (Exception $e){
            return H_apiResError($e);
        }

    }

    public function deleteScope($scope) {
        try {
            $data = ModuleGenerator::deleteScope($scope);
            if ($data->result) return H_apiResponse($data);
            else return H_apiResponse($data, $data->message, 400);
        } catch (Exception $e){
            return H_apiResError($e);
        }

    }

    /* UTILITIES */

    public function upload(){
        try {
            $request = $this->request;
            if (!$request->hasFile('file')) throw new Exception("No file uploaded!");

            $upload = Uploader::upload(
                $request->file('file'), 
                'public/uploads', 
                uniqid("FU_")
            );
            return H_apiResponse($upload, "Upload sucessfully");

        } catch (Exception $e){
            return H_apiResError($e);
        }
    }


    /* Application Setting  */

    public function initSetting () {
        try {
            $settings = $this->ApplicationSettings->init();
            return H_apiResponse($settings);
        } catch (Exception $e){
            return H_apiResError($e);
        }
    }

    public function settingList (Request $request) {
        try {
            $payload = $request->all();
            $module = H_hasProps($payload, 'module');
            $settings = $this->ApplicationSettings->list($module);
            return H_apiResponse($settings);
        } catch (Exception $e){
            return H_apiResError($e);
        }
    }

    public function getSetting (Request $request, $module, $config) {
        try {
            $payload = $request->all();
            $settings = $this->ApplicationSettings->getConfig($module, $config);
            return H_apiResponse($settings);
        } catch (Exception $e){
            return H_apiResError($e);
        }
    }

    public function updateSetting (Request $request) {
        try {
            $payload = $request->all();
            $id = H_hasProps($payload, 'id');
            $module = H_hasProps($payload, 'module');
            $config = H_hasProps($payload, 'config');
            $value = H_hasProps($payload, 'value');

            $data = $this->ApplicationSettings->update($id, $value, $module, $config);
            return H_apiResponse($data);
        } catch (Exception $e){
            return H_apiResError($e);
        }
    }


}
  
        