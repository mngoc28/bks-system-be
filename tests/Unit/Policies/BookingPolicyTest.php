<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Building;
use App\Models\Room;
use App\Models\User;
use App\Policies\BookingPolicy;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for BookingPolicy.
 *
 * Mapped to plan task T1.6 / T1.14.
 */
final class BookingPolicyTest extends TestCase
{
    private BookingPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new BookingPolicy();
    }

    public function test_admin_bypasses_all_checks(): void
    {
        $admin = $this->makeUser(role: 'admin');
        $booking = $this->makeBookingFor(otherPartnerId: 42);

        $this->assertTrue((bool) $this->policy->before($admin, 'cancel'));
        $this->assertTrue((bool) $this->policy->before($admin, 'noShow'));
    }

    public function test_partner_can_confirm_own_pending_booking(): void
    {
        $partner = $this->makeUser(id: 7, role: 'partner');
        $booking = $this->makeBookingFor(otherPartnerId: 7, status: BookingStatus::PENDING);

        $this->assertTrue($this->policy->confirm($partner, $booking));
    }

    public function test_partner_cannot_confirm_other_partners_booking(): void
    {
        $partner = $this->makeUser(id: 7, role: 'partner');
        $booking = $this->makeBookingFor(otherPartnerId: 99, status: BookingStatus::PENDING);

        $this->assertFalse($this->policy->confirm($partner, $booking));
    }

    public function test_partner_cannot_confirm_already_confirmed_booking(): void
    {
        $partner = $this->makeUser(id: 7, role: 'partner');
        $booking = $this->makeBookingFor(otherPartnerId: 7, status: BookingStatus::CONFIRMED);

        $this->assertFalse($this->policy->confirm($partner, $booking));
    }

    public function test_partner_can_cancel_pending_or_confirmed_but_not_cancelled(): void
    {
        $partner = $this->makeUser(id: 7, role: 'partner');

        $this->assertTrue($this->policy->cancel(
            $partner,
            $this->makeBookingFor(otherPartnerId: 7, status: BookingStatus::PENDING),
        ));
        $this->assertTrue($this->policy->cancel(
            $partner,
            $this->makeBookingFor(otherPartnerId: 7, status: BookingStatus::CONFIRMED),
        ));
        $this->assertFalse($this->policy->cancel(
            $partner,
            $this->makeBookingFor(otherPartnerId: 7, status: BookingStatus::CANCELLED),
        ));
    }

    public function test_partner_can_no_show_only_confirmed_booking(): void
    {
        $partner = $this->makeUser(id: 7, role: 'partner');

        $this->assertTrue($this->policy->noShow(
            $partner,
            $this->makeBookingFor(otherPartnerId: 7, status: BookingStatus::CONFIRMED),
        ));
        $this->assertFalse($this->policy->noShow(
            $partner,
            $this->makeBookingFor(otherPartnerId: 7, status: BookingStatus::PENDING),
        ));
    }

    public function test_regular_user_cannot_perform_partner_actions(): void
    {
        $user = $this->makeUser(id: 7, role: 'user');
        $booking = $this->makeBookingFor(otherPartnerId: 7, status: BookingStatus::PENDING);

        $this->assertFalse($this->policy->confirm($user, $booking));
        $this->assertFalse($this->policy->cancel($user, $booking));
        $this->assertFalse($this->policy->noShow($user, $booking));
    }

    /**
     * Construct a User model instance without booting Eloquent connections.
     */
    private function makeUser(int $id = 1, string $role = 'partner'): User
    {
        $user = new User();
        $user->id = $id;
        $user->role = $role;

        return $user;
    }

    /**
     * Construct a Booking with stubbed room->building relation that returns a
     * building owned by the given partner id. We rely on Eloquent's
     * `setRelation` so the policy reads the in-memory graph instead of
     * triggering a DB query.
     */
    private function makeBookingFor(
        int $otherPartnerId,
        BookingStatus $status = BookingStatus::PENDING,
    ): Booking {
        $building = new Building();
        $building->id = 100;
        $building->user_id = $otherPartnerId;

        $room = new Room();
        $room->id = 200;
        $room->setRelation('building', $building);

        $booking = new Booking();
        $booking->id = 300;
        $booking->status = $status->value;
        $booking->setRelation('room', $room);

        return $booking;
    }
}
