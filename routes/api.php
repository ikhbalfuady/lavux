<?php
use App\Modules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the 'api' middleware group. Make something great!
|
*/
$ApiNS =  'App\Http\Controllers\API\\'; // Api Name Space
$WebNS =  'App\Http\Controllers\\'; // Web Name Space

/* Global & Main App */
Route::get('/', function () { return env('APP_NAME'); });
Route::get('/unauthorized', $ApiNS .'AuthenticateController@unauthorized')->name('unauthorized');
Route::post('/upload', $WebNS .'GlobalController@upload');


/* Application */

Route::group(['prefix' => '/app'], function() use ($router, $ApiNS, $WebNS) {
    Route::get('/version', function () { return H_apiResponse('1.0.1'); });
    Route::get('/menus', function () {  return H_apiResponse(config('lv_menu', [])); });
    // setting
    Route::get('/settings', $WebNS . 'GlobalController@settingList');
    Route::get('/settings/init', $WebNS . 'GlobalController@settingList');
    Route::get('settings/{module}/{config}', $WebNS . 'GlobalController@getSetting');
    // authentication & profile
    Route::post('/login', $ApiNS . 'AuthenticateController@login');
    Route::post('/register', $ApiNS . 'AuthenticateController@register');
    Route::get('/user-profile', $ApiNS . 'UsersController@userProfile');
    // generator section
    Route::get('/scopes', $WebNS . 'GlobalController@listScope');
    Route::put('/scopes', $WebNS . 'GlobalController@insertScope');
    Route::put('/scopes/{scope}', $WebNS . 'GlobalController@updateScope');
    Route::delete('/scopes/{scope}', $WebNS . 'GlobalController@deleteScope');
});


/* Users Authenticated */
Route::middleware('auth:sanctum')->group( function () use ($ApiNS)  {
    Route::get('/check-session', $ApiNS .'AuthenticateController@checkSession');

    Route::group(['prefix' => '/me'], function() use ($ApiNS) {
        Route::get('/', $ApiNS .'UsersController@info');
        Route::get('/permissions', $ApiNS .'UsersController@permissions');
        Route::post('/update-password', $ApiNS .'UsersController@updatePassword');
        Route::post('/update-profile', $ApiNS .'UsersController@updateProfile');
        Route::post('/upload-picture', $ApiNS .'UsersController@uploadPicture');
        Route::get('/notifications', $ApiNS .'UsersController@notifications');
        Route::get('/notification-list', $ApiNS .'UsersController@notificationList');
        Route::put('/read-notifications', $ApiNS .'UsersController@readNotifications');
        Route::put('/send-notice', $ApiNS .'UsersController@sendNotice');
    });
});

/* Without Authorization */
// your custom route here

/*  Module Need Authorization */
Route::middleware('auth:sanctum')->group( function () use ($ApiNS, $WebNS)  {

    Route::put("app/settings", $WebNS . "GlobalController@updateSetting");

    $modules = config('lv_modules', []);
    $excludeModules = [
        //'YourModule',
    ];

    foreach ($modules as $module) {
        if (!in_array($module, $excludeModules)) {
            $slug = H_makeSlug($module);
            Route::group(['prefix' => "/$slug"], function() use ($module, $WebNS, $ApiNS) {
                Route::get('/', $ApiNS . $module . 'Controller@index');
                Route::get('/{id}', $ApiNS . $module  . 'Controller@detail');
                Route::post('/', $ApiNS . $module  . 'Controller@store');
                Route::put('/delete', $ApiNS . $module  . 'Controller@delete');
                Route::put('/restore', $ApiNS . $module  . 'Controller@restore');
                Route::put('/{id}', $ApiNS . $module  . 'Controller@store');
            });
        }

    }

});
