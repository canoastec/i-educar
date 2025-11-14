<?php

return [

    'ui' => [

        'enabled' => true,

        'endpoint' => 'web',

        'view' => 'ui::index',

        // https://laravel.com/docs/packages#views
        'views' => [
            'ui' => base_path('resources'),
        ],

        'type' => 'view',

        'headers' => [
            'Accept' => '*/*',
        ],

        // https://laravel.com/docs/middleware
        'middleware' => [
            'web',
            'ieducar.suspended',
            'auth',
            'ieducar.checkresetpassword',
        ],

        'replaces' => [
            env('UI_FIND') => env('UI_REPLACE_WITH'),
        ],

        'publishes' => [],

        'cache' => true,

    ],

];