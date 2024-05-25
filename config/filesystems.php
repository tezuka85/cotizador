<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            //'root' => storage_path('app'),
            'root' => tmp_path('cotizaciones/storage/app'),
            //'root' => env('APP_LOG_PATH') .'claro-envios-app.log',
        ],

        'public' => [
            'driver' => 'local',
            //'root' => storage_path('app/public'),
            'root' => tmp_path('cotizaciones/storage/app/web'),
            //'root' => env('APP_LOG_PATH') .'claro-envios-debugbar.log',
            //'url' => env('APP_URL').'/storage',
            'url' => tmp_path('cotizaciones/storage'),
            'visibility' => 'public',
        ],
        'controllers' => [
            'driver' => 'local',
            'root' => app_path('Http/Controllers/Admin'),
        ],

        'views' => [
            'driver' => 'local',
            'root' => base_path('resources/views/admin'),
        ],
        'tmp' => [
            'driver' => 'local',
//            'root' => tmp_path('cotizaciones/storage/app/web'),
            'root' => tmp_path('/tmp'),],
    ],

];
