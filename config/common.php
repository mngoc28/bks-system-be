<?php

return [
    'status_response' => [
        'success' => 'Success',
        'error' => 'Error',
    ],
    'encrypt_secret_key' => env('ENCRYPT_SECRET_KEY', 'dHJhbiBuaGF0IHRoaWVuaWVu'),
    'secret_iv' => env('SECRET_IV', 'c2VjcmV0X2l2c2VjcmV0X2l2A'),
];
