# Test Case Specification: BCP — Chính sách hủy & yêu cầu hủy đặt phòng (Stay / Partner / My Bookings / Admin)

## Document Information

| Trường | Giá trị |
|---|---|
| **Testcase ID** | TC002 |
| **Related SRS** | [docs/SRC/srs_booking_cancellation_policy.md](../SRC/srs_booking_cancellation_policy.md) |
| **Related Design** | [docs/designs/design_002.md](../designs/design_002.md) |
| **Related Plan** | [docs/plans/plan_002.md](../plans/plan_002.md) |
| **Canonical DB** | [docs/databases_docs/db_overview_etc_core_schema.md](../databases_docs/db_overview_etc_core_schema.md) |
| **Status** | Draft |
| **Ngôn ngữ thực thi QC** | Tiếng Việt (kịch bản); log lỗi có thể vi/en tùy cấu hình i18n |

## Scope

### In-scope (theo SRS + plan P002 đã triển khai)

- **Stay (JWT):** `GET /api/v1/stay/cancellation-reasons`, `POST /api/v1/stay/bookings/{id}/cancel`, `POST /api/v1/stay/bookings/{id}/cancel-request` (Note: `sync-local` has been decommissioned).
- **Partner (JWT + role partner):** `GET /api/v1/partner/cancellation-requests`, `POST …/{id}/approve`, `POST …/{id}/reject`.
- **Admin (JWT + role admin):** `GET /api/v1/admin/booking-cancellation-metrics`.
- **Feature flag:** `BCP_CANCELLATION_V1` / middleware `bcp.cancellation` (kỳ vọng 403 khi tắt).
- **Trạng thái:** `pending_cancellation` (status 4), bảng `booking_cancellation_requests`, timeline/metadata chính sách (tier), cooldown, idempotency.
- **FE (tham chiếu UAT):** Stay (CTA cancel vs cancel-request, countdown 429), Partner inbox + Echo (theo plan B3/B4).

### Out-of-scope

- Tự động hoàn tiền cổng thanh toán; no-show / trả phòng sớm như một SRS riêng.
- Phân phí theo từng Partner/loại phòng (SRS giai đoạn đầu loại trừ).
- Điều chỉnh số % production trong `cancellation_policy_tiers` sau research OTA (chỉ xác minh placeholder đã seed và logic tier cơ bản).

## Preconditions

| ID | Điều kiện |
|---|---|
| P-01 | Môi trường có DB đã migrate BCP (bảng `cancellation_reason_codes`, `cancellation_policy_versions`, `cancellation_policy_tiers`, `booking_cancellation_requests`, cột BCP trên `bookings`). |
| P-02 | Đã chạy seeder lý do + baseline policy (ví dụ `CancellationReasonCodesSeeder`, `CancellationPolicyBaselineSeeder`) trên môi trường test. |
| P-03 | `BCP_CANCELLATION_V1=true` cho nhóm test “happy path API”; có thêm một bản build/toggle riêng với `false` cho TC feature-off. |
| P-04 | Có tài khoản **Stay** (user) sở hữu booking; tài khoản **Partner** sở hữu property/room của booking đó; tài khoản **Admin**; tài khoản Stay **khác** (negative ownership). |
| P-05 | Chuẩn bị booking: (A) `status=pending` + `stay_status` cho phép hủy; (B) `status=confirmed` + chưa check-in; (C) `stay_status` thuộc nhóm chặn (`checked_in` / `checked_out` / `no_show` theo SRS); (D) đã `status=pending_cancellation` với request `pending`. |
| P-06 | Client test (Postman/Insomnia) có thể gửi header `Authorization: Bearer <jwt>` và body JSON đúng schema. |

## Test Data (gợi ý)

| Mã | Mô tả |
|---|---|
| TD-01 | `reason_code` hợp lệ từ `cancellation-reasons`, `requires_note=false`. |
| TD-02 | `reason_code` có `requires_note=true` + `reason_text` không rỗng. |
| TD-03 | `reason_code` không tồn tại hoặc `is_active=0`. |
| TD-04 | `idempotency_key` cố định (UUID) để lặp lại request `cancel-request`. |
| TD-05 | (Decommissioned/Removed) |

