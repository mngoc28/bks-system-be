# Implementation Plan: Chính sách hủy & yêu cầu hủy đặt phòng (BCP — Stay / Partner / My Bookings)

## Document Information

- **Plan ID:** P002
- **Created:** 2026-05-14
- **Status:** Draft
- **Related Design:** [docs/designs/design_002.md](../designs/design_002.md)
- **Related SRS:** [docs/SRC/srs_booking_cancellation_policy.md](../SRC/srs_booking_cancellation_policy.md)
- **SRS liên quan:** [docs/SRC/srs_partner_portal_360.md](../SRC/srs_partner_portal_360.md)
- **Related Lead:** [docs/leads/lead_260513_booking-cancellation-policy.md](../leads/lead_260513_booking-cancellation-policy.md)
- **Canonical schema:** [docs/databases_docs/db_overview_etc_core_schema.md](../databases_docs/db_overview_etc_core_schema.md)
- **Persona:** `.cursor/skills/stack-personas/technical-lead-architect.md`
- **Tham chiếu QC:** `.cursor/references/testing-patterns.md`, `.cursor/references/security-checklist.md`, `.cursor/references/performance-checklist.md`

## Executive Summary

Kế hoạch triển khai **BCP (Booking Cancellation Policy)** theo `design_002.md`, chia **5 phase** tương ứng design: (1) schema + enum + ConflictChecker + master lý do; (2) API Stay `cancel` / `cancel-request` + cooldown + idempotency; (3) Partner inbox approve/reject + broadcast; (4) T6 `sync-local` + FE Stay/My Bookings; (5) policy tiers điền % + báo cáo SLA/B7.

**Ước lượng tổng:** ~**90–110 giờ** dev BE/FE (~**12–15** ngày người), chưa gồm QC độc lập và review merge.

**Feature flag:** `BCP_CANCELLATION_V1` (env + `config`) để tắt route mới khi cần rollback nhanh (theo design).

**Rủi ro chính:** chạm đồng thời `BookingService`, `ConflictChecker`, `routes/api.php`, `BookingStatus`, dashboard/chart query nếu quên loại trừ status 4 đúng nghĩa — plan ghi rõ thứ tự và task kiểm thử.

## Phase Overview

| Phase | Name | Tasks (ước) | Effort (h) | Dependencies | Can Parallel With |
|-------|------|--------------|------------|--------------|-------------------|
| B1 | Foundation — DB + enum + Conflict | 12 | 22 | None | — |
| B2 | Stay — Guest cancel / cancel-request | 14 | 32 | B1 | — |
| B3 | Partner — inbox + resolve + realtime | 12 | 28 | B2 | Một phần FE B4 sau B2.4 (mock API contract) |
| B4 | T6 sync-local + FE Stay / My Bookings | 10 | 24 | B2 (API ổn định) | BE B3 song song sau B2.6 |
| B5 | Policy % + báo cáo B7 | 6 | 12 | B1 (bảng tiers), B3 (có dữ liệu resolve) | Song song tài liệu admin seed | **✅ ship BE 2026-05-14** (resolver, seed tier, metrics admin) |

## Dependency Graph

