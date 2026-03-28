/**
 * @api {get} /api/v1/admin/buildings Get Buildings (Pagination + Search + Sort)
 * @apiName GetAllOrSearchBuildings
 * @apiGroup Buildings
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Returns paginated list of buildings with search, filter, and sort functionality.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam {String} [name] Search by building name (case-insensitive, partial match)
 * @apiParam {String} [ward] Search by ward name (case-insensitive, partial match)
 * @apiParam {String} [province] Search by province name (case-insensitive, partial match)
 * @apiParam {Number} [year_built] Filter by year built (exact match)
 * @apiParam {String} [building_type] Filter by building type (exact match)
 * @apiParam {Number} [area_min] Filter by minimum area (>=)
 * @apiParam {Number} [area_max] Filter by maximum area (<=)
 * @apiParam {String} [sort[0][field]] First sort field. Available fields: id, name, address_detail, number_of_floors, number_of_units, year_built, building_type, area, description, created_at, updated_at, user_name, province_name, ward_name
 * @apiParam {String} [sort[0][order]] First sort order: "asc" or "desc" (default: "asc")
 * @apiParam {String} [sort[1][field]] Second sort field (optional, for multi-level sorting)
 * @apiParam {String} [sort[1][order]] Second sort order: "asc" or "desc" (default: "asc")
 * @apiParam {Number} [page=1] Current page number
 * @apiParam {Number} [per_page=10] Number of records per page (default: 10)
 *
 * @apiExample {curl} Example Request - Search with filters and sort:
 * curl -X GET "http://localhost:8000/api/v1/admin/buildings?name=Building&province=Hà Nội&area_min=100&area_max=500&sort[0][field]=area&sort[0][order]=desc&sort[1][field]=created_at&sort[1][order]=asc&page=1&per_page=20" \
 *   -H "Authorization: Bearer {token}"
 *
 * @apiExample {json} Example Request - JSON format (POST body):
 * {
 *   "name": "Building",
 *   "province": "Hà Nội",
 *   "area_min": 100,
 *   "area_max": 500,
 *   "sort": [
 *     {"field": "area", "order": "desc"},
 *     {"field": "created_at", "order": "asc"}
 *   ],
 *   "page": 1,
 *   "per_page": 20
 * }
 *
 * @apiSampleRequest /api/v1/admin/buildings
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Buildings retrieved successfully",
 *   "data": {
 *     "current_page": 1,
 *     "data": [
 *       {
 *         "id": 31,
 *         "name": "Building pariatur",
 *         "address_detail": "32 Phố Bạch Tân Kỷ, Xã Thắng Trường",
 *         "number_of_floors": 5,
 *         "number_of_units": 20,
 *         "year_built": 2020,
 *         "building_type": "apartment",
 *         "area": 150.5,
 *         "description": "Delectus in eos impedit veritatis est.",
 *         "user_id": 10,
 *         "province_id": 1,
 *         "ward_id": 5,
 *         "user_name": "Nguyễn Văn A",
 *         "province_name": "Hà Nội",
 *         "ward_name": "Phường Tràng Tiền",
 *         "created_by": 1,
 *         "updated_by": 1,
 *         "created_at": "2025-10-10T09:37:02.000000Z",
 *         "updated_at": "2025-10-10T09:37:02.000000Z"
 *       }
 *     ],
 *     "first_page_url": "http://localhost:8000/api/v1/admin/buildings?page=1",
 *     "from": 1,
 *     "last_page": 7,
 *     "last_page_url": "http://localhost:8000/api/v1/admin/buildings?page=7",
 *     "links": [
 *       {
 *         "url": null,
 *         "label": "&laquo; Previous",
 *         "active": false
 *       },
 *       {
 *         "url": "http://localhost:8000/api/v1/admin/buildings?page=1",
 *         "label": "1",
 *         "active": true
 *       },
 *       {
 *         "url": "http://localhost:8000/api/v1/admin/buildings?page=2",
 *         "label": "2",
 *         "active": false
 *       },
 *       {
 *         "url": "http://localhost:8000/api/v1/admin/buildings?page=3",
 *         "label": "3",
 *         "active": false
 *       },
 *       {
 *         "url": "http://localhost:8000/api/v1/admin/buildings?page=7",
 *         "label": "7",
 *         "active": false
 *       },
 *       {
 *         "url": "http://localhost:8000/api/v1/admin/buildings?page=2",
 *         "label": "Next &raquo;",
 *         "active": false
 *       }
 *     ],
 *     "next_page_url": "http://localhost:8000/api/v1/admin/buildings?page=2",
 *     "path": "http://localhost:8000/api/v1/admin/buildings",
 *     "per_page": 10,
 *     "prev_page_url": null,
 *     "to": 10,
 *     "total": 70
 *   }
 * }
 *
 * @apiErrorExample {json} Unauthorized:
 * HTTP/1.1 401 Unauthorized
 * {
 *   "success": false,
 *   "message": "Unauthorized",
 *   "data": null
 * }
 *
 * @apiErrorExample {json} Forbidden:
 * HTTP/1.1 403 Forbidden
 * {
 *   "success": false,
 *   "message": "Forbidden",
 *   "data": null
 * }
 *
 * @apiErrorExample {json} Validation Error:
 * HTTP/1.1 422 Unprocessable Entity
 * {
 *   "success": false,
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "area_min": [
 *       "The area min must be a number."
 *     ],
 *     "area_max": [
 *       "The area max must be a number."
 *     ]
 *   }
 * }
 */

