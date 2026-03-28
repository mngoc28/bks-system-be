/**
 * @api {get} /api/v1/admin/wards/{provinceId} Get Wards by Province ID
 * @apiName GetWardsByProvinceId
 * @apiGroup Wards
 * @apiVersion 1.0.0
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Returns list of wards by province id.
 * @apiParam {Number} provinceId Province ID
 * @apiSuccess {Object} data Wards by province id
 * @apiSampleRequest /api/v1/admin/wards/1
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Wards by province id retrieved successfully",
 *   "data": [
 *     {
 *       "id": 1,
 *       "name": "Phường 1",
 *       "province_id": 1
 *     }
 *   ]
 * }
 */
