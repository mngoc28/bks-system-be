<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

if (!function_exists("get_over_time")) {
    function get_over_time($endTime): float
    {
        $now = Carbon::now();

        if ($now->lessThanOrEqualTo($endTime)) {
            return 0;
        }

        return $now->diffInMinutes($endTime);
    }
}

if (!function_exists("cryptEncrypt")) {
    function cryptEncrypt(
        string $payload,
        $encryptMethod = "AES-256-CBC"
    ): string {
        // hash
        $key = hash("sha256", config("common.encrypt_secret_key"));
        $iv = substr(hash("sha256", config("common.secret_iv")), 0, 16);

        return base64_encode(
            openssl_encrypt($payload, $encryptMethod, $key, 0, $iv)
        );
    }
}

if (!function_exists("cryptDecrypt")) {
    function cryptDecrypt(
        string $payload,
        $encryptMethod = "AES-256-CBC"
    ): string {
        // hash
        $key = hash("sha256", config("common.encrypt_secret_key"));
        $iv = substr(hash("sha256", config("common.secret_iv")), 0, 16);

        return openssl_decrypt(
            base64_decode($payload),
            $encryptMethod,
            $key,
            0,
            $iv
        );
    }
}