/**
 * @api {get} /api/v1/admin/buildings/:id Get Building Details
 * @apiName GetBuildingById
 * @apiGroup Buildings
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Returns a specific building by ID.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam (Path) {Number} id Building ID (required, must exist in buildings table)
 *
 * @apiSampleRequest /api/v1/admin/buildings/1
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Object} data Building data
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Building retrieved successfully",
 *   "data": {
 *     "id": 6,
 *     "user_id": 10,
 *     "province_id": 1,
 *     "ward_id": 5,
 *     "name": "Building aut",
 *     "address_detail": "1 Phố Ngân Đài Như, Ấp Khâu Đan",
 *     "number_of_floors": 5,
 *     "number_of_units": 20,
 *     "year_built": 2020,
 *     "building_type": 1,
 *     "area": 150.5,
 *     "description": "Cumque voluptas libero corrupti amet fuga natus delectus.",
 *     "created_by": 1,
 *     "updated_by": 1,
 *     "created_at": "2025-10-10T09:37:02.000000Z",
 *     "updated_at": "2025-10-10T09:37:02.000000Z"
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
 *   "message": "Building not found",
 *   "data": null
 * }
 */

/**
 * @api {post} /api/v1/admin/buildings Create a New Building
 * @apiName CreateBuilding
 * @apiGroup Buildings
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Creates a new building.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam (Body) {Number} user_id User ID who owns the building (required, must exist in users table)
 * @apiParam (Body) {Number} province_id Province ID (required, must exist in provinces table)
 * @apiParam (Body) {Number} ward_id Ward ID (required, must exist in wards table)
 * @apiParam (Body) {String{1..255}} name Building name (required, max: 255 characters)
 * @apiParam (Body) {String{0..255}} [address_detail] Detailed address - house number, street name (optional, max: 255 characters)
 * @apiParam (Body) {Number} [number_of_floors=1] Number of floors (optional, integer, default: 1)
 * @apiParam (Body) {Number} [number_of_units=0] Number of units/rooms (optional, integer, default: 0)
 * @apiParam (Body) {Number} [year_built] Year built (optional, integer, format: YYYY)
 * @apiParam (Body) {Number} [building_type] Building type (optional, integer, 1: apartment building, 2: building, 3: villa, 4: townhouse, 5: serviced apartment, 6: boarding house, 7: hotel, 8: office, 9: other)
 * @apiParam (Body) {Number} [area] Total area in square meters (optional, decimal 10,2)
 * @apiParam (Body) {String} [description] Building description (optional, text)
 * @apiParam (Body) {Number} [created_by] Creator user ID (optional, must exist in users table)
 * @apiParam (Body) {Number} [updated_by] Updater user ID (optional, must exist in users table)
 *
 * @apiParamExample {json} Request-Example:
 * {
 *   "user_id": 10,
 *   "province_id": 1,
 *   "ward_id": 5,
 *   "name": "Tòa nhà B 000",
 *   "address_detail": "456 Nguyễn Trãi, Phường Tràng Tiền",
 *   "number_of_floors": 5,
 *   "number_of_units": 20,
 *   "year_built": 2020,
 *   "building_type": 1,
 *   "area": 150.5,
 *   "description": "Tòa nhà 5 tầng, có thang máy và phòng họp lớn.",
 *   "created_by": 1,
 *   "updated_by": 1
 * }
 *
 * @apiSampleRequest /api/v1/admin/buildings
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Object} data Created building data
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 201 Created
 * {
 *   "success": true,
 *   "message": "Building created successfully",
 *   "data": {
 *     "id": 65,
 *     "user_id": 10,
 *     "province_id": 1,
 *     "ward_id": 5,
 *     "name": "Tòa nhà B 000",
 *     "address_detail": "456 Nguyễn Trãi, Phường Tràng Tiền",
 *     "number_of_floors": 5,
 *     "number_of_units": 20,
 *     "year_built": 2020,
 *     "building_type": 1,
 *     "area": 150.5,
 *     "description": "Tòa nhà 5 tầng, có thang máy và phòng họp lớn.",
 *     "created_by": 1,
 *     "updated_by": 1,
 *     "created_at": "2025-10-24T07:14:57.000000Z",
 *     "updated_at": "2025-10-24T07:14:57.000000Z"
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
 *       "User ID is required",
 *       "The selected user id does not exist."
 *     ],
 *     "province_id": [
 *       "Province ID is required",
 *       "The selected province id does not exist."
 *     ],
 *     "ward_id": [
 *       "Ward ID is required",
 *       "The selected ward id does not exist."
 *     ],
 *     "name": [
 *       "Building name is required"
 *     ]
 *   },
 *   "data": null
 * }
 */

