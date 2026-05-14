/**
 * @api {post} /api/v1/admin/cloudinary/upload-image Upload Single Image
 * @apiName UploadSingleImage
 * @apiGroup Cloudinary
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Uploads a single image to Cloudinary.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam (Body) {File} image Image file to upload (required, max: 10MB, formats: JPEG, JPG, PNG, GIF, WEBP)
 * @apiParam (Body) {String} [folder=properties] Folder name in Cloudinary (optional, default: "properties")
 *
 * @apiParamExample {multipart/form-data} Request-Example:
 * image: [binary file]
 * folder: "properties"
 *
 * @apiSampleRequest /api/v1/admin/cloudinary/upload-image
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Object} data Response data
 * @apiSuccess {String} data.url Secure URL of uploaded image
 * @apiSuccess {String} data.public_id Public ID of uploaded image
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Image uploaded successfully",
 *   "data": {
 *     "url": "https://res.cloudinary.com/example/image/upload/v1234567890/properties/example.jpg",
 *     "public_id": "properties/example"
 *   }
 * }
 *
 * @apiError (400) {String} success Response status (false)
 * @apiError (400) {String} message Error message
 * @apiError (400) {Object} data Error data (null)
 *
 * @apiErrorExample {json} Error-Response (Invalid File):
 * HTTP/1.1 400 Bad Request
 * {
 *   "success": false,
 *   "message": "Error uploading image: File is invalid",
 *   "data": null
 * }
 *
 * @apiErrorExample {json} Error-Response (Invalid Format):
 * HTTP/1.1 400 Bad Request
 * {
 *   "success": false,
 *   "message": "Error uploading image: Invalid image format. Only accepts: JPEG, JPG, PNG, GIF, WEBP",
 *   "data": null
 * }
 *
 * @apiErrorExample {json} Error-Response (File Too Large):
 * HTTP/1.1 400 Bad Request
 * {
 *   "success": false,
 *   "message": "Error uploading image: Image size must not exceed 10MB",
 *   "data": null
 * }
 *
 * @apiError (422) {String} success Response status (false)
 * @apiError (422) {Object} message Validation errors
 * @apiError (422) {Object} data Error data (null)
 *
 * @apiErrorExample {json} Error-Response (Validation Error):
 * HTTP/1.1 422 Unprocessable Entity
 * {
 *   "success": false,
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "image": [
 *       "Please select an image to upload"
 *     ]
 *   },
 *   "data": null
 * }
 */

/**
 * @api {post} /api/v1/admin/cloudinary/upload-multiple-images Upload Multiple Images
 * @apiName UploadMultipleImages
 * @apiGroup Cloudinary
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Uploads multiple images (1-10) to Cloudinary.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam (Body) {File} images Image files to upload (required, min: 1, max: 10 files, each max: 10MB, formats: JPEG, JPG, PNG, GIF, WEBP). Use array format: images[] for multiple files.
 * @apiParam (Body) {String} [folder=properties] Folder name in Cloudinary (optional, default: "properties")
 *
 * @apiParamExample {multipart/form-data} Request-Example:
 * images[]: [binary file 1]
 * images[]: [binary file 2]
 * images[]: [binary file 3]
 * folder: "properties"
 *
 * @apiSampleRequest /api/v1/admin/cloudinary/upload-multiple-images
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Object} data Response data
 * @apiSuccess {Array} data.images Array of uploaded image objects
 * @apiSuccess {String} data.images[].url Secure URL of uploaded image
 * @apiSuccess {String} data.images[].public_id Public ID of uploaded image
 * @apiSuccess {Number} data.total Total number of successfully uploaded images
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Successfully uploaded 3 image(s)",
 *   "data": {
 *     "images": [
 *       {
 *         "url": "https://res.cloudinary.com/example/image/upload/v1234567890/properties/image1.jpg",
 *         "public_id": "properties/image1"
 *       },
 *       {
 *         "url": "https://res.cloudinary.com/example/image/upload/v1234567890/properties/image2.jpg",
 *         "public_id": "properties/image2"
 *       },
 *       {
 *         "url": "https://res.cloudinary.com/example/image/upload/v1234567890/properties/image3.jpg",
 *         "public_id": "properties/image3"
 *       }
 *     ],
 *     "total": 3
 *   }
 * }
 *
 * @apiError (400) {String} success Response status (false)
 * @apiError (400) {String} message Error message
 * @apiError (400) {Object} data Error data
 * @apiError (400) {Array} data.errors Array of error messages for failed uploads
 *
 * @apiErrorExample {json} Error-Response (No Images Uploaded):
 * HTTP/1.1 400 Bad Request
 * {
 *   "success": false,
 *   "message": "No images were uploaded successfully",
 *   "data": {
 *     "errors": [
 *       "Invalid image format. Only accepts: JPEG, JPG, PNG, GIF, WEBP",
 *       "Image size must not exceed 10MB"
 *     ]
 *   }
 * }
 *
 * @apiError (422) {String} success Response status (false)
 * @apiError (422) {Object} message Validation errors
 * @apiError (422) {Object} data Error data (null)
 *
 * @apiErrorExample {json} Error-Response (Validation Error):
 * HTTP/1.1 422 Unprocessable Entity
 * {
 *   "success": false,
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "images": [
 *       "Please select images to upload"
 *     ],
 *     "images.max": [
 *       "Maximum 10 images per upload"
 *     ]
 *   },
 *   "data": null
 * }
 */

/**
 * @api {delete} /api/v1/admin/cloudinary/delete-image Delete Image
 * @apiName DeleteImage
 * @apiGroup Cloudinary
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role. Deletes an image from Cloudinary using its public_id.
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam (Body) {String} public_id Public ID of the image to delete (required)
 *
 * @apiParamExample {json} Request-Example:
 * {
 *   "public_id": "properties/example"
 * }
 *
 * @apiSampleRequest /api/v1/admin/cloudinary/delete-image
 *
 * @apiSuccess {String} success Response status (true)
 * @apiSuccess {String} message Success message
 * @apiSuccess {Object} data Response data (null)
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "success": true,
 *   "message": "Image deleted successfully",
 *   "data": null
 * }
 *
 * @apiError (400) {String} success Response status (false)
 * @apiError (400) {String} message Error message
 * @apiError (400) {Object} data Error data (null)
 *
 * @apiErrorExample {json} Error-Response (Delete Failed):
 * HTTP/1.1 400 Bad Request
 * {
 *   "success": false,
 *   "message": "Error deleting image: Image not found",
 *   "data": null
 * }
 *
 * @apiError (422) {String} success Response status (false)
 * @apiError (422) {Object} message Validation errors
 * @apiError (422) {Object} data Error data (null)
 *
 * @apiErrorExample {json} Error-Response (Validation Error):
 * HTTP/1.1 422 Unprocessable Entity
 * {
 *   "success": false,
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "public_id": [
 *       "Public ID is required"
 *     ]
 *   },
 *   "data": null
 * }
 */

