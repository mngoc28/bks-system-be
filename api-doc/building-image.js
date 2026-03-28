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
 * @apiParam {Number} buildingId Building ID (required, must exist in buildings table)
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
 * @apiParam {Number} id Building image ID (required, must exist in building_images table)
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
 * @apiParam {Number} id Building image ID (required, must exist in building_images table)
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
 * @apiParam {Number} id Building image ID (required, must exist in building_images table)
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
 * @api {post} /api/v1/admin/building-images/sort Sort Building Images
 * @apiName SortBuildingImages
 * @apiGroup BuildingImages
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Sorts building images.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam (Body) {Array} ids Array of building image IDs (required, must exist in building_images table)
 *
 * @apiParamExample {json} Request-Example:
 * {
 *   "ids": [1, 2, 3]
 * }
 *
 * @apiSampleRequest /api/v1/admin/building-images/sort/1
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Boolean} data Sort result (true)
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Building images sorted successfully",
 *   "data": true
 * }
 */
