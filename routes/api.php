<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BroadcastAuthController;
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
use App\Http\Controllers\PropertyTypeController;
use App\Http\Controllers\Partner\PartnerBookingController;
use App\Http\Controllers\Partner\PartnerBuildingController;
use App\Http\Controllers\Partner\PartnerRoomController;
use App\Http\Controllers\Partner\PartnerContractController;
use App\Http\Controllers\Partner\PartnerDashboardController;
use App\Http\Controllers\Partner\PartnerChatController;
use App\Http\Controllers\Partner\PartnerPriceRuleController;
use App\Http\Controllers\Partner\PartnerReportController;
use App\Http\Controllers\Partner\PartnerStayServiceController;
use App\Http\Controllers\Partner\PartnerRoomBlockController;
use App\Http\Controllers\Partner\PartnerCalendarController;
use App\Http\Controllers\Stay\StayController;
use App\Http\Controllers\Stay\StayContractController;
use App\Http\Controllers\Stay\StayServiceController;
use App\Http\Controllers\NotificationController;
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
     * Broadcast channel authorization (Pusher protocol).
     * FE Echo client gửi POST /api/v1/broadcasting/auth kèm Authorization: Bearer <jwt>.
     * Trả về Pusher signed payload để client subscribe private/presence channel.
     */
    Route::middleware(['jwt.auth'])->post('broadcasting/auth', [BroadcastAuthController::class, 'authenticate']);

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
         * Base Url /api/v1/admin/auth/
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
         * Base Url /api/v1/admin/auth/
         */
        Route::middleware('jwt.auth')->prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
        });

        /**
         * Users API
         * Base Url /api/v1/admin/users/
         */
        Route::middleware(['jwt.auth', 'role:admin'])->prefix('users')->group(function () {
            Route::post('create', [UserController::class, 'store']);
            Route::get('{id}', [UserController::class, 'show']);
            Route::put('{id}', [UserController::class, 'update']);
            Route::post('/reset-password/{id}', [UserController::class, 'resetPassword']);
            Route::delete('{id}', [UserController::class, 'destroy']);
            Route::get('/', [UserController::class, 'index']);
            Route::post('/avatar/{id}', [UserController::class, 'uploadAvatar']);
        });

        /**
         * Services API
         * Base Url /api/v1/admin/services/
         */
        Route::middleware(['jwt.auth', 'role:admin'])->prefix('services')->group(function () {
            Route::get('/all', [ServiceController::class, 'getAllServices']);
            Route::post('', [ServiceController::class, 'store']);
            Route::put('{id}', [ServiceController::class, 'update'])->whereNumber('id');
            Route::delete('{id}', [ServiceController::class, 'destroy'])->whereNumber('id');
            Route::get('/search', [ServiceController::class, 'index']);
            Route::get('{id}', [ServiceController::class, 'show'])->whereNumber('id');
        });

        /**
         * Buildings API
         * Base Url /api/v1/admin/buildings/
         */
        Route::prefix("buildings")->group(function () {
            Route::middleware(["jwt.auth", "role:admin"])->group(function () {
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
         * Cloudinary API
         * Base Url /api/v1/admin/cloudinary/
         */
        Route::middleware(['jwt.auth', 'role:admin'])->prefix('cloudinary')->group(function () {
            Route::post('/upload-image', [CloudinaryController::class, 'uploadImage']);
            Route::post('/upload-multiple-images', [CloudinaryController::class, 'uploadMultipleImages']);
            Route::delete('/delete-image', [CloudinaryController::class, 'deleteImage']);
        });

        /**
         * Building Images API
         * Base Url /api/v1/admin/building-images/
         */
        Route::middleware(['jwt.auth', 'role:admin'])->prefix('building-images')->group(function () {
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
         * Room Images API
         * Base Url /api/v1/admin/room-images/
         */
        Route::middleware(['jwt.auth', 'role:admin'])->prefix('room-images')->group(function () {
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
         * Base Url /api/v1/admin/rooms/
         */
        Route::prefix('rooms')->group(function () {
            Route::get('/search', [RoomsController::class, 'index']);
            Route::get('{id}', [RoomsController::class, 'show']);
            Route::get('building/{buildingId}', [RoomsController::class, 'getRoomNamesByBuildingId']);
        });

        /**
         * Rooms API - Protected
         * Base Url /api/v1/admin/rooms/
         */
        Route::middleware(['jwt.auth', 'role:admin'])->prefix('rooms')->group(function () {
            Route::post('store', [RoomsController::class, 'store']);
            Route::put('{id}', [RoomsController::class, 'update']);
            Route::delete('{id}', [RoomsController::class, 'destroy']);
        });

        /**
         * Amenity API
         * Base Url /api/v1/admin/amenities/
         */
        Route::middleware(['jwt.auth'])->prefix('amenities')->group(function () {
            Route::get('/', [AmenityController::class, 'index']);
            Route::get('/all', [AmenityController::class, 'getAllAmenities']);
            Route::get('{id}', [AmenityController::class, 'show']);
            Route::post('store', [AmenityController::class, 'store']);
            Route::put('{id}', [AmenityController::class, 'update']);
            Route::delete('{id}', [AmenityController::class, 'destroy']);
        });

        /**
         * Coupon API
         * Base Url /api/v1/admin/coupons/
         */
        Route::middleware(['jwt.auth'])->prefix('coupons')->group(function () {
            Route::get('/', [CouponController::class, 'index']);
            Route::post('create', [CouponController::class, 'store']);
            Route::put('update/{id}', [CouponController::class, 'update']);
            Route::delete('delete/{id}', [CouponController::class, 'destroy']);
        });

        /**
         * Room Maintenance API
         * Base Url /api/v1/admin/room-maintenances/
         */
        Route::middleware(['jwt.auth'])->prefix('room-maintenances')->group(function () {
            Route::get('/', [RoomMaintenanceController::class, 'index']);
            Route::post('/', [RoomMaintenanceController::class, 'store']);
        });

        /**
         * Chatbot API
         * Base Url /api/v1/admin/chatbot/
         */
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

        /**
         * Reports API
         * Base Url /api/v1/admin/reports/
         */
        Route::middleware(['jwt.auth'])->prefix('reports')->group(function () {
            Route::post('/', [UserReportController::class, 'store']);
        });

        /**
         * Property Type API
         * Base Url /api/v1/admin/property-types/
         */
        Route::middleware(['jwt.auth'])->prefix('property-types')->group(function () {
            Route::post('/', [PropertyTypeController::class, 'store']);
            Route::get('/', [PropertyTypeController::class, 'index']);
            Route::get('{id}', [PropertyTypeController::class, 'show'])->whereNumber('id');
            Route::put('{id}', [PropertyTypeController::class, 'update'])->whereNumber('id');
            Route::patch('{id}/status', [PropertyTypeController::class, 'updateStatus'])->whereNumber('id');
        });

        /**
         * Price package API
         * Base Url /api/v1/admin/price-packages/
         */
        Route::middleware(['jwt.auth', 'role:admin'])->prefix('price-packages')->group(function () {
            Route::get('/', [PricePackageController::class, 'index']);
            Route::get('room/{roomId}', [PricePackageController::class, 'getByRoomId']);
        });

        /**
         * Bookings API - Protected
         * Base Url /api/v1/admin/bookings/
         */
        Route::middleware(['jwt.auth', 'role:admin'])->prefix('bookings')->group(function () {
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
         * Base Url /api/v1/admin/user/
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
         * Base Url /api/v1/admin/
         */
        Route::middleware(['jwt.auth', 'role:admin'])->group(function () {
            Route::apiResource('users', UserController::class);
            Route::delete('bookings/{id}', [BookingController::class, 'destroy']);
        });

        /**
         * Dashboard API
         * Base Url /api/v1/admin/dashboard/
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
         * Base Url /api/v1/admin/provinces/
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
         * Base Url /api/v1/admin/wards/
         */
        Route::prefix('wards')->group(function () {
            // get wards by province id
            Route::get('/{provinceId}', [WardsController::class, 'getWardsByProvinceId'])->whereNumber('provinceId');
        });

        /**
         * Partner API
         * Base Url /api/v1/admin/partner/
         */
        Route::prefix('partner')->middleware(['jwt.auth', 'role:admin'])->group(function () {
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
        Route::prefix('news')->middleware(['jwt.auth', 'role:admin'])->group(function () {
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
     * PARTNER API
     * ============================================
     * Base URL: /api/v1/partner/
     */

    /**
     * Auth API - Public
     * Base Url /api/v1/partner/auth/
     */
    Route::prefix('partner/auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
        Route::post('send-mail-reset-password', [AuthController::class, 'sendMailResetPassword']);
        Route::post('reset-password/{token}', [AuthController::class, 'setPassword']);
    });

    Route::middleware(['jwt.auth', 'role:partner'])->prefix('partner')->group(function () {
        /**
         * Auth API - Authenticated
         * Base Url /api/v1/partner/auth/
         */
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
        });

        /**
         * User Profile API
         * Base Url /api/v1/partner/user/
         */
        Route::prefix('user')->group(function () {
            Route::get('profile', [UserController::class, 'show']);
            Route::put('profile', [UserController::class, 'updateProfile']);
            Route::put('profile/change-password', [UserController::class, 'changePassword']);
            Route::post('avatar/{id}', [UserController::class, 'uploadAvatar']);
        });

        /**
         * Partner Profile Info
         * Base Url /api/v1/partner/
         */
        Route::get('profile', [PartnerInforController::class, 'showSelf']);
        // update partner information
        Route::put('profile', [PartnerInforController::class, 'updateSelf']);
        // detail partner information
        Route::get('detail/{id}', [PartnerInforController::class, 'show']);

        /**
         * Services API
         * Base Url /api/v1/partner/services/
         */
        Route::prefix('services')->group(function () {
            Route::get('/all', [ServiceController::class, 'getAllServices']);
            Route::get('/search', [ServiceController::class, 'index']);
            Route::get('{id}', [ServiceController::class, 'show'])->whereNumber('id');
            Route::post('', [ServiceController::class, 'store']);
            Route::put('{id}', [ServiceController::class, 'update'])->whereNumber('id');
            Route::delete('{id}', [ServiceController::class, 'destroy'])->whereNumber('id');
        });

        /**
         * Provinces API (for partner building form)
         * Base Url /api/v1/partner/provinces/
         */
        Route::prefix('provinces')->group(function () {
            Route::get('all', [ProvincesController::class, 'getAllProvincesTypes']);
        });

        /**
         * Wards API (for partner building form)
         * Base Url /api/v1/partner/wards/
         */
        Route::prefix('wards')->group(function () {
            Route::get('{provinceId}', [WardsController::class, 'getWardsByProvinceId'])->whereNumber('provinceId');
        });

        /**
         * Buildings API
         * Base Url /api/v1/partner/buildings/
         */
        Route::prefix("buildings")->group(function () {
            Route::get("searchAll", [PartnerBuildingController::class, "index"]);
            Route::get("types", [BuildingsController::class, "getAllBuildingsTypes"]);
            Route::get("bookings/{id}", [BuildingsController::class, "getBookingsByBuilding"])->whereNumber("id");
            Route::get("{id}", [PartnerBuildingController::class, "show"])->whereNumber("id");
            Route::post("", [PartnerBuildingController::class, "store"]);
            Route::put("{id}", [PartnerBuildingController::class, "update"])->whereNumber("id");
            Route::delete("{id}", [PartnerBuildingController::class, "destroy"])->whereNumber("id");
            Route::get("all", [PartnerBuildingController::class, "getBuildingNames"]);
        });

        /**
         * Rooms API
         * Base Url /api/v1/partner/rooms/
         */
        Route::prefix('rooms')->group(function () {
            Route::get('/price-packages', [PartnerRoomController::class, 'getPricePackages']);
            Route::get('/occupancy', [PartnerRoomController::class, 'occupancy']);
            Route::get('/search', [PartnerRoomController::class, 'index']);
            Route::get('{id}', [PartnerRoomController::class, 'show']);
            Route::get('building/{buildingId}', [RoomsController::class, 'getRoomNamesByBuildingId']);
            Route::post('/', [PartnerRoomController::class, 'store']);
            Route::post('bulk-store', [PartnerRoomController::class, 'bulkStore']);
            Route::post('bulk-update-status', [PartnerRoomController::class, 'bulkUpdateStatus']);
            Route::post('bulk-delete', [PartnerRoomController::class, 'bulkDelete']);
            Route::put('{id}', [PartnerRoomController::class, 'update']);
            Route::delete('{id}', [RoomsController::class, 'destroy']);
        });

        /**
         * Cloudinary API
         * Base Url /api/v1/partner/cloudinary/
         */
        Route::prefix('cloudinary')->group(function () {
            Route::post('/upload-image', [CloudinaryController::class, 'uploadImage']);
            Route::post('/upload-multiple-images', [CloudinaryController::class, 'uploadMultipleImages']);
            Route::delete('/delete-image', [CloudinaryController::class, 'deleteImage']);
        });

        /**
         * Building Images API
         * Base Url /api/v1/partner/building-images/
         */
        Route::prefix('building-images')->group(function () {
            Route::get('building/{id}', [PartnerBuildingController::class, 'getImages']);
            Route::post('/{id}', [PartnerBuildingController::class, 'storeImages']);
            Route::delete('/{id}/{imageId}', [PartnerBuildingController::class, 'deleteImage']);
            Route::put('/sort/{buildingId}', [BuildingImageController::class, 'sort'])->whereNumber('buildingId');
        });

        /**
         * Room Images API
         * Base Url /api/v1/partner/room-images/
         */
        Route::prefix('room-images')->group(function () {
            Route::get('room/{id}', [PartnerRoomController::class, 'getImages']);
            Route::post('/{id}', [PartnerRoomController::class, 'storeImages']);
            Route::delete('/{id}/{imageId}', [PartnerRoomController::class, 'deleteImage']);
            Route::put('{roomId}/update-sort/{imageIdA}/{imageIdB}', [RoomImageController::class, 'updateSort'])
                ->whereNumber('roomId');
            Route::delete('/', [RoomImageController::class, 'destroy']);
        });

        /**
         * Amenities API - List only
         * Base Url /api/v1/partner/amenities/
         */
        Route::prefix('amenities')->group(function () {
            Route::get('/', [AmenityController::class, 'index']);
            Route::get('/all', [AmenityController::class, 'getAllAmenities']);
            Route::get('{id}', [AmenityController::class, 'show']);
        });

        /**
         * Price package API
         * Base Url /api/v1/partner/price-packages/
         */
        Route::prefix('price-packages')->group(function () {
            Route::get('/', [PricePackageController::class, 'index']);
            Route::get('room/{roomId}', [PricePackageController::class, 'getByRoomId']);
        });

        /**
         * Room Blocks API (Partner Portal 360 Phase 3)
         * Base Url /api/v1/partner/room-blocks/
         */
        Route::prefix('room-blocks')->middleware('partner360')->group(function () {
            Route::get('/', [PartnerRoomBlockController::class, 'index']);
            Route::post('/', [PartnerRoomBlockController::class, 'store']);
            Route::delete('{id}', [PartnerRoomBlockController::class, 'destroy'])->whereNumber('id');
        });

        /**
         * Calendar API (Partner Portal 360 Phase 3)
         * Base Url /api/v1/partner/calendar
         */
        Route::middleware('partner360')->get('calendar', [PartnerCalendarController::class, 'index']);

        /**
         * Bookings API
         * Base Url /api/v1/partner/bookings/
         */
        Route::prefix('bookings')->group(function () {
            Route::get('/', [PartnerBookingController::class, 'index']);
            Route::post('/', [BookingController::class, 'store']);
            // Phase 3/4 (T3.13, T4.4): gắn feature flag partner360
            Route::middleware('partner360')->group(function () {
                Route::post('bulk-confirm', [PartnerBookingController::class, 'bulkConfirm']);
                Route::post('bulk-cancel', [PartnerBookingController::class, 'bulkCancel']);
                Route::put('{id}/move', [PartnerBookingController::class, 'move'])->whereNumber('id');
            });
            Route::get('{id}', [BookingController::class, 'show'])->whereNumber('id');
            Route::put('{id}', [BookingController::class, 'update'])->whereNumber('id');
            Route::put('{id}/cancel', [PartnerBookingController::class, 'cancel'])->whereNumber('id');
            Route::put('{id}/confirm', [PartnerBookingController::class, 'confirm'])->whereNumber('id');
            Route::put('{id}/no-show', [PartnerBookingController::class, 'noShow'])->whereNumber('id');
            Route::put('{id}/check-in', [PartnerBookingController::class, 'checkIn'])->whereNumber('id');
            Route::put('{id}/check-out', [PartnerBookingController::class, 'checkOut'])->whereNumber('id');
        });

        /**
         * Chat API
         * Base Url /api/v1/partner/chat/
         */
        Route::prefix('chat')->group(function () {
            Route::get('/', [PartnerChatController::class, 'index']);
            Route::get('{id}', [PartnerChatController::class, 'show'])->whereNumber('id');
            Route::post('/', [PartnerChatController::class, 'store']);
        });

        /**
         * Price Rules API
         * Base Url /api/v1/partner/price-rules/
         */
        Route::prefix('price-rules')->group(function () {
            Route::get('/', [PartnerPriceRuleController::class, 'index']);
            Route::post('/', [PartnerPriceRuleController::class, 'store']);
            Route::put('{id}', [PartnerPriceRuleController::class, 'update'])->whereNumber('id');
            Route::delete('{id}', [PartnerPriceRuleController::class, 'destroy'])->whereNumber('id');
        });

        /**
         * Reports API
         * Base Url /api/v1/partner/reports/
         */
        Route::prefix('reports')->group(function () {
            Route::get('kpis', [PartnerReportController::class, 'getKPIs']);
        });

        /**
         * Room Maintenances API
         * Base Url /api/v1/partner/room-maintenances/
         */
        Route::prefix('room-maintenances')->group(function () {
            Route::get('/', [RoomMaintenanceController::class, 'index']);
            Route::post('/', [RoomMaintenanceController::class, 'store']);
        });

        Route::prefix('stay-services')->group(function () {
            Route::get('/', [PartnerStayServiceController::class, 'index']);
            Route::patch('/{id}', [PartnerStayServiceController::class, 'update']);
        });

        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::put('{id}/read', [NotificationController::class, 'markAsRead'])->whereNumber('id');
            Route::put('read-all', [NotificationController::class, 'markAllAsRead']);
            Route::delete('{id}', [NotificationController::class, 'destroy'])->whereNumber('id');
        });

        /**
         * Coupons API
         * Base Url /api/v1/partner/coupons/
         */
        Route::prefix('coupons')->group(function () {
            Route::get('/', [CouponController::class, 'index']);
            Route::post('create', [CouponController::class, 'store']);
            Route::put('update/{id}', [CouponController::class, 'update']);
            Route::delete('delete/{id}', [CouponController::class, 'destroy']);
        });

        /**
         * Dashboard API
         * Base Url /api/v1/partner/dashboard/
         */
        Route::prefix('dashboard')->group(function () {
            Route::get('/system-building', [PartnerDashboardController::class, 'getSystemBuilding']);
            Route::get('/system-room', [PartnerDashboardController::class, 'getSystemRoom']);
            Route::get('/bookings-per-month', [PartnerDashboardController::class, 'bookingsPerMonth']);
            Route::get('/revenue-per-month', [PartnerDashboardController::class, 'revenuePerMonth']);
            Route::get('/buildings-bookings-count', [
                PartnerDashboardController::class,
                'getAllBuildingsBookingsCount'
            ]);
            Route::get('/stats', [PartnerDashboardController::class, 'getStats']);
            Route::get('/kpis', [PartnerDashboardController::class, 'getKpis']);
            // Phase 4/5 (T4.1, T4.2, T5.6): chart endpoints gắn feature flag.
            Route::middleware('partner360')->group(function () {
                Route::get('/charts/occupancy', [PartnerDashboardController::class, 'getOccupancyChart']);
                Route::get('/charts/gmv', [PartnerDashboardController::class, 'getGmvChart']);
            });
            Route::get('/pending-bookings', [PartnerDashboardController::class, 'getPendingBookings']);
            Route::get('/urgent-maintenances', [PartnerDashboardController::class, 'getUrgentMaintenances']);
            Route::get('/revenue-analytics', [PartnerDashboardController::class, 'getRevenueAnalytics']);
        });

        /**
         * News API
         * Base Url /api/v1/partner/news/
         */
        Route::prefix('news')->group(function () {
            Route::get('', [NewsController::class, 'index']);
            Route::get('{id}', [NewsController::class, 'show'])->whereNumber('id');
            Route::post('', [NewsController::class, 'store']);
            Route::put('{id}', [NewsController::class, 'update'])->whereNumber('id');
            Route::delete('{id}', [NewsController::class, 'destroy'])->whereNumber('id');
        });

        /**
         * Contracts API
         * Base Url /api/v1/partner/contracts/
         */
        Route::prefix('contracts')->group(function () {
            Route::get('/', [PartnerContractController::class, 'index']);
            Route::post('/', [PartnerContractController::class, 'store']);
            // Phase 5 (T5.1, T5.5): renewal + termination + alert listing.
            Route::middleware('partner360')->group(function () {
                Route::get('expiring-soon', [PartnerContractController::class, 'expiringSoon']);
                Route::put('{id}/renewal-reminder', [PartnerContractController::class, 'setRenewalReminder'])
                    ->whereNumber('id');
                Route::post('{id}/terminate', [PartnerContractController::class, 'terminate'])
                    ->whereNumber('id');
            });
            Route::get('{id}', [PartnerContractController::class, 'show'])->whereNumber('id');
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
        Route::get('/building-types', [BuildingsController::class, 'getAllBuildingsTypes']);
        Route::get('/wards/{provinceId}', [WardsController::class, 'getWardsByProvinceId'])->whereNumber('provinceId');
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

    /**
     * ============================================
     * STAY PORTAL API
     * ============================================
     * Base URL: /api/v1/stay/
     */
    Route::middleware(['jwt.auth'])->prefix('stay')->group(function () {
        Route::get('dashboard', [StayController::class, 'getDashboard']);
        Route::get('bookings', [StayController::class, 'getBookings']);
        Route::get('bookings/{id}', [StayController::class, 'show']);
        Route::post('bookings/{id}/extend', [StayController::class, 'extend']);

        Route::prefix('contracts')->group(function () {
            Route::get('/', [StayContractController::class, 'index']);
            Route::get('{id}', [StayContractController::class, 'show'])->whereNumber('id');
            Route::put('{id}/sign', [StayContractController::class, 'sign'])->whereNumber('id');
        });

        Route::prefix('services')->group(function () {
            Route::get('{bookingId}', [StayServiceController::class, 'index'])->whereNumber('bookingId');
            Route::post('{bookingId}', [StayServiceController::class, 'order'])->whereNumber('bookingId');
        });

        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::put('{id}/read', [NotificationController::class, 'markAsRead'])->whereNumber('id');
            Route::put('read-all', [NotificationController::class, 'markAllAsRead']);
            Route::delete('{id}', [NotificationController::class, 'destroy'])->whereNumber('id');
        });
    });
});