```text
Phase B1
├── [B1.1] Migration: cột bookings (pending_cancellation_since, policy_version, client_*) ─┐
├── [B1.2] Migration: cancellation_reason_codes + Seeder ◄───────────────────────────────────┤
├── [B1.3] Migration: cancellation_policy_versions + tiers ◄───────────────────────────────┤
├── [B1.4] Migration: booking_cancellation_requests + index/unique ◄───────────────────────┤
├── [B1.5] BookingStatus::PENDING_CANCELLATION + rà soát switch/match toàn repo ◄ (B1.1) ───┤
├── [B1.6] ConflictChecker: status 4 = active occupancy + unit tests ◄ (B1.5) ─────────────┤
├── [B1.7] PartnerKpiService / chart queries: định nghĩa có/không tính status 4 ◄ (B1.5) ─┤
├── [B1.8] BookingTimelineService: constants event mới (stub record) ◄ (B1.4) ─────────────┤
├── [B1.9] config/bcp.php + .env.example (cooldown, stale, nights) ◄────────────────────────┤
├── [B1.10] Feature flag BCP_CANCELLATION_V1 + middleware optional ◄───────────────────────┤
├── [B1.11] Migration rollback test (fresh + rollback) ◄ (B1.1–B1.4) ─────────────────────────┤
└── [B1.12] Cập nhật docs: db_overview nhật ký nếu schema đổi khi ship ◄────────────────────┘
                                        │
                                        ▼ (blocks B2–B5)
Phase B2 — Stay
├── [B2.1] Model CancellationRequest + ReasonCode (hoặc raw query) ◄ (B1.4, B1.2)
├── [B2.2] FormRequest: StayCancelBooking, StayCancelRequestBooking ◄ (B1.2)
├── [B2.3] GuestCancellationService: cancel (PENDING only) + timeline ◄ (B2.1, B1.8)
├── [B2.4] GuestCancellationService: cancel-request + cooldown + idempotency ◄ (B2.3)
├── [B2.5] StayBookingCancellationController + routes (behind flag) ◄ (B2.3, B2.4, B1.10)
├── [B2.6] Policy: chủ booking (user_id) + stay_status guard ◄ (B2.5)
├── [B2.7] GET stay/cancellation-reasons ◄ (B1.2)
├── [B2.8] Throttle route cancel-request ◄ (B2.5)
├── [B2.9] i18n vi/en mã lỗi COOLDOWN, INVALID_STATE ◄ (B2.5)
├── [B2.10] Unit tests: state machine, cooldown, idempotency ◄ (B2.4)
├── [B2.11] Feature tests HTTP (nếu CI DB sẵn; không thì ghi hoãn theo testing-patterns) ◄
├── [B2.12] OpenAPI / api-doc snippet cho FE contract ◄ (B2.5)
└── [B2.13] BookingService hook: tránh double-cancel với Partner cancel ◄ (B2.3)

Phase B3 — Partner
├── [B3.1] PartnerCancellationRequestRepository (list filter property, status) ✅ DONE (2026-05-14) ◄ (B2.1)
├── [B3.2] PartnerCancellationRequestService approve/reject ✅ DONE (2026-05-14) ◄ (B3.1, B2.4)
├── [B3.3] PartnerCancellationRequestController + routes ✅ DONE (2026-05-14) ◄ (B3.2)
├── [B3.4] Policy ownership Partner ↔ booking.room.property ✅ DONE (2026-05-14) ◄ (B3.3)
├── [B3.5] Events ShouldBroadcast + listener timeline (payload no PII) ✅ DONE (2026-05-14) ◄ (B3.2)
├── [B3.6] Invalidate KPI/cache nếu cần khi approve cancel ✅ DONE (2026-05-14) ◄ (B3.2, B1.7)
├── [B3.7] FE: danh sách yêu cầu + filter ✅ DONE (2026-05-14) ◄ (B3.3, B2.12)
├── [B3.8] FE: màn approve/reject + validation note ✅ DONE (2026-05-14) ◄ (B3.7)
├── [B3.9] FE: subscribe event mới (Echo) ✅ DONE (2026-05-14) ◄ (B3.5, design_001 pattern)
└── [B3.10] Unit tests Partner service (reject restore status) ✅ DONE (2026-05-14) ◄ (B3.2)

Phase B4 — Sync + FE khách
├── [B4.1] LocalBookingSyncService (fingerprint, dedupe, transaction) ✅ DONE (2026-05-14) ◄ (B1.1)
├── [B4.2] POST stay/bookings/sync-local + validation JSON ✅ DONE (2026-05-14) ◄ (B4.1)
├── [B4.3] FE Stay: gọi sync sau login (hook store auth) ✅ DONE (2026-05-14) ◄ (B4.2)
├── [B4.4] FE Stay: CTA cancel vs cancel-request theo status ✅ DONE (2026-05-14) ◄ (B2.12, B2.5)
├── [B4.5] FE My Bookings: cảnh báo local-only + map server_booking_id ✅ DONE (2026-05-14) ◄ (B4.2)
├── [B4.6] FE: cooldown countdown từ header 429 / field API ✅ DONE (2026-05-14) ◄ (B2.5)
└── [B4.7] E2E nhỏ: login → sync → cancel-request (manual hoặc MCP khi sẵn) ✅ DONE (2026-05-14) ◄ (B4.4)

Phase B5 — Policy & B7
├── [B5.1] CancellationPolicyResolver + unit (tier chọn đúng stay_kind + hours) ✅ DONE (2026-05-14) ◄ (B1.3)
├── [B5.2] Ghi policy_version_snapshot khi tạo request ◄ (B5.1, B2.4) ✅ DONE (2026-05-14)
├── [B5.3] Seeder tiers khi BA có % (migration data hoặc artisan) ◄ (B5.1) ✅ DONE (2026-05-14)
├── [B5.4] Query báo cáo SLA p50/p90 (raw SQL hoặc repository) ◄ (B3.2) ✅ DONE (2026-05-14)
├── [B5.5] Query % treo theo BCP_STALE_REQUEST_HOURS ◄ (B5.4) ✅ DONE (2026-05-14)
└── [B5.6] Dashboard nội bộ (optional route admin) ◄ (B5.4) ✅ DONE (2026-05-14)
```