## Test Cases

| TC ID | Requirement Ref | Screen/Module | Scenario | Steps | Test Data | Expected Result | Priority |
|------|-----------------|---------------|----------|-------|-----------|-----------------|----------|
| TC002-001 | BCP-005, F-BCP-03 | Stay API | Danh sách lý do hủy | 1) Login Stay lấy JWT.<br>2) `GET /api/v1/stay/cancellation-reasons` (BCP bật). | JWT hợp lệ | HTTP 200; mảng `code`, `label`, `requires_note`; chỉ mã `is_active=true`. | High |
| TC002-002 | Flag BCP | Stay API | Tắt feature | 1) `BCP_CANCELLATION_V1=false`.<br>2) Gọi `cancellation-reasons` hoặc `cancel`. | JWT hợp lệ | HTTP 403; mã lỗi kiểu `BCP_DISABLED` (hoặc thông điệp tương đương contract). | High |
| TC002-003 | BCP-002, F-BCP-01 | Stay API | Hủy trực tiếp bậc thấp | 1) Booking `pending`, stay cho phép.<br>2) `POST …/cancel` với `reason_code` TD-01. | TD-01 | 200; booking chuyển trạng thái terminal hủy (theo policy); không tạo row `booking_cancellation_requests` **pending** (hoặc không vào status 4). | High |
| TC002-004 | BCP-005 | Stay API | Cancel thiếu lý do | `POST …/cancel` không gửi `reason_code`. | — | 422; thông báo validation. | High |
| TC002-005 | BCP-005 | Stay API | Cancel mã lý do không hợp lệ | `reason_code` không tồn tại. | TD-03 | 422. | Medium |
| TC002-006 | BCP-005 | Stay API | Cancel bắt buộc ghi chú | `reason_code` requires_note=true, `reason_text` rỗng. | TD-02 bỏ text | 422; lỗi `reason_text`. | High |
| TC002-007 | BCP-001 | Stay API | Chặn khi đã ở / checkout | Booking `stay_status` thuộc nhóm chặn. | TD từ P-05-C | 409 hoặc mã `STAY_NOT_CANCELLABLE` / thông điệp SRS tương đương. | High |
| TC002-008 | BCP-003, F-BCP-02 | Stay API | Gửi yêu cầu hủy bậc cao | Booking `confirmed`.<br>`POST …/cancel-request` đủ field + `idempotency_key`. | TD-01 + TD-04 | 200; booking `pending_cancellation` (4); có row `booking_cancellation_requests` status `pending`. | High |
| TC002-009 | BCP-005 | Stay API | Cancel-request thiếu idempotency | Body thiếu `idempotency_key`. | TD-01 | 422. | High |
| TC002-010 | BCP-006 | Stay API | Cooldown cancel-request | 1) Gửi `cancel-request` thành công.<br>2) Gửi lại ngay với key khác. | Hai key khác nhau | 429; payload có `retry_after_seconds` (hoặc tương đương). | High |
| TC002-011 | BCP-007 | Stay API | Idempotency replay | Gửi lại **cùng** `idempotency_key` + cùng booking sau bước thành công. | TD-04 | 200; không nhân đôi request pending; có thể mã `IDEMPOTENT_REPLAY`. | High |
| TC002-012 | BCP-003 | Stay API | Đã có một pending | Booking đã có request `pending`, thử gửi thêm. | P-05-D | 409 `ALREADY_PENDING` hoặc tương đương. | High |
| TC002-013 | BCP-002 | Stay API | Sai lộ: cancel-request khi chỉ được cancel | Booking `pending` nhưng gọi `cancel-request`. | — | 409 `INVALID_STATE` hoặc thông điệp “chưa cần yêu cầu hủy”. | Medium |
| TC002-014 | BCP-003 | Stay API | Sai lộ: cancel khi đã confirmed | Booking `confirmed` gọi `cancel`. | — | 409 `INVALID_STATE`. | Medium |
| TC002-015 | Auth | Stay API | Không JWT | Gọi API không header auth. | — | 401. | High |
| TC002-016 | Auth | Stay API | Booking người khác | JWT user A, `booking_id` của user B. | — | 403 `FORBIDDEN` / policy guest. | High |
| TC002-017 | (Decommissioned/Removed) | | | | | | |
| TC002-018 | (Decommissioned/Removed) | | | | | | |
| TC002-019 | BCP-009, F-BCP-05 | Partner API | Inbox danh sách | `GET /partner/cancellation-requests` (filter optional). | JWT Partner | 200; có phân trang/filter theo query đã design. | High |
| TC002-020 | BCP-009 | Partner API | Approve yêu cầu | Chọn request `pending` thuộc property Partner.<br>`POST …/{id}/approve` (note optional nếu có). | Request hợp lệ | 200; booking `cancelled`; request `approved`; có timeline/broadcast marker (kiểm DB hoặc log). | High |
| TC002-021 | BCP-009, F-BCP-06 | Partner API | Reject + ghi chú | `POST …/{id}/reject` với `note` ≥ 5 ký tự. | Note hợp lệ | 200; booking khôi phục `previous_booking_status`; request `rejected`. | High |
| TC002-022 | Validation | Partner API | Reject thiếu / ngắn note | `note` < 5 ký tự hoặc thiếu. | — | 422. | High |
| TC002-023 | Auth | Partner API | Partner khác property | JWT Partner B gọi approve/reject request của Partner A. | — | 404/403 theo policy (không lộ tồn tại request). | High |
| TC002-024 | BCP-009 | Partner API | Approve/reject request không pending | Request đã `approved`/`rejected`. | — | 409 hoặc mã nghiệp vụ tương ứng. | Medium |
| TC002-025 | BCP-010, BCP-011 | DB / Timeline | Snapshot chính sách khi tạo request | Sau TC002-008, kiểm DB `policy_version_snapshot`, `cancellation_policy_version` trên booking; xem `booking_timeline_events` metadata (stay_kind, hours_before_checkin, policy_tier_id, %). | — | Snapshot version ≤32 ký tự; metadata có các field trace tier (theo implement B5). | Medium |
| TC002-026 | B1.6 / Conflict | Partner KPI / Calendar | Status 4 vẫn giữ chỗ | Có booking status 4; thử tạo booking chồng lịch cùng phòng (theo quy trình nghiệp vụ confirm conflict). | Theo script conflict | Hệ thống không cho overbooking; thông điệp conflict đúng design. | Medium |
| TC002-027 | B7, plan B5 | Admin API | Metrics nội bộ | `GET /api/v1/admin/booking-cancellation-metrics` với JWT admin. | Admin JWT | 200; JSON có `sla_seconds` (p50, p90, sample_size) và `pending_stale` (open, stale, stale_percent_of_open). | Medium |
| TC002-028 | Auth | Admin API | User không phải admin | JWT Stay/Partner gọi metrics. | — | 403. | High |
| TC002-029 | Auth | Admin API | Không JWT | — | — | 401. | High |
| TC002-030 | Throttle | Stay API | Giới hạn cancel-request | Gửi > 10 request/phút (theo route throttle) từ cùng IP/user. | — | 429 Too Many Requests (Laravel throttle). | Low |
| TC002-031 | (Decommissioned/Removed) | | | | | | |
| TC002-032 | FE B4 | Stay | CTA đúng bậc trạng thái | Mở chi tiết booking pending vs confirmed. | — | Pending: nút hủy trực tiếp; Confirmed: gửi yêu cầu hủy. | High |
| TC002-033 | BCP-006 | Stay FE | Countdown 429 | Sau khi API trả 429 cooldown, UI hiển thị thời gian chờ (từ `retry_after_seconds` hoặc header). | — | Người dùng thấy số đếm / thông báo rõ. | Medium |
| TC002-034 | Realtime B3 | Partner FE | Inbox cập nhật | Khi Stay tạo request mới, Partner đang mở inbox nhận toast/cập nhật danh sách (Echo). | 2 browser/profile | Danh sách refresh hoặc realtime không cần F5 thủ công. | Medium |
| TC002-035 | Regression | Partner Booking | Partner confirm trong lúc chờ hủy | Race: khách đang pending_cancellation, Partner thử confirm (nếu luồng còn tồn tại). | Script | Hệ thống chặn đúng design (409/422); không corrupt DB. | Low |
| TC002-036 | i18n | Stay API | Mã lỗi thân thiện | Gọi các lỗi 409/422 với `Accept-Language` vi/en. | Header | Thông điệp đúng ngôn ngữ (nếu app hỗ trợ). | Low |

