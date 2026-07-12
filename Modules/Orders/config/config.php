<?php

return [
    'name' => 'Orders',
    'gateway' => env('PAYMENT_GATEWAY', 'fake'),
    'frontend_result_url' => env('FRONTEND_PAYMENT_RESULT_URL', 'http://localhost:3000/payment/result'),
];
