/**
 * @api {post} /api/v1/admin/auth/login User Login
 * @apiName LoginUser
 * @apiGroup Auth
 * @apiVersion 1.0.0
 *
 * @apiParam {String} email User email
 * @apiParam {String} password User password
 *
 * @apiSampleRequest /api/v1/admin/auth/login
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
 * {
 *   "status": "success",
 *   "message": "Login successful.",
 *   "data": {
 *     "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYXBpL3YxL2xvZ2luIiwiaWF0IjoxNzYxMjg5MzA4LCJleHAiOjE3NjEyOTI5MDgsIm5iZiI6MTc2MTI4OTMwOCwianRpIjoidmhGWlZ2a29STERyekhFNiIsInN1YiI6IjEzMiIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.DwgqsHTkvTQDKFQ4AGX6vWEQ22SpKGiII_76HUrNwpw",
 *     "user": {
 *       "id": 132,
 *       "name": "admin",
 *       "email": "admin@gmail.com",
 *       "role": "admin",
 *       "phone": "099999991"
 *     }
 *   }
 * }
 */

/**
 * @api {post} /api/v1/admin/auth/register User Register
 * @apiName RegisterUser
 * @apiGroup Auth
 * @apiVersion 1.0.0
 *
 * @apiParam {String} name User name
 * @apiParam {String} email User email
 * @apiParam {String} password User password
 * @apiParam {String} password_confirmation Password confirmation
 * @apiParam {String} [phone] User phone
 *
 * @apiSampleRequest /api/v1/admin/auth/register
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 201 Success
 * {
 *   "status": "success",
 *   "message": "Register successful.",
 *   "data": {
 *     "id": 125,
 *     "name": "good boy",
 *     "email": "goodboy@gmail.com",
 *     "role": "user",
 *     "phone": "0987654321"
 *   }
 * }
 */

/**
 * @api {post} /api/v1/admin/auth/logout User Logout
 * @apiName LogoutUser
 * @apiGroup Auth
 * @apiVersion 1.0.0
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiDescription Protected endpoint - Requires authentication
 *
 * @apiSampleRequest /api/v1/admin/auth/logout
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
 * {
 *   "status": "success",
 *   "message": "Logout successful.",
 *   "data": null
 * }
 */
