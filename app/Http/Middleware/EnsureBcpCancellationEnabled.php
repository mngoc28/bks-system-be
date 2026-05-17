<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\HttpStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates Stay/Partner BCP routes behind `config('bcp.enabled')` / `BCP_CANCELLATION_V1`.
 */
final class EnsureBcpCancellationEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! self::isEnabled()) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Tính năng chính sách hủy (BCP) hiện tạm tắt.',
                'code'    => 'BCP_DISABLED',
            ], HttpStatus::FORBIDDEN->value);
        }

        return $next($request);
    }

    public static function isEnabled(): bool
    {
        $configured = config('bcp.enabled', false);

        return filter_var($configured, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === true;
    }
}
