<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Frontend Route
|--------------------------------------------------------------------------
|
| It's a shame, for your web route it looks like it will be fully used by the "frontend" application, 
| maybe you need to be careful when you really need to add a special route to this section, 
| because you are afraid there will be a conflict between the routes that have been made.
|
*/


try {
    $uiRoutes = include('ui_route.php');

    if ($uiRoutes) {
        foreach ($uiRoutes as $r) {
            $path = preg_replace('/:([^\/]+)/', '{$1}', $r['path']);
            Route::get($path, function () {
                return view('frontend');
            });
        }
    } else {
        Route::get('/', function () {
            return 'Welcome to Lavux';
        });
    }
} catch (\Exception $e) {
    /* 
        [routes/ui_route.php] not found
        make sure you has run follow the Deploy rules, see detail by accesing url dev '/generator' 
    */
}
