<?php

declare(strict_types=1);

return [
    'spot_min_rooms' => (int) env('HOMEPAGE_SPOT_MIN_ROOMS', 4),
    'spot_fallback_region' => (bool) env('HOMEPAGE_SPOT_FALLBACK_REGION', false),
    'cache' => [
        'room_ttl' => (int) env('HOMEPAGE_ROOM_CACHE_TTL', 600),
        'metadata_ttl' => (int) env('HOMEPAGE_METADATA_CACHE_TTL', 3600),
        'http_max_age' => (int) env('HOMEPAGE_HTTP_CACHE_MAX_AGE', 3600),
    ],
    'bootstrap' => [
        'tourist_spots_limit' => (int) env('HOMEPAGE_BOOTSTRAP_TOURIST_SPOTS_LIMIT', 50),
    ],
];