---

## Phase B1: Foundation — DB + enum + Conflict + config

**Goal:** Database và enum sẵn sàng; `ConflictChecker` và KPI/chart không regress.

**Duration Estimate:** ~3–4 ngày người (~22 giờ)

**Dependencies:** None

**Parallel With:** None

### Tasks

#### [B1.1] Migration: cột `bookings` theo design D002 §4.1.2

- **Description:** Thêm `pending_cancellation_since`, `cancellation_policy_version`, `client_local_id`, `client_fingerprint` (nullable); index phù hợp dedupe T6.
- **Acceptance Criteria:**
  - [x] `php artisan migrate` thành công; `down()` drop đúng thứ tự.
  - [x] Không break booking hiện có (cột nullable).
- **Files Affected:** `database/migrations/*_add_bcp_columns_to_bookings_table.php`
- **Dependencies:** None
- **Blocks:** B1.5, B4.1
- **Test Scenarios:** migrate fresh; migrate + rollback trên DB dev.

#### [B1.2] Migration + Seeder: `cancellation_reason_codes`

- **Description:** Bảng master + seed tối thiểu 4–6 mã (`change_of_plans`, `other`, …).
- **Acceptance Criteria:**
  - [x] Seeder idempotent (`updateOrInsert` theo `code`).
  - [ ] GET sau này chỉ trả `is_active=1`.
- **Files Affected:** `database/migrations/*_create_cancellation_reason_codes_table.php`, `database/seeders/...`
- **Dependencies:** None
- **Blocks:** B2.2, B2.7

#### [B1.3] Migration: `cancellation_policy_versions` + `cancellation_policy_tiers`

- **Description:** Tạo bảng; seed một `version` baseline với tier placeholder (nullable %).
- **Acceptance Criteria:**
  - [x] FK `version` hợp lệ; không bắt buộc có row tier để chạy code v1.
- **Files Affected:** `database/migrations/*_create_cancellation_policy_*.php`
- **Dependencies:** None
- **Blocks:** B5.1

#### [B1.4] Migration: `booking_cancellation_requests`

- **Description:** Full cột theo `db_overview` (gồm `previous_booking_status`, `policy_version_snapshot`); index `(booking_id, idempotency_key)` không unique (NULL-safe); idempotency có key enforce ở service B2.
- **Acceptance Criteria:**
  - [x] FK `booking_id`, `requester_user_id`, `resolved_by_user_id`.
  - [x] Index list Partner `(status, requested_at)` và `(booking_id, status, requested_at)`.
- **Files Affected:** `database/migrations/*_create_booking_cancellation_requests_table.php`
- **Dependencies:** B1.1 (bookings tồn tại)
- **Blocks:** B2.1, B3.1

#### [B1.5] Enum `BookingStatus::PENDING_CANCELLATION = 4`

