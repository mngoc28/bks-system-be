/**
 * @api {get} /api/v1/admin/dashboard/total-user Get Total Users
 * @apiName GetTotalUsers
 * @apiGroup Dashboard
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin role. Get total number of users.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiSampleRequest /api/v1/admin/dashboard/total-user
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
 * {
 *   "success": true,
 *   "message": "Stats fetched successfully",
 *   "data": {
 *     "overview": {
 *       "totalRooms": 150,
 *       "availableRooms": 100,
 *       "bookedRooms": 40,
 *       "maintenanceRooms": 10,
 *       "totalProperties": 5,
 *       "totalServices": 26,
 *       "totalUsers": 99,
 *       "totalStaff": 20
 *     },
 *     "bookings": {
 *       "total": 500,
 *       "pending": 50,
 *       "confirmed": 300,
 *       "completed": 120,
 *       "cancelled": 30
 *     },
 *     "metrics": {
 *       "averageRoomPrice": 500000.00,
 *       "occupancyRate": 26.67,
 *       "bookingSuccessRate": 84.00
 *     },
 *     "dateRange": {
 *       "startDate": "2025-01-01",
 *       "endDate": "2025-01-31"
 *     }
 *   }
 * }
 */

/**
 * @api {get} /api/v1/admin/dashboard/bookings-per-month Get Bookings Per Month
 * @apiName GetBookingsPerMonth
 * @apiGroup Dashboard
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin role. Get number of bookings grouped by month within a date range.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam {String} [start_date] Start date (format: Y-m-d, default: first day of current month)
 * @apiParam {String} [end_date] End date (format: Y-m-d, default: last day of current month)
 *
 * @apiSampleRequest /api/v1/admin/dashboard/bookings-per-month?start_date=2025-01-01&end_date=2025-12-31
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
 * {
 *   "success": true,
 *   "message": "Lấy số lượng đặt phòng theo tháng thành công",
 *   "data": {
 *     "bookingsPerMonth": [
 *       {
 *         "month": "2025-01",
 *         "total": 45
 *       },
 *       {
 *         "month": "2025-02",
 *         "total": 52
 *       },
 *       {
 *         "month": "2025-03",
 *         "total": 38
 *       }
 *     ],
 *     "dateRange": {
 *       "startDate": "2025-01-01",
 *       "endDate": "2025-12-31"
 *     }
 *   }
 * }
 */

/**
 * @api {get} /api/v1/admin/dashboard/revenue-per-month Get Revenue Per Month
 * @apiName GetRevenuePerMonth
 * @apiGroup Dashboard
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin role. Get revenue grouped by month within a date range. Only counts bookings with status 'confirmed' or 'completed'.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam {String} [start_date] Start date (format: Y-m-d, default: first day of current month)
 * @apiParam {String} [end_date] End date (format: Y-m-d, default: last day of current month)
 *
 * @apiSampleRequest /api/v1/admin/dashboard/revenue-per-month?start_date=2025-01-01&end_date=2025-12-31
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
 * {
 *   "success": true,
 *   "message": "Lấy doanh thu theo tháng thành công",
 *   "data": {
 *     "revenueByMonth": [
 *       {
 *         "month": "2025-01",
 *         "revenue": 150000000.00
 *       },
 *       {
 *         "month": "2025-02",
 *         "revenue": 180000000.00
 *       },
 *       {
 *         "month": "2025-03",
 *         "revenue": 165000000.00
 *       }
 *     ],
 *     "totalRevenue": 495000000.00,
 *     "dateRange": {
 *       "startDate": "2025-01-01",
 *       "endDate": "2025-12-31"
 *     }
 *   }
 * }
 */

/**
 * @api {get} /api/v1/admin/dashboard/properties-bookings-count Get Bookings Count by Property
 * @apiName GetAllPropertiesBookingsCount
 * @apiGroup Dashboard
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin role. Get total number of bookings grouped by property. Only counts bookings with status != 'cancelled'.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiSampleRequest /api/v1/admin/dashboard/properties-bookings-count
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
 * {
 *   "success": true,
 *   "message": "Lấy số lượng đặt phòng theo từng cơ sở thành công",
 *   "data": [
 *     {
 *       "property_id": 1,
 *       "property_name": "Cơ sở A",
 *       "total": 25
 *     },
 *     {
 *       "property_id": 2,
 *       "property_name": "Cơ sở B",
 *       "total": 18
 *     },
 *     {
 *       "property_id": 3,
 *       "property_name": "Cơ sở C",
 *       "total": 32
 *     }
 *   ]
 * }
 */

/**
 * @api {get} /api/v1/admin/dashboard/total-partner Get Total Partners
 * @apiName GetTotalPartner
 * @apiGroup Dashboard
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin role. Get total number of partners and related counters.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiSampleRequest /api/v1/admin/dashboard/total-partner
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
 * {
 *   "success": true,
 *   "message": "Stats fetched successfully",
 *   "data": {
 *     "totalPartners": 12,
 *     "newUPartnerThisMonth": 2,
 *     "partnerPending": 1,
 *     "partnerBlock": 0
 *   }
 * }
 */

/**
 * @api {get} /api/v1/admin/dashboard/system-property Get System Properties
 * @apiName GetSystemProperty
 * @apiGroup Dashboard
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin role. Get total number of properties in the system.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiSampleRequest /api/v1/admin/dashboard/system-property
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
 * {
 *   "success": true,
 *   "message": "Stats fetched successfully",
 *   "data": {
 *     "totalProperties": 42
 *   }
 * }
 */

/**
 * @api {get} /api/v1/admin/dashboard/system-room Get System Rooms
 * @apiName GetSystemRoom
 * @apiGroup Dashboard
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin role. Get total number of rooms in the system.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiSampleRequest /api/v1/admin/dashboard/system-room
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
 * {
 *   "success": true,
 *   "message": "Stats fetched successfully",
 *   "data": {
 *     "totalRooms": 150,
 *     "totalPrivateRooms": 20,
 *     "totalPublicRooms": 130,
 *     "totalAvailableRooms": 45
 *   }
 * }
 */