## Validation Matrix (API chính)

| Module | Field / query | Rule | Valid | Invalid | Ghi chú |
|--------|---------------|------|-------|---------|---------|
| Stay cancel | `reason_code` | required, exists active | TD-01 | thiếu / TD-03 | |
| Stay cancel | `reason_text` | required if code.requires_note | Có text khi bắt buộc | Rỗng khi bắt buộc | Custom validator |
| Stay cancel-request | `idempotency_key` | required, max 64 | UUID | thiếu | |
| Partner reject | `note` | required, min 5, max 2000 | ≥5 ký tự | 4 ký tự | |
| Partner inbox | `per_page` | optional, 1–50 | 20 | 0, 99 | |
| Partner inbox | `status` | enum | `pending` | `invalid` | |

## Traceability Matrix

| Requirement / Function | Covered By | Ghi chú |
|------------------------|------------|---------|
| BCP-001 | TC002-007 | stay blocked |
| BCP-002 | TC002-003, TC002-013, TC002-014 | Hai lộ cancel |
| BCP-003 | TC002-008, TC002-012, TC002-013, TC002-014 | pending_cancellation |
| BCP-004 | TC002-013, TC002-014 | Ma trận bậc (partial — enum “chờ thanh toán” chờ BA map) |
| BCP-005 | TC002-004 — TC002-006, TC002-009 | Lý do |
| BCP-006 | TC002-010, TC002-033 | Cooldown |
| BCP-007 | TC002-011 | Idempotency |
| BCP-008 | (Decommissioned/Removed) | |
| BCP-009 | TC002-019 — TC002-024, TC002-034 | Partner resolve |
| BCP-010 / BCP-011 | TC002-025 | Policy tier + snapshot |
| B7 KPI | TC002-027 — TC002-029 | Admin metrics |
| F-BCP-01 — F-BCP-07 | TC002-003, TC002-008, TC002-019 — TC002-021 | Catalog |
| Feature flag | TC002-002 | Rollback |

