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
 *       "totalBuildings": 5,
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
 * @api {get} /api/v1/admin/dashboard/buildings-bookings-count Get Bookings Count by Building
 * @apiName GetAllBuildingsBookingsCount
 * @apiGroup Dashboard
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin role. Get total number of bookings grouped by building. Only counts bookings with status != 'cancelled'.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiSampleRequest /api/v1/admin/dashboard/buildings-bookings-count
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
 * {
 *   "success": true,
 *   "message": "Lấy số lượng đặt phòng của tất cả các tòa nhà thành công",
 *   "data": [
 *     {
 *       "building_id": 1,
 *       "building_name": "Tòa nhà A",
 *       "total": 25
 *     },
 *     {
 *       "building_id": 2,
 *       "building_name": "Tòa nhà B",
 *       "total": 18
 *     },
 *     {
 *       "building_id": 3,
 *       "building_name": "Tòa nhà C",
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
 * @apiDescription Protected endpoint - Requires authentication and admin role. Get total number of partners.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiSampleRequest /api/v1/admin/dashboard/total-partner
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
 * {
 *   "success": true,
 *   "message": "Lấy danh sách phòng theo trạng thái thành công",
 *   "data": [
 *     {
 *       "status": "available",
 *       "total": 45
 *     },
 *     {
 *       "status": "booked",
 *       "total": 30
 *     },
 *     {
 *       "status": "maintenance",
 *       "total": 5
 *     }
 *   ]
 * }
 */

/**
 * @api {get} /api/v1/admin/dashboard/system-building Get System Buildings
 * @apiName GetSystemBuilding
 * @apiGroup Dashboard
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin role. Get total number of buildings in the system.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiSampleRequest /api/v1/admin/dashboard/system-building
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
 * {
 *   "success": true,
 *   "message": "Lấy danh sách phòng theo tòa nhà thành công",
 *   "data": [
 *     {
 *       "building_id": 1,
 *       "building_name": "Tòa nhà A",
 *       "total": 50
 *     },
 *     {
 *       "building_id": 2,
 *       "building_name": "Tòa nhà B",
 *       "total": 35
 *     },
 *     {
 *       "building_id": 3,
 *       "building_name": "Tòa nhà C",
 *       "total": 42
 *     }
 *   ]
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
 *   "message": "Lấy danh sách đặt phòng gần đây thành công",
 *   "data": {
 *     "recentBookings": [
 *       {
 *         "id": 150,
 *         "user_name": "Nguyễn Văn A",
 *         "room_number": "101",
 *         "start_date": "2025-01-20",
 *         "end_date": "2025-01-25",
 *         "status": "confirmed",
 *         "created_at": "2025-01-15T10:30:00.000000Z"
 *       },
 *       {
 *         "id": 149,
 *         "user_name": "Trần Thị B",
 *         "room_number": "205",
 *         "start_date": "2025-01-18",
 *         "end_date": "2025-01-22",
 *         "status": "pending",
 *         "created_at": "2025-01-14T15:20:00.000000Z"
 *       }
 *     ],
 *     "limit": 20
 *   }
 * }
 */
