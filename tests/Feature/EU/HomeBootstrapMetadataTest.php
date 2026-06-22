<?php

declare(strict_types=1);

namespace Tests\Feature\EU;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class HomeBootstrapMetadataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_home_bootstrap_metadata_returns_grouped_payload(): void
    {
        $response = $this->getJson('/api/v1/home/bootstrap-metadata');

        $response->assertOk();
        $response->assertJsonPath('status', 'success');
        $response->assertJsonStructure([
            'data' => [
                'provinces',
                'property_types',
                'tourist_spots',
            ],
        ]);
        $this->assertNotEmpty($response->json('data.provinces'));
        $this->assertNotEmpty($response->json('data.property_types'));
    }

    public function test_home_bootstrap_metadata_sets_public_cache_headers(): void
    {
        $response = $this->getJson('/api/v1/home/bootstrap-metadata');

        $response->assertOk();
        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=3600', $cacheControl);
    }

    public function test_home_provinces_sets_public_cache_headers(): void
    {
        $response = $this->getJson('/api/v1/home/provinces');

        $response->assertOk();
        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=3600', $cacheControl);
    }

    public function test_top_rated_rooms_response_is_stable_with_cache_enabled(): void
    {
        Cache::flush();

        $first = $this->getJson('/api/v1/home/rooms/getTopRatedRoom?limit=12');
        $first->assertOk();

        $second = $this->getJson('/api/v1/home/rooms/getTopRatedRoom?limit=12');
        $second->assertOk();
        $this->assertSame($first->json('data'), $second->json('data'));
    }
}
