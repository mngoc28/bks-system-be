/**
 * @api {get} /api/v1/admin/coupons Coupon list
 * @apiName CouponList
 * @apiGroup Coupon
 * @apiPermission AuthenticatedUser
 *
 * @apiHeader {String} Authorization Bearer token
 *
 * @apiParam (Query) {Number} [pagination] Items per page.
 * @apiParam (Query) {String} [sort_by] Sort field.
 * @apiParam (Query) {String="asc","desc"} [direction] Sort direction.
 *
 * @apiSampleRequest /api/v1/admin/coupons
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "status": "success",
 *   "message": "Coupons retrieved successfully.",
 *   "data": {
 *     "data": [
 *       {
 *         "id": 1,
 *         "code": "SUMMER50",
 *         "type": "percent",
 *         "value": 50,
 *         "min_order_value": 200000,
 *         "max_discount_value": 500000,
 *         "usage_limit": 100,
 *         "used_count": 0,
 *         "start_date": "2025-06-01T00:00:00",
 *         "end_date": "2025-08-31T23:59:59",
 *         "status": "active",
 *         "created_at": "2025-12-01T12:00:00",
 *         "updated_at": "2025-12-01T12:00:00"
 *       }
 *     ],
 *     "meta": {
 *       "current_page": 1,
 *       "per_page": 10,
 *       "total": 1
 *     }
 *   }
 * }
 */

/**
 * @api {post} /api/v1/admin/coupons/create Coupon create
 * @apiName CouponCreate
 * @apiGroup Coupon
 * @apiPermission AuthenticatedUser
 *
 * @apiHeader {String} Authorization Bearer token
 *
 * @apiBody {String{..50}} code Coupon code (*).
 * @apiBody {String="percent","fixed"} type Discount type (*).
 * @apiBody {Number} value Discount value (*).
 * @apiBody {Number} [min_order_value] Minimum order value.
 * @apiBody {Number} [max_discount_value] Maximum discount value.
 * @apiBody {Number} [usage_limit] Usage limit.
 * @apiBody {String} [start_date] Start date (Y-m-d H:i:s).
 * @apiBody {String} [end_date] End date (Y-m-d H:i:s).
 * @apiBody {String="active","inactive"} status Status (*).
 *
 * @apiParamExample {json} Request-Example:
 * {
 *   "code": "WELCOME2026",
 *   "type": "percent",
 *   "value": 20,
 *   "min_order_value": 500000,
 *   "max_discount_value": 200000,
 *   "usage_limit": 100,
 *   "start_date": "2026-01-01 00:00:00",
 *   "end_date": "2026-06-30 23:59:59",
 *   "status": "active"
 * }
 *
 * @apiSampleRequest /api/v1/admin/coupons/create
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "status": "success",
 *   "message": "Coupon created successfully.",
 *   "data": {
 *     "id": 3,
 *     "code": "WELCOME2026",
 *     "type": "percent",
 *     "value": 20,
 *     "min_order_value": 500000,
 *     "max_discount_value": 200000,
 *     "usage_limit": 100,
 *     "used_count": 0,
 *     "start_date": "2026-01-01T00:00:00",
 *     "end_date": "2026-06-30T23:59:59",
 *     "status": "active",
 *     "created_at": "2025-12-04T12:00:00",
 *     "updated_at": "2025-12-04T12:00:00"
 *   }
 * }
 */

/**
 * @api {put} /api/v1/admin/coupons/update/:id Coupon update
 * @apiName CouponUpdate
 * @apiGroup Coupon
 * @apiPermission AuthenticatedUser
 *
 * @apiHeader {String} Authorization Bearer token
 *
 * @apiParam (Path) {Number} id Coupon ID (*).
 *
 * @apiBody {String{..50}} [code] Coupon code.
 * @apiBody {String="percent","fixed"} [type] Discount type.
 * @apiBody {Number} [value] Discount value.
 * @apiBody {Number} [min_order_value] Minimum order value.
 * @apiBody {Number} [max_discount_value] Maximum discount value.
 * @apiBody {Number} [usage_limit] Usage limit.
 * @apiBody {String} [start_date] Start date (Y-m-d H:i:s).
 * @apiBody {String} [end_date] End date (Y-m-d H:i:s).
 * @apiBody {String="active","inactive"} [status] Status.
 *
 * @apiParamExample {json} Request-Example:
 * {
 *   "value": 25,
 *   "status": "inactive"
 * }
 *
 * @apiSampleRequest /api/v1/admin/coupons/update/3
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "status": "success",
 *   "message": "Coupon updated successfully.",
 *   "data": {
 *     "id": 3,
 *     "code": "WELCOME2026",
 *     "type": "percent",
 *     "value": 25,
 *     "min_order_value": 500000,
 *     "max_discount_value": 200000,
 *     "usage_limit": 100,
 *     "used_count": 0,
 *     "start_date": "2026-01-01T00:00:00",
 *     "end_date": "2026-06-30T23:59:59",
 *     "status": "inactive",
 *     "created_at": "2025-12-04T12:00:00",
 *     "updated_at": "2025-12-05T09:00:00"
 *   }
 * }
 */

/**
 * @api {delete} /api/v1/admin/coupons/delete/:id Coupon delete
 * @apiName CouponDelete
 * @apiGroup Coupon
 * @apiPermission AuthenticatedUser
 *
 * @apiHeader {String} Authorization Bearer token
 *
 * @apiParam (Path) {Number} id Coupon ID (*).
 *
 * @apiSampleRequest /api/v1/admin/coupons/delete/3
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "status": "success",
 *   "message": "Coupon deleted successfully.",
 *   "data": null
 * }
 */
