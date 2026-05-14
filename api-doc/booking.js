/**
 * Property image admin APIs are documented in property-image.js (avoid duplicate apidoc entries).
 */

/**
 * @api {post} /api/v1/bookings/:roomId/user-create User Create Booking
 * @apiName UserCreateBooking
 * @apiGroup Bookings
 * @apiVersion 1.0.0
 *
 * @apiDescription Public endpoint - No authentication required. Allows users to create a booking for a specific room. An email confirmation will be sent after successful booking creation.
 *
 * @apiParam (Path) {Number} roomId Room ID (required, must exist in rooms table)
 *
 * @apiParam (Body) {String} name Customer full name (required, max 255 characters)
 * @apiParam (Body) {String} email Customer email address (required, must be valid email format, max 255 characters)
 * @apiParam (Body) {String} phone Customer phone number (required, max 20 characters)
 * @apiParam (Body) {String} start_date Booking start date (required, must be a valid date in YYYY-MM-DD format)
 * @apiParam (Body) {String} end_date Booking end date (required, must be a valid date in YYYY-MM-DD format, must be after start_date)
 * @apiParam (Body) {Array} [service_ids] Array of service IDs to attach to the booking (optional, array of integers)
 * @apiParam (Body) {String} [note] Additional notes (optional, string)
 *
 * @apiSampleRequest /api/v1/bookings/100/user-create
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Object} data Booking data
 * @apiSuccess {Number} data.id Booking ID
 * @apiSuccess {String} data.booking_code Unique booking code
 * @apiSuccess {Number} data.room_id Room ID
 * @apiSuccess {Number} data.user_id User ID (null for guest bookings)
 * @apiSuccess {String} data.start_date Booking start date
 * @apiSuccess {String} data.end_date Booking end date
 * @apiSuccess {Number} data.total_amount Total booking amount
 * @apiSuccess {Number} data.status Booking status (0: pending, 1: confirmed, 2: cancelled, 3: completed)
 * @apiSuccess {String} data.note Additional notes
 * @apiSuccess {String} data.created_at Creation timestamp
 * @apiSuccess {String} data.updated_at Update timestamp
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Đặt phòng thành công! Vui lòng kiểm tra email để xem chi tiết.",
 *   "data": {
 *     "id": 123,
 *     "booking_code": "BK20251229001",
 *     "room_id": 100,
 *     "user_id": null,
 *     "start_date": "2026-01-01",
 *     "end_date": "2026-01-30",
 *     "total_amount": 15000000,
 *     "status": 0,
 *     "note": "Khách vãng lai đặt phòng",
 *     "created_at": "2025-12-29T10:00:00.000000Z",
 *     "updated_at": "2025-12-29T10:00:00.000000Z"
 *   }
 * }
 *
 * @apiError (422) {String} success Response status (false)
 * @apiError (422) {Object} message Validation errors
 *
 * @apiErrorExample {json} Error-Response (Validation Error):
 * HTTP/1.1 422 Unprocessable Entity
 * {
 *   "success": false,
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "name": [
 *       "Tên là bắt buộc."
 *     ],
 *     "email": [
 *       "Vui lòng nhập email.",
 *       "Email không đúng định dạng."
 *     ],
 *     "phone": [
 *       "Số điện thoại là bắt buộc."
 *     ],
 *     "start_date": [
 *       "Vui lòng nhập ngày bắt đầu."
 *     ],
 *     "end_date": [
 *       "Vui lòng nhập ngày kết thúc.",
 *       "Ngày kết thúc phải sau ngày bắt đầu."
 *     ],
 *     "price_id": [
 *       "Vui lòng chọn gói giá.",
 *       "Gói giá không tồn tại."
 *     ]
 *   },
 *   "data": null
 * }
 *
 * @apiError (404) {String} success Response status (false)
 * @apiError (404) {String} message Room not found error
 *
 * @apiErrorExample {json} Error-Response (Room Not Found):
 * HTTP/1.1 404 Not Found
 * {
 *   "success": false,
 *   "message": "Phòng không tồn tại.",
 *   "data": null
 * }
 *
 * @apiError (500) {String} success Response status (false)
 * @apiError (500) {String} message Internal server error
 *
 * @apiErrorExample {json} Error-Response (Internal Server Error):
 * HTTP/1.1 500 Internal Server Error
 * {
 *   "success": true,
 *   "message": "Đã xảy ra lỗi không mong muốn. Vui lòng thử lại.",
 *   "data": null
 * }
 */

