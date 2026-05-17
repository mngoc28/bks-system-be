/**
 * @api {get} /api/v1/stay/cancellation-reasons Stay — Cancellation reason codes
 * @apiName StayCancellationReasons
 * @apiGroup StayCancellation
 * @apiVersion 1.0.0
 *
 * @apiDescription Requires JWT (`Authorization: Bearer`) and feature flag `BCP_CANCELLATION_V1` (middleware `bcp.cancellation`). Returns active reason codes for cancel / cancel-request forms.
 *
 * @apiHeader {String} Authorization JWT bearer token
 *
 * @apiSuccess {String} status success
 * @apiSuccess {Object[]} data List of reason codes
 * @apiSuccess {String} data.code Machine-readable code (matches DB `cancellation_reason_codes.code`)
 * @apiSuccess {String} data.label Display label (currently Vietnamese `label_vi`)
 * @apiSuccess {Boolean} data.requires_note Whether free-text `reason_text` is mandatory
 *
 * @apiSampleRequest /api/v1/stay/cancellation-reasons
 */

/**
 * @api {post} /api/v1/stay/bookings/:id/cancel Stay — Direct cancel (low tier)
 * @apiName StayBookingCancelDirect
 * @apiGroup StayCancellation
 * @apiVersion 1.0.0
 *
 * @apiDescription Allowed only when booking `status` is **pending (0)** and guest is owner (`bookings.user_id`). Blocked when `stay_status` is checked_in / checked_out / no_show.
 *
 * @apiParam (Path) {Number} id Booking id
 * @apiParam (Body) {String} reason_code Required; must exist in `cancellation_reason_codes` and be active
 * @apiParam (Body) {String} [reason_text] Required when the selected code has `requires_note = true`
 *
 * @apiSampleRequest /api/v1/stay/bookings/42/cancel
 */

/**
 * @api {post} /api/v1/stay/bookings/:id/cancel-request Stay — Cancel request (high tier)
 * @apiName StayBookingCancelRequest
 * @apiGroup StayCancellation
 * @apiVersion 1.0.0
 *
 * @apiDescription Allowed when booking `status` is **confirmed (1)**. Sets booking to `pending_cancellation` (4) and creates a `booking_cancellation_requests` row. Cooldown and idempotency are enforced server-side.
 *
 * @apiParam (Path) {Number} id Booking id
 * @apiParam (Body) {String} reason_code Required
 * @apiParam (Body) {String} [reason_text] Optional unless required by code
 * @apiParam (Body) {String} idempotency_key Required (max 64 chars). Reuse after a terminal request returns 409 `IDEMPOTENCY_REUSE`.
 *
 * @apiError (429) {String} code CANCEL_REQUEST_COOLDOWN
 * @apiError (429) {Number} retry_after_seconds Seconds until next allowed cancel-request
 *
 * @apiSampleRequest /api/v1/stay/bookings/42/cancel-request
 */