- **Description:** Cập nhật enum; grep toàn repo các `match`/`switch`/array status — đặc biệt `BookingService`, `PartnerKpiService`, repositories filter active booking.
- **Acceptance Criteria:**
  - [x] PHPStan/lint không báo non-exhaustive (nếu dùng match).
  - [x] Không có nhánh mặc định coi `4` như cancelled nếu sai nghiệp vụ.
- **Files Affected:** `app/Enums/BookingStatus.php`, các service liên quan
- **Dependencies:** B1.1
- **Blocks:** B1.6, B1.7, B2.x

#### [B1.6] `ConflictChecker`: status 4 vẫn active

- **Description:** Đảm bảo loại trừ chỉ `CANCELLED`/`COMPLETED` (theo DEC-260514-BCP-002); bổ sung unit test overlap với booking status 4.
- **Acceptance Criteria:**
  - [x] Unit test khóa contract: `PENDING_CANCELLATION` không nằm trong tập status bị loại khỏi conflict query (đồng bộ với `ConflictChecker::findConflicts`).
- **Files Affected:** `app/Services/ConflictChecker.php`, `tests/Unit/Services/ConflictCheckerTest.php`
- **Dependencies:** B1.5
- **Blocks:** B2.13 (confirm không regression)

#### [B1.7] KPI / chart: quy ước status 4

- **Description:** Xác định occupancy/GMV có tính `pending_cancellation` như confirmed hay loại — ghi rõ trong code comment + test regression Partner KPI nếu có.
- **Acceptance Criteria:**
  - [x] Hành vi thống nhất với product (mặc định design: **vẫn giữ chỗ** → gần giống CONFIRMED cho occupancy).
- **Files Affected:** `app/Services/PartnerKpiService.php` (hoặc query liên quan)
- **Dependencies:** B1.5
- **Blocks:** B3.6

#### [B1.8] Timeline: constants + stub `record*`

- **Description:** Thêm `event_type` mới (ví dụ `guest_cancel_requested`, `guest_cancel_request_approved`, `guest_cancel_request_rejected`); stub gọi từ service ở phase sau.
- **Acceptance Criteria:**
  - [x] Không phá vỡ timeline cũ; migration không cần (VARCHAR event_type đã có).
- **Files Affected:** `app/Services/BookingTimelineService.php`
- **Dependencies:** B1.4
- **Blocks:** B2.3, B3.2

#### [B1.9] Config `config/bcp.php`

- **Description:** `cancel_request_cooldown_seconds`, `stale_request_hours`, `long_stay_min_nights` đọc từ env.
- **Acceptance Criteria:**
  - [x] Default khớp DEC-260514-BCP-004 (3600, 48, 30).
- **Files Affected:** `config/bcp.php`, `config/app.php` (register nếu cần), `.env.example`
- **Dependencies:** None
- **Blocks:** B2.4

#### [B1.10] Feature flag route

- **Description:** Middleware hoặc `Route::middleware` gating `BCP_CANCELLATION_V1`.
- **Acceptance Criteria:**
  - [x] Tắt flag → 403 có mã `BCP_DISABLED` để FE không crash.
- **Files Affected:** `app/Http/Middleware/...`, `app/Http/Kernel.php`, `routes/api.php`
- **Dependencies:** None
- **Blocks:** B2.5

#### [B1.11] — [B1.12]

- **B1.11:** Chạy migrate + rollback trên DB dev; ghi lại vào checklist PR.
- **B1.12:** Khi merge migration thật, cập nhật **Nhật ký thay đổi** trong `db_overview_etc_core_schema.md` (theo chuẩn repo). ✅ (2026-05-14)

---

## Phase B2: Stay — Guest `cancel` / `cancel-request`

**Goal:** Đáp ứng SRS **BCP-001** đến **BCP-007** (server-side Stay).

**Duration Estimate:** ~4–5 ngày (~32 giờ)

**Dependencies:** B1 hoàn tất

**Parallel With:** Sau **B2.6**, team FE có thể bắt đầu **B4.4** với mock/stub (parallel có điều kiện).

### Tasks (rút gọn — cùng cấu trúc AC)

