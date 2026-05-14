/**
 * @api {get} /api/v1/admin/rooms/search Get Rooms (Pagination + Search)
 * @apiName GetAllOrSearchRooms
 * @apiGroup Rooms
 * @apiVersion 1.0.0
 *
 * @apiDescription Public endpoint - No authentication required. Returns paginated list of rooms with search functionality.
 *
 * @apiParam {String} [room_number]     Search by room number
 * @apiParam {Number} [property_id]  Search by property ID
 * @apiParam {Number} [price_min]     Minimum price filter
 * @apiParam {Number} [price_max]     Maximum price filter
 * @apiParam {Number} [area_min]     Minimum area filter
 * @apiParam {Number} [area_max]     Maximum area filter
 * @apiParam {String} [status]     Room status filter
 * @apiParam {String} [sort_by]     Sort by field (price, room_number, status, created_at)
 * @apiParam {String} [sort_order]     Sort order (asc, desc)
 * @apiParam {Number} [page=1]   Current page number
 * @apiParam {Number} [per_page=10] Number of records per page (default: 10)
 *
 *
 * @apiSampleRequest /api/v1/admin/rooms/search
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
 * {
 *   "status": "success",
 *   "message": "Rooms retrieved successfully",
 *   "data": {
 *     "current_page": 1,
 *     "data": [
 *       {
 *         "id": 6,
 *         "property_id": 30,
 *         "room_number": "R006",
 *         "price": "3468107.00",
 *         "area": 38.5,
 *         "status": "booked",
 *         "description": "Illum iusto ipsa voluptate id fugit qui ad sit voluptatem.",
 *         "image_url": "/images/rooms/room_6.jpg",
 *         "created_by": null,
 *         "updated_by": null,
 *         "created_at": "2025-10-10T09:37:03.000000Z",
 *         "updated_at": "2025-10-10T09:37:03.000000Z"
 *       }
 *     ],
 *     "first_page_url": "http://localhost:8000/api/v1/rooms/search?page=1",
 *     "from": 1,
 *     "last_page": 10,
 *     "last_page_url": "http://localhost:8000/api/v1/rooms/search?page=10",
 *     "links": [
 *       {
 *         "url": null,
 *         "label": "&laquo; Previous",
 *         "active": false
 *       },
 *       {
 *         "url": "http://localhost:8000/api/v1/rooms/search?page=1",
 *         "label": "1",
 *         "active": true
 *       },
 *       {
 *         "url": "http://localhost:8000/api/v1/rooms/search?page=2",
 *         "label": "2",
 *         "active": false
 *       },
 *       {
 *         "url": "http://localhost:8000/api/v1/rooms/search?page=3",
 *         "label": "3",
 *         "active": false
 *       },
 *       {
 *         "url": "http://localhost:8000/api/v1/rooms/search?page=4",
 *         "label": "4",
 *         "active": false
 *       },
 *       {
 *         "url": "http://localhost:8000/api/v1/rooms/search?page=5",
 *         "label": "5",
 *         "active": false
 *       },
 *       {
 *         "url": "http://localhost:8000/api/v1/rooms/search?page=6",
 *         "label": "6",
 *         "active": false
 *       },
 *       {
 *         "url": "http://localhost:8000/api/v1/rooms/search?page=7",
 *         "label": "7",
 *         "active": false
 *       },
 *       {
 *         "url": "http://localhost:8000/api/v1/rooms/search?page=8",
 *         "label": "8",
 *         "active": false
 *       },
 *       {
 *         "url": "http://localhost:8000/api/v1/rooms/search?page=9",
 *         "label": "9",
 *         "active": false
 *       },
 *       {
 *         "url": "http://localhost:8000/api/v1/rooms/search?page=10",
 *         "label": "10",
 *         "active": false
 *       },
 *       {
 *         "url": "http://localhost:8000/api/v1/rooms/search?page=2",
 *         "label": "Next &raquo;",
 *         "active": false
 *       }
 *     ],
 *     "next_page_url": "http://localhost:8000/api/v1/rooms/search?page=2",
 *     "path": "http://localhost:8000/api/v1/rooms/search",
 *     "per_page": 10,
 *     "prev_page_url": null,
 *     "to": 10,
 *     "total": 100
 *   }
 * }
 */

