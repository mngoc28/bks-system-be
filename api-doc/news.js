/**
 * @api {get} /api/v1/admin/news Get all news
 * @apiName GetAllNews
 * @apiGroup News
 * @apiVersion 1.0.0
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Returns paginated list of news with filtering and sorting options.
 * @apiParam {Number} [page=1] Page number
 * @apiParam {Number} [per_page=10] Number of items per page
 * @apiParam {String} [title] Filter by title (partial match)
 * @apiParam {String} [content] Filter by content (partial match)
 * @apiParam {Number} [status] Filter by status
 * @apiParam {String} [published_at_start] Filter by published date start (format: YYYY-MM-DD)
 * @apiParam {String} [published_at_end] Filter by published date end (format: YYYY-MM-DD)
 * @apiParam {String} [user_name] Filter by user name (partial match)
 * @apiParam {String} [sort_field=id] Sort field (allowed: id, title, created_at, user_name)
 * @apiParam {String} [sort_direction=asc] Sort direction (asc or desc)
 * @apiSuccess {Object} data Paginated news list
 * @apiSuccess {Array} data.data Array of news items
 * @apiSuccess {Number} data.current_page Current page number
 * @apiSuccess {Number} data.per_page Items per page
 * @apiSuccess {Number} data.total Total number of items
 * @apiSuccess {Number} data.last_page Last page number
 * @apiSampleRequest /api/v1/admin/news
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Lấy danh sách tin tức thành công",
 *   "data": {
 *     "current_page": 1,
 *     "data": [
 *       {
 *         "id": 1,
 *         "title": "Tin tức mới",
 *         "content": "Nội dung tin tức",
 *         "status": 1,
 *         "published_at": "2025-12-09 10:00:00",
 *         "user_id": 1,
 *         "user_name": "admin",
 *         "created_at": "2025-12-09 10:00:00",
 *         "updated_at": "2025-12-09 10:00:00"
 *       }
 *     ],
 *     "first_page_url": "http://localhost/api/v1/admin/news?page=1",
 *     "from": 1,
 *     "last_page": 1,
 *     "last_page_url": "http://localhost/api/v1/admin/news?page=1",
 *     "links": [...],
 *     "next_page_url": null,
 *     "path": "http://localhost/api/v1/admin/news",
 *     "per_page": 10,
 *     "prev_page_url": null,
 *     "to": 1,
 *     "total": 1
 *   }
 * }
 * @apiErrorExample {json} Validation-Error-Response:
 * HTTP/1.1 422 Unprocessable Entity
 * {
 *   "success": false,
 *   "message": "Validation error",
 *   "errors": {
 *     "page": ["Trang phải là số nguyên"],
 *     "per_page": ["Số lượng mỗi trang phải là số nguyên"]
 *   }
 * }
 */