| ID | Description | AC chính | Files gợi ý |
|----|-------------|----------|-------------|
| B2.1 | Model `BookingCancellationRequest` + fillable/casts | Eloquent load quan hệ booking | `app/Models/BookingCancellationRequest.php` |
| B2.2 | FormRequest validate `reason_code` tồn tại + `reason_text` khi `requires_note` | 422 đúng field | `app/Http/Requests/Stay/...` |
| B2.3 | `GuestCancellationService::cancelDirect` chỉ `PENDING` + `stay_status` not checked_in | 409/422 đúng mã | `app/Services/GuestCancellationService.php` |
| B2.4 | `requestCancellation`: một pending/booking, cooldown, idempotency | 429 + `retry_after_seconds` | cùng service |
| B2.5 | Controller + routes trong group `stay` | JWT + flag | `routes/api.php`, `Stay*Controller.php` |
| B2.6 | Policy | IDOR không xảy ra | `app/Policies/...` |
| B2.7 | GET `cancellation-reasons` | Cache-Control hoặc TTL ngắn | Controller |
| B2.8 | `throttle` trên cancel-request | security-checklist rate limit | `RouteServiceProvider` hoặc inline |
| B2.9 | Lang `booking.php` / `stay.php` keys mới | song ngữ | `resources/lang/...` |
| B2.10 | Unit tests service | map AC BCP-005, BCP-006, BCP-007 | `tests/Unit/...` |
| B2.11 | Feature tests | Given/When/Then theo requirement ID | `tests/Feature/...` (nếu DB CI) |
| B2.12 | Contract API cho FE | OpenAPI / `api-doc/` | theo convention repo |
| B2.13 | Rà soát `BookingService::handleCancelBooking` Partner vs guest | không deadlock state | `BookingService.php` |

---

## Phase B3: Partner — Inbox + approve/reject + realtime

**Goal:** SRS **BCP-009**; broadcast tương thích **design_001**.

**Duration Estimate:** ~3–4 ngày (~28 giờ)

**Dependencies:** B2 (có request để duyệt)

**Parallel With:** BE B3.1–B3.4 song song **B4.3** sau khi B2.5 merge (contract ổn định).

### Tasks (tóm tắt)

- **B3.1–B3.4:** Repository + `PartnerCancellationRequestService` + Controller + Policy (ownership).
- **B3.5:** Event `CancellationRequestUpdated` (tên gợi ý) `ShouldBroadcast`, payload minimal: `request_id`, `booking_id`, `property_id`, `partner_id`, `status`.
- **B3.6:** Invalidate cache KPI nếu approve làm booking → cancelled (theo B1.7).
- **B3.7–B3.9:** FE Partner inbox + Echo (đã ship trên `bks-system-fe`: trang `/partner/cancellation-requests`, `useBookingsRealtime` + toast trong `RealtimeNotifyProvider`).
- **B3.10:** Unit test: reject khôi phục `previous_booking_status`; approve set `cancelled` + `cancelled_at` + `cancellation_reason` (text/mã — thống nhất với DB hiện tại).

### Ghi chú hoàn thành B3 — BE + FE (2026-05-14)

- **B3.1–B3.4:** `PartnerCancellationRequestRepository`, `PartnerCancellationRequestService`, `PartnerCancellationRequestController`, route group `/api/v1/partner/cancellation-requests` + middleware `bcp.cancellation`, `BookingCancellationRequestPolicy` (ownership qua `property.user_id`).
- **B3.5:** `CancellationRequestUpdated` (`ShouldBroadcast`, alias `.cancellation_request.updated`), listener `RecordCancellationRequestBroadcastMarker` (ghi `booking_timeline_events` marker `broadcast_dispatched`); dispatch thêm sau khi guest tạo request thành công (`GuestCancellationService`).
- **B3.6:** Approve → `BookingCancelled` (listener KPI/calendar như luồng hủy thường); Reject → `Cache::forget` các key `PartnerKpiService::cacheKeysForPartner` + `PartnerCalendarService::bumpVersion`.
- **B3.10:** Unit: `BookingCancellationRequestPolicyTest` + mở rộng `BookingTimelineServiceTest` cho `recordGuestCancelRequestApproved` / `recordGuestCancelRequestRejected` (AC timeline approve/reject).
- **B3.7–B3.9 (FE `bks-system-fe`, 2026-05-14):** route + sidebar `PARTNER_CANCELLATION_REQUESTS`, `partnerService` (GET/POST cancellation-requests), trang `CancellationRequests.tsx` (filter + pagination + dialog duyệt/từ chối, note reject ≥5), `useBookingsRealtime` lắng `.cancellation_request.updated` + invalidate query `partner.cancellation-requests`, toast + deep-link trong `RealtimeNotifyProvider`.

