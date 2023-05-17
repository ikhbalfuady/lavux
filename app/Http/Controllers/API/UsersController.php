<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Repositories\UsersRepository;
use App\Repositories\UserNotificationsRepository;
use App\Providers\AuthProvider;
use App\Services\Uploader;
use Exception;
use App\Services\Notify;

class UsersController extends Controller
{
    protected $repository;
    protected $UserNotificationsRepository;
    protected $request;
    protected $relationDefault = [
        'single' => [
            'Role'
        ],
        'collection' => [
            'Role'
        ],
    ];

    public function __construct(
        Request $request,
        UsersRepository $repository,
        UserNotificationsRepository $UserNotificationsRepository
    ){
        $this->request = $request;
        $this->repository = $repository;
        $this->UserNotificationsRepository = $UserNotificationsRepository;
    }
    
    public function index() {
        try {
            // for passing permissions add query params "?_force"
            $this->handlePermission(H_getControllerName(get_class()), 'browse');
            $data = $this->repository->searchable($this->request, $this->relationDefault['collection']);
            return H_apiResponse($data);
        } catch (Exception $e){
            return H_apiResError($e);
        }
    }

    public function detail($id) {
        try {
            $this->handlePermission(H_getControllerName(get_class()), 'read');
            $data = $this->repository->isExist($id, $this->relationDefault['single']);
            return H_apiResponse($data);
        } catch (Exception $e){
            return H_apiResError($e);
        }
    }
    
    public function store($id = null) {
        DB::beginTransaction();
        try {
            if ($id) $this->handlePermission(H_getControllerName(get_class()), 'update');
            else $this->handlePermission(H_getControllerName(get_class()), 'create');
           
            $this->validateStore($this->request, $id);
            $data = $this->repository->store($id);
            DB::commit();

            $msg = !$id ? 'Succes saving data' : 'Succes updating data';
            return H_apiResponse($data, $msg, 200);
        } catch (Exception $e){
            DB::rollback();
            return H_apiResError($e);
        }
    }  

    /**
     * Method need is PUT, for retrieve id in array
     */
    public function delete() {
        DB::beginTransaction();
        try {
            $this->handlePermission(H_getControllerName(get_class()), 'delete');
            $payload = $this->request->all();
            $ids = H_hasProps($payload, 'id', []);
            $permanent = H_checkProps($payload, 'permanent');
            $totalId = count($ids);
            if (!$totalId) throw new Exception("There's no data selected to delete.");

            $data = $this->repository->deleteRestoreBatch($ids, 'delete', $permanent);
            DB::commit();
            return H_apiResponse($data, "Success deleted $totalId data.", 200);
        } catch (Exception $e){
            DB::rollback();
            return H_apiResError($e);
        }
    }

    /**
     * Method need is PUT, for retrieve id in array
     */
    public function restore() {
        DB::beginTransaction();
        try {
            $this->handlePermission(H_getControllerName(get_class()), 'restore');
            $payload = $this->request->all();
            $ids = H_hasProps($payload, 'id', []);
            $totalId = count($ids);
            if (!$totalId) throw new Exception("There's no data selected to delete.");

            $data = $this->repository->deleteRestoreBatch($ids, 'restore');
            DB::commit();
            return H_apiResponse($data, "Success restored $totalId data.", 200);
        } catch (Exception $e){
            DB::rollback();
            return H_apiResError($e);
        }
    }

    public function info() {
        try {
            $user = $this->repository->auth();
            $data = $this->repository->model->find($user->id);
            return H_apiResponse($data);
        } catch (Exception $e){
            return H_apiResError($e);
        }
    }

    public function permissions($id = null) {
        try {
            $user = $this->repository->auth();
            if ($id == null) $id = $user->id;
            $user = $this->repository->model->find($user->id);
            if ($user) {
                $data = AuthProvider::permissionBase($id, false);
                return H_apiResponse($data);
            } else {
                return H_apiResponse(null, 'User not found', 200);
            }
        } catch (Exception $e){
            return H_apiResError($e);
        }
    }

    public function updatePassword() {
        try {
            $id = AuthProvider::userId();
            if (!$id) throw new Exception("Cannot perform this action, please login first!");

            $user = $this->repository->model->find($id);
            if (!$user) throw new Exception("Data with id ($id) not found");
            
            $payload = $this->request->all();
            $current = H_hasProps($payload, 'current_password');
            $new = H_hasProps($payload, 'password');

            $data = AuthProvider::changePassword($id, $current, $new);
            return H_apiResponse($data, 'Password has been updated!');
        } catch (Exception $e){
            return H_apiResError($e);
        }
    }

