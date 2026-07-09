<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

final class HomePageCacheService
{
    private const ROOMS_VERSION_KEY = 'homepage:rooms:version';

    private const METADATA_VERSION_KEY = 'homepage:metadata:version';

    /**
     * @param callable(): array{success: bool, data: mixed, message: string} $resolver
     * @return array{success: bool, data: mixed, message: string}
     */
    public function rememberTopRatedRooms(Request $request, callable $resolver): array
    {
        $ttl = (int) config('homepage.cache.room_ttl', 600);

        /** @var array{success: bool, data: mixed, message: string} $payload */
        $payload = Cache::remember(
            $this->topRatedRoomsCacheKey($request),
            $ttl,
            $resolver,
        );

        return $payload;
    }

    /**
     * @param callable(): array{success: bool, data?: mixed, message: string} $resolver
     * @return array{success: bool, data?: mixed, message: string}
     */
    public function rememberSuggestedRoomsByTouristSpot(Request $request, callable $resolver): array
    {
        $ttl = (int) config('homepage.cache.room_ttl', 600);

        /** @var array{success: bool, data?: mixed, message: string} $payload */
        $payload = Cache::remember(
            $this->suggestedRoomsByTouristSpotCacheKey($request),
            $ttl,
            $resolver,
        );

        return $payload;
    }

    /**
     * @param callable(): array<string, mixed> $resolver
     * @return array<string, mixed>
     */
    public function rememberBootstrapMetadata(callable $resolver): array
    {
        $cacheKey = $this->bootstrapMetadataCacheKey();
        $cached = Cache::get($cacheKey);

        if (is_array($cached) && $this->isBootstrapMetadataComplete($cached)) {
            return $cached;
        }

        if ($cached !== null) {
            Cache::forget($cacheKey);
        }

        /** @var array<string, mixed> $payload */
        $payload = $resolver();

        if (! $this->isBootstrapMetadataComplete($payload)) {
            throw new \RuntimeException('Bootstrap metadata payload is incomplete.');
        }

        $ttl = (int) config('homepage.cache.metadata_ttl', 3600);
        Cache::put($cacheKey, $payload, $ttl);

        return $payload;
    }

    public function bumpRoomsCacheVersion(): void
    {
        $this->bumpVersion(self::ROOMS_VERSION_KEY);
    }

    public function bumpMetadataCacheVersion(): void
    {
        $this->bumpVersion(self::METADATA_VERSION_KEY);
        Cache::forget($this->bootstrapMetadataCacheKey());
    }

    private function topRatedRoomsCacheKey(Request $request): string
    {
        $fingerprint = md5((string) json_encode([
            'limit' => $request->input('limit'),
            'include_tourist_summary' => $request->boolean('include_tourist_summary'),
        ]));

        return sprintf('homepage:rooms:v%d:top-rated:%s', $this->roomsCacheVersion(), $fingerprint);
    }

    private function suggestedRoomsByTouristSpotCacheKey(Request $request): string
    {
        $spotIds = $request->input('tourist_spot_ids', []);
        $spotSlugs = $request->input('tourist_spot_slugs', []);

        $fingerprint = md5((string) json_encode([
            'tourist_spot_ids' => is_array($spotIds) ? array_values($spotIds) : [],
            'tourist_spot_slugs' => is_array($spotSlugs) ? array_values($spotSlugs) : [],
            'limit' => $request->input('limit'),
            'include_tourist_summary' => $request->boolean('include_tourist_summary'),
            'spot_min_rooms' => (int) config('homepage.spot_min_rooms', 4),
        ]));

        return sprintf('homepage:rooms:v%d:suggested-by-spot:%s', $this->roomsCacheVersion(), $fingerprint);
    }

    private function bootstrapMetadataCacheKey(): string
    {
        return sprintf(
            'homepage:metadata:v%d:bootstrap:spots-%d',
            $this->metadataCacheVersion(),
            (int) config('homepage.bootstrap.tourist_spots_limit', 50),
        );
    }

    private function roomsCacheVersion(): int
    {
        return $this->currentVersion(self::ROOMS_VERSION_KEY);
    }

    private function metadataCacheVersion(): int
    {
        return $this->currentVersion(self::METADATA_VERSION_KEY);
    }

    private function currentVersion(string $key): int
    {
        $value = Cache::get($key);

        if ($value === null) {
            Cache::put($key, 1, now()->addDays(30));

            return 1;
        }

        return (int) $value;
    }

    private function bumpVersion(string $key): void
    {
        if (Cache::has($key)) {
            Cache::increment($key);

            return;
        }

        Cache::put($key, 2, now()->addDays(30));
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function isBootstrapMetadataComplete(array $payload): bool
    {
        return $this->hasItems($payload['provinces'] ?? null)
            && $this->hasItems($payload['property_types'] ?? null);
    }

    private function hasItems(mixed $value): bool
    {
        if (is_array($value)) {
            return $value !== [];
        }

        if ($value instanceof \Countable) {
            return count($value) > 0;
        }

        return false;
    }
}