/**
 * @api {get} /api/v1/admin/news/:id Get news by ID
 * @apiName GetNewsById
 * @apiGroup News
 * @apiVersion 1.0.0
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Returns a single news item by ID. Users can only access news they created unless they are admin.
 * @apiParam {Number} id News ID (required, path parameter)
 * @apiSuccess {Object} data News object
 * @apiSuccess {Number} data.id News ID
 * @apiSuccess {Number} data.user_id User ID who created the news
 * @apiSuccess {String} data.title News title
 * @apiSuccess {String} data.slug News slug
 * @apiSuccess {String} data.summary News summary
 * @apiSuccess {String} data.content News content
 * @apiSuccess {Number} data.status News status (0: inactive, 1: active)
 * @apiSuccess {String} data.published_at Published date (format: YYYY-MM-DD HH:mm:ss)
 * @apiSuccess {String} data.image_url Image URL
 * @apiSuccess {String} data.id_image_cloudinary Cloudinary image ID
 * @apiSuccess {Number} data.created_by User ID who created the record
 * @apiSuccess {Number} data.updated_by User ID who updated the record
 * @apiSuccess {String} data.created_at Created timestamp
 * @apiSuccess {String} data.updated_at Updated timestamp
 * @apiSuccess {String} data.user_name Name of the user who created the news
 * @apiSampleRequest /api/v1/admin/news/1
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Lấy danh sách tin tức thành công",
 *   "data": {
 *     "id": 1,
 *     "user_id": 1,
 *     "title": "Hướng dẫn đặt phòng trực tuyến nhanh chóng và tiện lợi",
 *     "slug": "huong-dan-dat-phong-truc-tuyen-nhanh-chong-va-tien-loi-1",
 *     "summary": "Hướng dẫn chi tiết về cách đặt phòng trực tuyến một cách nhanh chóng và tiện lợi nhất",
 *     "content": "Trong bài viết này, chúng tôi sẽ hướng dẫn bạn cách đặt phòng trực tuyến...",
 *     "status": 1,
 *     "published_at": "2024-01-01T10:00:00.000000Z",
 *     "image_url": "/images/news/news_1.jpg",
 *     "id_image_cloudinary": "news_1_randomstring",
 *     "created_by": 1,
 *     "updated_by": 1,
 *     "created_at": "2024-01-01T09:00:00.000000Z",
 *     "updated_at": "2024-01-01T09:00:00.000000Z",
 *     "user_name": "admin"
 *   }
 * }
 * @apiErrorExample {json} Not-Found-Error-Response:
 * HTTP/1.1 422 Unprocessable Entity
 * {
 *   "success": false,
 *   "message": "Validation error",
 *   "errors": {
 *     "id": ["The selected id is invalid."]
 *   }
 * }
 * @apiErrorExample {json} Access-Denied-Error-Response:
 * HTTP/1.1 400 Bad Request
 * {
 *   "success": false,
 *   "message": "Lấy danh sách tin tức thất bại",
 *   "data": null
 * }
 */

/**
 * @api {post} /api/v1/admin/news Create news
 * @apiName CreateNews
 * @apiGroup News
 * @apiVersion 1.0.0
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Creates a new news item. Slug is automatically generated from title.
 * @apiParam {String} title News title (required)
 * @apiParam {String} [summary] News summary
 * @apiParam {String} content News content (required)
 * @apiParam {Number} [status] News status (0: inactive, 1: active)
 * @apiParam {String} [published_at] Published date (format: YYYY-MM-DD or YYYY-MM-DD HH:mm:ss)
 * @apiParam {String} [image_url] Image URL
 * @apiParam {String} [id_image_cloudinary] Cloudinary image ID
 * @apiSuccess {Object} data Created news object
 * @apiSuccess {Number} data.id News ID
 * @apiSuccess {Number} data.user_id User ID who created the news
 * @apiSuccess {String} data.title News title
 * @apiSuccess {String} data.slug News slug (auto-generated from title)
 * @apiSuccess {String} data.summary News summary
 * @apiSuccess {String} data.content News content
 * @apiSuccess {Number} data.status News status
 * @apiSuccess {String} data.published_at Published date
 * @apiSuccess {String} data.image_url Image URL
 * @apiSuccess {String} data.id_image_cloudinary Cloudinary image ID
 * @apiSampleRequest /api/v1/admin/news
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Tạo tin tức thành công",
 *   "data": {
 *     "id": 1,
 *     "user_id": 1,
 *     "title": "Tin tức mới",
 *     "slug": "tin-tuc-moi",
 *     "summary": "Tóm tắt tin tức",
 *     "content": "Nội dung chi tiết tin tức",
 *     "status": 1,
 *     "published_at": "2025-12-26 10:00:00",
 *     "image_url": "/images/news/news_1.jpg",
 *     "id_image_cloudinary": "news_1_abc123xyz",
 *     "created_by": 1,
 *     "updated_by": 1,
 *     "created_at": "2025-12-26 10:00:00",
 *     "updated_at": "2025-12-26 10:00:00"
 *   }
 * }
 * @apiErrorExample {json} Validation-Error-Response:
 * HTTP/1.1 422 Unprocessable Entity
 * {
 *   "success": false,
 *   "message": "Validation error",
 *   "errors": {
 *     "title": ["Tiêu đề là bắt buộc"],
 *     "content": ["Nội dung là bắt buộc"]
 *   }
 * }
 * @apiErrorExample {json} Unauthorized-Error-Response:
 * HTTP/1.1 401 Unauthorized
 * {
 *   "success": false,
 *   "message": "Unauthenticated",
 *   "data": null
 * }
 * @apiErrorExample {json} Forbidden-Error-Response:
 * HTTP/1.1 403 Forbidden
 * {
 *   "success": false,
 *   "message": "You are not authorized to perform this action.",
 *   "data": null
 * }
 */

