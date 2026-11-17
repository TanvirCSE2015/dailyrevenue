<?php

return [
    'base_url' => rtrim(env('JANATAPAY_BASE_URL', 'https://sandbox-pg.janatapay.com'), '/'),
    'merchant_uid' => env('JANATAPAY_MERCHANT_UID', ''),
    'merchant_username' => env('JANATAPAY_MERCHANT_USERNAME', ''),
    'merchant_password' => env('JANATAPAY_MERCHANT_PASSWORD', ''),
    // Optional: if omitted, fetch the public key from the sandbox's public endpoint.
    'public_key' => env('JANATAPAY_PUBLIC_KEY', ''),
];