/**
 * @api {get} /api/v1/admin/rooms/:id Get Room Details
 * @apiName GetRoomById
 * @apiGroup Rooms
 * @apiVersion 1.0.0
 *
 * @apiDescription Public endpoint - No authentication required
 *
 *
 * @apiSampleRequest /api/v1/admin/rooms/1
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
{
    "status": "success",
    "message": "Room found successfully",
    "data": {
        "id": 2,
        "property_id": 9,
        "room_number": "R002",
        "price": "4418507.00",
        "area": 41.5,
        "status": "booked",
        "description": "Alias quia ut praesentium molestiae cumque repudiandae quisquam et et.",
        "image_url": "/images/rooms/room_2.jpg",
        "created_by": null,
        "updated_by": null,
        "created_at": "2025-10-10T09:43:53.000000Z",
        "updated_at": "2025-10-10T09:43:53.000000Z",
        "room_services": [
            {
                "id": 44,
                "room_id": 2,
                "service_id": 3,
                "is_included": true,
                "created_by": null,
                "updated_by": null,
                "created_at": "2025-10-28T10:41:00.000000Z",
                "updated_at": "2025-10-28T10:41:00.000000Z",
                "service": {
                    "id": 3,
                    "name": "Minus",
                    "type": "other",
                    "price": "63639.15",
                    "description": "Adipisci ullam consectetur nulla laborum.",
                    "created_at": "2025-10-28T10:39:01.000000Z",
                    "updated_at": "2025-10-28T10:39:01.000000Z",
                    "created_by": null,
                    "updated_by": null
                }
            },
            {
                "id": 50,
                "room_id": 2,
                "service_id": 9,
                "is_included": false,
                "created_by": null,
                "updated_by": null,
                "created_at": "2025-10-28T10:41:00.000000Z",
                "updated_at": "2025-10-28T10:41:00.000000Z",
                "service": {
                    "id": 9,
                    "name": "Aliquid",
                    "type": "other",
                    "price": "119706.14",
                    "description": "Consequatur qui voluptatem quas aut.",
                    "created_at": "2025-10-28T10:39:01.000000Z",
                    "updated_at": "2025-10-28T10:39:01.000000Z",
                    "created_by": null,
                    "updated_by": null
                }
            },
            {
                "id": 1,
                "room_id": 2,
                "service_id": 14,
                "is_included": false,
                "created_by": null,
                "updated_by": null,
                "created_at": "2025-10-28T10:41:00.000000Z",
                "updated_at": "2025-10-28T10:41:00.000000Z",
                "service": {
                    "id": 14,
                    "name": "Et",
                    "type": "other",
                    "price": "155951.77",
                    "description": "Aut in repudiandae eaque.",
                    "created_at": "2025-10-28T10:39:01.000000Z",
                    "updated_at": "2025-10-28T10:39:01.000000Z",
                    "created_by": null,
                    "updated_by": null
                }
            }
        ]
    }
}
 */

/**
 * @api {post} /api/v1/admin/rooms/store Create a New Room
 * @apiName CreateRoom
 * @apiGroup Rooms
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 * @apiParam {String{1..50}} room_number Room number
 * @apiParam {Number} price Room price
 * @apiParam {Number} area Room area (in square meters)
 * @apiParam {String} [status] Room status (available, occupied, maintenance, booked)
 * @apiParam {String} [description] Room description
 * @apiParam {String} [image_url] Room image URL
 * @apiParam {Number} property_id Property ID
 * @apiParam {Number} [created_by] Creator ID
 * @apiParam {Array} [service_ids] List of services to attach to the room
 *
 * @apiSampleRequest /api/v1/admin/rooms/store
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 201 Success
 * {
 *   "status": "success",
 *   "message": "Room created successfully",
 *   "data": {
 *       "property_id": "5",
 *       "room_number": "R200",
 *       "price": "3400000.00",
 *       "area": 34.5,
 *       "status": "available",
 *       "description": "chuynnnnnnnnnnnnnnnnnnnnnnnnn",
 *       "updated_at": "2025-10-24T08:57:53.000000Z",
 *       "created_at": "2025-10-24T08:57:53.000000Z",
 *       "id": 102
 *   }
 * }
 */

/**
 * @api {put} /api/v1/admin/rooms/:id Update a Room
 * @apiName UpdateRoom
 * @apiGroup Rooms
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 *
 * @apiParam {String} [room_number] Room number
 * @apiParam {Number} [price] Room price
 * @apiParam {Number} [area] Room area (in square meters)
 * @apiParam {String} [status] Room status (available, occupied, maintenance, booked)
 * @apiParam {String} [description] Room description
 * @apiParam {String} [image_url] Room image URL
 * @apiParam {Number} [property_id] Property ID
 * @apiParam {Number} [updated_by] Updater ID
 * @apiParam {Array} [service_ids] List of services to attach to the room
 *
 * @apiSampleRequest /api/v1/admin/rooms/1
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
 * {
 *   "status": "success",
 *   "message": "Room updated successfully",
 *   "data": true
 * }
 */

/**
 * @api {delete} /api/v1/admin/rooms/:id Delete a Room
 * @apiName DeleteRoom
 * @apiGroup Rooms
 * @apiVersion 1.0.0
 *
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 *
 *
 * @apiSampleRequest /api/v1/admin/rooms/1
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
 * {
 *   "status": "success",
 *   "message": "Room deleted successfully",
 *   "data": null
 * }
 */