/**
 * @api {get} /api/v1/admin/bookings Get All Bookings (Admin)
 * @apiName GetAllBookings
 * @apiGroup Bookings
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Returns paginated list of all bookings with optional filters.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam (Query) {Number} [property_id] Filter by property ID (optional)
 * @apiParam (Query) {Number} [room_id] Filter by room ID (optional)
 * @apiParam (Query) {Number} [user_id] Filter by user ID (optional)
 * @apiParam (Query) {String} [start_date] Filter bookings from this date (YYYY-MM-DD format, optional)
 * @apiParam (Query) {String} [end_date] Filter bookings to this date (YYYY-MM-DD format, optional)
 * @apiParam (Query) {Number} [status] Filter by booking status (0: pending, 1: confirmed, 2: cancelled, 3: completed, optional)
 * @apiParam (Query) {Number} [page] Page number for pagination (default: 1, optional)
 * @apiParam (Query) {Number} [per_page] Number of items per page (default: 15, optional)
 *
 * @apiSampleRequest /api/v1/admin/bookings?property_id=30&status=1&page=1&per_page=10
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Object} data Paginated bookings data
 * @apiSuccess {Number} data.current_page Current page number
 * @apiSuccess {Array} data.data Array of booking rows (joined list view)
 * @apiSuccess {Number} data.data[].id Booking ID
 * @apiSuccess {String} data.data[].user_name Guest / booker display name
 * @apiSuccess {String} data.data[].user_phone Booker's phone
 * @apiSuccess {String} data.data[].room_name Room label (e.g. room number)
 * @apiSuccess {Number} data.data[].room_id Room ID
 * @apiSuccess {String} data.data[].property_name Property name
 * @apiSuccess {Number} data.data[].property_id Property ID
 * @apiSuccess {String} data.data[].start_date Booking start date
 * @apiSuccess {String} data.data[].end_date Booking end date
 * @apiSuccess {Number} data.data[].price Room price (from selected price row)
 * @apiSuccess {Number} data.data[].booking_status Booking status code
 * @apiSuccess {String} data.data[].note Additional notes
 * @apiSuccess {String} data.data[].partner_name Partner (property owner) name
 * @apiSuccess {String} data.data[].created_at Creation timestamp
 * @apiSuccess {Number} data.from First item number on current page
 * @apiSuccess {Number} data.last_page Last page number
 * @apiSuccess {String} data.next_page_url Next page URL
 * @apiSuccess {String} data.prev_page_url Previous page URL
 * @apiSuccess {Number} data.per_page Items per page
 * @apiSuccess {Number} data.to Last item number on current page
 * @apiSuccess {Number} data.total Total number of bookings
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Bookings retrieved successfully",
 *   "data": {
 *     "current_page": 1,
 *     "data": [
 *       {
 *         "id": 1,
 *         "user_name": "Nguyễn Văn A",
 *         "user_phone": "0901234567",
 *         "room_name": "101",
 *         "room_id": 21,
 *         "property_name": "BKS Property",
 *         "property_id": 30,
 *         "start_date": "2026-01-04T00:00:00.000000Z",
 *         "end_date": "2026-01-05T00:00:00.000000Z",
 *         "price": 500000,
 *         "booking_status": 1,
 *         "note": "Booked phòng để đi date",
 *         "partner_name": "Partner One",
 *         "created_at": "2025-12-29T10:00:00.000000Z"
 *       }
 *     ],
 *     "from": 1,
 *     "last_page": 1,
 *     "next_page_url": null,
 *     "per_page": 10,
 *     "prev_page_url": null,
 *     "to": 1,
 *     "total": 1
 *   }
 * }
 *
 * @apiError (401) {String} success Response status (false)
 * @apiError (401) {String} message Unauthorized error
 *
 * @apiErrorExample {json} Error-Response (Unauthorized):
 * HTTP/1.1 401 Unauthorized
 * {
 *   "success": false,
 *   "message": "Bạn không có quyền truy cập.",
 *   "data": null
 * }
 */

