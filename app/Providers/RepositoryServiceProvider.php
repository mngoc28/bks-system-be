<?php

namespace App\Providers;

use App\Repositories\AmenityRepository\AmenityRepository;
use App\Repositories\AmenityRepository\AmenityRepositoryInterface;
use App\Repositories\BaseRepository;
use App\Repositories\BookingRepository\BookingRepository;
use App\Repositories\BookingRepository\BookingRepositoryInterface;
use App\Repositories\PropertyRepository\PropertyRepository;
use App\Repositories\PropertyRepository\PropertyRepositoryInterface;
use App\Repositories\ProvincesRepository\ProvincesRepository;
use App\Repositories\ProvincesRepository\ProvincesRepositoryInterface;
use App\Repositories\RepositoryInterface;
use App\Repositories\RoomServiceRepository\RoomServiceRepository;
use App\Repositories\RoomServiceRepository\RoomServiceRepositoryInterface;
use App\Repositories\RoomsRepository\RoomsRepository;
use App\Repositories\RoomsRepository\RoomsRepositoryInterface;
use App\Repositories\ServiceRepository\ServiceRepository;
use App\Repositories\ServiceRepository\ServiceRepositoryInterface;
use App\Repositories\RoomMaintenanceRepository\RoomMaintenanceRepository;
use App\Repositories\RoomMaintenanceRepository\RoomMaintenanceRepositoryInterface;
use App\Repositories\UsersRepository\UsersRepository;
use App\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Repositories\RoomImageRepository\RoomImageRepository;
use App\Repositories\RoomImageRepository\RoomImageRepositoryInterface;
use App\Repositories\RoomAmenityRepository\RoomAmenityRepository;
use App\Repositories\RoomAmenityRepository\RoomAmenityRepositoryInterface;
use App\Repositories\RoomPriceRepository\RoomPriceRepository;
use App\Repositories\RoomPriceRepository\RoomPriceRepositoryInterface;
use App\Repositories\ChatbotAnswerRepository\ChatbotAnswerRepository;
use App\Repositories\ChatbotAnswerRepository\ChatbotAnswerRepositoryInterface;
use App\Repositories\ChatbotQuestionRepository\ChatbotQuestionRepository;
use App\Repositories\ChatbotQuestionRepository\ChatbotQuestionRepositoryInterface;
use App\Repositories\PropertyImageRepository\PropertyImageRepository;
use App\Repositories\PropertyImageRepository\PropertyImageRepositoryInterface;
use App\Repositories\CouponRepository\CouponRepository;
use App\Repositories\CouponRepository\CouponRepositoryInterface;
use App\Repositories\NewRoomRepository\NewRoomRepository;
use App\Repositories\NewRoomRepository\NewRoomRepositoryInterface;
use App\Repositories\NewsRepository\NewsRepository;
use App\Repositories\NewsRepository\NewsRepositoryInterface;
use App\Repositories\PartnerInforRepository\PartnerInforRepository;
use App\Repositories\PartnerInforRepository\PartnerInforRepositoryInterface;
use App\Repositories\UserReportRepository\UserReportRepository;
use App\Repositories\UserReportRepository\UserReportRepositoryInterface;
use App\Repositories\WardsRepository\WardsRepository;
use App\Repositories\WardsRepository\WardsRepositoryInterface;
use App\Repositories\PricePackageRepository\PricePackageRepository;
use App\Repositories\PricePackageRepository\PricePackageRepositoryInterface;
use App\Repositories\PropertyTypeRepository\PropertyTypeRepository;
use App\Repositories\PropertyTypeRepository\PropertyTypeRepositoryInterface;
use App\Repositories\ContractRepository\EloquentContractRepository;
use App\Repositories\ContractRepository\ContractRepositoryInterface;
use App\Repositories\BookingTimelineRepository\BookingTimelineRepository;
use App\Repositories\BookingTimelineRepository\BookingTimelineRepositoryInterface;
use App\Repositories\RoomBlockRepository\RoomBlockRepository;
use App\Repositories\RoomBlockRepository\RoomBlockRepositoryInterface;
use App\Repositories\PartnerCancellationRequestRepository\PartnerCancellationRequestRepository;
use App\Repositories\PartnerCancellationRequestRepository\PartnerCancellationRequestRepositoryInterface;
use App\Repositories\ReviewRepository\ReviewRepository;
use App\Repositories\ReviewRepository\ReviewRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            RepositoryInterface::class,
            BaseRepository::class
        );
        $this->app->singleton(
            UsersRepositoryInterface::class,
            UsersRepository::class
        );
        $this->app->singleton(
            PropertyRepositoryInterface::class,
            PropertyRepository::class
        );
        $this->app->singleton(
            ServiceRepositoryInterface::class,
            ServiceRepository::class
        );
        $this->app->singleton(
            RoomServiceRepositoryInterface::class,
            RoomServiceRepository::class
        );
        $this->app->singleton(
            RoomsRepositoryInterface::class,
            RoomsRepository::class
        );
        $this->app->singleton(
            BookingRepositoryInterface::class,
            BookingRepository::class
        );
        $this->app->singleton(
            ProvincesRepositoryInterface::class,
            ProvincesRepository::class
        );
        $this->app->singleton(
            WardsRepositoryInterface::class,
            WardsRepository::class
        );
        $this->app->singleton(
            RoomImageRepositoryInterface::class,
            RoomImageRepository::class
        );
        $this->app->singleton(
            RoomAmenityRepositoryInterface::class,
            RoomAmenityRepository::class
        );
        $this->app->singleton(
            RoomPriceRepositoryInterface::class,
            RoomPriceRepository::class
        );
        $this->app->singleton(
            PricePackageRepositoryInterface::class,
            PricePackageRepository::class
        );
        $this->app->singleton(
            AmenityRepositoryInterface::class,
            AmenityRepository::class
        );
        $this->app->singleton(
            ChatbotQuestionRepositoryInterface::class,
            ChatbotQuestionRepository::class
        );
        $this->app->singleton(
            ChatbotAnswerRepositoryInterface::class,
            ChatbotAnswerRepository::class
        );
        $this->app->singleton(
            PropertyImageRepositoryInterface::class,
            PropertyImageRepository::class
        );
        $this->app->singleton(
            CouponRepositoryInterface::class,
            CouponRepository::class
        );
        $this->app->singleton(
            PartnerInforRepositoryInterface::class,
            PartnerInforRepository::class
        );
        $this->app->singleton(
            UserReportRepositoryInterface::class,
            UserReportRepository::class
        );
        $this->app->singleton(
            PropertyTypeRepositoryInterface::class,
            PropertyTypeRepository::class
        );
        $this->app->singleton(
            NewsRepositoryInterface::class,
            NewsRepository::class
        );
        $this->app->singleton(
            RoomMaintenanceRepositoryInterface::class,
            RoomMaintenanceRepository::class
        );
        $this->app->singleton(
            ContractRepositoryInterface::class,
            EloquentContractRepository::class
        );
        $this->app->singleton(
            BookingTimelineRepositoryInterface::class,
            BookingTimelineRepository::class
        );
        $this->app->singleton(
            RoomBlockRepositoryInterface::class,
            RoomBlockRepository::class
        );
        $this->app->singleton(
            PartnerCancellationRequestRepositoryInterface::class,
            PartnerCancellationRequestRepository::class
        );
        $this->app->singleton(
            ReviewRepositoryInterface::class,
            ReviewRepository::class
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
