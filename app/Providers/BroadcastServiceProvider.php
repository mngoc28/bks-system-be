<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * BroadcastServiceProvider
 *
 * KHÔNG dùng `Broadcast::routes()` mặc định vì FE Partner Portal 360 dùng JWT
 * Bearer token (không cookie session). Route auth `/broadcasting/auth` được
 * đăng ký thủ công trong `routes/api.php` với middleware `jwt.auth`, xử lý bởi
 * `App\Http\Controllers\BroadcastAuthController`.
 *
 * Provider này chỉ chịu trách nhiệm load `routes/channels.php` để các định
 * nghĩa channel có hiệu lực.
 */
class BroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        require base_path('routes/channels.php');
    }
}