/**
 * @api {put} /api/v1/admin/buildings/:id Update a Building
 * @apiName UpdateBuilding
 * @apiGroup Buildings
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Updates a building. All fields are optional but if provided, must follow validation rules.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam (Path) {Number} id Building ID (required, must exist in buildings table)
 * @apiParam (Body) {Number} [user_id] User ID who owns the building (optional, must exist in users table)
 * @apiParam (Body) {Number} [province_id] Province ID (optional, must exist in provinces table)
 * @apiParam (Body) {Number} [ward_id] Ward ID (optional, must exist in wards table)
 * @apiParam (Body) {String{1..255}} [name] Building name (optional, max: 255 characters, required if provided)
 * @apiParam (Body) {String{0..255}} [address_detail] Detailed address - house number, street name (optional, max: 255 characters)
 * @apiParam (Body) {Number} [number_of_floors] Number of floors (optional, integer)
 * @apiParam (Body) {Number} [number_of_units] Number of units/rooms (optional, integer)
 * @apiParam (Body) {Number} [year_built] Year built (optional, integer, format: YYYY)
 * @apiParam (Body) {Number} [building_type] Building type (optional, integer, 1: apartment building, 2: building, 3: villa, 4: townhouse, 5: serviced apartment, 6: boarding house, 7: hotel, 8: office, 9: other)
 * @apiParam (Body) {Number} [area] Total area in square meters (optional, decimal 10,2)
 * @apiParam (Body) {String} [description] Building description (optional, text)
 * @apiParam (Body) {Number} [created_by] Creator user ID (optional, must exist in users table)
 * @apiParam (Body) {Number} [updated_by] Updater user ID (optional, must exist in users table)
 *
 * @apiParamExample {json} Request-Example:
 * {
 *   "name": "Tòa nhà B 001",
 *   "address_detail": "789 Nguyễn Trãi, Phường Tràng Tiền",
 *   "number_of_floors": 6,
 *   "number_of_units": 25,
 *   "year_built": 2021,
 *   "building_type": 1,
 *   "area": 200.75,
 *   "description": "Tòa nhà 6 tầng, có thang máy và phòng họp lớn.",
 *   "updated_by": 1
 * }
 *
 * @apiSampleRequest /api/v1/admin/buildings/1
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Boolean} data Update result (true)
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Building updated successfully",
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
 *   "message": "Building not found",
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
 *     "name": [
 *       "Building name is required"
 *     ]
 *   },
 *   "data": null
 * }
 */

