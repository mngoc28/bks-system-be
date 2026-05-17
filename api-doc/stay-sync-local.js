/**
 * @api {post} /api/v1/stay/bookings/sync-local Stay — Merge local booking drafts (T6)
 * @apiName StayBookingsSyncLocal
 * @apiGroup StayBooking
 * @apiVersion 1.0.0
 *
 * @apiDescription Requires JWT (`Authorization: Bearer`) for a **user** (guest) account. Idempotent per `client_fingerprint` (and per stay slot: same user + room + dates links an existing server row created earlier without fingerprint). Atomic: validation or business failure on any item rolls back the whole batch.
 *
 * @apiHeader {String} Authorization JWT bearer token
 *
 * @apiParam (Body) {Object[]} items List of local rows (max 50)
 * @apiParam (Body) {String} items.local_id Client-generated id (e.g. UUID), max 64 chars
 * @apiParam (Body) {String} items.fingerprint Lowercase hex SHA-256 (64 chars) of `room_id|YYYY-MM-DD|YYYY-MM-DD|normalized_email`
 * @apiParam (Body) {Number} items.room_id Room id
 * @apiParam (Body) {String} items.start_date Check-in date (Y-m-d)
 * @apiParam (Body) {String} items.end_date Check-out date (Y-m-d)
 * @apiParam (Body) {String} [items.email] If set, must equal the authenticated user email
 * @apiParam (Body) {Number} [items.price_id] Optional `room_prices.id`; when missing or invalid, server picks default package price for the room
 *
 * @apiSuccess {String} status success
 * @apiSuccess {Object} data
 * @apiSuccess {Object[]} data.mapped Per input item order
 * @apiSuccess {String} data.mapped.local_id Echo of client `local_id`
 * @apiSuccess {Number} data.mapped.server_booking_id Booking id on server
 * @apiSuccess {String} data.mapped.action `linked` (already existed) or `created`
 *
 * @apiError (422) {Object} errors Validation or per-item business messages
 *
 * @apiSampleRequest /api/v1/stay/bookings/sync-local
 */
