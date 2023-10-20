<?php

return [
    'build' => env('MT_BUILD'),
    'services' => [
        'fulfillment_service_name' => env('FULFILLMENT_SERVICE_NAME', ''),
        's2oms' => [
            'webhook_receiver' => env('S2OMS_WEBHOOK_RECEIVER_URL'),
        ],
        'oms2s' => [
            'webhook_receiver' => env('OMS2S_WEBHOOK_RECEIVER_URL'),
        ],
    ],
    'oms2s' => [
        'verification_key' => env('OMS2S_VERIFICATION_KEY'),
    ],
    'oms' => [
        'api_url' => env('OMS_API_URL'),
        'api_user' => env('OMS_API_USER'),
        'api_key' => env('OMS_API_KEY'),
    ],
    'verification_key' => env('VERIFICATION_KEY'),
];
