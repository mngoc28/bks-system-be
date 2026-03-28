/**
 * @api {get} /api/v1/admin/building-images/building/:buildingId Get Images by Building ID
 * @apiName GetImagesByBuildingId
 * @apiGroup BuildingImages
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Returns all images for a specific building, ordered by sort field.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam (Path) {Number} buildingId Building ID (required, must exist in buildings table)
 *
 * @apiSampleRequest /api/v1/admin/building-images/building/1
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Array} data Array of building images
 * @apiSuccess {Number} data[].id Building image ID
 * @apiSuccess {Number} data[].building_id Building ID
 * @apiSuccess {String} data[].image_url Image URL
 * @apiSuccess {String} data[].id_image_cloudinary Cloudinary image ID
 * @apiSuccess {Number} data[].image_type Image type (0: other, 1: exterior, 2: interior, 3: bathroom, 4: kitchen)
 * @apiSuccess {Number} data[].sort Sort order
 * @apiSuccess {Number} data[].created_by Creator user ID
 * @apiSuccess {Number} data[].updated_by Updater user ID
 * @apiSuccess {String} data[].created_at Creation timestamp
 * @apiSuccess {String} data[].updated_at Update timestamp
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Building images retrieved successfully",
 *   "data": [
 *     {
 *       "id": 1,
 *       "building_id": 1,
 *       "image_url": "https://res.cloudinary.com/example/image/upload/v1234567890/buildings/image1.jpg",
 *       "id_image_cloudinary": "buildings/image1",
 *       "image_type": 0,
 *       "sort": 1,
 *       "created_by": 1,
 *       "updated_by": 1,
 *       "created_at": "2025-11-24T10:00:00.000000Z",
 *       "updated_at": "2025-11-24T10:00:00.000000Z"
 *     },
 *     {
 *       "id": 2,
 *       "building_id": 1,
 *       "image_url": "https://res.cloudinary.com/example/image/upload/v1234567890/buildings/image2.jpg",
 *       "id_image_cloudinary": "buildings/image2",
 *       "image_type": 1,
 *       "sort": 2,
 *       "created_by": 1,
 *       "updated_by": 1,
 *       "created_at": "2025-11-24T10:00:00.000000Z",
 *       "updated_at": "2025-11-24T10:00:00.000000Z"
 *     }
 *   ]
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
 *     "building_id": [
 *       "Building ID is required",
 *       "The selected building id does not exist."
 *     ]
 *   },
 *   "data": null
 * }
 */

/**
 * @api {get} /api/v1/admin/building-images/:id Get Building Image by ID
 * @apiName GetBuildingImageById
 * @apiGroup BuildingImages
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Returns a specific building image.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam (Path) {Number} id Building image ID (required, must exist in building_images table)
 *
 * @apiSampleRequest /api/v1/admin/building-images/1
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Object} data Building image data
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Building image retrieved successfully",
 *   "data": {
 *     "id": 1,
 *     "building_id": 1,
 *     "image_url": "https://res.cloudinary.com/example/image/upload/v1234567890/buildings/image1.jpg",
 *     "id_image_cloudinary": "buildings/image1",
 *     "image_type": 0,
 *     "sort": 1,
 *     "created_by": 1,
 *     "updated_by": 1,
 *     "created_at": "2025-11-24T10:00:00.000000Z",
 *     "updated_at": "2025-11-24T10:00:00.000000Z"
 *   }
 * }
 *
 * @apiError (404) {String} success Response status (false)
 * @apiError (404) {String} message Error message
 *
 * @apiErrorExample {json} Error-Response (Not Found):
 * HTTP/1.1 404 Not Found
 * {
 *   "success": false,
 *   "message": "Building image not found",
 *   "data": null
 * }
 */

/**
 * @api {post} /api/v1/admin/building-images Create Building Image
 * @apiName CreateBuildingImage
 * @apiGroup BuildingImages
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Creates a new building image. Sort will be automatically set to max(sort) + 1 for the building.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam (Body) {Number} building_id Building ID (required, must exist in buildings table)
 * @apiParam (Body) {String} image_url Image URL (required, max: 255)
 * @apiParam (Body) {String} id_image_cloudinary Cloudinary image ID (required, max: 255)
 * @apiParam (Body) {Number} image_type Image type (required, integer, 0: other, 1: exterior, 2: interior, 3: bathroom, 4: kitchen)
 *
 * @apiParamExample {json} Request-Example:
 * {
 *   "building_id": 1,
 *   "image_url": "https://res.cloudinary.com/example/image/upload/v1234567890/buildings/image1.jpg",
 *   "id_image_cloudinary": "buildings/image1",
 *   "image_type": 0
 * }
 *
 * @apiSampleRequest /api/v1/admin/building-images
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Object} data Created building image data (sort is automatically set to max(sort) + 1)
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Building image created successfully",
 *   "data": {
 *     "id": 1,
 *     "building_id": 1,
 *     "image_url": "https://res.cloudinary.com/example/image/upload/v1234567890/buildings/image1.jpg",
 *     "id_image_cloudinary": "buildings/image1",
 *     "image_type": 0,
 *     "sort": 1,
 *     "created_by": 1,
 *     "updated_by": 1,
 *     "created_at": "2025-11-24T10:00:00.000000Z",
 *     "updated_at": "2025-11-24T10:00:00.000000Z"
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
 *     "building_id": [
 *       "Building ID is required",
 *       "The selected building id does not exist."
 *     ],
 *     "image_url": [
 *       "Image URL is required"
 *     ],
 *     "image_type": [
 *       "Image type is required",
 *       "Image type must be an integer."
 *     ]
 *   },
 *   "data": null
 * }
 */

