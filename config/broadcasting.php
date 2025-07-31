<?php

return [
    'pusher' => [
        'driver' => 'pusher',
        'key' => env('PUSHER_APP_KEY', '00e7e382ce019a1fa987'),
        'secret' => env('PUSHER_APP_SECRET', 'f8393d56f91f8e01fdbe'),
        'app_id' => env('PUSHER_APP_ID', '1992988'),
        'options' => [
            'cluster' => env('PUSHER_APP_CLUSTER', 'ap1'),
            'useTLS' => true,
        ],
    ],
]; 