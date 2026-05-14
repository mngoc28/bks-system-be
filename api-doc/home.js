/**
 * @api {get} /api/v1/home/rooms/getLatest Get Latest Rooms
 * @apiName GetLatestRooms
 * @apiGroup Home
 * @apiVersion 1.0.0
 *
 * @apiDescription Public endpoint - No authentication required. Returns the latest rooms for homepage display.
 *
 * @apiParam (Query) {Number} [limit] Number of rooms to return (optional, default: 10, max: 50)
 *
 * @apiSampleRequest /api/v1/home/rooms/getLatest
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Array} data Array of latest rooms
 * @apiSuccess {Number} data[].id Room ID
 * @apiSuccess {String} data[].title Room title
 * @apiSuccess {String} data[].room_type Room type code
 * @apiSuccess {Number} data[].people Max guests (capacity)
 * @apiSuccess {String} data[].description Room description
 * @apiSuccess {Number} data[].area Room area (m²)
 * @apiSuccess {Number} data[].property_id Property ID
 * @apiSuccess {String} data[].property_name Property name
 * @apiSuccess {String} data[].province_name Province name (Vietnamese)
 * @apiSuccess {String} data[].province_name_en Province slug / English key
 * @apiSuccess {String} data[].room_image Main room image URL (from joined row)
 * @apiSuccess {String} data[].property_address Property address detail (legacy JSON key)
 * @apiSuccess {String} data[].property_type_name Property type label
 * @apiSuccess {Number} data[].property_type_id Property type ID
 * @apiSuccess {String} data[].amenities Comma-separated amenity names
 * @apiSuccess {Number} data[].cheapest_daily_price Cheapest effective daily price
 * @apiSuccess {Number} data[].cheapest_monthly_price Cheapest monthly package price
 * @apiSuccess {String} data[].all_prices JSON array string of price objects (unit, price, deposit_amount, minimum_stay)
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Latest rooms retrieved successfully",
 *   "data": [
 *     {
 *       "id": 1,
 *       "title": "Phòng Deluxe 101",
 *       "room_type": "studio",
 *       "people": 2,
 *       "description": "Phòng 1 phòng ngủ cao cấp với view biển",
 *       "area": 45,
 *       "property_id": 1,
 *       "property_name": "BKS Beach Resort",
 *       "province_name": "Khánh Hòa",
 *       "province_name_en": "khanh_hoa",
 *       "room_image": "https://res.cloudinary.com/example/image/upload/v1234567890/rooms/room1.jpg",
 *       "property_address": "123 Đường Biển, Nha Trang",
 *       "property_type_name": "Resort",
 *       "property_type_id": 2,
 *       "amenities": "WiFi,Điều hòa",
 *       "cheapest_daily_price": 1500000,
 *       "cheapest_monthly_price": 30000000,
 *       "all_prices": "[{\"unit\":\"day\",\"price\":1500000,\"deposit_amount\":500000,\"minimum_stay\":1}]"
 *     }
 *   ]
 * }
 *
 * @apiError (400) {String} success Response status (false)
 * @apiError (400) {String} message Invalid limit parameter
 *
 * @apiErrorExample {json} Error-Response (Invalid Limit):
 * HTTP/1.1 400 Bad Request
 * {
 *   "success": false,
 *   "message": "Limit must be between 1 and 50.",
 *   "data": null
 * }
 */

/**
 * @api {get} /api/v1/home/provinces Get All Provinces
 * @apiName GetAllProvinces
 * @apiGroup Home
 * @apiVersion 1.0.0
 *
 * @apiDescription Public endpoint - No authentication required. Returns all provinces for location selection on homepage.
 *
 * @apiSampleRequest /api/v1/home/provinces
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Array} data Array of provinces
 * @apiSuccess {Number} data[].id Province ID
 * @apiSuccess {String} data[].name Province name in Vietnamese
 * @apiSuccess {String} data[].name_en Province name in English/slug format
 * @apiSuccess {String} data[].created_at Creation timestamp
 * @apiSuccess {String} data[].updated_at Update timestamp
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Provinces retrieved successfully",
 *   "data": [
 *     {
 *       "id": 1,
 *       "name": "Hồ Chí Minh",
 *       "name_en": "ho_chi_minh",
 *       "created_at": "2025-01-01T00:00:00.000000Z",
 *       "updated_at": "2025-01-01T00:00:00.000000Z"
 *     },
 *     {
 *       "id": 2,
 *       "name": "Hà Nội",
 *       "name_en": "ha_noi",
 *       "created_at": "2025-01-01T00:00:00.000000Z",
 *       "updated_at": "2025-01-01T00:00:00.000000Z"
 *     }
 *   ]
 * }
 */

