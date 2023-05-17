<?php
/** Application Modules */

return [
    /*
    |--------------------------------------------------------------------------
    | Module list
    |--------------------------------------------------------------------------
    |
    | This required for Backend only, for generating route & bining Repository
    | Every you create new module, you must register here 
    |
    */

    /** Company Setting */ 
    [
        "name" => "company",
        "comment" => "Configuration default of your company information, you can customize your config by editing file '/config/lv_setting.php' ",
        "list" => [ 
            "name" => "PT Solusi Pembantu Usaha",
            "app_name" => "Lavux Enterprise Systems",
            "tag_line" => "Component library for Enterprise system",
            "phone" => "012 345 6789",
            "web" => "http://lavux.sopeus.com",
            "email" => "lavux@sopeus.com",
            "url_logo" => '/assets/logo.png', // url logo (default as sidebard logo)
            "url_icon" => '/assets/icon.png', // url icon
            "url_logo_light" => '/assets/logo-light.png', // url logo for "Top Menu Mode"
        ],
    ],
    // your configuration
 
];