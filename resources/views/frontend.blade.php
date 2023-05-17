<?php 

$uiDir = 'public/';
$dir = base_path($uiDir); // entry point frontend folder
$app = $dir . '/app.html';

if (!file_exists($app)) {
   if (env('APP_ENV') == 'local') die("file in folder : $uiDir/app.html not found, make sure you has run : quasar \"build\" inside \ui folder. ");
   else echo env('APP_NAME');
} else {

    // $updateIndex = file_get_contents($index);

    // // run fix file path
    // if (strpos($updateIndex, 'name=deployment:false') !== false) {
    //     $updateIndex = str_replace('name=deployment:false', 'name=deployment:true', $updateIndex); // fix icons path
    //     $updateIndex = str_replace('icons/', '_ui/icons/', $updateIndex); // fix icons path
    //     $updateIndex = str_replace('/css/', '/_ui/css/', $updateIndex); // fix css path
    //     $updateIndex = str_replace('/js/', '/_ui/js/', $updateIndex); // fix js path
    // }

    // if (file_put_contents($index, $updateIndex) !== false) {
    //     include $index;
    // } else {
    //     echo "Unable to load index";
    // }

    include $app;
}