/**
 * @api {get} /api/v1/home/partners/random Get Random Partners
 * @apiName GetRandomPartners
 * @apiGroup Home
 * @apiVersion 1.0.0
 *
 * @apiDescription Public endpoint - No authentication required. Returns random partners for homepage display.
 *
 * @apiParam (Query) {Number} [limit] Number of partners to return (optional, default: 10, max: 50)
 *
 * @apiSampleRequest /api/v1/home/partners/random
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Array} data Array of random partners
 * @apiSuccess {Number} data[].id Partner ID
 * @apiSuccess {String} data[].name Partner name
 * @apiSuccess {String} data[].description Partner description
 * @apiSuccess {String} data[].logo Partner logo URL
 * @apiSuccess {String} data[].website Partner website URL
 * @apiSuccess {String} data[].phone Partner phone number
 * @apiSuccess {String} data[].email Partner email
 * @apiSuccess {String} data[].address Partner address
 * @apiSuccess {Number} data[].status Partner status (1: active, 0: inactive)
 * @apiSuccess {String} data[].created_at Creation timestamp
 * @apiSuccess {String} data[].updated_at Update timestamp
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Random partners retrieved successfully",
 *   "data": [
 *     {
 *       "id": 1,
 *       "name": "Vingroup",
 *       "description": "Tập đoàn Bất động sản hàng đầu Việt Nam",
 *       "logo": "https://res.cloudinary.com/example/image/upload/v1234567890/partners/vingroup.jpg",
 *       "website": "https://vingroup.net",
 *       "phone": "1900 1234",
 *       "email": "contact@vingroup.net",
 *       "address": "Số 7 Đường Bằng Lăng 1, Vinhomes Central Park, Thành phố Hồ Chí Minh",
 *       "status": 1,
 *       "created_at": "2025-01-01T00:00:00.000000Z",
 *       "updated_at": "2025-01-01T00:00:00.000000Z"
 *     }
 *   ]
 * }
 *
 * @apiError (400) {String} success Response status (false)
 * @apiError (400) {String} message Invalid limit parameter
 */

/**
 * @api {get} /api/v1/home/news/latest Get Latest News
 * @apiName GetLatestNews
 * @apiGroup Home
 * @apiVersion 1.0.0
 *
 * @apiDescription Public endpoint - No authentication required. Returns the latest news articles for homepage display.
 *
 * @apiParam (Query) {Number} [limit] Number of news articles to return (optional, default: 10, max: 50)
 *
 * @apiSampleRequest /api/v1/home/news/latest
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Array} data Array of latest news articles
 * @apiSuccess {Number} data[].id News ID
 * @apiSuccess {String} data[].title News title
 * @apiSuccess {String} data[].content News content (HTML format)
 * @apiSuccess {String} data[].excerpt Short excerpt/summary
 * @apiSuccess {String} data[].thumbnail News thumbnail image URL
 * @apiSuccess {Array} data[].images Array of news image URLs
 * @apiSuccess {String} data[].slug URL slug for SEO
 * @apiSuccess {Number} data[].status News status (1: published, 0: draft)
 * @apiSuccess {Number} data[].created_by User ID who created the news
 * @apiSuccess {Number} data[].updated_by User ID who last updated the news
 * @apiSuccess {String} data[].published_at Publication timestamp
 * @apiSuccess {String} data[].created_at Creation timestamp
 * @apiSuccess {String} data[].updated_at Update timestamp
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Latest news retrieved successfully",
 *   "data": [
 *     {
 *       "id": 1,
 *       "title": "BKS System ra mắt tính năng đặt phòng online mới",
 *       "content": "<p>Nội dung bài viết chi tiết về tính năng mới...</p>",
 *       "excerpt": "BKS System vừa ra mắt tính năng đặt phòng online với nhiều cải tiến mới...",
 *       "thumbnail": "https://res.cloudinary.com/example/image/upload/v1234567890/news/news1.jpg",
 *       "images": [
 *         "https://res.cloudinary.com/example/image/upload/v1234567890/news/news1.jpg",
 *         "https://res.cloudinary.com/example/image/upload/v1234567890/news/news1_2.jpg"
 *       ],
 *       "slug": "bks-system-ra-mat-tinh-nang-dat-phong-online-moi",
 *       "status": 1,
 *       "created_by": 1,
 *       "updated_by": 1,
 *       "published_at": "2025-12-29T10:00:00.000000Z",
 *       "created_at": "2025-12-29T09:00:00.000000Z",
 *       "updated_at": "2025-12-29T10:00:00.000000Z"
 *     }
 *   ]
 * }
 *
 * @apiError (400) {String} success Response status (false)
 * @apiError (400) {String} message Invalid limit parameter
 *
 * @apiErrorExample {json} Error-Response (Invalid Limit):
 * HTTP/1.1 400 Bad Request
 * {
 *   "success": false,
 *   "message": "Limit must be between 1 and 50.",
 *   "data": null
 * }
 */