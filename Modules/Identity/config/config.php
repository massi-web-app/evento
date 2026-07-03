<?php

return [
    'name' => 'Identity',

    'otp' => [
        'length' => 6,
        'ttl_seconds' => 120,          // مطابق سند master: otp.expiry_seconds=120
        'max_attempts' => 5,

        'send_limit' => [              // ضد SMS-bombing
            'max_per_window' => 3,
            'window_seconds' => 600,
        ],
    ],
];
