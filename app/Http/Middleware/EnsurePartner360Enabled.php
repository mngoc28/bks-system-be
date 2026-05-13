<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\HttpStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for Partner Portal 360 routes introduced from Phase 3 onward.
 *
 * The feature flag is read from `config('app.partner_360_enabled')` which
 * falls back to the `PARTNER_360_ENABLED` env variable (default true). When
 * disabled, the middleware returns 403 with a deterministic JSON envelope so
 * the frontend can hide the corresponding UI gracefully.
 *
 * The endpoints `/partner/dashboard/kpis`, `/partner/dashboard/stats`,
 * `/partner/contracts` (CRUD) and any other pre-Phase-3 routes are NOT
 * protected by this middleware to keep backwards compatibility.
 */
final class EnsurePartner360Enabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! self::isEnabled()) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Tính năng Partner Portal 360 hiện tạm tắt.',
                'code'    => 'PARTNER_360_DISABLED',
            ], HttpStatus::FORBIDDEN->value);
        }

        return $next($request);
    }

    public static function isEnabled(): bool
    {
        $configured = config('app.partner_360_enabled', env('PARTNER_360_ENABLED', true));
        return filter_var($configured, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
    }
}
