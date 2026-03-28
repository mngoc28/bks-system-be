/**
 * @api {get} /api/v1/news/list-news Get list news for user
 * @apiName GetAllNewsForUser
 * @apiGroup NewsForUser
 * @apiVersion 1.0.0
 *
 * @apiDescription Get paginated list of news for user with filter and sort
 *
 * @apiParam {Number} [page=1] Page number
 * @apiParam {Number} [per_page=15] Items per page (max 50)
 * @apiParam {String} [title] Filter by title (LIKE)
 * @apiParam {String} [content] Filter by content (LIKE)
 * @apiParam {String="id","title"} [sort_field=id] Sort field
 * @apiParam {String="asc","desc"} [sort_direction=asc] Sort direction
 *
 * @apiSuccess {String} status Response status
 * @apiSuccess {String} message Response message
 * @apiSuccess {Object} data Pagination object
 *
 * @apiSuccess {Number} data.current_page Current page
 * @apiSuccess {Array}  data.data List of news
 * @apiSuccess {Number} data.per_page Items per page
 * @apiSuccess {Number} data.total Total records
 * @apiSuccess {Number} data.last_page Last page number
 *
 * @apiSampleRequest /api/v1/news/list-news
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "status": "success",
 *   "message": "Lấy danh sách tin tức thành công",
 *   "data": {
 *     "current_page": 1,
 *     "data": [
 *       {
 *         "id": 1,
 *         "title": "Hướng dẫn sử dụng các tiện ích trong phòng",
 *         "slug": "huong-dan-su-dung",
 *         "summary": "...",
 *         "content": "...",
 *         "status": 1,
 *         "image_url": "/images/news/news_1.jpg",
 *         "created_at": "2025-11-06T03:18:38.000000Z"
 *       }
 *     ],
 *     "per_page": 15,
 *     "total": 30,
 *     "last_page": 2
 *   }
 * }
 *
 * @apiErrorExample {json} Validation Error:
 * HTTP/1.1 422 Unprocessable Entity
 * {
 *   "status": "error",
 *   "message": "Validation error",
 *   "errors": {
 *     "page": ["Page must be integer"]
 *   }
 * }
 */

/**
 * @api {get} /api/v1/news/detail-news/:id Get news detail for user
 * @apiName GetNewsByIdForUser
 * @apiGroup NewsForUser
 * @apiVersion 1.0.0
 *
 * @apiDescription Get single news by ID
 *
 * @apiSuccess {String} status Response status
 * @apiSuccess {String} message Response message
 * @apiSuccess {Object} data News detail
 *
 * @apiSampleRequest /api/v1/news/detail-news/2
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "status": "success",
 *   "message": "Lấy tin tức thành công",
 *   "data": {
 *     "id": 2,
 *     "title": "Hướng dẫn sử dụng các tiện ích trong phòng",
 *     "slug": "huong-dan-su-dung",
 *     "content": "...",
 *     "status": 1,
 *     "image_url": "/images/news/news_2.jpg",
 *     "created_at": "2025-10-31T03:18:38.000000Z"
 *   }
 * }
 *
 * @apiErrorExample {json} Not Found:
 * HTTP/1.1 404 Not Found
 * {
 *   "status": "error",
 *   "message": "News not found"
 * }
 *
 * @apiErrorExample {json} Access Denied:
 * HTTP/1.1 403 Forbidden
 * {
 *   "status": "error",
 *   "message": "Access denied"
 * }
 */

/**
 * @api {get} /api/v1/common/newroom List newest rooms
 * @apiName ListNewestRooms
 * @apiGroup NewsForUser
 * @apiVersion 1.0.0
 *
 * @apiDescription Get list of 10 newest rooms (sorted by latest update)
 *
 * @apiParam {Number} [page=1] Page number
 * @apiParam {Number} [per_page=10] Number of rooms per page
 *
 * @apiSuccess {String} status Response status
 * @apiSuccess {String} message Response message
 * @apiSuccess {Object} data Wrapper response object
 *
 * @apiSuccess {Boolean} data.success Query execution result
 * @apiSuccess {Array} data.data List of newest rooms
 * @apiSuccess {String} data.message Internal service message
 *
 * @apiSuccess {Number} data.data.id Room ID
 * @apiSuccess {Number} data.data.building_id Building ID
 * @apiSuccess {String} data.data.title Room title
 * @apiSuccess {String} data.data.room_number Room number
 * @apiSuccess {String|null} data.data.deposit Deposit amount
 * @apiSuccess {String} data.data.area Room area (m²)
 * @apiSuccess {Number} data.data.floor_number Floor number
 * @apiSuccess {Number} data.data.people Maximum people
 * @apiSuccess {Number} data.data.room_type Room type
 * @apiSuccess {Boolean} data.data.status Room status
 * @apiSuccess {String} data.data.description Room description
 * @apiSuccess {Number} data.data.created_by Created user ID
 * @apiSuccess {Number} data.data.updated_by Updated user ID
 * @apiSuccess {String} data.data.created_at Created datetime
 * @apiSuccess {String} data.data.updated_at Updated datetime
 *
 * @apiSampleRequest /api/v1/common/newroom?province_id=48&ward_id=208
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "status": "success",
 *   "message": "Lấy danh sách 10 phòng mới nhất thành công!",
 *   "data": {
 *     "success": true,
 *     "data": [
 *       {
 *         "id": 66,
 *         "building_id": 42,
 *         "title": "Phòng Studio phong cách minimalist 66",
 *         "room_number": "R066",
 *         "deposit": "3377147.00",
 *         "area": "34.83",
 *         "floor_number": 6,
 *         "people": 5,
 *         "room_type": 5,
 *         "status": true,
 *         "description": "Phòng 2 phòng ngủ rộng 50m²...",
 *         "created_by": 20,
 *         "updated_by": 20,
 *         "created_at": "2025-12-08T03:18:39.000000Z",
 *         "updated_at": "2025-12-05T03:18:39.000000Z"
 *       }
 *     ],
 *     "message": "Truy xuất danh sách 10 phòng cập nhật gần nhất thành công!"
 *   }
 * }
 *
 * @apiErrorExample {json} Validation Error:
 * HTTP/1.1 422 Unprocessable Entity
 * {
 *   "status": "error",
 *   "message": "Validation error",
 *   "errors": {
 *     "page": ["Page must be an integer"]
 *   }
 * }
 *
 * @apiErrorExample {json} Fetch Error:
 * HTTP/1.1 400 Bad Request
 * {
 *   "status": "error",
 *   "message": "Lấy danh sách phòng thất bại"
 * }
 */

