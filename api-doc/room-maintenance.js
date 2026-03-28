/**
 * @api {get} /api/v1/admin/room-maintenances List room maintenance records
 * @apiName ListRoomMaintenances
 * @apiGroup RoomMaintenance
 * @apiPermission AuthenticatedAdmin
 *
 * @apiHeader {String} Authorization Bearer token
 *
 * @apiQuery {Number} [room_id] Filter by room ID
 * @apiQuery {Number} [property_id] Filter by property ID
 * @apiQuery {String="planned","in_progress","completed","cancelled"} [status] Filter by maintenance status
 * @apiQuery {String="scheduled","emergency"} [maintenance_type] Filter by maintenance type
 * @apiQuery {String} [from_date] Filter start date (YYYY-MM-DD)
 * @apiQuery {String} [to_date] Filter end date (YYYY-MM-DD)
 * @apiQuery {Number} [pagination] Items per page (enable pagination)
 *
 * @apiSampleRequest /api/v1/admin/room-maintenances
 */

/**
 * @api {post} /api/v1/admin/room-maintenances Create room maintenance
 * @apiName CreateRoomMaintenance
 * @apiGroup RoomMaintenance
 * @apiPermission AuthenticatedAdmin
 *
 * @apiHeader {String} Authorization Bearer token
 *
 * @apiBody {Number} room_id Room ID (* Required)
 * @apiBody {String} title Maintenance title (* Required)
 * @apiBody {String} [description] Maintenance description
 * @apiBody {String="scheduled","emergency"} maintenance_type Maintenance type (* Required)
 * @apiBody {String} start_time Start time (ISO 8601) (* Required)
 * @apiBody {String} [end_time] End time (ISO 8601)
 *
 * @apiSampleRequest /api/v1/admin/room-maintenances
 */
