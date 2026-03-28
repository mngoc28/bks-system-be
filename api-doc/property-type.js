/**
 * @api {get} /api/v1/admin/property-types Property types list
 * @apiName AdminPropertyTypesList
 * @apiGroup PropertyType
 * @apiPermission AuthenticatedUser
 *
 * @apiHeader {String} Authorization Bearer token
 *
 * @apiParam (Query) {Number{1..}} [pagination] Items per page.
 *
 * @apiSampleRequest /api/v1/admin/property-types
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
 * {
 *   "status": "success",
 *   "message": "Fetch property types successfully.",
 *   "data": {
 *     "current_page": 1,
 *     "data": [
 *       {
 *         "id": 8,
 *         "name": "Resort",
 *         "slug": "resort",
 *         "description": "Full-service resort property featuring on-site dining, spa, and recreational facilities.",
 *         "icon_url": null,
 *         "is_active": true,
 *         "created_at": "2025-12-08T10:48:15.000000Z",
 *         "updated_at": "2025-12-08T10:48:15.000000Z"
 *       }
 *     ],
 *     "first_page_url": "http://127.0.0.1:8000/api/v1/admin/property-types?page=1",
 *     "from": 1,
 *     "last_page": 2,
 *     "last_page_url": "http://127.0.0.1:8000/api/v1/admin/property-types?page=2",
 *     "links": [
 *       { "url": null, "label": "&laquo; Previous", "active": false },
 *       { "url": "http://127.0.0.1:8000/api/v1/admin/property-types?page=1", "label": "1", "active": true },
 *       { "url": "http://127.0.0.1:8000/api/v1/admin/property-types?page=2", "label": "2", "active": false },
 *       { "url": "http://127.0.0.1:8000/api/v1/admin/property-types?page=2", "label": "Next &raquo;", "active": false }
 *     ],
 *     "next_page_url": "http://127.0.0.1:8000/api/v1/admin/property-types?page=2",
 *     "path": "http://127.0.0.1:8000/api/v1/admin/property-types",
 *     "per_page": 5,
 *     "prev_page_url": null,
 *     "to": 5,
 *     "total": 8
 *   }
 * }
 */

/**
 * @api {post} /api/v1/admin/property-types Create property type
 * @apiName AdminPropertyTypeCreate
 * @apiGroup PropertyType
 * @apiPermission AuthenticatedUser
 *
 * @apiHeader {String} Authorization Bearer token
 *
 * @apiBody {String} name Property type name (*).
 * @apiBody {String} [slug] Custom slug.
 * @apiBody {String} [description] Description.
 * @apiBody {String} [icon_url] Icon URL.
 * @apiBody {Boolean} is_active Active status (*).
 *
 * @apiSampleRequest /api/v1/admin/property-types
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 201 Created
 * {
 *   "status": "success",
 *   "message": "Create property type successfully.",
 *   "data": {
 *     "id": 11,
 *     "name": "Villa",
 *     "slug": "villa",
 *     "description": "Spacious standalone property ideal for families or group getaways.",
 *     "icon_url": "https://cdn.example.com/icons/villa.svg",
 *     "is_active": true,
 *     "created_at": "2025-12-09T07:32:18.000000Z",
 *     "updated_at": "2025-12-09T07:32:18.000000Z"
 *   }
 * }
 */

/**
 * @api {get} /api/v1/admin/property-types/:id Property type detail
 * @apiName AdminPropertyTypeDetail
 * @apiGroup PropertyType
 * @apiPermission AuthenticatedUser
 *
 * @apiHeader {String} Authorization Bearer token
 *
 * @apiParam (Path) {Number} id Property type identifier.
 *
 * @apiSampleRequest /api/v1/admin/property-types/1
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
 * {
 *   "status": "success",
 *   "message": "Fetch property types successfully.",
 *   "data": {
 *     "id": 1,
 *     "name": "Apartment",
 *     "slug": "apartment",
 *     "description": "Modern high-rise apartment with full facilities and elevator access, suitable for long-term stays.",
 *     "icon_url": null,
 *     "is_active": true,
 *     "created_at": "2025-12-08T10:48:15.000000Z",
 *     "updated_at": "2025-12-08T10:48:15.000000Z"
 *   }
 * }
 */

/**
 * @api {put} /api/v1/admin/property-types/:id Update property type
 * @apiName AdminPropertyTypeUpdate
 * @apiGroup PropertyType
 * @apiPermission AuthenticatedUser
 *
 * @apiHeader {String} Authorization Bearer token
 *
 * @apiParam (Path) {Number} id Property type identifier.
 *
 * @apiBody {String} name Property type name (*).
 * @apiBody {String} [slug] Custom slug.
 * @apiBody {String} [description] Description.
 * @apiBody {String} [icon_url] Icon URL.
 * @apiBody {Boolean} is_active Active status (*).
 *
 * @apiSampleRequest /api/v1/admin/property-types/1
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
 * {
 *   "status": "success",
 *   "message": "Update property type successfully.",
 *   "data": {
 *     "id": 1,
 *     "name": "Apartment",
 *     "slug": "apartment",
 *     "description": "Modern high-rise apartment with full facilities and elevator access, suitable for long-term stays.",
 *     "icon_url": null,
 *     "is_active": true,
 *     "created_at": "2025-12-08T10:48:15.000000Z",
 *     "updated_at": "2025-12-09T11:03:22.000000Z"
 *   }
 * }
 */

/**
 * @api {patch} /api/v1/admin/property-types/:id/status Update property type status
 * @apiName AdminPropertyTypeUpdateStatus
 * @apiGroup PropertyType
 * @apiPermission AuthenticatedUser
 *
 * @apiHeader {String} Authorization Bearer token
 *
 * @apiParam (Path) {Number} id Property type identifier.
 *
 * @apiBody {Boolean} is_active Active status (*).
 *
 * @apiSampleRequest /api/v1/admin/property-types/1/status
 *
 * @apiSuccessExample {json} Success-Response (Activate):
 * HTTP/1.1 200 Success
 * {
 *   "status": "success",
 *   "message": "Property type activated successfully.",
 *   "data": {
 *     "id": 1,
 *     "name": "Apartment",
 *     "slug": "apartment",
 *     "description": "Modern high-rise apartment with full facilities and elevator access, suitable for long-term stays.",
 *     "icon_url": null,
 *     "is_active": true,
 *     "created_at": "2025-12-08T10:48:15.000000Z",
 *     "updated_at": "2025-12-09T15:45:00.000000Z"
 *   }
 * }
 *
 * @apiSuccessExample {json} Success-Response (Deactivate):
 * HTTP/1.1 200 Success
 * {
 *   "status": "success",
 *   "message": "Property type deactivated successfully.",
 *   "data": {
 *     "id": 1,
 *     "name": "Apartment",
 *     "slug": "apartment",
 *     "description": "Modern high-rise apartment with full facilities and elevator access, suitable for long-term stays.",
 *     "icon_url": null,
 *     "is_active": false,
 *     "created_at": "2025-12-08T10:48:15.000000Z",
 *     "updated_at": "2025-12-09T16:10:00.000000Z"
 *   }
 * }
 */