/**
 * @api {put} /api/v1/admin/news/:id Update news
 * @apiName UpdateNews
 * @apiGroup News
 * @apiVersion 1.0.0
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Updates a news item by ID. Users can only update news they created unless they are admin. Slug is automatically generated from title if title is updated.
 * @apiParam {Number} id News ID (required, path parameter)
 * @apiParam {Number} [user_id] User ID who created the news
 * @apiParam {String} [title] News title
 * @apiParam {String} [summary] News summary
 * @apiParam {String} [content] News content
 * @apiParam {Number} [status] News status (0: inactive, 1: active)
 * @apiParam {String} [published_at] Published date (format: YYYY-MM-DD or YYYY-MM-DD HH:mm:ss)
 * @apiParam {String} [image_url] Image URL
 * @apiParam {String} [id_image_cloudinary] Cloudinary image ID
 * @apiSuccess {Object} data Update result (boolean)
 * @apiSampleRequest /api/v1/admin/news/1
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Cập nhật tin tức thành công",
 *   "data": true
 * }
 * @apiErrorExample {json} Not-Found-Error-Response:
 * HTTP/1.1 422 Unprocessable Entity
 * {
 *   "success": false,
 *   "message": "Validation error",
 *   "errors": {
 *     "id": ["The selected id is invalid."]
 *   }
 * }
 * @apiErrorExample {json} Access-Denied-Error-Response:
 * HTTP/1.1 400 Bad Request
 * {
 *   "success": false,
 *   "message": "Cập nhật tin tức thất bại",
 *   "data": null
 * }
 * @apiErrorExample {json} Validation-Error-Response:
 * HTTP/1.1 422 Unprocessable Entity
 * {
 *   "success": false,
 *   "message": "Validation error",
 *   "errors": {
 *     "id": ["Id is required", "Id must be an integer"],
 *     "user_id": ["The selected user_id is invalid."]
 *   }
 * }
 */

/**
 * @api {delete} /api/v1/admin/news/:id Delete news
 * @apiName DeleteNews
 * @apiGroup News
 * @apiVersion 1.0.0
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Deletes a news item by ID. Users can only delete news they created unless they are admin. Also deletes associated image from Cloudinary.
 * @apiParam {Number} id News ID (required, path parameter)
 * @apiSuccess {Object} data Delete result (null)
 * @apiSampleRequest /api/v1/admin/news/1
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Xóa tin tức thành công",
 *   "data": null
 * }
 * @apiErrorExample {json} Not-Found-Error-Response:
 * HTTP/1.1 422 Unprocessable Entity
 * {
 *   "success": false,
 *   "message": "Validation error",
 *   "errors": {
 *     "id": ["The selected id is invalid."]
 *   }
 * }
 * @apiErrorExample {json} Access-Denied-Error-Response:
 * HTTP/1.1 400 Bad Request
 * {
 *   "success": false,
 *   "message": "Xóa tin tức thất bại",
 *   "data": null
 * }
 * @apiErrorExample {json} Validation-Error-Response:
 * HTTP/1.1 422 Unprocessable Entity
 * {
 *   "success": false,
 *   "message": "Validation error",
 *   "errors": {
 *     "id": ["Id is required", "Id must be an integer"]
 *   }
 * }
 */

