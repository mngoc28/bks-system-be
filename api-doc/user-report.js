/**
 * @api {post} /api/v1/admin/reports Submit user report
 * @apiName SubmitUserReport
 * @apiGroup UserReport
 * @apiPermission AuthenticatedUser
 *
 * @apiHeader {String} Authorization Bearer token
 *
 * @apiBody {Number} reporter_id Reporter ID (*).
 * @apiBody {Number} reported_user_id Reported user ID (*).
 * @apiBody {Number} [booking_id] Booking ID related to the incident.
 * @apiBody {String="behavior","harassment","fraud","property_damage","scam","noise","payment_issue","other"} type Violation type (*).
 * @apiBody {String} title Report title (*).
 * @apiBody {String} description Report description (*).
 * @apiBody {String="low","medium","high","critical"} severity Severity level (*).
 * @apiBody {String="pending","reviewing","resolved","rejected"} status Investigation status (*).
 * @apiBody {String} [admin_note] Note from admin.
 *
 * @apiParamExample {json} Request-Example:
 * {
 *   "reporter_id": 3,
 *   "reported_user_id": 5,
 *   "booking_id": 12,
 *   "type": "harassment",
 *   "title": "Guest reported harassment",
 *   "description": "The guest reported that the host behaved inappropriately during the stay.",
 *   "severity": "high",
 *   "status": "pending",
 *   "admin_note": "Initial submission"
 * }
 *
 * @apiSampleRequest /api/v1/admin/reports
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 201 Created
 * {
 *   "status": "success",
 *   "message": "User report submitted successfully.",
 *   "data": {
 *     "id": 8,
 *     "reporter_id": 3,
 *     "reported_user_id": 5,
 *     "booking_id": 12,
 *     "type": "harassment",
 *     "title": "Guest reported harassment",
 *     "description": "The guest reported that the host behaved inappropriately during the stay.",
 *     "severity": "high",
 *     "status": "pending",
 *     "admin_note": "Initial submission",
 *     "created_at": "2025-12-05T03:20:00.000000Z",
 *     "updated_at": "2025-12-05T03:20:00.000000Z"
 *   }
 * }
 */
