<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Partner\BulkBookingActionRequest;
use PHPUnit\Framework\TestCase;

final class BulkBookingActionRequestTest extends TestCase
{
    public function test_bulk_confirm_rules_limit_ids_to_twenty(): void
    {
        $request = BulkBookingActionRequest::create('/api/v1/partner/bookings/bulk-confirm', 'POST');

        $rules = $request->rules();

        $this->assertSame(['required', 'array', 'min:1', 'max:20'], $rules['ids']);
        $this->assertSame(['required', 'integer', 'distinct', 'exists:bookings,id'], $rules['ids.*']);
        $this->assertArrayNotHasKey('reason', $rules);
    }

    public function test_bulk_cancel_requires_shared_reason(): void
    {
        $request = BulkBookingActionRequest::create('/api/v1/partner/bookings/bulk-cancel', 'POST');

        $rules = $request->rules();

        $this->assertSame(['required', 'string', 'min:5', 'max:500'], $rules['reason']);
    }
}
