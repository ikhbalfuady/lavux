<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Users;
use App\Models\Roles;
use Exception;

class AuthProvider extends ServiceProvider
{

    /* Checker */
    public static function has($modelName, $permission) {
        $user_id = AuthProvider::userId();
        $forced = H_keyExist($_GET, '_force');
        $res = true;
        $module = H_splitUppercaseWithSpace($modelName);

        if ($forced) $res = true;
        else {
            if (!$user_id) $res = false;
            else {
                $selector = '
                    users.id as user_id,
                    users.role_id as role_id,
                    role_permissions.permission_id as permission_id,
                    permissions.module as module,
                    permissions.name as name
                ';

                $role_permissions = 'role_permissions';
                $permissions = 'permissions';

                $raw = Users::selectRaw($selector)
                        ->where('users.id', $user_id)
                        ->leftJoin($role_permissions, $role_permissions . '.role_id', '=', 'users.role_id')
                        ->leftJoin($permissions, $permissions . '.id', '=', $role_permissions . '.permission_id')
                        ->where($permissions . '.module', $module)
                        ->where($permissions . '.name', $permission)
                        ->whereNull($role_permissions . '.deleted_at')
                        ->first();

                $res = !empty($raw) ? true : false;
            }
        }

        if (!$res) echo H_api403(); 
    }

    public static function permissionBase ($user_id, $group = true) {
        $selector = '
            users.id as user_id,
            users.role_id as role_id,
            role_permissions.permission_id as permission_id,
            permissions.module as module,
            permissions.name as name
        ';

        $role_permissions = 'role_permissions';
        $permissions = 'permissions';

        $raw = Users::selectRaw($selector)
        ->where('users.id', $user_id)
        ->join($role_permissions, $role_permissions . '.role_id', '=', 'users.role_id')
        ->whereNull($role_permissions . '.deleted_at')
        ->join($permissions, $permissions . '.id', '=', $role_permissions . '.permission_id');

        
        $raw = $raw->get();
        if ($group) $raw = $raw->groupBy('module');

        return H_toArrayObject($raw);
    }

    public static function getPermissionUser (Request $request) {
        try {
            $user_id = AuthProvider::userId();
            if (!$user_id) throw new Exception('user id not set');
            $data = AuthProvider::permissionBase($user_id);
            return H_toArrayObject($data);
        } catch (Exception $e){ 
            throw new Exception('[AuthProvider::getPermissionUser] ' . $e->getMessage());
        }        
    }

    /* Login & Register Handler */
    public static function registerUser(Request $request) {
        try {
            $input = $request->all();

            if (AuthProvider::checkEmail($input['email'])) throw new Exception('Email has registered, please use another.');
            if (AuthProvider::checkUsername($input['username'])) throw new Exception('username has registered, please use another.');
            
            $input['password'] = bcrypt($input['password']);
            $user = Users::create($input);
            $user['token'] = AuthProvider::generateToken($user);
            $user = $user->toArray();
 
            return $user;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[AuthProvider::register]'));
        } 

    }

    public static function checkEmail($email) : bool {
        try {
            $user = DB::table((new Users)->getTable())->select(['email'])->whereEmail($email)->first();
            return $user ? true : false;
        } catch (Exception $e){ 
            throw new Exception(H_throw($e, '[AuthProvider::checkEmail]'));
        } 

    }

    public static function checkUsername($username) : bool {
        try {
            $user = DB::table((new Users)->getTable())->select(['username'])->whereUsername($username)->first();
            return $user ? true : false;
        } catch (Exception $e){ 
            throw new Exception(H_throw($e, '[AuthProvider::checkUsername]'));
        } 

    }

    public static function loginAlt($userName_Email, $password, $type = 'email') : object {
        try {
            $check = null;
            $checkPass = null;
            if ($type == 'email') {
                if (!$userName_Email) throw new Exception('Email is required.');
                $check = AuthProvider::checkEmail($userName_Email);
                if (!$check) throw new Exception('Email user is not registered.');
                $checkPass = Auth::attempt(['email' => $userName_Email, 'password' => $password]);

            } else if ($type == 'username') {
                if (!$userName_Email) throw new Exception('Username is required.');
                $check = AuthProvider::checkUsername($userName_Email);
                if (!$check) throw new Exception('Username is not registered.');
                $checkPass = Auth::attempt(['username' => $userName_Email, 'password' => $password]);

            } else throw new Exception('Username or email not defined.');

            $auth = AuthProvider::auth();

            // checking password
            if (!$checkPass) throw new Exception('Wrong password.');
            return $auth;

        } catch (Exception $e){ 
            throw new Exception(H_throw($e, '[Users::loginAlt]'));
        } 
    }

