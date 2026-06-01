<?php

declare(strict_types=1);

return [
  'spot_min_rooms' => (int) env('HOMEPAGE_SPOT_MIN_ROOMS', 4),
  'spot_fallback_region' => (bool) env('HOMEPAGE_SPOT_FALLBACK_REGION', false),
];