/**
 * @api {put} /api/v1/admin/building-images/:id Update Building Image
 * @apiName UpdateBuildingImage
 * @apiGroup BuildingImages
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Updates a building image. Building ID and sort will remain unchanged.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam (Path) {Number} id Building image ID (required, must exist in building_images table)
 * @apiParam (Body) {String} image_url Image URL (required, max: 255)
 * @apiParam (Body) {String} id_image_cloudinary Cloudinary image ID (required, max: 255)
 * @apiParam (Body) {Number} image_type Image type (required, integer, 0: other, 1: exterior, 2: interior, 3: bathroom, 4: kitchen)
 *
 * @apiParamExample {json} Request-Example:
 * {
 *   "image_url": "https://res.cloudinary.com/example/image/upload/v1234567890/buildings/image1_updated.jpg",
 *   "id_image_cloudinary": "buildings/image1_updated",
 *   "image_type": 1
 * }
 *
 * @apiSampleRequest /api/v1/admin/building-images/1
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Boolean} data Update result (true, building_id and sort remain unchanged)
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Building image updated successfully",
 *   "data": true
 * }
 *
 * @apiError (404) {String} success Response status (false)
 * @apiError (404) {String} message Error message
 *
 * @apiErrorExample {json} Error-Response (Not Found):
 * HTTP/1.1 404 Not Found
 * {
 *   "success": false,
 *   "message": "Building image not found",
 *   "data": null
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
 *     "id": [
 *       "Building image ID is required",
 *       "The selected id does not exist."
 *     ],
 *     "image_url": [
 *       "Image URL is required"
 *     ]
 *   },
 *   "data": null
 * }
 */

/**
 * @api {delete} /api/v1/admin/building-images/:id Delete Building Image
 * @apiName DeleteBuildingImage
 * @apiGroup BuildingImages
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Deletes a building image.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam (Path) {Number} id Building image ID (required, must exist in building_images table)
 *
 * @apiSampleRequest /api/v1/admin/building-images/1
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Boolean} data Delete result (true)
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Building image deleted successfully",
 *   "data": true
 * }
 *
 * @apiError (404) {String} success Response status (false)
 * @apiError (404) {String} message Error message
 *
 * @apiErrorExample {json} Error-Response (Not Found):
 * HTTP/1.1 404 Not Found
 * {
 *   "success": false,
 *   "message": "Building image not found",
 *   "data": null
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
 *     "id": [
 *       "Building image ID is required",
 *       "The selected id does not exist."
 *     ]
 *   },
 *   "data": null
 * }
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
 * @apiParam (Query) {Number} [building_id] Filter by building ID (optional)
 * @apiParam (Query) {Number} [room_id] Filter by room ID (optional)
 * @apiParam (Query) {Number} [user_id] Filter by user ID (optional)
 * @apiParam (Query) {String} [start_date] Filter bookings from this date (YYYY-MM-DD format, optional)
 * @apiParam (Query) {String} [end_date] Filter bookings to this date (YYYY-MM-DD format, optional)
 * @apiParam (Query) {Number} [status] Filter by booking status (0: pending, 1: confirmed, 2: cancelled, 3: completed, optional)
 * @apiParam (Query) {Number} [page] Page number for pagination (default: 1, optional)
 * @apiParam (Query) {Number} [per_page] Number of items per page (default: 15, optional)
 *
 * @apiSampleRequest /api/v1/admin/bookings?building_id=30&status=1&page=1&per_page=10
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Object} data Paginated bookings data
 * @apiSuccess {Number} data.current_page Current page number
 * @apiSuccess {Array} data.data Array of bookings
 * @apiSuccess {Number} data.data[].id Booking ID
 * @apiSuccess {String} data.data[].booking_code Unique booking code
 * @apiSuccess {Number} data.data[].room_id Room ID
 * @apiSuccess {Number} data.data[].user_id User ID
 * @apiSuccess {String} data.data[].start_date Booking start date
 * @apiSuccess {String} data.data[].end_date Booking end date
 * @apiSuccess {Number} data.data[].total_amount Total booking amount
 * @apiSuccess {Number} data.data[].status Booking status
 * @apiSuccess {String} data.data[].note Additional notes
 * @apiSuccess {String} data.data[].created_at Creation timestamp
 * @apiSuccess {String} data.data[].updated_at Update timestamp
 * @apiSuccess {Object} data.data[].room Room information
 * @apiSuccess {Object} data.data[].user User information (if applicable)
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
 *         "booking_code": "BK20251229001",
 *         "room_id": 21,
 *         "user_id": 101,
 *         "start_date": "2026-01-04",
 *         "end_date": "2026-01-05",
 *         "total_amount": 500000,
 *         "status": 1,
 *         "note": "Booked phòng để đi date",
 *         "created_at": "2025-12-29T10:00:00.000000Z",
 *         "updated_at": "2025-12-29T10:00:00.000000Z",
 *         "room": {
 *           "id": 21,
 *           "title": "Phòng Deluxe 101",
 *           "building_name": "BKS Building"
 *         },
 *         "user": {
 *           "id": 101,
 *           "name": "Nguyễn Văn A",
 *           "email": "nguyenvana@example.com"
 *         }
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
 *       "building_name": "BKS Building"
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

