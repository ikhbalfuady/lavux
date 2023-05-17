<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use App\Providers\AuthProvider;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * set params "?_force" to force permission check
     * 
     * @param $modelName    :  use H_getControllerName(get_class()) to simplify get real model name
     * @param $permisssion  :  permission you want to check, ex : browse, read, create etc...
     */
    public function handlePermission ($modelName, $permisssion) {
        return AuthProvider::has($modelName, $permisssion);
    }
}
