<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Models\Booking;
use App\Models\BookingCancellationRequest;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Policies\BookingCancellationRequestPolicy;
use PHPUnit\Framework\TestCase;

final class BookingCancellationRequestPolicyTest extends TestCase
{
    private BookingCancellationRequestPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new BookingCancellationRequestPolicy();
    }

    public function test_partner_can_view_when_property_owner_matches(): void
    {
        $partner = $this->makeUser(10, 'partner');
        $request = $this->makeRequestForPartnerProperty(partnerUserId: 10);

        $this->assertTrue($this->policy->view($partner, $request));
    }

    public function test_partner_cannot_view_other_property_requests(): void
    {
        $partner = $this->makeUser(10, 'partner');
        $request = $this->makeRequestForPartnerProperty(partnerUserId: 99);

        $this->assertFalse($this->policy->view($partner, $request));
    }

    public function test_non_partner_denied(): void
    {
        $user    = $this->makeUser(3, 'user');
        $request = $this->makeRequestForPartnerProperty(partnerUserId: 10);

        $this->assertFalse($this->policy->view($user, $request));
    }

    private function makeUser(int $id, string $role): User
    {
        $u       = new User();
        $u->id   = $id;
        $u->role = $role;

        return $u;
    }

    private function makeRequestForPartnerProperty(int $partnerUserId): BookingCancellationRequest
    {
        $property         = new Property();
        $property->id     = 700;
        $property->user_id = $partnerUserId;
        $property->name   = 'Test property';

        $room            = new Room();
        $room->id        = 800;
        $room->property_id = $property->id;
        $room->setRelation('property', $property);

        $booking          = new Booking();
        $booking->id      = 900;
        $booking->room_id = $room->id;
        $booking->setRelation('room', $room);

        $request            = new BookingCancellationRequest();
        $request->id        = 1000;
        $request->booking_id = $booking->id;
        $request->setRelation('booking', $booking);

        return $request;
    }
}