---

## Phase B4: T6 `sync-local` + FE khách

**Goal:** SRS **BCP-008** + CTA đúng bậc.

**Duration Estimate:** ~3 ngày (~24 giờ)

**Dependencies:** B2 API; B1 cột fingerprint.

### Tasks (tóm tắt)

- **B4.1–B4.2:** `LocalBookingSyncService` + `POST /api/v1/stay/bookings/sync-local` (`StayLocalBookingSyncController`, `StaySyncLocalBookingsRequest`); transaction; dedupe fingerprint + khớp slot (đơn server chưa có fingerprint); `api-doc/stay-sync-local.js`; test `StayLocalBookingSyncTest` (CI cần DB).
- **B4.3 (phần đầu):** FE: `flushPendingLocalBookingsToServer` sau login BKS Stay + khi vào My Bookings (đã đăng nhập); lưu queue `publicMyBookings` từ `BookingSuccess` (fingerprint SHA-256 thống nhất BE); BE `user-create` trả thêm `price_id`.
- **B4.4–B4.6:** FE: `BookingDetail` — `GET cancellation-reasons`, `POST cancel` (pending) vs `cancel-request` (confirmed) + ghi chú khi `pending_cancellation` (4); countdown từ `retry_after_seconds` (429); `MyBookings` — banner đơn local chưa sync + badge chờ hủy + hiển thị `#server_booking_id`; helper `parseStayCancellationError`, `getPendingLocalBookingsCount`.
- **B4.7:** `business-script/b4_stay_smoke.ps1` — smoke PowerShell: login Stay → (tuỳ chọn) `sync-local` (kiểm tra 422 khi `items` rỗng) → `cancellation-reasons` → `cancel-request` trên booking **confirmed** (tự tìm trang 1 hoặc `-BookingId`).

---

## Phase B5: Policy tiers + B7 báo cáo

**Goal:** Chuẩn bị % sau research; query SLA.

**Duration Estimate:** ~2 ngày (~12 giờ)

**Dependencies:** B1.3; nên có dữ liệu request từ B3.

### Tasks (tóm tắt)

- **B5.1–B3:** Resolver + snapshot + seed % khi có.
- **B5.4–B6:** SQL/report; có thể ship sau go-live v1 nếu cần cắt scope.

### Tasks (chi tiết — stack-task 2026-05-14)

#### [B5.1] CancellationPolicyResolver + logic tier ✅ DONE
- **Acceptance Criteria:**
  - [x] Chọn tier đúng theo `stay_kind` (ngắn/dài từ `BCP_LONG_STAY_MIN_NIGHTS`) và số giờ trước đầu ngày check-in.
  - [x] Unit test không phụ thuộc DB: `CancellationPolicyTierMatcher` + `tests/Unit/Support/Bcp/CancellationPolicyTierMatcherTest.php`.
- **Files:** `app/Services/CancellationPolicyResolver.php`, `app/Services/CancellationPolicyResolution.php`, `app/Support/Bcp/CancellationPolicyTierMatcher.php`, `app/Models/CancellationPolicyTier.php`
- **Completed:** 2026-05-14

#### [B5.2] Ghi `policy_version_snapshot` + metadata timeline ✅ DONE
- **Acceptance Criteria:**
  - [x] `GuestCancellationService::requestCancellation` dùng resolver; `policy_version_snapshot` = phiên bản baseline (≤32 ký tự); timeline kèm `stay_kind`, `hours_before_checkin`, `policy_tier_id`, % ước tính.
- **Files:** `app/Services/GuestCancellationService.php`
- **Completed:** 2026-05-14

