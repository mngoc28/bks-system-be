<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Commission Rate
    |--------------------------------------------------------------------------
    |
    | The default commission rate (percentage fee) applied to partner
    | room and services revenue. 0.05 represents a 5% platform fee.
    |
    */
    'commission_rate' => 0.05,

    /*
    |--------------------------------------------------------------------------
    | Billing Cycle Configuration
    |--------------------------------------------------------------------------
    |
    | The days of the month on which settlements are calculated/generated.
    | Day 5: Calculates bookings checkout between 16th of previous month to end of previous month.
    | Day 20: Calculates bookings checkout between 1st to 15th of current month.
    |
    */
    'cycle_days' => [5, 20],

    /*
    |--------------------------------------------------------------------------
    | Due Days
    |--------------------------------------------------------------------------
    |
    | Number of days the partner has to pay the commission fee after the
    | settlement invoice is issued.
    |
    */
    'due_days' => 5,

    /*
    |--------------------------------------------------------------------------
    | BKS Bank Transfer Information
    |--------------------------------------------------------------------------
    |
    | Banking information displayed to partners on the dashboard.
    |
    */
    'bank_info' => [
        'bank_name' => 'Vietcombank',
        'account_number' => '1023456789',
        'account_holder' => 'CONG TY CP BKS STAY',
        'transfer_syntax_prefix' => 'BKSBILL',
    ],
];
