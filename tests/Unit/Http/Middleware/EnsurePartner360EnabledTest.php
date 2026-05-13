<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\EnsurePartner360Enabled;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Unit tests for the Partner Portal 360 feature flag middleware (T5.6).
 */
final class EnsurePartner360EnabledTest extends TestCase
{
    public function test_passes_request_through_when_flag_is_enabled(): void
    {
        config()->set('app.partner_360_enabled', true);

        $middleware = new EnsurePartner360Enabled();
        $request = Request::create('/api/v1/partner/calendar', 'GET');

        $response = $middleware->handle($request, fn () => response()->json(['ok' => true]));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"ok":true}', $response->getContent());
    }

    public function test_returns_forbidden_payload_when_flag_is_disabled(): void
    {
        config()->set('app.partner_360_enabled', false);

        $middleware = new EnsurePartner360Enabled();
        $request = Request::create('/api/v1/partner/calendar', 'GET');

        $response = $middleware->handle($request, fn () => response()->json(['ok' => true]));

        $this->assertSame(403, $response->getStatusCode());

        $payload = json_decode($response->getContent() ?: '{}', true);
        $this->assertIsArray($payload);
        $this->assertFalse($payload['success']);
        $this->assertSame('PARTNER_360_DISABLED', $payload['code']);
    }
}