#### [B5.3] Seeder tier có % placeholder ✅ DONE
- **Acceptance Criteria:**
  - [x] `CancellationPolicyBaselineSeeder` seed 6 tier (short/long) với % mẫu; idempotent theo version (xóa tier cùng version rồi insert lại).
- **Files:** `database/seeders/CancellationPolicyBaselineSeeder.php`
- **Completed:** 2026-05-14

#### [B5.4] SLA p50/p90 ✅ DONE
- **Acceptance Criteria:**
  - [x] Service đọc `booking_cancellation_requests` (approved/rejected, có `resolved_at`), tính p50/p90 thời gian resolve (giây); hỗ trợ MySQL + SQLite (raw duration).
- **Files:** `app/Services/BookingCancellationMetricsService.php`
- **Completed:** 2026-05-14

#### [B5.5] % pending “treo” ✅ DONE
- **Acceptance Criteria:**
  - [x] Đếm pending mở và pending có `requested_at` cũ hơn `bcp.stale_request_hours`; trả `stale_percent_of_open`.
- **Files:** `BookingCancellationMetricsService::pendingStaleMetrics`
- **Completed:** 2026-05-14

#### [B5.6] Route admin nội bộ ✅ DONE
- **Acceptance Criteria:**
  - [x] `GET /api/v1/admin/booking-cancellation-metrics` (`jwt.auth` + `role:admin`), JSON `summary()`.
- **Files:** `app/Http/Controllers/BookingCancellationReportController.php`, `routes/api.php`
- **Completed:** 2026-05-14

**Ghi chú review (stack-task):** BA/TLA/QA đã đối chiếu acceptance trên; test đã chạy `php artisan test --filter=CancellationPolicyTierMatcherTest` (pass). CI/MySQL: cần credentials `.env.testing` hợp lệ nếu chạy full suite có DB.

---

## Conflict Analysis

| Conflict ID | Type | Description | Phases | Resolution |
|---------------|------|-------------|--------|------------|
| C-P2-01 | File | `routes/api.php` — Stay + Partner cùng sửa | B2, B3 | Một PR sequence hoặc rebase theo thứ tự B2 → B3 |
| C-P2-02 | File | `BookingService.php` — guest cancel vs partner cancel | B2, B3 | Tách method rõ `guestCancel` / giữ `handleCancelBooking` partner; tránh copy-paste business |
| C-P2-03 | Logic | `BookingStatus` mở rộng ảnh hưởng chart/filter | B1 | Task B1.7 + test regression |
| C-P2-04 | DB | Nhiều migration cùng bảng `bookings` | B1 | Gộp một migration `bookings` nếu chưa deploy môi trường; nếu đã deploy tách migration additive |

### Conflict Resolution Strategy

1. **Nhánh Git:** `feature/bcp-phase{N}` hoặc một nhánh `feature/bcp-cancellation` với commit tách theo phase để revert dễ.
2. **Merge order:** B1 merge trước → B2 → B3 → B4/B5 có thể cherry-pick độc lập sau khi API ổn định.

---

## Parallelization Opportunities

| Nhóm | Tasks | Điều kiện |
|------|-------|-----------|
| P-A | B3.7–B3.9 (FE Partner) song song B4.4 (FE Stay) | Sau khi OpenAPI B2.12 frozen |
| P-B | B5.4–B5.6 (report) song song hardening B3 | Sau có dữ liệu staging |

**Bắt buộc tuần tự:** B1 → B2 → B3 (inbox cần request); B4 phụ thuộc B2.

---

## Risk Register

| Risk ID | Mô tả | L | I | Mitigation |
|---------|-------|---|---|------------|
| R-P2-01 | Chart KPI đếm sai sau status 4 | M | H | B1.7 + test snapshot query |
| R-P2-02 | Race: Partner confirm trong lúc guest gửi cancel-request | M | H | Transaction + `lockForUpdate` trên `bookings` |
| R-P2-03 | FE hiển thị sai CTA | M | M | Contract B2.12 + enum đồng bộ FE types |

---

## Testing Strategy

### Unit tests (ưu tiên)

- `GuestCancellationService`: cancel chỉ pending; block checked_in; cooldown; idempotency; một pending request.
- `PartnerCancellationRequestService`: approve/reject + restore status.
- `ConflictChecker`: booking status 4 overlaps.