    public static function login(Request $request) : object {
        try {
            $input = $request->all();

            $email = H_hasProps($input, 'email');
            $username = H_hasProps($input, 'username');
            $password = H_hasProps($input, 'password');

            // login handler
            $user = null;
            if ($email) $user = AuthProvider::loginAlt($email, $password, 'email');
            else if ($username) $user = AuthProvider::loginAlt($username, $password, 'username');
            else throw new Exception('Username or email not defined.');
 
            if (!$user) throw new Exception('Authenticate failed, please try again.');
            $user['token'] = AuthProvider::generateToken($user);
            $role= Roles::find($user['role_id']);
            $user['control_branch'] = $role ? $role->control_branch : false;
            $user['control_department'] = $role ? $role->control_department : false;
            $user['role'] = $role;
            $user['permissions'] = AuthProvider::permissionBase($user['id'], false);
            $user = $user;

            return $user;
        }   catch (Exception $e){
            throw new Exception(H_throw($e, '[AuthProvider::login]'));
        }

    }

    public static function generateToken ($userEloquent) : string{
        return $userEloquent->createToken(env('APP_NAME'))->plainTextToken;
    }

    public static function checkPassword ($id, $password) : bool {
        $user = DB::table((new Users)->getTable())->select(['password'])->whereId($id)->first();
        $current_password = $user ? $user->password : null;
        return Hash::check($password, $current_password);
    }

    public static function checkAvail($type = 'email', $val, $id, $showCredential = false) : void {
        $check = DB::table((new Users)->getTable())
                ->select([
                    '*',
                    DB::raw('(SELECT name FROM '.(new Roles)->getTable().' WHERE id = role_id LIMIT 1) as role_name'),
                ])
                ->where($type, $val);

        if ($id) $check = $check->where('id', '!=', $id);
        $check = $check->first();

        if ($check) {
            $msg = 'User data with '.$type.' "<b>'.$val.'</b>" already exist. <br><br> <b>Found : </b><br>';
            if ($showCredential) {
                $msg .= 'Name : '.$check->name.'<br>';
                $msg .= 'Username : '.$check->username.'<br>';
                $msg .= 'Email : '.$check->email.'<br>';
                $msg .= 'Role : '.$check->role_name.'<br>';
            }
            throw new Exception($msg); 
        }
    }

    public static function auth($raw = true) : object {
        $res = Auth::user();
        if (!$raw) H_toArrayObject(Auth::user());
        return $res;
    }

    public static function userId() : mixed {
        $userId = AuthProvider::auth();
        $userId = $userId ? $userId['id'] : null;
        return $userId;
    }

    /* Management */

    public static function changePassword ($id, $current, $new) : mixed {
        try {
            $userId = AuthProvider::userId();
            if (!$userId) throw new Exception('You cannot perfom this, please login first!');
            if (!$current) throw new Exception('Current password is required!');
            if (!$new) throw new Exception('New password is required!');
            if ($current === $new) throw new Exception('The new password is the same as the old password, please use a different password!');
            if(!AuthProvider::checkPassword($id, $current)) throw new Exception('Current password is wrong!');
            if ($current === $new) throw new Exception('New password cannot be same as before!');
            
            return Users::whereId($id)->update([ 
                'password' => bcrypt($new),
                'updated_at' => H_today(),
                'updated_by' => $userId,
                'updated_ip' => H_getIpClient(),
            ]);

        }   catch (Exception $e){
            throw new Exception(H_throw($e, '[AuthProvider::changePassword]'));
        }
    }

    public static function updateProfile ($id, $data) : mixed {
        try {
            $userId = AuthProvider::userId();
            if (!$userId) throw new Exception('You cannot perfom this, please login first!');

            $fields =[ 
                'name',
                'username',
                'email',
                'picture',
            ];

            $send = [];
            $showCredential = false;
            foreach ($fields as $field) {
                $value = H_hasProps($data, $field);
                if ($value) {
                    if ($field === 'name') AuthProvider::checkAvail($field, $value, $id, $showCredential);
                    if ($field === 'username') AuthProvider::checkAvail($field, $value, $id, $showCredential);
                    if ($field === 'email') AuthProvider::checkAvail($field, $value, $id, $showCredential);
                    $send[$field] = $value;
                }
            }
            
            return Users::whereId($id)->update([ 
                ...$send,
                'updated_at' => H_today(),
                'updated_by' => $userId,
                'updated_ip' => H_getIpClient(),
            ]);

        }   catch (Exception $e){
            throw new Exception(H_throw($e, '[AuthProvider::updateProfile]'));
        }
    }

    /* Utils */ 


}