/**
 * @api {get} /api/v1/admin/bookings/:id Get Booking by ID (Admin)
 * @apiName GetBookingById
 * @apiGroup Bookings
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Returns detailed information of a specific booking.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam (Path) {Number} id Booking ID (required, must exist in bookings table)
 *
 * @apiSampleRequest /api/v1/admin/bookings/1
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Object} data Booking data
 * @apiSuccess {Number} data.id Booking ID
 * @apiSuccess {String} data.booking_code Unique booking code
 * @apiSuccess {Number} data.room_id Room ID
 * @apiSuccess {Number} data.user_id User ID
 * @apiSuccess {String} data.start_date Booking start date
 * @apiSuccess {String} data.end_date Booking end date
 * @apiSuccess {Number} data.total_amount Total booking amount
 * @apiSuccess {Number} data.status Booking status
 * @apiSuccess {String} data.note Additional notes
 * @apiSuccess {String} data.created_at Creation timestamp
 * @apiSuccess {String} data.updated_at Update timestamp
 * @apiSuccess {Object} data.room Room information
 * @apiSuccess {Object} data.user User information (if applicable)
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Booking retrieved successfully",
 *   "data": {
 *     "id": 1,
 *     "booking_code": "BK20251229001",
 *     "room_id": 21,
 *     "user_id": 101,
 *     "start_date": "2026-01-04",
 *     "end_date": "2026-01-05",
 *     "total_amount": 500000,
 *     "status": 1,
 *     "note": "Booked phòng để đi date",
 *     "created_at": "2025-12-29T10:00:00.000000Z",
 *     "updated_at": "2025-12-29T10:00:00.000000Z",
 *     "room": {
 *       "id": 21,
 *       "title": "Phòng Deluxe 101",
 *       "property_name": "BKS Property"
 *     },
 *     "user": {
 *       "id": 101,
 *       "name": "Nguyễn Văn A",
 *       "email": "nguyenvana@example.com"
 *     }
 *   }
 * }
 *
 * @apiError (401) {String} success Response status (false)
 * @apiError (401) {String} message Unauthorized error
 *
 * @apiError (404) {String} success Response status (false)
 * @apiError (404) {String} message Booking not found error
 *
 * @apiErrorExample {json} Error-Response (Not Found):
 * HTTP/1.1 404 Not Found
 * {
 *   "success": false,
 *   "message": "Booking not found.",
 *   "data": null
 * }
 */

/**
 * @api {post} /api/v1/admin/bookings Create Booking (Admin)
 * @apiName CreateBooking
 * @apiGroup Bookings
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Creates a new booking for an existing user.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam (Body) {Number} user_id User ID (required, must exist in users table)
 * @apiParam (Body) {Number} room_id Room ID (required, must exist in rooms table)
 * @apiParam (Body) {String} start_date Booking start date (required, must be a valid date in YYYY-MM-DD format)
 * @apiParam (Body) {String} end_date Booking end date (required, must be a valid date in YYYY-MM-DD format, must be after start_date)
 * @apiParam (Body) {String} [note] Additional notes (optional, string)
 *
 * @apiSampleRequest /api/v1/admin/bookings
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Object} data Booking data
 * @apiSuccess {Number} data.id Booking ID
 * @apiSuccess {String} data.booking_code Unique booking code
 * @apiSuccess {Number} data.room_id Room ID
 * @apiSuccess {Number} data.user_id User ID
 * @apiSuccess {String} data.start_date Booking start date
 * @apiSuccess {String} data.end_date Booking end date
 * @apiSuccess {Number} data.total_amount Total booking amount
 * @apiSuccess {Number} data.status Booking status
 * @apiSuccess {String} data.note Additional notes
 * @apiSuccess {String} data.created_at Creation timestamp
 * @apiSuccess {String} data.updated_at Update timestamp
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 201 Created
 * {
 *   "success": true,
 *   "message": "Booking created successfully",
 *   "data": {
 *     "id": 123,
 *     "booking_code": "BK20251229002",
 *     "room_id": 21,
 *     "user_id": 101,
 *     "start_date": "2026-01-04",
 *     "end_date": "2026-01-05",
 *     "total_amount": 500000,
 *     "status": 0,
 *     "note": "Booked phòng để đi date",
 *     "created_at": "2025-12-29T10:00:00.000000Z",
 *     "updated_at": "2025-12-29T10:00:00.000000Z"
 *   }
 * }
 *
 * @apiError (422) {String} success Response status (false)
 * @apiError (422) {Object} message Validation errors
 *
 * @apiErrorExample {json} Error-Response (Validation Error):
 * HTTP/1.1 422 Unprocessable Entity
 * {
 *   "success": false,
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "user_id": [
 *       "User ID là bắt buộc.",
 *       "User không tồn tại."
 *     ],
 *     "room_id": [
 *       "Room ID là bắt buộc.",
 *       "Room không tồn tại."
 *     ],
 *     "start_date": [
 *       "Ngày bắt đầu là bắt buộc."
 *     ],
 *     "end_date": [
 *       "Ngày kết thúc là bắt buộc.",
 *       "Ngày kết thúc phải sau ngày bắt đầu."
 *     ]
 *   },
 *   "data": null
 * }
 */

