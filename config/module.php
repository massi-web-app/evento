<?php

return [
    'namespace' => 'Modules',
    'excluded' => ['Main'],
    'routes' => [
        'web' => [
            'middleware' => ['web'],
            'prefix' => null,
        ],
        'api' => [
            'middleware' => ['api'],
            'prefix' => 'api',
        ],
        'admin' => [
            'middleware' => ['api'],
            'prefix' => 'api/admin',
        ],
    ],
];
