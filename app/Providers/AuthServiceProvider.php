<?php

namespace App\Providers;

use App\Models\Service;
use App\Models\User;
use App\Models\Room;
use App\Models\Building;
use App\Models\Booking;
use App\Policies\ServicePolicy;
use App\Policies\RoomPolicy;
use App\Policies\BuildingPolicy;
use App\Policies\BookingPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Summary of policies
     * @var array
     */
    protected $policies = [
        User::class => UserPolicy::class,
        // Building::class => BuildingPolicy::class,
        // Room::class => RoomPolicy::class,
        // Booking::class => BookingPolicy::class,
        // Service::class => ServicePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
