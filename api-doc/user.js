/**
 * @api {get} /api/v1/admin/user/profile Get user profile
 * @apiName GetUserProfile
 * @apiGroup User
 * @apiVersion 1.0.0
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiDescription Protected endpoint - Requires authentication
 * @apiSuccess {Object} data User profile information
 * @apiSampleRequest /api/v1/admin/user/profile
 * @apiSuccessExample {json} Success-Response:
 *   {
 *     "status": "success",
 *     "message": "User retrieved successfully.",
 *     "data": {
 *       "id": 132,
 *       "name": "admin",
 *       "email": "admin@gmail.com",
 *       "role": "admin",
 *       "phone": "099999991"
 *     }
 *   }
 */
/**
 * @api {put} /api/v1/admin/user/profile Update user profile
 * @apiName UpdateUserProfile
 * @apiGroup User
 * @apiVersion 1.0.0
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiDescription Protected endpoint - Requires authentication
 * @apiParam {String} name User name
 * @apiParam {String} email User email
 * @apiParam {String} [phone] User phone
 * @apiSuccess {Object} data Updated user profile information
 * @apiSampleRequest /api/v1/admin/user/profile
 * @apiSuccessExample {json} Success-Response:
 *   {
 *     "status": "success",
 *     "message": "User updated successfully.",
 *     "data": {
 *       "id": 132,
 *       "name": "admin",
 *       "email": "admin@gmail.com",
 *       "role": "admin",
 *       "phone": "099999991"
 *     }
 *   }
 */
/**
 * @api {put} /api/v1/admin/user/profile/change-password Change user password
 * @apiName ChangeUserPassword
 * @apiGroup User
 * @apiVersion 1.0.0
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiDescription Protected endpoint - Requires authentication
 * @apiParam {String} current_password Current password
 * @apiParam {String} new_password New password
 * @apiParam {String} new_password_confirmation New password confirmation
 * @apiSuccess {Object} data Change password information
 * @apiSampleRequest /api/v1/admin/user/profile/change-password
 * @apiSuccessExample {json} Success-Response:
 *   {
 *     "status": "success",
 *     "message": "Password changed successfully.",
 *     "data": null
 *   }
 */
/**
 * @api {get} /api/v1/admin/users Get user list
 * @apiName GetAllUsers
 * @apiGroup Admin
 * @apiVersion 1.0.0
 * @apiHeader {String} Authorization Bearer token
 * @apiSampleRequest /api/v1/admin/users
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "success": true,
 *       "data": [...],
 *       "message": "Get user list successfully"
 *     }
 */

/**
 * @api {delete} /api/v1/admin/users/:id Delete user
 * @apiName DeleteUser
 * @apiGroup Admin
 * @apiVersion 1.0.0
 * @apiHeader {String} Authorization Bearer token
 * @apiSuccess {Object} data User information
 * @apiSampleRequest /api/v1/admin/users/:id
 * @apiSuccessExample {json} Success-Response:
 *   {
 *     "status": "success",
 *     "message": "User deleted successfully.",
 *     "data": null
 *   }
 */

/**
 * @api {post} /api/v1/admin/users/create Create new user
 * @apiName CreateUser
 * @apiGroup Admin
 * @apiVersion 1.0.0
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiDescription Protected endpoint - Requires authentication
 * @apiParam {String} name Name
 * @apiParam {String} email Email
 * @apiParam {String} phone Phone number
 * @apiParam {String} password Password
 * @apiParam {String} password_confirmation Password confirmation
 * @apiSuccess {Object} data Created user information
 * @apiSampleRequest /api/v1/admin/users/create
 * @apiSuccessExample {json} Success-Response:
 * {
 *   "status": "success",
 *   "message": "User created successfully.",
 *   "data": {
 *       "id": 134,
 *       "name": "user2",
 *       "email": "user2@gmail.com",
 *       "role": "user",
 *       "phone": "0987654321"
 *     }
 *   }
 */
/**
 * @api {put} /api/v1/admin/users/:id Update user
 * @apiName UpdateUser
 * @apiGroup Admin
 * @apiVersion 1.0.0
 * @apiHeader {String} Authorization Bearer token
 * @apiParam {String} [name] Name
 * @apiParam {String} [email] Email
 * @apiParam {String} [phone] Phone number
 * @apiSuccess {Object} data Updated user information
 * @apiSampleRequest /api/v1/admin/users/:id
 * @apiSuccessExample {json} Success-Response:
 * {
 *   "status": "success",
 *   "message": "User updated successfully.",
 *   "data": {
 *       "id": 134,
 *       "name": "user2",
 *       "email": "user2@gmail.com",
 *       "role": "user",
 *       "phone": "0987654321"
 *     }
 *   }
 */