## Execution Notes for QC

1. **Thứ tự gợi ý:** TC002-002 (flag) → TC002-001 (master data) → Stay happy path (003, 008) → negative Stay (004–007, 009–016) → Partner (019–024) → Admin (027–029) → FE (031–034) → regression (035–036).
2. **Phụ thuộc dữ liệu:** TC Partner cần request `pending` từ Stay trước; chuẩn bị script SQL hoặc factory để reset booking giữa các lần chạy.
3. **Realtime:** TC002-034 cần cấu hình Echo/Pusher/Soketi giống môi trường staging.
4. **Ràng buộc CI:** Một số TC cần DB thật; nếu môi trường không có MySQL, hoãn TC DB-heavy và ghi rõ trong báo cáo chạy.
5. **Đối chiếu contract:** `api-doc/` snippets Stay cancel / Partner cancellation (sync-local has been decommissioned).

## Smoke Regression (sau mỗi release BCP)

| # | Mô tả ngắn | Kỳ vọng |
|---|------------|---------|
| S-01 | `cancellation-reasons` 200 | Danh sách không rỗng sau seed |
| S-02 | `cancel` trên booking pending | 200, booking cancelled |
| S-03 | `cancel-request` trên booking confirmed | 200, status 4 + request pending |
| S-04 | Partner `GET` inbox | Thấy request vừa tạo |
| S-05 | Partner approve | Booking cancelled, request approved |

---

*Tài liệu sinh theo skill `stack-testcase`, đồng bộ plan P002 và SRS BCP.*
