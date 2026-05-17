<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\EnsureBcpCancellationEnabled;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class EnsureBcpCancellationEnabledTest extends TestCase
{
    public function test_returns_403_when_bcp_disabled(): void
    {
        config(['bcp.enabled' => false]);

        Route::middleware(EnsureBcpCancellationEnabled::class)->get(
            '/__probe_bcp_disabled',
            static fn () => response()->json(['ok' => true]),
        );

        $this->getJson('/__probe_bcp_disabled')
            ->assertStatus(403)
            ->assertJsonPath('code', 'BCP_DISABLED');
    }

    public function test_allows_request_when_bcp_enabled(): void
    {
        config(['bcp.enabled' => true]);

        Route::middleware(EnsureBcpCancellationEnabled::class)->get(
            '/__probe_bcp_enabled',
            static fn () => response()->json(['ok' => true]),
        );

        $this->getJson('/__probe_bcp_enabled')
            ->assertOk()
            ->assertJson(['ok' => true]);
    }
}
