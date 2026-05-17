<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Booking Cancellation Policy (BCP) feature flag
    |--------------------------------------------------------------------------
    |
    | When false, routes guarded by `bcp.cancellation` return 403 with code
    | `BCP_DISABLED` so Stay/Partner clients can degrade gracefully.
    |
    */

    'enabled' => filter_var(
        env('BCP_CANCELLATION_V1', false),
        FILTER_VALIDATE_BOOLEAN,
        FILTER_NULL_ON_FAILURE,
    ) ?? false,

    /*
    |--------------------------------------------------------------------------
    | Cooldown between repeated cancel-request submissions (same booking)
    |--------------------------------------------------------------------------
    */

    'cancel_request_cooldown_seconds' => (int) env('BCP_CANCEL_REQUEST_COOLDOWN_SECONDS', 3600),

    /*
    |--------------------------------------------------------------------------
    | SLA / reporting: treat open requests older than this as "stale"
    |--------------------------------------------------------------------------
    */

    'stale_request_hours' => (int) env('BCP_STALE_REQUEST_HOURS', 48),

    /*
    |--------------------------------------------------------------------------
    | Minimum nights to classify a stay as "long" for policy tiers
    |--------------------------------------------------------------------------
    */

    'long_stay_min_nights' => (int) env('BCP_LONG_STAY_MIN_NIGHTS', 30),

    /** Default policy version row seeded in `cancellation_policy_versions`. */
    'baseline_policy_version' => (string) env('BCP_BASELINE_POLICY_VERSION', '2026-baseline-v1'),
];