/**
 * @api {put} /api/v1/admin/bookings/:id Update Booking (Admin)
 * @apiName UpdateBooking
 * @apiGroup Bookings
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Updates booking information including dates and status.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam (Path) {Number} id Booking ID (required, must exist in bookings table)
 *
 * @apiParam (Body) {String} [start_date] Booking start date (optional, must be a valid date in YYYY-MM-DD format)
 * @apiParam (Body) {String} [end_date] Booking end date (optional, must be a valid date in YYYY-MM-DD format, must be after start_date)
 * @apiParam (Body) {Number} [status] Booking status (optional, 0: pending, 1: confirmed, 2: cancelled, 3: completed)
 *
 * @apiSampleRequest /api/v1/admin/bookings/171
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Object} data Updated booking data
 * @apiSuccess {Number} data.id Booking ID
 * @apiSuccess {String} data.booking_code Unique booking code
 * @apiSuccess {Number} data.room_id Room ID
 * @apiSuccess {Number} data.user_id User ID
 * @apiSuccess {String} data.start_date Booking start date
 * @apiSuccess {String} data.end_date Booking end date
 * @apiSuccess {Number} data.total_amount Total booking amount
 * @apiSuccess {Number} data.status Booking status
 * @apiSuccess {String} data.note Additional notes
 * @apiSuccess {String} data.created_at Creation timestamp
 * @apiSuccess {String} data.updated_at Update timestamp
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Booking updated successfully",
 *   "data": {
 *     "id": 171,
 *     "booking_code": "BK20251229001",
 *     "room_id": 21,
 *     "user_id": 101,
 *     "start_date": "2026-01-01",
 *     "end_date": "2026-01-02",
 *     "total_amount": 500000,
 *     "status": 3,
 *     "note": "Booked phòng để đi date",
 *     "created_at": "2025-12-29T10:00:00.000000Z",
 *     "updated_at": "2025-12-29T10:30:00.000000Z"
 *   }
 * }
 *
 * @apiError (404) {String} success Response status (false)
 * @apiError (404) {String} message Booking not found error
 *
 * @apiError (422) {String} success Response status (false)
 * @apiError (422) {Object} message Validation errors
 */

/**
 * @api {delete} /api/v1/admin/bookings/:id Delete Booking (Admin)
 * @apiName DeleteBooking
 * @apiGroup Bookings
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Deletes a booking record.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam (Path) {Number} id Booking ID (required, must exist in bookings table)
 *
 * @apiSampleRequest /api/v1/admin/bookings/171
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Object} data Null (no data returned for delete operations)
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Booking deleted successfully",
 *   "data": null
 * }
 *
 * @apiError (404) {String} success Response status (false)
 * @apiError (404) {String} message Booking not found error
 *
 * @apiErrorExample {json} Error-Response (Not Found):
 * HTTP/1.1 404 Not Found
 * {
 *   "success": false,
 *   "message": "Booking not found.",
 *   "data": null
 * }
 */