/**
 * @api {delete} /api/v1/admin/buildings/:id Delete a Building
 * @apiName DeleteBuilding
 * @apiGroup Buildings
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Deletes a building. Building can only be deleted if it has no rooms and no bookings.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam (Path) {Number} id Building ID (required, must exist in buildings table)
 *
 * @apiSampleRequest /api/v1/admin/buildings/1
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Object} data Delete result (null)
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Building deleted successfully",
 *   "data": null
 * }
 *
 * @apiError (404) {String} success Response status (false)
 * @apiError (404) {String} message Error message
 *
 * @apiErrorExample {json} Error-Response (Not Found):
 * HTTP/1.1 404 Not Found
 * {
 *   "success": false,
 *   "message": "Building not found",
 *   "data": null
 * }
 *
 * @apiError (400) {String} success Response status (false)
 * @apiError (400) {String} message Error message
 *
 * @apiErrorExample {json} Error-Response (Cannot Delete):
 * HTTP/1.1 400 Bad Request
 * {
 *   "success": false,
 *   "message": "Cannot delete building. Building has associated rooms or bookings.",
 *   "data": null
 * }
 */

/**
 * @api {get} /api/v1/admin/buildings/bookings/:id Get Bookings by Building ID
 * @apiName GetBookingsByBuilding
 * @apiGroup Buildings
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Returns paginated list of bookings for a specific building with optional filters.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam (Path) {Number} id Building ID (required, must exist in buildings table)
 * @apiParam {String} [start_date] Filter by start date (optional, format: YYYY-MM-DD)
 * @apiParam {String} [end_date] Filter by end date (optional, format: YYYY-MM-DD, must be after or equal to start_date)
 * @apiParam {String} [status] Filter by booking status (optional, values: pending, confirmed, completed, cancelled)
 * @apiParam {Number} [page=1] Current page number (optional, min: 1)
 * @apiParam {Number} [per_page=10] Number of records per page (optional, min: 1)
 *
 * @apiSampleRequest /api/v1/admin/buildings/bookings/1?start_date=2025-01-01&end_date=2025-12-31&status=confirmed&page=1&per_page=10
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Object} data Paginated bookings data
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
 *         "user_id": 10,
 *         "room_id": 5,
 *         "building_id": 1,
 *         "start_time": "2025-01-15 10:00:00",
 *         "end_time": "2025-01-15 12:00:00",
 *         "status": "confirmed",
 *         "note": "Meeting room booking",
 *         "created_at": "2025-01-10T08:00:00.000000Z",
 *         "updated_at": "2025-01-10T08:00:00.000000Z"
 *       }
 *     ],
 *     "total": 50,
 *     "per_page": 10,
 *     "current_page": 1,
 *     "last_page": 5
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
 *   "message": "Building not found",
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
 *     "end_date": [
 *       "The end date must be a date after or equal to start date."
 *     ],
 *     "status": [
 *       "The selected status is invalid."
 *     ]
 *   },
 *   "data": null
 * }
 */

/**
 * @api {get} /api/v1/admin/buildings/types Get All Building Types
 * @apiName GetAllBuildingsTypes
 * @apiGroup Buildings
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Returns all available building types with their labels.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiSampleRequest /api/v1/admin/buildings/types
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Array} data Array of building types
 * @apiSuccess {String} data[].value Building type value
 * @apiSuccess {String} data[].label Building type label
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Building types retrieved successfully",
 *   "data": [
 *     {
 *       "value": "apartment",
 *       "label": "Apartment"
 *     },
 *     {
 *       "value": "office",
 *       "label": "Office"
 *     },
 *     {
 *       "value": "warehouse",
 *       "label": "Warehouse"
 *     }
 *   ]
 * }
 *
 * @apiError (400) {String} success Response status (false)
 * @apiError (400) {String} message Error message
 *
 * @apiErrorExample {json} Error-Response:
 * HTTP/1.1 400 Bad Request
 * {
 *   "success": false,
 *   "message": "Failed to retrieve building types",
 *   "data": null
 * }
 */
