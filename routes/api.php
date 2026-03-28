<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BuildingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProvincesController;
use App\Http\Controllers\RoomsController;
use App\Http\Controllers\RoomMaintenanceController;
use App\Http\Controllers\AmenityController;
use App\Http\Controllers\CloudinaryController;
use App\Http\Controllers\BuildingImageController;
use App\Http\Controllers\RoomImageController;
use App\Http\Controllers\PricePackageController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\WardsController;
use App\Http\Controllers\UserReportController;
use App\Http\Controllers\EU\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PartnerInforController;
use App\Http\Controllers\EU\PartnerController;
use App\Http\Controllers\EU\RoomController as EURoomsController;
use App\Http\Controllers\NewRoomController;
use App\Http\Controllers\PropertyTypeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
|
| Structure:
| /api/v1/admin/     - Admin API
| /api/v1/user/      - User API
| /api/v1/partner/   - Partner API
|
*/

Route::group([
    'prefix' => 'v1',
], function () {

    /**
     *  Refresh JWT token
     */
    Route::post('auth/refresh', [AuthController::class, 'refreshToken']);

    /**
     * Check permission
     */
    Route::get('auth/check-permission', [AuthController::class, 'checkPermission']);

    /**
     * ============================================
     * ADMIN API
     * ============================================
     * Base URL: /api/v1/admin/
     */
    Route::prefix('admin')->group(function () {

        /**
         * Auth API - Public
         */
        Route::prefix('auth')->group(function () {
            Route::post('register', [AuthController::class, 'register']);
            Route::post('login', [AuthController::class, 'login']);
            Route::get('verify-email/{token}', [AuthController::class, 'verifyEmail']);
            Route::post('reset-token-verify-email', [AuthController::class, 'handleResetTokenVerify']);
            Route::post('send-mail-reset-password', [AuthController::class, 'sendMailResetPassword']);
        });

        /**
         * Auth API - Authenticated
         */
        Route::middleware('jwt.auth')->prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
        });

        /**
         * Users API - Protected (Admin Only)
         */
        Route::middleware(['jwt.auth', 'role:admin,partner'])->prefix('users')->group(function () {
            Route::post('create', [UserController::class, 'store']);
            Route::get('{id}', [UserController::class, 'show']);
            Route::put('{id}', [UserController::class, 'update']);
            Route::post('/reset-password/{id}', [UserController::class, 'resetPassword']);
            Route::delete('{id}', [UserController::class, 'destroy']);
            Route::get('/', [UserController::class, 'index']);
            Route::post('/avatar/{id}', [UserController::class, 'uploadAvatar']);
        });

        /**
         * Services API - Protected (Admin & Partner)
         */
        Route::middleware(['jwt.auth', 'role:admin,partner'])->prefix('services')->group(function () {
            Route::get('/all', [ServiceController::class, 'getAllServices']);
            Route::post('', [ServiceController::class, 'store']);
            Route::put('{id}', [ServiceController::class, 'update'])->whereNumber('id');
            Route::delete('{id}', [ServiceController::class, 'destroy'])->whereNumber('id');
            Route::get('/search', [ServiceController::class, 'index']);
            Route::get('{id}', [ServiceController::class, 'show'])->whereNumber('id');
        });

        /**
         * Buildings API - Protected (Admin & Partner)
         */
        Route::prefix("buildings")->group(function () {
            Route::middleware(["jwt.auth", "role:admin,partner"])->group(function () {
                Route::get("searchAll", [BuildingsController::class, "index"]);
                Route::get("types", [BuildingsController::class, "getAllBuildingsTypes"]);
                Route::get("bookings/{id}", [BuildingsController::class, "getBookingsByBuilding"])->whereNumber("id");
                Route::get("{id}", [BuildingsController::class, "show"])->whereNumber("id");
                Route::post("", [BuildingsController::class, "store"]);
                Route::put("{id}", [BuildingsController::class, "update"])->whereNumber("id");
                Route::delete("{id}", [BuildingsController::class, "destroy"])->whereNumber("id");
                Route::get("all", [BuildingsController::class, "getAllBuildingNames"]);
            });
        });

        /**
         * Cloudinary API - Protected (Admin & Partner)
         */
        Route::middleware(['jwt.auth', 'role:admin,partner'])->prefix('cloudinary')->group(function () {
            Route::post('/upload-image', [CloudinaryController::class, 'uploadImage']);
            Route::post('/upload-multiple-images', [CloudinaryController::class, 'uploadMultipleImages']);
            Route::delete('/delete-image', [CloudinaryController::class, 'deleteImage']);
        });

        /**
         * Building Images API - Protected (Admin & Partner)
         */
        Route::middleware(['jwt.auth', 'role:admin,partner'])->prefix('building-images')->group(function () {
            // get building images by building id
            Route::get('building/{buildingId}', [BuildingImageController::class, 'getByBuildingId']);
            // get building image by id
            Route::get('{id}', [BuildingImageController::class, 'show'])->whereNumber('id');
            // create building image
            Route::post('/', [BuildingImageController::class, 'store']);
            // update building image
            Route::put('{id}', [BuildingImageController::class, 'update'])->whereNumber('id');
            // delete building image
            Route::delete('{id}', [BuildingImageController::class, 'destroy'])->whereNumber('id');
            // sort building images
            Route::put('/sort/{buildingId}', [BuildingImageController::class, 'sort'])->whereNumber('buildingId');
        });

        /**
         * Room Images API - Protected (Admin & Partner)
         */
        Route::middleware(['jwt.auth', 'role:admin,partner'])->prefix('room-images')->group(function () {
            Route::get('room/{roomId}', [RoomImageController::class, 'getByRoomId']);
            Route::get('{id}', [RoomImageController::class, 'show'])->whereNumber('id');
            Route::post('/', [RoomImageController::class, 'store']);
            Route::put('/update-type', [RoomImageController::class, 'updateType']);
            Route::put('{roomId}/update-sort/{imageIdA}/{imageIdB}', [RoomImageController::class, 'updateSort'])
                ->whereNumber('roomId');
            Route::delete('/', [RoomImageController::class, 'destroy']);
        });

        /**
         * Rooms API - Public
         */
        Route::prefix('rooms')->group(function () {
            Route::get('/search', [RoomsController::class, 'index']);
            Route::get('{id}', [RoomsController::class, 'show']);
            Route::get('building/{buildingId}', [RoomsController::class, 'getRoomNamesByBuildingId']);
        });

        /**
         * Rooms API - Protected (Admin & Partner)
         */
        Route::middleware(['jwt.auth', 'role:admin,partner'])->prefix('rooms')->group(function () {
            Route::post('store', [RoomsController::class, 'store']);
            Route::put('{id}', [RoomsController::class, 'update']);
            Route::delete('{id}', [RoomsController::class, 'destroy']);
        });

        /**
         * Amenity API
         */
        Route::middleware(['jwt.auth'])->prefix('amenities')->group(function () {
            Route::get('/', [AmenityController::class, 'index']);
            Route::get('/all', [AmenityController::class, 'getAllAmenities']);
            Route::get('{id}', [AmenityController::class, 'show']);
            Route::post('store', [AmenityController::class, 'store']);
            Route::put('{id}', [AmenityController::class, 'update']);
            Route::delete('{id}', [AmenityController::class, 'destroy']);
        });

        Route::middleware(['jwt.auth'])->prefix('coupons')->group(function () {
            Route::get('/', [CouponController::class, 'index']);
            Route::post('create', [CouponController::class, 'store']);
            Route::put('update/{id}', [CouponController::class, 'update']);
            Route::delete('delete/{id}', [CouponController::class, 'destroy']);
        });

        Route::middleware(['jwt.auth'])->prefix('room-maintenances')->group(function () {
            Route::get('/', [RoomMaintenanceController::class, 'index']);
            Route::post('/', [RoomMaintenanceController::class, 'store']);
        });

        Route::middleware(['jwt.auth'])->prefix('chatbot')->group(function () {
            Route::get('/', [ChatbotController::class, 'index']);
            Route::get('list-question-flow', [ChatbotController::class, 'listQuestionFlow']);
            Route::get('detail/{id}', [ChatbotController::class, 'show']);
            Route::post('create', [ChatbotController::class, 'store']);
            Route::put('update/{id}', [ChatbotController::class, 'update']);
            Route::put('update-line-flow/{id}', [ChatbotController::class, 'updateLineFlow']);
            Route::put('update-position/{id}', [ChatbotController::class, 'updatePosition']);
            Route::delete('delete/{id}', [ChatbotController::class, 'destroy']);
        });

        Route::middleware(['jwt.auth'])->prefix('reports')->group(function () {
            Route::post('/', [UserReportController::class, 'store']);
        });

        Route::middleware(['jwt.auth'])->prefix('property-types')->group(function () {
            Route::post('/', [PropertyTypeController::class, 'store']);
            Route::get('/', [PropertyTypeController::class, 'index']);
            Route::get('{id}', [PropertyTypeController::class, 'show'])->whereNumber('id');
            Route::put('{id}', [PropertyTypeController::class, 'update'])->whereNumber('id');
            Route::patch('{id}/status', [PropertyTypeController::class, 'updateStatus'])->whereNumber('id');
        });

        /**
         * Price package API
         */
        Route::middleware(['jwt.auth', 'role:admin,partner'])->prefix('price-packages')->group(function () {
            Route::get('/', [PricePackageController::class, 'index']);
            Route::get('room/{roomId}', [PricePackageController::class, 'getByRoomId']);
        });

        /**
         * Bookings API - Protected (Admin & Partner)
         */
        Route::middleware(['jwt.auth', 'role:admin,partner'])->prefix('bookings')->group(function () {
            Route::get('/', [BookingController::class, 'index']);
            Route::post('/', [BookingController::class, 'store']);
            Route::get('{id}', [BookingController::class, 'show'])->whereNumber('id');
            Route::put('{id}', [BookingController::class, 'update'])->whereNumber('id');
            Route::put('{id}/cancel', [BookingController::class, 'cancel'])->whereNumber('id');
            Route::put('{id}/confirm', [BookingController::class, 'confirmBooking'])->whereNumber('id');
            Route::put('{id}/cancel', [BookingController::class, 'cancelBooking'])->whereNumber('id');
        });

        /**
         * User Profile API - Authenticated (All roles)
         */
        Route::middleware('jwt.auth')->prefix('user')->group(function () {
            Route::get('profile', [UserController::class, 'show']);
            Route::get('profile/{id}', [UserController::class, 'show']);
            Route::put('profile', [UserController::class, 'updateProfile']);
            Route::delete('profile', [UserController::class, 'destroy']);
            Route::put('profile/change-password', [UserController::class, 'changePassword']);
        });

        /**
         * Users Management API - Admin Only
         */
        Route::middleware(['jwt.auth', 'role:admin'])->group(function () {
            Route::apiResource('users', UserController::class);
            Route::delete('bookings/{id}', [BookingController::class, 'destroy']);
        });

        /**
         * Dashboard API - Admin & Partner
         */
        Route::middleware(['jwt.auth', 'role:admin'])->prefix('dashboard')->group(function () {
            Route::get('/total-user', [DashboardController::class, 'getTotalUsers']);
            Route::get('/total-partner', [DashboardController::class, 'getTotalPartner']);
            Route::get('/system-building', [DashboardController::class, 'getSystemBuilding']);
            Route::get('/system-room', [DashboardController::class, 'getSystemRoom']);
            Route::get('/bookings-per-month', [DashboardController::class, 'bookingsPerMonth']);
            Route::get('/revenue-per-month', [DashboardController::class, 'revenuePerMonth']);
            Route::get('/buildings-bookings-count', [DashboardController::class, 'getAllBuildingsBookingsCount']);
        });
        /**
         * Provinces API - Public
         */
        Route::prefix('provinces')->group(function () {
            // get all provinces types
            Route::get('/types', [ProvincesController::class, 'getAllProvincesTypes']);
        });
        Route::prefix('provinces')->middleware(['jwt.auth', 'role:admin'])->group(function () {
            Route::get('{id}', [ProvincesController::class, 'show']);
            Route::get('/', [ProvincesController::class, 'index']);
        });

        /**
         * Wards API
         */
        Route::prefix('wards')->group(function () {
            // get wards by province id
            Route::get('/{provinceId}', [WardsController::class, 'getWardsByProvinceId'])->whereNumber('provinceId');
        });

        /**
         * Partner API - public
         */
        Route::prefix('partner')->middleware(['jwt.auth', 'role:admin,partner'])->group(function () {
            //list partner information and search
            Route::get('/search', [PartnerInforController::class, 'index']);
            //detail partner information
            Route::get('{id}', [PartnerInforController::class, 'show']);
            //update partner information
            Route::post('{id}', [PartnerInforController::class, 'update']);
        });

        /**
         * News API
         * Base Url /api/v1/admin/news/
         */
        Route::prefix('news')->middleware(['jwt.auth', 'role:admin,partner'])->group(function () {
            // get all news
            Route::get('', [NewsController::class, 'index']);
            // get news by id
            Route::get('{id}', [NewsController::class, 'show'])->whereNumber('id');
            // create news
            Route::post('', [NewsController::class, 'store']);
            // update news
            Route::put('{id}', [NewsController::class, 'update'])->whereNumber('id');
            // delete news
            Route::delete('{id}', [NewsController::class, 'destroy'])->whereNumber('id');
        });
    });

    /**
     * ============================================
     * COMMON API
     * ============================================
     * Base URL: /api/v1/common/
     */
    Route::prefix('common')->group(function () {
        Route::get('chatbot/start-question', [ChatbotController::class, 'startQuestion']);
        Route::get('chatbot/next-question/{id}', [ChatbotController::class, 'nextQuestion'])
            ->whereNumber('id');
    });

    // Home public APIs
    Route::prefix('home')->group(function () {
        Route::prefix('rooms')->group(function () {
            Route::get('/getLatest', [HomeController::class, 'getLatestRooms']);
            Route::get('/by-province', [HomeController::class, 'getRoomsByProvince']);
            Route::get('/filter', [HomeController::class, 'filterRooms']);
        });
        Route::get('/provinces', [HomeController::class, 'getProvinces']);
        Route::get('/partners/random', [HomeController::class, 'getRandomPartners']);
        Route::get('/news/latest', [HomeController::class, 'getLatestNews']);
    });

    // Rooms public APIs
    Route::prefix('rooms')->group(function () {
        Route::get('/search', [EURoomsController::class, 'roomList']);
        Route::get('{id}', [EURoomsController::class, 'publicRoomDetail'])->whereNumber('id');
    });

    // Bookings public APIs
    Route::prefix('bookings')->group(function () {
        Route::post('{roomId}/user-create', [BookingController::class, 'userCreateBooking'])->whereNumber('roomId');
    });
    Route::post('set-password/{token}', [AuthController::class, 'setPassword']);

    // Partner public APIs
    Route::prefix('partners')->group(function () {
        Route::get('/{provinceId}', [PartnerController::class, 'getPartnersByProvinceId'])->whereNumber('provinceId');
        Route::get('/detail/{id}', [PartnerController::class, 'partnerDetail'])->whereNumber('id');
    });

    // News publics APIs
    Route::prefix('news')->group(function () {
        Route::get('list-news', [NewsController::class, 'listNews']);
        Route::get('detail-news/{id}', [NewsController::class, 'detailNews']);
    });
});
