<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\UsersRepository;
use App\Providers\AuthProvider;
use Illuminate\Support\Facades\Validator;
use App\Services\ApplicationSettings;
use Exception;

class AuthenticateController extends Controller
{
    protected $UsersRepository;
    protected $request;
    protected $ApplicationSettings;

    public function __construct(
        Request $request,
        UsersRepository $UsersRepository,
        ApplicationSettings $ApplicationSettings
    ){
        $this->request = $request;
        $this->UsersRepository = $UsersRepository;
        $this->ApplicationSettings = $ApplicationSettings;

    }

    public function unauthorized() {
        return H_apiResponse(null, 'Unauthorized', 401);
    }

    public function register(Request $request) {
        try {
            $validator = Validator::make($request->all(), 
                [
                    'name' => 'required',
                    'username' => 'required',
                    'email' => 'required|email',
                    'password' => 'required',
                    'c_password' => 'required|same:password',
                ],
                [
                    'name.required' => 'name is required',
                    'username.required' => 'username is required',
                    'email.required' => 'email is required',
                    'password.required' => 'password is required',
                    'c_password.required' => 'confirm password is required',
                    'c_password.same' => 'confirm password must be same with password',

                ]
            );

            if($validator->fails()){
                $msg = $validator->messages()->first();
                throw new Exception($msg); 
            }

            $data = AuthProvider::registerUser($request);
    
            return H_apiResponse($data);
        } catch (Exception $e){
            return H_apiResError($e);
        }

    }

    public function login(Request $request) {
        try {
            $validator = $this->UsersRepository->validator(
                $request->all(),
                [
                    'password' => 'required',
                ],
                [
                    'password.required' => 'password is required',
                ]
            );
            if($validator->fails()){
                $msg = $validator->messages()->first();
                throw new Exception($msg); 
            }

            $data = AuthProvider::login($request);
            $data->settings = $this->ApplicationSettings->list(); // inject data settings
    
            return H_apiResponse($data);
        } catch (Exception $e){
            return H_apiResError($e);
        }

    }

    public function checkSession () {
        return H_apiResponse(null, 'Session checked', 201);
    }

}
  
        