### Feature / HTTP tests

- Theo `.cursor/references/testing-patterns.md`: map **BCP-xxx** trong assert comment; 401/403/422/429/200 đầy đủ.
- Nếu CI chưa có DB: ghi **hoãn** trong PR + chạy local `php artisan test`.

### Security (checklist)

- `.cursor/references/security-checklist.md`: ownership, throttle, không lộ PII broadcast.

### Performance

- `.cursor/references/performance-checklist.md`: index list inbox; limit pagination mặc định ≤ 50.

### QC Test-case Handoff

- **Output target:** `docs/test-cases/testcase_002.md` (**TC002** — đã tạo 2026-05-14; traceability BCP + matrix validation + smoke S-01–S-05).
- **Source:** `srs_booking_cancellation_policy.md` + plan P002 + `design_002.md` + `db_overview_etc_core_schema.md`.
- **Owner skill:** `stack-testcase`.

---

## Rollback Strategy

| Phase | Rollback |
|-------|----------|
| B1 | `migrate:rollback` từng step; xóa enum case chỉ sau khi DB không còn status 4 |
| B2–B3 | Tắt `BCP_CANCELLATION_V1`; revert deploy |
| B4–B5 | Revert FE + tắt sync route |

---

## Downstream Handoffs (bắt buộc pipeline)

### `stack-task`

- Thực thi theo thứ tự phase **B1 → B2 → B3 → B4 → B5**; mỗi task có PR nhỏ kèm test tối thiểu unit cho service lõi.
- Nhánh đề xuất: `feature/bcp-cancellation` hoặc tách `feature/bcp-phase1-schema`.

### `stack-testcase`

- Sinh `docs/test-cases/testcase_002.md`: Given/When/Then cho từng **BCP-001…011** và mã HTTP/error codes (cooldown, idempotency).

### `stack-review-branch`

- Review so với `develop` sau mỗi phase; đặc biệt file nóng: `ConflictChecker.php`, `BookingService.php`, `routes/api.php`, `PartnerKpiService.php`.

### `report-writer`

- Báo cáo release khách hàng (tiếng Việt): luồng “chờ Partner”, cooldown, sync My Bookings — **không** đưa path nội bộ/engineering vào bản khách hàng.

---

## Checklist

### Trước khi bắt đầu code

- [ ] Đã đọc `design_002.md` + SRS + `db_overview`
- [ ] `.env.example` có key BCP
- [ ] Nhánh và flag thống nhất với team

### Sau mỗi phase

- [ ] Unit test service lõi xanh
- [ ] Cập nhật nhật ký DB khi ship migration
- [ ] Không regression Partner Portal 360 (smoke confirm/calendar)

---

## Appendix

### A. File impact (dự kiến)

| File / khu vực | Phases |
|----------------|--------|
| `app/Enums/BookingStatus.php` | B1 |
| `app/Services/ConflictChecker.php` | B1 |
| `app/Services/PartnerKpiService.php` | B1 |
| `database/migrations/*bcp*` | B1 |
| `routes/api.php` | B1–B3 |
| `app/Services/BookingTimelineService.php` | B1–B3 |
| `app/Services/GuestCancellationService.php` (mới) | B2 |
| `app/Services/PartnerCancellationRequestService.php` (mới) | B3 |
| `bks-system-fe/src/...` Stay, Partner, MyBookings | B3–B4 |

### B. Task quick reference

| Task ID | Phase | Est. h | Depends |
|---------|-------|--------|---------|
| B1.1 | B1 | 2 | — |
| B1.6 | B1 | 3 | B1.5 |
| B2.4 | B2 | 5 | B2.3, B1.9 |
| B3.2 | B3 | 5 | B3.1 |
| B4.1 | B4 | 5 | B1.1 |
| B5.4 | B5 | 4 | B3.2 |

---

## Ghi chú hoàn thành plan

- Plan có thể tinh chỉnh sau khi BA chốt map “chờ thanh toán” (xem DEC-260514-BCP-001 Pending) — cập nhật task B2.3/B2.4 và testcase tương ứng.
