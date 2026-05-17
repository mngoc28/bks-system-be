/**
 * Partner BCP — cancellation request inbox (feature flag BCP_CANCELLATION_V1).
 *
 * Base URL prefix: `/api/v1/partner/cancellation-requests`
 * Auth: `Authorization: Bearer` (JWT), role `partner`.
 * Middleware: `bcp.cancellation` (403 if `BCP_CANCELLATION_V1` disabled).
 *
 * @apiDefine PartnerCancellationRequests Partner cancellation requests (BCP)
 */

/**
 * @api {get} /partner/cancellation-requests List guest cancellation requests
 * @apiName PartnerCancellationRequestsIndex
 * @apiGroup PartnerCancellationRequests
 * @apiVersion 1.0.0
 * @apiDescription Inbox for the authenticated partner. Optional filters: `status` (pending|approved|rejected|withdrawn), `property_id`, `per_page` (1–50, default 15).
 *
 * @apiSuccess {Object} data
 * @apiSuccess {Object[]} data.items
 * @apiSuccess {Number} data.items.id Request id
 * @apiSuccess {Number} data.items.booking_id
 * @apiSuccess {String} data.items.status
 * @apiSuccess {String} data.items.requested_at ISO8601
 * @apiSuccess {String} data.items.reason_code
 * @apiSuccess {String} [data.items.reason_text]
 * @apiSuccess {Number} [data.items.booking_status] Numeric booking status
 * @apiSuccess {Object} [data.items.property] `{ id, name }`
 * @apiSuccess {Object} [data.items.room] `{ id, title, room_number }`
 * @apiSuccess {Object} data.meta Pagination (`current_page`, `last_page`, `per_page`, `total`)
 */

/**
 * @api {post} /partner/cancellation-requests/:id/approve Approve pending cancellation request
 * @apiName PartnerCancellationRequestApprove
 * @apiGroup PartnerCancellationRequests
 * @apiVersion 1.0.0
 * @apiDescription Sets booking to **cancelled**, clears BCP pending fields, dispatches `booking.cancelled` + `cancellation_request.updated` (after commit). Optional body: `{ "note": "..." }` (max 2000).
 *
 * @apiParam {Number} id Request id
 *
 * @apiSuccess {Object} data `{ request, booking }`
 */

/**
 * @api {post} /partner/cancellation-requests/:id/reject Reject pending cancellation request
 * @apiName PartnerCancellationRequestReject
 * @apiGroup PartnerCancellationRequests
 * @apiVersion 1.0.0
 * @apiDescription Restores `bookings.status` from `previous_booking_status` on the request; clears pending BCP columns. Body **requires** `{ "note": "...." }` with **min 5** characters. Dispatches `cancellation_request.updated`; clears Partner KPI cache slots and bumps calendar version.
 *
 * @apiParam {Number} id Request id
 *
 * @apiSuccess {Object} data `{ request, booking }`
 */

/**
 * Realtime: private channel `private-partner.{partnerId}` and `private-property.{propertyId}`.
 * Event alias: `.cancellation_request.updated` with payload `{ request_id, booking_id, property_id, partner_id, status }` (no guest PII).
 */
