<?php

namespace App\Providers;

use App\Models\Service;
use App\Models\User;
use App\Models\Room;
use App\Models\Property;
use App\Models\Booking;
use App\Models\BookingCancellationRequest;
use App\Models\Contract;
use App\Models\RoomBlock;
use App\Policies\ServicePolicy;
use App\Policies\RoomPolicy;
use App\Policies\PropertyPolicy;
use App\Policies\BookingPolicy;
use App\Policies\BookingCancellationRequestPolicy;
use App\Policies\ContractPolicy;
use App\Policies\RoomBlockPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Summary of policies
     * @var array
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Booking::class => BookingPolicy::class,
        BookingCancellationRequest::class => BookingCancellationRequestPolicy::class,
        Contract::class => ContractPolicy::class,
        RoomBlock::class => RoomBlockPolicy::class,
        // Property::class => PropertyPolicy::class,
        // Room::class => RoomPolicy::class,
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

        // RoomBlockPolicy gắn với model RoomBlock, nhưng các ability
        // `createForRoom` / `viewForRoom` nhận Room làm đối số. Laravel sẽ tìm
        // Policy của Room — không tồn tại — và mặc định deny. Đăng ký Gate
        // tường minh, vừa giữ admin bypass vừa uỷ quyền cho RoomBlockPolicy.
        Gate::define('createForRoom', function (User $user, Room $room) {
            if ($user->role === 'admin') {
                return true;
            }

            return app(RoomBlockPolicy::class)->createForRoom($user, $room);
        });
        Gate::define('viewForRoom', function (User $user, Room $room) {
            if ($user->role === 'admin') {
                return true;
            }

            return app(RoomBlockPolicy::class)->viewForRoom($user, $room);
        });
    }
}
