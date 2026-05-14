/**
 * @api {get} /api/v1/admin/property-images/property/:propertyId Get Images by Property ID
 * @apiName GetImagesByPropertyId
 * @apiGroup PropertyImages
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Returns all images for a specific property, ordered by sort field.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam {Number} propertyId Property ID (required, must exist in properties table)
 *
 * @apiSampleRequest /api/v1/admin/property-images/property/1
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Array} data Array of property images
 * @apiSuccess {Number} data[].id Property image ID
 * @apiSuccess {Number} data[].property_id Property ID
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
 *   "message": "Property images retrieved successfully",
 *   "data": [
 *     {
 *       "id": 1,
 *       "property_id": 1,
 *       "image_url": "https://res.cloudinary.com/example/image/upload/v1234567890/properties/image1.jpg",
 *       "id_image_cloudinary": "properties/image1",
 *       "image_type": 0,
 *       "sort": 1,
 *       "created_by": 1,
 *       "updated_by": 1,
 *       "created_at": "2025-11-24T10:00:00.000000Z",
 *       "updated_at": "2025-11-24T10:00:00.000000Z"
 *     },
 *     {
 *       "id": 2,
 *       "property_id": 1,
 *       "image_url": "https://res.cloudinary.com/example/image/upload/v1234567890/properties/image2.jpg",
 *       "id_image_cloudinary": "properties/image2",
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
 *     "property_id": [
 *       "Property ID is required",
 *       "The selected property id does not exist."
 *     ]
 *   },
 *   "data": null
 * }
 */

/**
 * @api {get} /api/v1/admin/property-images/:id Get Property Image by ID
 * @apiName GetPropertyImageById
 * @apiGroup PropertyImages
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Returns a specific property image.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam {Number} id Property image ID (required, must exist in property_images table)
 *
 * @apiSampleRequest /api/v1/admin/property-images/1
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Object} data Property image data
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Property image retrieved successfully",
 *   "data": {
 *     "id": 1,
 *     "property_id": 1,
 *     "image_url": "https://res.cloudinary.com/example/image/upload/v1234567890/properties/image1.jpg",
 *     "id_image_cloudinary": "properties/image1",
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
 *   "message": "Property image not found",
 *   "data": null
 * }
 */

/**
 * @api {post} /api/v1/admin/property-images Create Property Image
 * @apiName CreatePropertyImage
 * @apiGroup PropertyImages
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Creates a new property image. Sort will be automatically set to max(sort) + 1 for the property.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam (Body) {Number} property_id Property ID (required, must exist in properties table)
 * @apiParam (Body) {String} image_url Image URL (required, max: 255)
 * @apiParam (Body) {String} id_image_cloudinary Cloudinary image ID (required, max: 255)
 * @apiParam (Body) {Number} image_type Image type (required, integer, 0: other, 1: exterior, 2: interior, 3: bathroom, 4: kitchen)
 *
 * @apiParamExample {json} Request-Example:
 * {
 *   "property_id": 1,
 *   "image_url": "https://res.cloudinary.com/example/image/upload/v1234567890/properties/image1.jpg",
 *   "id_image_cloudinary": "properties/image1",
 *   "image_type": 0
 * }
 *
 * @apiSampleRequest /api/v1/admin/property-images
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Object} data Created property image data (sort is automatically set to max(sort) + 1)
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Property image created successfully",
 *   "data": {
 *     "id": 1,
 *     "property_id": 1,
 *     "image_url": "https://res.cloudinary.com/example/image/upload/v1234567890/properties/image1.jpg",
 *     "id_image_cloudinary": "properties/image1",
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
 *     "property_id": [
 *       "Property ID is required",
 *       "The selected property id does not exist."
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
 * @api {put} /api/v1/admin/property-images/:id Update Property Image
 * @apiName UpdatePropertyImage
 * @apiGroup PropertyImages
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Updates a property image. Property ID and sort will remain unchanged.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam {Number} id Property image ID (required, must exist in property_images table)
 * @apiParam (Body) {String} image_url Image URL (required, max: 255)
 * @apiParam (Body) {String} id_image_cloudinary Cloudinary image ID (required, max: 255)
 * @apiParam (Body) {Number} image_type Image type (required, integer, 0: other, 1: exterior, 2: interior, 3: bathroom, 4: kitchen)
 *
 * @apiParamExample {json} Request-Example:
 * {
 *   "image_url": "https://res.cloudinary.com/example/image/upload/v1234567890/properties/image1_updated.jpg",
 *   "id_image_cloudinary": "properties/image1_updated",
 *   "image_type": 1
 * }
 *
 * @apiSampleRequest /api/v1/admin/property-images/1
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Boolean} data Update result (true, property_id and sort remain unchanged)
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Property image updated successfully",
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
 *   "message": "Property image not found",
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
 *       "Property image ID is required",
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
 * @api {delete} /api/v1/admin/property-images/:id Delete Property Image
 * @apiName DeletePropertyImage
 * @apiGroup PropertyImages
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Deletes a property image.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam {Number} id Property image ID (required, must exist in property_images table)
 *
 * @apiSampleRequest /api/v1/admin/property-images/1
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Boolean} data Delete result (true)
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Property image deleted successfully",
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
 *   "message": "Property image not found",
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
 *       "Property image ID is required",
 *       "The selected id does not exist."
 *     ]
 *   },
 *   "data": null
 * }
 */

/**
 * @api {post} /api/v1/admin/property-images/sort Sort Property Images
 * @apiName SortPropertyImages
 * @apiGroup PropertyImages
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Sorts property images.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam (Body) {Array} ids Array of property image IDs (required, must exist in property_images table)
 *
 * @apiParamExample {json} Request-Example:
 * {
 *   "ids": [1, 2, 3]
 * }
 *
 * @apiSampleRequest /api/v1/admin/property-images/sort/1
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Boolean} data Sort result (true)
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Property images sorted successfully",
 *   "data": true
 * }
 */