    public function updateProfile() {
        try {
            $id = AuthProvider::userId();
            if (!$id) throw new Exception("Cannot perform this action, please login first!");

            $user = $this->repository->model->find($id);
            if (!$user) throw new Exception("Data with id ($id) not found");

            $payload = $this->request->all();
            $user->name = H_hasProps($payload, 'name', $user->name);
            $user->username = H_hasProps($payload, 'username', $user->username);
            $user->email = H_hasProps($payload, 'email', $user->email);

            $data = AuthProvider::updateProfile($id, $user);

            return H_apiResponse($data, 'Profile updated!');
        } catch (Exception $e){
            return H_apiResError($e);
        }
    }

    public function uploadPicture() {
        try {
            $request = $this->request;
            $payload = $request->all();
            $userId = H_hasProps($payload, 'user_id');
            $name = H_hasProps($payload, 'name');

            if (!$userId) throw new Exception("Cannot perform this action, please login first!");
            if (!$request->hasFile('file')) throw new Exception("No file uploaded!");

            if (!$name) $name = uniqid("IMG_{$userId}_");
            else $name = "{$name}{$userId}";

            $file = $request->file('file');
            $upload = Uploader::upload($file, 'public/uploads', $name);
            $this->repository->model->whereId($userId)->update([ 'picture' => $upload->url ]);

            return H_apiResponse($upload, "Upload sucessfully");

        } catch (Exception $e){
            return H_apiResError($e);
        }
    }

    /**
     * For Notify badge in UI for summary notification
     */
    public function notifications() {
        try {
            $userId = $this->repository->userId();
            if (!$userId) throw new Exception("You cannot perform this action, please login first!",);
            
            $data = $this->UserNotificationsRepository->getByUser($userId);
            return H_apiResponse($data);

        } catch (Exception $e){
            return H_apiResError($e);
        }
    }

    /**
     * For all notification in table view
     */
    public function notificationList() {
        try {
            $userId = $this->repository->userId();
            if (!$userId) throw new Exception("You cannot perform this action, please login first!",);
            
            $data = $this->UserNotificationsRepository->getListByUser($userId);
            return H_apiResponse($data);

        } catch (Exception $e){
            return H_apiResError($e);
        }
    }

    public function readNotifications() {
        try {
            $userId = $this->repository->userId();
            if (!$userId) throw new Exception("You cannot perform this action, please login first!",);
            
            $data = $this->UserNotificationsRepository->readNotification($userId);
            return H_apiResponse($data);

        } catch (Exception $e){
            return H_apiResError($e);
        }
    }

    public function sendNotice() {
        try {
            $userId = $this->repository->userId();
            if (!$userId) throw new Exception("You cannot perform this action, please login first!");

            $payload = $this->request->all();
            $toUser = H_hasProps($payload, 'user_id', []);

            // validation
            if (!count($toUser)) throw new Exception("Please pick user to set receiver of this notification!");
            if (!H_hasProps($payload, 'title')) throw new Exception("Please set a title!");
            if (!H_hasProps($payload, 'content')) throw new Exception("Please set a content!");

            $template = Notify::template($payload);
            $send = Notify::make($template, $userId, $toUser);

            return H_apiResponse($send, $send ? 'Succes send notification' : 'Failed send notification');

        } catch (Exception $e){
            return H_apiResError($e);
        }
    }

    public function userProfile() {
        try {
            $data = $this->repository->userProfile();
            return H_apiResponse($data);

        } catch (Exception $e){
            return H_apiResError($e);
        }
    }

    // Validator
    public function validateStore($request, $id = null) {
        try {
            $payload = $request->all();
            if ($id && !$this->repository->checkDataBy('id', $id))  throw new Exception('Data not found');

            $validator = $this->repository->validator($request->all(),
                [
                    'name' => 'required',  
                    'username' => 'required',  
                    'email' => 'required',  
                    'role_id' => 'required',  

                ],
                [
                    'name.required' => 'name is required',  
                    'username.required' => 'username is required',  
                    'email.required' => 'email is required',  
                    'role_id.required' => 'role_id is required',  

                ]
            );
            if ($validator->fails()) {
                $message = $validator->messages()->first();
                throw new Exception($message);
            }
        } catch (Exception $e){
            throw new Exception($e->getMessage());
        }
    }

}

        