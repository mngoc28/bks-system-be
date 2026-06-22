# Repository Knowledge Base

## 2026-06-19 - Partner Properties list filter & UX gaps

### Nguồn tham chiếu

- `docs/SRC/prd_partner_properties_list_gaps.md`
- `docs/plans/plan_015_partner_properties_list_gaps.md`
- `docs/plans/plan_009_partner_properties_api_optimization.md`
- `bks-system-fe/src/pages/Partner/Properties.tsx`
- `app/Repositories/PropertyRepository/PropertyRepository.php`

### Kiến thức nghiệp vụ đã chốt (plan)

- Trang `/partner/properties` đủ CRUD cốt lõi nhưng thiếu lớp vận hành: lọc `rent_category`, tìm địa chỉ, sort, thêm phòng tại chỗ, confirm xóa đơn.
- Must release (Slice B): keyword search, rent filter, add room CTA, single delete dialog.
- Should release (Slice C): Tỉnh/Phường, sort 4 option, cover thumbnail.
- Could (Slice D): occupancy/rating filter, URL persist, preview filter, export Excel (optional).

### Kiến thức kỹ thuật đã chốt (plan)

- API `GET /partner/properties/searchAll` đã hỗ trợ `rent_category`, `province_name`, `ward_name`, `sort`, `include=cover`; cần thêm `keyword` (Must) và `occupancy_filter` / `min_rating` / `has_rooms` (Could).
- Không migration DB cho Must/Should; `properties.address_detail` dùng cho keyword LIKE.
- Giữ `with_rooms=0` + lazy preview theo plan 009; không revert eager load cũ.
- Filter mobile: panel nâng cao collapse (PD-PP-015-004).

## 2026-05-31 - Đối soát doanh thu Admin

### Nguồn tham chiếu

- `docs/SRC/srs_admin_revenue_reconciliation.md`
- `docs/designs/design_006.md`
- `docs/plans/plan_006_admin_revenue_reconciliation.md`
- `docs/reports/domain/domain_review_admin_revenue_reconciliation.md`
- `docs/databases_docs/db_overview_etc_core_schema.md`

### Kiến thức nghiệp vụ đã chốt

- **Mô hình đối soát:** Chọn Model A - Đối soát định kỳ, Partner nộp phí 5% Platform Commission cho BKS. Lý do là khách trả trực tiếp tại quầy, BKS không giữ hộ tiền nên không thể khấu trừ trực tiếp.
- **Chu kỳ đối soát:** Ngày 05 và ngày 20 hàng tháng (2 kỳ/tháng).
  - Kỳ 1 (01 - 15): phát hành ngày 05 tháng sau.
  - Kỳ 2 (16 - cuối tháng): phát hành ngày 20 tháng sau.
- **Quy tắc chốt đơn:** Đơn chỉ vào kỳ đối soát khi thỏa mãn:
  - Trạng thái booking `status = COMPLETED` (3).
  - Trạng thái lưu trú `stay_status = checked_out`.
  - `no_show_at` IS NULL và `settlement_period_id` IS NULL (chưa thuộc kỳ đối soát nào).
  - `end_date` (check-out) nằm trong khoảng của kỳ đối soát.
  - Đối tác được phê duyệt (`partner_info.approved_at` IS NOT NULL).
- **Vòng đời đối soát:** `draft` (hệ thống tự tạo nháp) -> `issued` (Admin duyệt phát hành, gửi thông báo nợ phí) -> `paid` (Admin xác nhận chuyển khoản ngân hàng thực tế) -> `closed` (Khóa sổ kỳ đối soát, cấm sửa đổi booking liên quan). Trường hợp đối tác khiếu nại, kỳ chuyển sang `disputed` để Admin xử lý và nhập dòng điều chỉnh (Adjustment).
- **Đồng bộ hóa 5%:** Chỉnh sửa copy trên FAQ (BecomeAPartner) và mẫu hợp đồng (PartnerOnboardingWizard) về tỷ lệ phí 5% cố định để tránh tranh chấp pháp lý.

### Kiến thức kỹ thuật đã chốt

- **Kiến trúc & Component:** Áp dụng mô hình Service-Repository kết hợp Queue Job. `SettlementService` xử lý logic nghiệp vụ chốt kỳ, duyệt và nộp phí. `RevenueReportingService` quản lý truy vấn báo cáo GMV và Commission. Job `GenerateSettlementPeriodsJob` được kích hoạt tự động qua Scheduler chạy nền bằng Redis.
- **Cơ sở dữ liệu:**
  - Bảng `partner_settlement_periods` lưu thông tin kỳ đối soát, tổng GMV, commission và trạng thái nợ phí.
  - Bảng `settlement_line_items` lưu chi tiết booking trong kỳ.
  - Bảng `settlement_adjustments` lưu nhật ký dòng điều chỉnh tăng/giảm tiền hoa hồng do Admin nhập.
  - Cột `bookings.settlement_period_id` gán khóa ngoại để khóa dữ liệu và chống double-count.
  - Cột `bookings.payment_collected_at` (nullable) cho Partner đánh dấu đã thu tiền tại quầy.
- **Khóa dữ liệu (Locking):** Kích hoạt logic chặn sửa đổi booking tại `BookingService::update` khi `bookings.settlement_period_id` đã được gán vào kỳ đối soát không ở trạng thái `draft` hoặc `disputed`.
- **Bảo mật & Phân quyền:** Phân quyền API thông qua middleware JWT (`role:admin` cho Admin và `role:partner` cho Partner). Kiểm tra ownership nghiêm ngặt (`partner_id === Auth::id()`) tại Controller đối tác.
- **Tối ưu hiệu năng:** Job quét đơn chạy theo chunk 500 records. Eager Loading (`with('lineItems.booking.room')`) được áp dụng tại các API để tránh lỗi N+1 Query.
- **FE:** Partner Dashboard có cú pháp copy chuyển khoản chuẩn `BKS-SETTLE-{partner_id}-{period_code}`, cho phép tải bảng kê PDF/Excel và bấm Khiếu nại khi kỳ ở trạng thái `issued`.

## 2026-05-29 - Homepage gợi ý phòng theo điểm du lịch cụ thể

### Nguồn tham chiếu

- `docs/plans/plan_007_homepage_suggested_rooms_by_tourist_spot.md`
- `docs/plans/plan_004.md`, `docs/designs/design_004.md`
- `bks-system-fe/src/pages/EndUser/Home/components/SuggestedRoomsByProvince.tsx`
- `app/Services/RoomsService.php` (`handleSuggestedRoomsByProvince`)

### Kiến thức nghiệp vụ đã chốt (plan)

- Section homepage **"Phòng được gợi ý theo từng điểm đến"** sẽ dùng tab **tourist spot** (Sa Pa, Cát Bà, Lý Sơn, Bà Nà Hill, …), không còn tab tỉnh làm nguồn chính.
- Phòng vào tab khi có `room_tourist_spot_maps` tới spot tương ứng; sort: primary → rating → review count → travel time → updated_at.
- Tab không đủ phòng mapped → **ẩn tab** (mặc định); fallback theo `region_label` chỉ khi bật env `HOMEPAGE_SPOT_FALLBACK_REGION=true`.
- API mới: `GET /api/v1/home/rooms/rooms-by-tourist-spot`; API cũ `rooms-by-province` giữ tạm để rollback.

### Kiến thức kỹ thuật đã chốt (plan)

- Không migration schema mới; mở rộng seeder `tourist_spots` + query repository pattern giống `ROW_NUMBER() OVER (PARTITION BY tourist_spot_id ...)`.
- FE: component `SuggestedRoomsByTouristSpot`, constant `SUGGESTED_ROOM_SPOT_PRIORITY`, flag `VITE_HOMEPAGE_SUGGESTIONS_BY_SPOT`.
- CTA deep-link search: query `tourist_spot_slug` (phase D).

## 2026-05-25 - Cơ chế đánh giá kỳ nghỉ BKS Stay

### Nguồn tham chiếu

- `bks-system-fe/src/pages/EndUser/BksStay/BookingDetail.tsx`
- `bks-system-fe/src/hooks/useReviewQuery.ts`
- `bks-system-fe/src/api/reviewApi.ts`

### Kiến thức nghiệp vụ đã chốt

- Khối **đánh giá kỳ nghỉ** chỉ hiển thị khi booking đã ở trạng thái **hoàn thành** (`status = 3`) hoặc `stay_status = checked_out`.
- Một booking Stay có thể thu thập **hai loại đánh giá** trong cùng một lần gửi:
  - **Đánh giá phòng nghỉ**
  - **Đánh giá đối tác / chủ nhà**
- FE mặc định rating ban đầu cho cả hai phần là **5 sao**; **comment là tùy chọn**.
- Nếu booking đã có review trước đó, trang chi tiết booking hiển thị **danh sách review đã gửi ở chế độ read-only** thay vì mở lại form nhập mới.
- Hiện tại FE chưa có luồng **sửa / xóa** review; scope đang là **gửi mới** và **xem lại** review đã tồn tại.

### Kiến thức kỹ thuật đã chốt

- API gửi đánh giá dùng endpoint **`POST stay/reviews`** với payload:
  - `booking_id`
  - `room_rating`, `room_comment`
  - `partner_rating`, `partner_comment`
- API đọc review theo booking dùng **`GET stay/reviews/booking/{bookingId}`** để render lại trong `BookingDetail`.
- Dữ liệu review còn được tái sử dụng ở các bề mặt khác:
  - **Landing page:** `GET home/reviews`
  - **Room detail public:** `GET rooms/{id}/reviews`
  - **Partner detail public:** `GET partners/detail/{partnerId}/reviews`
- Sau khi submit thành công, FE invalidate tối thiểu:
  - `["bookingReviews", booking_id]`
  - `["landingReviews"]`

### Ghi chú vận hành / UX

- Cơ chế hiện tại gắn review trực tiếp với **booking đã lưu trú xong**, giúp đảm bảo review đến từ trải nghiệm có thật.
- Vì form và lịch sử review nằm chung trong `BookingDetail`, user không cần đi sang một route riêng để đánh giá.

## 2026-05-21 - Room-tourist mapping test cases

### Nguồn tham chiếu

- `docs/test-cases/testcase_004.md`
- `docs/SRC/srs_room_tourist_spot_mapping.md`
- `docs/plans/plan_004.md`

### Kiến thức kiểm thử đã chốt

- **Phạm vi kiểm thử public API**: xác nhận `tourist_summary` (name, travel_time_label, has_tourist_mapping) cho room card public, fallback an toàn khi thiếu mapping và mảng `tourist_spots` ở room detail.
- **Phạm vi kiểm thử admin CRUD & validation**: CRUD spot master và mapping, validation unique slug, category enum, travel time dương, distance không âm và duy nhất 1 primary mapping/phòng.
- **Kiểm thử cache & transaction**: cache invalidation sau khi thay đổi dữ liệu, transaction rollback khi ghi mapping lỗi, không để xảy ra N+1 query.

## 2026-05-21 - Room-tourist mapping implementation

### Nguồn tham chiếu

- `docs/plans/plan_004.md`
- `docs/designs/design_004.md`
- `docs/databases_docs/db_overview_etc_core_schema.md`

### Kiến thức triển khai đã chốt

- Migration thực tế đã tạo `tourist_spots` và `room_tourist_spot_maps`.
- `RoomsService` đã enrich public home/search/detail bằng `RoomTouristSummaryService`.
- Admin CRUD routes/controllers cho tourist spot và room-tourist map đã được wiring vào `routes/api.php`.
- Validation cho primary mapping, travel time và distance đã có request validators riêng.

### Trạng thái

- Code đã compile sạch trên các file backend vừa chạm.
- Runtime PHPUnit / feature execution vẫn cần chạy ở bước task tiếp theo nếu muốn đóng toàn bộ verification.

## 2026-05-21 - Room-tourist mapping plan

### Nguồn tham chiếu

- `docs/plans/plan_004.md`
- `docs/designs/design_004.md`
- `docs/SRC/srs_room_tourist_spot_mapping.md`

### Kiến thức triển khai đã chốt

- Thực thi theo 4 phase: schema foundation, public summary API, admin CRUD, verification.
- Public summary DTO phải được khóa ở Phase B2 trước khi FE/QA downstream bám vào.
- Cache invalidation dùng version-based approach để giữ public response ổn định.
- Admin CRUD phải đi qua service/repository layer, có transaction và validation rule cho primary spot / travel time / distance.

### Handoff tiếp theo

- Sau plan: `stack-task` có thể bắt đầu từ Phase A mà không cần đọc thêm scope ngoài plan.
- QA cần testcase requirement-centric cho public summary, fallback, admin CRUD và cache invalidation.

## 2026-05-21 - Room-tourist mapping design

### Nguồn tham chiếu

- `docs/designs/design_004.md`
- `docs/SRC/srs_room_tourist_spot_mapping.md`
- `docs/databases_docs/db_overview_etc_core_schema.md`

### Kiến thức kỹ thuật đã chốt

- Public home/search/room detail dùng chung một tourist summary DTO để tránh lệch payload.
- Admin CRUD cho `tourist_spots` và `room_tourist_spot_maps` đi qua service/repository layer, không thao tác DB trực tiếp từ controller.
- Cache ngắn hạn cho active tourist spots và room tourist summary, invalidation bằng version bump sau CRUD.
- Không tích hợp live routing trong design này; travel time tiếp tục là giá trị ước tính / quản trị.

### Kế hoạch kỹ thuật

- Phase 1: schema + model + repository.
- Phase 2: public summary API.
- Phase 3: admin CRUD + cache invalidation + validation.

## 2026-05-21 - Room-tourist mapping SRS

### Nguồn tham chiếu

- `docs/leads/lead_260521_room-tourist-mapping.md`
- `docs/SRC/srs_room_tourist_spot_mapping.md`
- `docs/databases_docs/db_overview_etc_core_schema.md`

### Kiến thức nghiệp vụ đã chốt

- Room public card trên home / search cần hiển thị tên điểm du lịch + thời gian di chuyển ước tính nếu có mapping.
- Scope ưu tiên gắn phòng nổi bật với các điểm đến nổi tiếng; không redesign homepage và không đổi route.
- Travel time trong scope này là giá trị ước tính / quản trị, không phải live routing.

### Kiến thức kỹ thuật đã chốt

- Schema canonical thêm `tourist_spots` và `room_tourist_spot_maps` để lưu master data + mapping.
- FE / API nên dùng cùng một payload room summary cho homepage và search results để tránh logic phân mảnh.
- Fallback khi thiếu mapping: ẩn nhãn du lịch hoặc quay về nhãn tỉnh / thành.

### Quyết định / trạng thái

- Bước tiếp theo sau analyze: `stack-design` để chốt API contract, chiến lược seed/admin data và cách ưu tiên điểm du lịch.

## 2026-05-14 - System Design D002 (Booking cancellation policy)

### Nguồn tham chiếu

- `docs/designs/design_002.md`
- `docs/SRC/srs_booking_cancellation_policy.md`
- `docs/databases_docs/db_overview_etc_core_schema.md`

### Kiến thức kỹ thuật đã chốt (design)

- **`BookingStatus::PENDING_CANCELLATION = 4`** trên DB; đồng bộ enum PHP.
- **API Stay:** `POST stay/bookings/{id}/cancel`, `POST stay/bookings/{id}/cancel-request`, `GET stay/cancellation-reasons` (Note: `POST stay/bookings/sync-local` has been decommissioned and removed).
- **API Partner:** `GET partner/cancellation-requests`, `POST …/approve`, `POST …/reject` (note reject ≥ 5 ký tự).
- **Cooldown:** env `CANCEL_REQUEST_COOLDOWN_SECONDS` mặc định **3600**; lỗi **429** + `retry_after_seconds`.
- **Reject:** khôi phục `bookings.status` từ cột **`previous_booking_status`** trên `booking_cancellation_requests` (snapshot lúc tạo request).
- **Conflict:** `ConflictChecker` **không** loại status 4 — đơn vẫn **giữ chỗ** cho đến khi approve hủy hoặc reject.
- **Master lý do:** bảng `cancellation_reason_codes` (seed BA).
- **Metric “treo”:** config `BCP_STALE_REQUEST_HOURS` mặc định **48**; đơn dài hạn ngưỡng đêm **`BCP_LONG_STAY_MIN_NIGHTS`** = **30**.

### Implementation

- **Phase B1–B2 (BE):** migration BCP, `GuestCancellationService`, Stay cancel/cancel-request, policy guest, Conflict/KPI/calendar semantics — theo `plan_002.md`.
- **Phase B3 (BE, 2026-05-14):** Partner inbox + resolve: `GET/POST /api/v1/partner/cancellation-requests`, `PartnerCancellationRequestService` (approve → cancelled + `BookingCancelled`; reject → restore status + xóa cột pending BCP), `CancellationRequestUpdated` broadcast + marker timeline, `api-doc/partner-cancellation-requests.js`.
- **Phase B3 (FE `bks-system-fe`, 2026-05-14):** trang inbox `/partner/cancellation-requests`, service API, Echo `.cancellation_request.updated` (invalidate + toast).
- **Phase B5 (BE, 2026-05-14):** `CancellationPolicyResolver` + `CancellationPolicyTierMatcher`; seed tier % placeholder trong `CancellationPolicyBaselineSeeder`; `GuestCancellationService` ghi snapshot + metadata tier trên timeline; `BookingCancellationMetricsService` + `GET /api/v1/admin/booking-cancellation-metrics` (SLA p50/p90, % pending stale); test `CancellationPolicyTierMatcherTest` (không cần DB).
- Kế hoạch chi tiết: `docs/plans/plan_002.md`. Handoff QC: **`docs/test-cases/testcase_002.md`** (TC002 BCP — kịch bản QC đầy đủ); tiếp theo `stack-review-branch` → `report-writer`.
- Landing page prominence docs now live under BE docs with renamed `design_003.md` and `plan_003.md`.

## 2026-05-14 - Implementation Plan P002 (BCP cancellation)

### Nguồn tham chiếu

- `docs/plans/plan_002.md`

### Tóm tắt

- **B1** migration + enum + ConflictChecker + config flag; **B2** Stay cancel/cancel-request; **B3** Partner inbox + broadcast; **B4** FE Stay/My Bookings wiring (Note: `sync-local` has been decommissioned and removed); **B5** policy % + báo cáo SLA.
- **Ước lượng:** ~90–110 giờ dev; flag `BCP_CANCELLATION_V1`.

## 2026-05-14 - Chính sách yêu cầu hủy phòng (My Bookings & Stay)

### Nguồn tham chiếu

- `docs/leads/lead_260513_booking-cancellation-policy.md`
- `docs/SRC/srs_booking_cancellation_policy.md`
- `docs/SRC/srs_partner_portal_360.md`
- `docs/databases_docs/db_overview_etc_core_schema.md`

### Kiến thức nghiệp vụ ổn định

- Hai lộ thao tác theo **bậc trạng thái đơn**: **`cancel`** (bậc thấp / Partner chưa xác nhận theo nghĩa đã chốt) và **`cancel-request`** (bậc cao / đã confirmed trở đi) → **`pending_cancellation`** chờ **Partner duyệt**.
- **Đang ở / đã check-in** không cho **hủy đặt** theo nghĩa business (tách với trả phòng sớm/no-show).
- **Lý do hủy bắt buộc**; phí/hoàn tiền **tạm không** tách theo Partner/loại phòng; bảng mốc thời gian tách **đơn ngắn hạn vs dài hạn** (cấu trúc + % sau research OTA + pháp lý VN).
- **Đồng bộ T6:** Đã hủy bỏ/Không sử dụng (Decommissioned). Việc lưu trữ cục bộ và đồng bộ khi login là dư thừa vì đơn đặt phòng đã có sẵn trên server ngay khi tạo.
- **Cooldown T7** giữa các lần **gửi lại** `cancel-request` trên cùng booking — tham số **N** chốt ở design (SRS đề xuất mặc định 60 phút cho dev).
- **Metric B7:** SLA Partner (`resolved_at - requested_at`), % không treo `pending_c`, hotline tag — đo DB + benchmark OTA.

### Kiến thức kỹ thuật ổn định

- Đề xuất mở rộng `bookings.status` với mã **`pending_cancellation`** (ví dụ **4**) cạnh các mã hiện có 0–3; cột `cancellation_policy_version` (các cột `client_local_id`, `client_fingerprint` đã được loại bỏ/không dùng).
- Bảng mới đề xuất: `booking_cancellation_requests`, `cancellation_policy_versions`, `cancellation_policy_tiers` (đã merge mô tả vào `db_overview_etc_core_schema.md`).
- **Ngưỡng đêm ngắn/dài** đề xuất căn chỉnh SRS Partner: **≥ 30 đêm = dài hạn**.

### Câu hỏi còn mở (giao stack-plan / BA)

- Map enum nội bộ “chờ thanh toán / chờ xác nhận” sang **cancel** vs **cancel-request** khi tách status DB.
- Giá trị **%** trong `cancellation_policy_tiers`: đã seed placeholder (B5); vẫn cần research OTA + pháp lý VN để thay số production.

### Đã chốt tại design D002 (tham chiếu `docs/designs/design_002.md`)

- Cooldown mặc định **3600s** (`CANCEL_REQUEST_COOLDOWN_SECONDS`); SLA “treo” **48h** (`BCP_STALE_REQUEST_HOURS`); ngưỡng đêm dài hạn **30** (`BCP_LONG_STAY_MIN_NIGHTS`).

## 2026-05-10 - Partner Portal 360 SRS

### Nguồn tham chiếu

- `docs/leads/lead_260510_partner-portal-360.md`
- `docs/SRC/srs_partner_portal_360.md`
- `docs/databases_docs/db_overview_etc_core_schema.md`
- `business-script/bks_srs_overview.md`
- `business-script/PRICING_RESTRUCTURE_PLAN.md`
- `business-script/E2E_BOOKING_PARTNER_USER_SCRIPT.md`

### Kiến thức nghiệp vụ ổn định

- BKS System được định vị là nền tảng kết hợp OTA, PMS và Connected Stay Portal.
- Role Partner là chủ nhà/đơn vị vận hành, quản lý tài sản, phòng, giá, lịch trống, booking, hợp đồng và doanh thu.
- Partner Portal phase hiện tại ưu tiên cải tiến Dashboard, Bookings và Calendar cho Serviced Apartment và Homestay.
- KPI chính của Partner Portal 360 gồm Occupancy Rate, GMV, time-to-confirm và Partner retention.
- Mô hình thương mại đang dùng cho lead này là commission-only, nên Dashboard cần hiển thị Net Revenue.
- Scope hiện tại loại trừ Channel Manager, native mobile app, payment/payout, AI, Chat, RBAC nội bộ Partner và onboarding/KYC.

### Kiến thức kỹ thuật ổn định

- Backend dùng Laravel với route group `/api/v1/partner`, middleware `jwt.auth` và `role:partner`.
- Frontend Partner Portal dùng React, các màn hình chính gồm `Dashboard.tsx`, `Bookings.tsx`, `Calendar.tsx`.
- Partner Dashboard redesign (2026-06-08): UI spec `docs/ui-designs/partner-dashboard/ui_design_v1.md`; plan `docs/plans/plan_012_partner_dashboard_redesign.md`. API `GET /partner/dashboard/stats` đã trả `todayCheckInCount`, `todayCheckOutCount`, `inStayCount`, `pendingCancellationCount` nhưng FE chưa render. Overbooking alert cần field mới trên KPI payload (DEC-260608-DASH-001).
- Dữ liệu ownership của Partner đi qua quan hệ `users -> properties -> rooms -> bookings`.
- Booking status hiện dùng `0 pending`, `1 confirmed`, `2 cancelled`, `3 completed`.
- Stay status hiện dùng `pending`, `checked_in`, `checked_out`, `no_show`.
- `contracts` đã có `contract_type`, `signature`, `signature_date`; booking dài hạn nên sinh `LEASE_AGREEMENT`, booking ngắn hạn dùng `TERMS_AND_CONDITIONS`.
- `utility_fees` đã tồn tại và liên kết `room_id`, phù hợp làm phụ lục/phí định kỳ cho hợp đồng dài hạn.

### Schema delta đã đề xuất

- Mở rộng `bookings`: `confirmed_at`, `cancelled_at`, `cancellation_reason`, `no_show_at`, `source`.
- Thêm `booking_timeline_events` để lưu audit/timeline từng booking.
- Thêm `room_blocks` để block lịch phòng mà không tạo booking giả.
- Mở rộng `contracts`: `renewal_reminder_at`, `terminated_at`, `termination_reason`.

### Câu hỏi còn mở

- Baseline time-to-confirm hiện tại chưa có.
- Chưa chốt pháp lý chữ ký hợp đồng dài hạn.

## 2026-05-10 - Partner Portal 360 System Design

### Nguồn tham chiếu

- `docs/designs/design_001.md`

### Quyết định kiến trúc đã chốt trong design pass

- Backend đang dùng Laravel 9.19, không thể dùng Reverb (yêu cầu Laravel 11). Realtime đi theo Pusher protocol qua **Soketi** (dev/staging) hoặc **Pusher Cloud** (prod). FE đã có sẵn `laravel-echo` và `pusher-js`.
- BE thêm dependency `pusher/pusher-php-server`; broadcast driver dùng `pusher`; queue dùng Redis.
- Channel realtime gồm `private-partner.{partnerId}` và `private-property.{propertyId}`. Authorize qua `BroadcastAuthController` mới đọc JWT và đối chiếu ownership.
- Conflict check không dùng exclusion constraint (MySQL không có). Dùng pessimistic lock theo `room_id` trong transaction confirm/move + index `bookings(room_id, start_date, end_date, status)`.
- Dashboard KPI cache Redis 60 giây/key, invalidate khi event `BookingConfirmed/Cancelled/NoShow/RoomBlockChanged` thuộc partner đó.
- SLA time-to-confirm chốt 5 phút; overbooking xử lý theo block tuyệt đối; bulk confirm/cancel tối đa 20 booking/lần.

### Component mới hoặc mở rộng

- Controllers mới: `PartnerRoomBlockController`, `PartnerCalendarController`, `PartnerBookingTimelineController`, `BroadcastAuthController`.
- Services mới: `RoomBlockService`, `BookingTimelineService`, `PartnerKpiService`, `ConflictChecker`.
- Repositories mới: `RoomBlockRepository`, `BookingTimelineRepository`.
- Policies mới: `BookingPolicy`, `RoomBlockPolicy`.
- Events mới: `BookingCreated`, `BookingConfirmed`, `BookingCancelled`, `RoomBlockChanged` (đều `ShouldBroadcast`).
- Listener mới: `RecordBookingTimeline` (queue `redis:broadcast`).

### Endpoint mới chính

- `GET /api/v1/partner/dashboard/kpis`
- `GET /api/v1/partner/dashboard/charts/occupancy`
- `GET /api/v1/partner/dashboard/charts/gmv`
- `PUT /api/v1/partner/bookings/{id}/no-show`
- `GET /api/v1/partner/bookings/{id}/timeline`
- `POST /api/v1/partner/bookings/bulk-confirm`
- `POST /api/v1/partner/bookings/bulk-cancel`
- `GET /api/v1/partner/calendar`
- `POST /api/v1/partner/room-blocks`
- `GET /api/v1/partner/room-blocks`
- `DELETE /api/v1/partner/room-blocks/{id}`
- `POST /broadcasting/auth` (custom JWT)

### Migration / rollout

- Triển khai theo 5 phase, có feature flag `PARTNER_360_ENABLED` và `VITE_PARTNER_REALTIME` để rollback nhanh.
- Backfill `bookings.confirmed_at = updated_at` cho `status = confirmed` để có baseline KPI; ghi `metadata.backfilled = true` ở timeline.

## 2026-05-10 - Partner Portal 360 Implementation Plan

### Nguồn tham chiếu

- `docs/plans/plan_001.md`
- `docs/designs/design_001.md`

### Cấu trúc kế hoạch triển khai

- 5 phase tuần tự, mỗi phase ≈ 1 sprint, tổng ~58 task ở mức 2–4 giờ/đầu việc, tổng ~168 giờ dev (~21 ngày người).
- Phase 1 Foundation: schema + audit timeline + KPI baseline + endpoint `/dashboard/kpis` (35h, 14 task).
- Phase 2 Realtime + Quick Confirm: Pusher protocol, BroadcastAuthController JWT, FE Echo + quick confirm + cancel dialog + polling fallback (41h, 14 task).
- Phase 3 Calendar + Room Block: room_blocks, ConflictChecker + pessimistic lock, all-properties view, drag-drop revert (47h, 16 task).
- Phase 4 Dashboard KPI nâng cao + Bulk action: charts 30 ngày, alert center, bulk endpoints + UI (30h, 9 task).
- Phase 5 Long-term Contract subset: ContractService renewal/termination + scheduler + FE detail + badge + alert (19h, 7 task).

### Quy ước branch và conflict resolution

- Mỗi phase = 1 PR + 1 feature branch `feature/pp360-phase{N}` từ `develop`.
- 10 conflict điểm chính đã được xác định (file `BookingService.php`, `routes/api.php`, `EventServiceProvider.php`, FE `Bookings.tsx`, FE `Calendar.tsx`, …); resolution là merge tuần tự + rebase trước khi mở PR phase kế.
- Mỗi PR migration phải kèm cập nhật `Nhật ký thay đổi` của `db_overview_etc_core_schema.md`.

### Downstream handoff

- `stack-task` chạy tuần tự theo plan; commit message dạng `feat(pp360): T1.9 confirm booking writes confirmed_at`.
- `stack-testcase` sinh `docs/test-cases/testcase_001.md` ánh xạ 100% requirement SRS PP360-* sang Given/When/Then.
- `stack-review-branch` chạy trước mỗi merge phase; tiêu chí pass = không còn finding High.
- `report-writer` tạo `docs/reports/release_pp360_phaseN.md` sau mỗi phase merge.

## 2026-05-10 - Partner Portal 360 Phase 1 Implementation

### Nguồn tham chiếu

- `docs/plans/plan_001.md` (Phase 1 ✅ DONE)
- 14 task hoàn tất, 16 unit tests xanh (46 assertions)

### Schema thực tế đã ship

- `database/migrations/2026_05_10_120001_add_partner_portal_360_columns_to_bookings_table.php`: thêm `confirmed_at`, `cancelled_at`, `cancellation_reason`, `no_show_at`, `source` + 5 index (`idx_bookings_confirmed_at`, `idx_bookings_cancelled_at`, `idx_bookings_status_created_at`, `idx_bookings_room_dates_status`, `idx_bookings_source`).
- `database/migrations/2026_05_10_120002_add_renewal_fields_to_contracts_table.php`: thêm `renewal_reminder_at`, `terminated_at`, `termination_reason` + index `idx_contracts_renewal_reminder`.
- `database/migrations/2026_05_10_120003_create_booking_timeline_events_table.php`: bảng audit append-only với FK CASCADE→bookings, SET NULL→users; index `(booking_id, created_at)`, `event_type`, `actor_id`.

### Component đã ship Phase 1

- Models: `App\Models\BookingTimelineEvent` (final, casts metadata→array). Bổ sung relation `Booking::timelineEvents()`.
- Repositories: `BookingTimelineRepository` + interface, bind ở `RepositoryServiceProvider` (singleton).
- Services: `BookingTimelineService` (8 method record + constants event_type / status). `PartnerKpiService` (computeDashboard MTD-only, cache 60s, commission 5%, exclude backfilled rows).
- Policies: `BookingPolicy` (before-hook admin bypass, ability `view/confirm/cancel/noShow/update`), đăng ký ở `AuthServiceProvider`.
- Controllers: `PartnerBookingController` thêm `confirm/cancel/noShow`. `PartnerDashboardController` thêm `getKpis`.
- Routes: `PUT /partner/bookings/{id}/{confirm|cancel|no-show}` chuyển sang `PartnerBookingController`. `GET /partner/dashboard/kpis` mới.
- Validations: `BookingValidation` thêm `partnerCancelBookingValidation` (reason required min:5 max:500) và `partnerNoShowBookingValidation`.
- Console: `BackfillBookingConfirmedAt` artisan command (`partner:backfill-confirmed-at`, `--dry-run`, `--chunk=500`).
- I18n: thêm key `confirmed_successfully`, `already_confirmed`, `cancellation_reason_required`, `no_show_*` trong `lang/{vi,en}/booking.php`.

### Logic nghiệp vụ chốt ở Phase 1

- Confirm idempotent: booking đã CONFIRMED → trả `already_confirmed`, không sinh contract trùng. Sinh `LEASE_AGREEMENT` cho `can-ho/apartment/can-ho-dich-vu`, còn lại `TERMS_AND_CONDITIONS` (auto-signed).
- Cancel: partner role bắt buộc reason; admin/system fallback message tự động. Timeline ghi `from_status` → `cancelled` với note = reason.
- No-show: chỉ cho phép khi status = CONFIRMED và `start_date <= today` (Asia/Ho_Chi_Minh). Set `stay_status='no_show'`, `no_show_at`, free room, ghi timeline.
- KPI MTD: GMV dùng formula sẵn có (`getRevenueByMonthForPartner`), Net = GMV × 0.95. AvgConfirmSeconds tính bằng `TIMESTAMPDIFF(SECOND, created_at, confirmed_at)`, exclude row có timeline event_type='backfilled' qua `whereNotExists`.
- Cache key: `partner:{id}:kpi:dashboard`, TTL 60s, invalidation thủ công sẽ được implement Phase 4 (T4.3).

### Hoãn lại từ Phase 1

- Skeleton event class `BookingConfirmed/Cancelled` → Phase 2 (xem DEC-260510-PP360-011).
- Filter query params (`property_id`, `from`, `to`) cho `/dashboard/kpis` → Phase 4 (xem DEC-260510-PP360-012).
- Feature tests HTTP-layer cho confirm/cancel/no-show/kpis → cần test DB infra (chưa setup); sẽ handoff cho `stack-testcase`.

### Test results

- `BookingTimelineServiceTest`: 5/5 ✅ (20 assertions)
- `PartnerKpiServiceTest`: 4/4 ✅ (13 assertions)
- `BookingPolicyTest`: 7/7 ✅ (13 assertions)
- Tổng: **16/16 unit tests, 46 assertions**, chạy bằng `vendor/bin/phpunit`.

## 2026-05-10 - Partner Portal 360 Phase 2 Implementation

### Nguồn tham chiếu

- `docs/plans/plan_001.md` (Phase 2 ✅ DONE)
- 14 task hoàn tất; 16 unit tests Phase 1 vẫn xanh; E2E qua MCP chrome-devtools 4/5 TC pass (TC-2.14 cần infra Pusher để chạy thủ công).

### Component đã ship Phase 2 (Backend)

- Composer: `pusher/pusher-php-server ^7.2` (auto-bumped để tương thích psr/log v3).
- Provider: `App\Providers\BroadcastServiceProvider` chỉ load `routes/channels.php`, KHÔNG `Broadcast::routes()` (xem DEC-260510-PP360-015). Đăng ký trong `config/app.php`.
- Controller: `App\Http\Controllers\BroadcastAuthController::authenticate` — đọc JWT qua `Auth::guard('api')`, trả 200 (signed payload) / 401 / 403, ghi log `broadcast_auth_denied`/`broadcast_auth_error`.
- Route: `POST /api/v1/broadcasting/auth` middleware `jwt.auth`.
- Channels (`routes/channels.php`):
  - `partner.{partnerId}` → cho phép khi `Auth::user()->id === partnerId`.
  - `property.{propertyId}` → cho phép khi `Property::find($propertyId)->user_id === Auth::user()->id`.
- Events `ShouldBroadcast`: `App\Events\BookingCreated|BookingConfirmed|BookingCancelled` với `broadcastOn` = `[private-partner.{X}, private-property.{Y}]`, `broadcastAs` = `booking.{created|confirmed|cancelled}`. Payload `broadcastWith()` chỉ gồm id/status/room_id/partner_id/property_id/dates/timestamps/actor_id; KHÔNG gửi PII (name/email/phone/note/reason text).
- Listener: `App\Listeners\RecordBookingTimeline implements ShouldQueue` — ghi marker `event_type='broadcast_dispatched'` cho audit realtime, KHÔNG ghi trùng timeline transition của Phase 1 (xem DEC-260510-PP360-014).
- Service hook: `BookingService` thêm `resolveBroadcastScope()` + `safeDispatch()`; dispatch event sau `DB::commit()` cho `handleCreateBooking|handleConfirmBooking|handleCancelBooking`.
- Config: `config/broadcasting.php` thêm `cluster` vào `pusher.options` để pusher-js v8 đọc.
- ENV: `.env.example` thêm hướng dẫn `PUSHER_*` cho cả Soketi (local) lẫn Pusher Cloud (prod), `PARTNER_360_ENABLED` flag.
- Infra: `docker-compose.soketi.yml` (image `quay.io/soketi/soketi:1.6-16-alpine`, port 6001 + 9601 metrics).
- Doc: `docs/runbooks/realtime_setup.md` step-by-step Soketi local + Pusher Cloud + rate limit + troubleshooting.

### Component đã ship Phase 2 (Frontend)

- `src/lib/echoClient.ts`: singleton Echo `pusher` broadcaster; authorizer custom POST `/broadcasting/auth` với `Authorization: Bearer <jwt>` (đọc qua `getAccessToken()`). Decode `sub` từ JWT để biết user id. Feature flag `VITE_PARTNER_REALTIME=false` để tắt hoàn toàn realtime.
- `src/store/useUserStore.ts`: gọi `disconnectEcho()` khi logout để giải phóng socket.
- `src/hooks/Partner/useBookingsRealtime.ts`: subscribe `private-partner.{userId}`, lắng 3 event, invalidate keys `['partner','bookings']`, `['partner','dashboard','kpis']`, `['partner-stats']`, `['partner-pending-bookings']`. Tích hợp polling fallback: nếu Echo `disconnected` > 5s → bật polling 30s. Cleanup channel + listener khi unmount (xem DEC-260510-PP360-017).
- `src/pages/Partner/components/RealtimeNotifyProvider.tsx`: dùng hook + show toast (`Có booking mới` / `Booking đã xác nhận` / `Booking bị huỷ`) + banner amber khi polling. Phát `CustomEvent("partner:realtime-booking")` cho Header.
- `src/pages/Partner/PartnerLayout.tsx`: bọc `<main>` trong `<RealtimeNotifyProvider>`.
- `src/pages/Partner/components/Header.tsx`: thêm nút Bell badge counter, lắng `partner:realtime-booking`, click → navigate `/partner/bookings?status=pending`.
- `src/hooks/Partner/useQuickConfirm.ts`: optimistic confirm + cửa sổ undo 30s; gọi API thực sau timeout; xử lý 409 conflict (revert UI + toast lỗi).
- `src/pages/Partner/components/CancelBookingDialog.tsx`: form reason 5–500 ký tự, counter realtime, button submit disabled khi invalid, hiển thị error từ response.
- `src/services/partnerService.ts`: thêm `quickConfirm`, `noShowBooking`, `cancelBooking(id, reason?)` (truyền body `{reason}` khi có).
- `src/pages/Partner/{Bookings,Dashboard}.tsx`: wire `useQuickConfirm` + `CancelBookingDialog`. Nút "Hoàn tác (XXs)" với đếm ngược.

### Logic nghiệp vụ chốt ở Phase 2

- Channel auth: 1 endpoint duy nhất qua JWT, callback ép kiểu int để chống type juggling. 401/403 phân biệt rõ.
- Broadcast payload an toàn: bằng `Booking::withoutRelations()` trong constructor event → SerializesModels không kéo theo eager-loaded relations với PII.
- safeDispatch: lỗi network/queue khi dispatch không bao giờ phá business flow (booking đã commit). Logged warning để ops giám sát.
- Polling fallback: chỉ kích hoạt khi WS thật sự không khả dụng > 5s; reconnect → tắt polling. Tần suất 30s khớp SLA worst-case.
- Quick confirm undo: optimistic UI ngay lập tức + đếm ngược 30s; API chỉ gọi sau timeout. 409 conflict → revert + toast.

### Hoãn / Phase tiếp theo

- BROADCAST_CONNECTION sang `redis` queue → Phase 4 (giảm block khi Pusher slow).
- Channel isolation feature test 2 partner thật → Phase 5 hoặc QC manual khi Soketi sẵn sàng (đã document trong `business-script/E2E_PARTNER_PORTAL_360_PHASE2.md`).
- Unit test cho `useQuickConfirm`/`useBookingsRealtime` (RTL) → khi FE thêm test infra.

### Test results

- Phase 1 unit tests: **16/16 ✅ (46 assertions)** — không regression.
- E2E qua MCP chrome-devtools (`partner@gmail.com` / `123456a!`):
  - TC-2.10 Header badge realtime: ✅
  - TC-2.11 Quick confirm + undo 30s (booking #127): ✅
  - TC-2.12 Cancel dialog reason validation → `PUT /bookings/127/cancel` 200, body `{reason}`: ✅
  - TC-2.13 Polling fallback banner + refetch 30s: ✅
  - TC-2.14 Channel isolation: documented for manual run khi infra Pusher sẵn sàng.

## 2026-05-10 - Partner Portal 360 Phase 3 Implementation

### Nguồn tham chiếu

- `docs/plans/plan_001.md` (Phase 3 ✅ DONE)
- 16 task hoàn tất; toàn bộ 28 unit tests xanh (Phase 1: 16, Phase 3: 12 mới — `ConflictCheckerTest` 6 + `RoomBlockServiceTest` 6).

### Component đã ship Phase 3 (Backend)

- Migration `room_blocks` (`2026_05_10_120004_create_room_blocks_table.php`) với CHECK constraints qua raw SQL (`chk_rb_dates`, `chk_rb_block_type`) — Laravel 9 schema builder không có API native cho CHECK.
- Model `App\Models\RoomBlock` (`final`, casts date, hằng số `BLOCK_TYPE_*`); repo `RoomBlockRepository` extends `BaseRepository` với `listForRoomsInRange`, `findConflicting`. Đăng ký binding ở `RepositoryServiceProvider`.
- `App\Policies\RoomBlockPolicy`: ownership qua `Room.property.user_id === Auth::id()`, admin bypass qua `before`.
- `App\Services\ConflictChecker` (non-final để mockable): `findConflicts(roomId, start, end, excludeBookingId, excludeBlockId, useLock)` truy vấn cả `bookings` (loại `CANCELLED|COMPLETED`) và `room_blocks`; static `intervalsOverlap(a1,a2,b1,b2)` cho unit test logic. Quy ước `[start,end)` exclusive — back-to-back KHÔNG conflict.
- `App\Services\RoomBlockService`: `create` chạy trong `DB::transaction` + `lockForUpdate` qua ConflictChecker; conflict trả `code=ROOM_BLOCK_CONFLICT` + payload chi tiết. `delete` policy + dispatch `RoomBlockChanged`. `safeDispatch`-style try/catch để failure broadcast không phá flow.
- `App\Services\PartnerCalendarService`: `getCalendar(partnerId, propertyId?, roomId?, from, to)` cap 31 ngày, cache 30s qua key `calendar:{partnerId}:v{version}:{scope}:{room}:{from}:{to}` (version-pointer pattern). Eager-load `room/user/price` để enrich payload (`room_label`, `room_title`, `guest_name`, `guest_phone`, `total_amount = price × số đêm`).
- `App\Listeners\InvalidateCalendarCache implements ShouldQueue`: handle `BookingCreated|BookingConfirmed|BookingCancelled|RoomBlockChanged` → `bumpVersion(partnerId)` (Cache::increment với fallback timestamp).
- `App\Services\BookingService::handleConfirmBooking` refactor: trong `DB::transaction` → ConflictChecker với `useLock=true` → conflict ⇒ `success=false`, ghi `BookingTimelineService::recordConflictDetected(bookingId, null, ['operation'=>'confirm', ...])`, return `code=BOOKING_CONFLICT`. Bổ sung `handleMove(bookingId, partnerId, payload)` cho drag-drop (chỉ cho phép booking active: `PENDING|CONFIRMED`).
- `App\Http\Controllers\Partner\PartnerBookingController`:
  - `confirm` map `code=BOOKING_CONFLICT` → 409 với data conflicts.
  - `move` (mới) — validate body, gọi service, 409 trên conflict.
- `App\Http\Controllers\Partner\PartnerRoomBlockController`: `index/store/destroy`. `RoomBlockValidation` cung cấp rules (block_type enum, date range, reason ≤ 255). Conflict trả 409 + `code=ROOM_BLOCK_CONFLICT`.
- `App\Http\Controllers\Partner\PartnerCalendarController`: `index` (max 31 ngày, optional `property_id`/`room_id`).
- `App\Events\RoomBlockChanged implements ShouldBroadcast`: kênh `private-partner.{partnerId}` + `private-property.{propertyId}`, `broadcastAs='room_block.changed'`, payload không PII.
- Routes mới (`routes/api.php` group partner):
  - `GET|POST /partner/room-blocks`, `DELETE /partner/room-blocks/{id}`.
  - `GET /partner/calendar`.
  - `PUT /partner/bookings/{id}/move`.
- Enum: `App\Enums\HttpStatus::CONFLICT = 409`.
- Lang: `resources/lang/{vi,en}/room_block.php` + bổ sung `booking.php` (`confirm_conflict`, `move_conflict`, `move_only_for_active`, `moved_successfully`).

### Component đã ship Phase 3 (Frontend)

- `src/services/partnerService.ts`: thêm `getCalendar`, `getRoomBlocks`, `createRoomBlock`, `deleteRoomBlock`, `moveBooking`.
- `src/hooks/Partner/useCalendar.ts`: TanStack Query hook (key `['partner','calendar', property|'all', room|'any', from, to]`), staleTime 30s; export `useInvalidatePartnerCalendar` + types `PartnerCalendarBooking/Block/Payload`.
- `src/hooks/Partner/useBookingsRealtime.ts`: mở rộng `BookingEventName` thêm `room_block.changed`, listen cùng một channel partner; mọi event đều invalidate prefix `['partner','calendar']`. Polling fallback cũng invalidate calendar.
- `src/pages/Partner/components/RoomBlockDialog.tsx` (mới): form chọn phòng/range/loại/lý do/note; xử lý 409/403/422.
- `src/pages/Partner/Calendar.tsx` (refactor):
  - Option "Tất cả tài sản" (`__all__`) → propertyId=null; merge rooms từ tất cả properties (Promise.all).
  - Render booking + block với màu/style khác (block có `repeating-linear-gradient` stripe).
  - Banner cảnh báo overbooking khi cùng `room_id` có 2+ booking giao nhau theo interval `[start,end)`.
  - Drag-drop bookings (FullCalendar `editable` + `eventAllow` chỉ `kind=='booking'`); drop/resize → `partnerService.moveBooking`; 409 → `info.revert()` + toast.
  - Dialog detail mode booking (Duyệt/Từ chối/Check-in/Check-out) hoặc block (Gỡ block).
  - Datesset → cập nhật `range`; range tự refetch.

### Logic nghiệp vụ chốt ở Phase 3

- Conflict semantics chốt: `[a,b) ∩ [c,d) ≠ ∅ ⇔ a<d ∧ c<b`. Back-to-back = NOT conflict.
- Booking conflict loại trừ `CANCELLED|COMPLETED`; block luôn luôn tính.
- Pessimistic lock theo `room_id` (cả `bookings` + `room_blocks`) trong `DB::transaction` cho confirm/move/create-block.
- Calendar API max 31 ngày; cache 30s; version-pointer invalidation.
- Realtime payload `room_block.changed` không PII (id/room_id/dates/block_type/property_id/partner_id/action/actor_id).
- FE phân biệt error code (`BOOKING_CONFLICT` / `ROOM_BLOCK_CONFLICT`) để hiển thị UI chuyên biệt.

### Hoãn / Phase tiếp theo

- Concurrency feature test (2 confirm song song dùng `pcntl_fork`) → CI Linux phase release-hardening (Windows dev không hỗ trợ pcntl).
- URL query sync cho property/room/range filter calendar → backlog UX.
- Cap số rooms trong "Tất cả tài sản" + virtualization khi partner > 200 phòng → Phase 4 nếu cần.

### Test results

- Unit suite: **28/28 ✅** (Phase 1: 16; Phase 3: ConflictCheckerTest 6 + RoomBlockServiceTest 6).
- ConflictChecker test cover: overlap, fully-contains, back-to-back (NOT conflict), disjoint, identical, single-day overlap.
- RoomBlockServiceTest cover: success+dispatch, conflict path, policy denies, invalid date range, delete success+dispatch, delete unauthorized.
- Lint FE/PHP: clean trên các file đã chạm.

## 2026-05-12 - Partner Portal 360 Phase 4 Implementation

### Nguồn tham chiếu

- `docs/plans/plan_001.md` (Phase 4 ✅ DONE)
- Backend Unit suite: **31/31 ✅ (80 assertions)**.
- Frontend `npm run build`: ✅ pass sau fix Recharts formatter type.

### Component đã ship Phase 4 (Backend)

- `PartnerKpiService` mở rộng:
  - `GET /partner/dashboard/charts/occupancy`: 30 điểm ngày liên tục, tính occupancy theo distinct rooms có booking `CONFIRMED|COMPLETED` overlap từng ngày, end_date exclusive.
  - `GET /partner/dashboard/charts/gmv`: 30 điểm ngày liên tục, GMV group theo `bookings.start_date`, net revenue = GMV × 95%.
  - Cache slots: `partner:{id}:kpi:dashboard`, `partner:{id}:kpi:charts:occupancy`, `partner:{id}:kpi:charts:gmv`.
- `InvalidatePartnerKpiCache`: clear explicit KPI cache keys khi `BookingCreated|BookingConfirmed|BookingCancelled|BookingNoShow|RoomBlockChanged`.
- Event mới `BookingNoShow` (non-broadcast domain event) để no-show cũng invalidate KPI cache sau commit.
- Bulk endpoints:
  - `POST /api/v1/partner/bookings/bulk-confirm`.
  - `POST /api/v1/partner/bookings/bulk-cancel`.
  - Request validation `BulkBookingActionRequest`: `ids` required array min 1 max 20, distinct, exists; `bulk-cancel` required `reason` 5-500.
  - `BookingService::handleBulkConfirm|handleBulkCancel` gọi lại single-action handlers để giữ policy, lock, timeline và broadcast. Mỗi booking độc lập; failed item không rollback succeeded item.

### Component đã ship Phase 4 (Frontend)

- Dashboard:
  - KPI cards mới: `Time-to-confirm TB` và `Net Revenue` có tooltip công thức.
  - `OccupancyChart` và `GmvChart` dùng `recharts`.
  - `AlertCenter`: 3 nhóm pending booking, overbooking CTA calendar, contract renewal placeholder Phase 5.
- Bookings:
  - Checkbox chọn nhiều booking, cap 20 item.
  - Bulk toolbar: `Xác nhận hàng loạt`, `Huỷ hàng loạt`, `Bỏ chọn`.
  - Reuse `CancelBookingDialog` cho reason bulk cancel.
  - Hiển thị kết quả `{succeeded, failed}` với id + reason.

### Test / Verify

- `vendor\bin\phpunit tests\Unit\Services\PartnerKpiServiceTest.php tests\Unit\Http\Requests\BulkBookingActionRequestTest.php`: **5/5 ✅, 14 assertions**.
- `vendor\bin\phpunit --testsuite Unit`: **31/31 ✅, 80 assertions**.
- `npx eslint <Phase 4 FE files>`: **0 errors**, còn warnings sẵn có kiểu `any`/Tailwind order.
- `npm run build`: **✅ pass**; còn Vite/Rollup warnings hiện hữu (chunk size, Recharts circular re-export ở file khác, lottie eval).

### Hoãn / Handoff

- Feature test DB-level bulk concurrency/deadlock chưa tự động hóa vì `phpunit.xml` chưa cấu hình DB testing riêng; handoff QC/CI chạy trên database isolated.
- Toggle Apartment/Homestay cho charts chưa bật vì BE endpoint chưa expose `property_type` filter; hiện chart là all-properties của partner.

## 2026-05-12 - Partner Portal 360 Phase 5 Implementation

### Nguồn tham chiếu

- `docs/plans/plan_001.md` (Phase 5 ✅ DONE)
- Backend Unit suite: **34/34 ✅ (96 assertions)**.
- Frontend `npm run build`: ✅ pass (sau khi xử lý nullable `nights`).

### Component đã ship Phase 5 (Backend)

- `ContractService`:
  - `setRenewalReminder(int $contractId, Carbon $remindAt)` — idempotent, từ chối non-LEASE/terminated, dispatch `ContractRenewalReminderQueued`.
  - `terminate(int $contractId, string $reason)` — reason ≥ 5 ký tự, idempotent (gọi lại → `CONTRACT_ALREADY_TERMINATED`).
  - `handleGetExpiringContractsForPartner()` — payload tối giản cho Alert Center.
  - `processDueReminders(int $daysAhead = 30)` — entry point cho scheduler, gọi repository + tag từng item.
- `EloquentContractRepository`:
  - `getLongTermContractsDueForReminder(int $daysAhead)`: query `contract_type=LEASE_AGREEMENT`, `renewal_reminder_at NULL`, `terminated_at NULL`, `booking.end_date BETWEEN today AND today+N`.
  - `getExpiringContractsForPartner(int $partnerId)`: alert listing.
  - `getPartnerContractDetail` eager-load thêm `booking.room.utilityFees`.
- `ContractPolicy` mới: admin bypass; partner check ownership qua `Booking → Room → Property.user_id` (`view`, `manageRenewal`, `terminate`).
- Console: `App\Console\Commands\SendContractRenewalReminders` (signature `partner:send-contract-renewal-reminders --days=30 --chunk=...`) + scheduler `dailyAt('06:00')->timezone('Asia/Ho_Chi_Minh')->withoutOverlapping()->onOneServer()`.
- Event mới `ContractRenewalReminderQueued` (`ShouldBroadcast`, channels `private-partner.{id}` + `private-property.{id}`, alias `contract.renewal_reminder`).
- Middleware `EnsurePartner360Enabled` (alias `partner360`): trả 403 `PARTNER_360_DISABLED` khi `config('app.partner_360_enabled')` (fallback env `PARTNER_360_ENABLED`) là false. Gắn vào: `/calendar`, `/room-blocks/*`, `/bookings/bulk-*`, `/bookings/{id}/move`, `/dashboard/charts/*`, và 3 endpoint contract Phase 5.
- `PartnerContractController` thêm: `expiringSoon()`, `setRenewalReminder()`, `terminate()`. Mapping error code → HTTP status (404/403/422/400) trong controller.

### Component đã ship Phase 5 (Frontend)

- `src/lib/featureFlags.ts` (mới): `isPartner360Enabled()` / `isPartnerRealtimeEnabled()` đọc `VITE_PARTNER_REALTIME`.
- `src/pages/Partner/ContractDetail.tsx` (mới) tại route `/partner/contracts/:id`: 3 card (thời hạn/khách-tài sản/utility_fees) + 2 CTA "Đánh dấu nhắc gia hạn" và "Chấm dứt hợp đồng" (dialog reason ≥5 chars). Detail-modal cũ trong `Contracts.tsx` được gỡ.
- Calendar badge "Contract":
  - Tính `nights` qua `countPartnerBookingNightsExclusive`; `isLongTerm = nights >= 30`.
  - Inline badge nhỏ ở góc phải event tile.
  - Badge to "Contract · N đêm" trong dialog chi tiết booking.
- AlertCenter:
  - Hook mới `useExpiringContracts` (TanStack Query, stale 60s).
  - Tile "Contract sắp hết hạn" hiển thị số thật + tóm tắt hợp đồng sớm nhất (date + guest); nút "Hợp đồng" navigate đến detail trực tiếp.
- Realtime:
  - `useBookingsRealtime` listen `.contract.renewal_reminder` → invalidate prefix `['partner','contracts']`.
- Bookings:
  - Bulk toolbar ẩn khi `isPartner360Enabled() === false`.
- Calendar:
  - Nút "Tạo block" ẩn khi `isPartner360Enabled() === false`.

### Test / Verify

- `vendor\bin\phpunit --testsuite Unit`: **34/34 ✅, 96 assertions** (ContractServiceTest 8 + EnsurePartner360EnabledTest 2 + Phase 3-4 cũ 24).
- `npm run build`: **✅ pass** (warnings sẵn có: chunk size, Recharts circular re-export, lottie eval).

### Hoãn / Handoff

- Feature concurrency test cho scheduler vs partner thao tác terminate hoãn tới QC do `phpunit.xml` chưa có DB testing.
- Renewal reminder hiện set một lần (không nhắc lặp). Re-trigger sau N ngày sẽ do partner manual hoặc thêm task backlog.
- Chấm dứt hợp đồng không tự update Booking status — booking handling sau termination thuộc backlog operations.

## 2026-06-18 - Partner room maintenance (bảo trì phòng)

### Nguồn tham chiếu

- `docs/SRC/srs_partner_maintenance.md`
- `docs/plans/plan_014_partner_maintenance.md`
- `docs/architecture/data-dictionary.md` §2.3.5
- `docs/databases_docs/db_overview_etc_core_schema.md`
- `app/Services/RoomBlockService.php`, `app/Services/ConflictChecker.php`

### Kiến thức nghiệp vụ đã chốt (plan)

- **Hai entity:** `room_maintenances` = phiếu công việc (lifecycle); `room_blocks` = khóa tồn bán trên Calendar.
- **Đồng bộ:** Khi tạo phiếu với `block_calendar=true` (mặc định), hệ thống tạo `room_block` type `maintenance` và lưu `room_block_id`. Khi `completed`/`cancelled`, gỡ block liên kết.
- **State machine:** `planned` → `in_progress` → `completed` | `cancelled`; không hủy phiếu `completed`.
- **Conflict:** Nếu `block_calendar=true` và overlap booking/block active → 409. Nếu `block_calendar=false`, cho phép ghi nhận sự cố kể cả khi có khách đang ở.
- **Out of scope phase 1:** End User báo sự cố, realtime event, recurring maintenance, vendor assignment.

### Kiến thức kỹ thuật đã chốt (plan)

- **Migration:** Thêm `room_block_id`, `block_calendar`, `source`, `cancellation_reason`, `started_at`, `completed_at`, `cancelled_at` + index scope Partner.
- **Service mới:** `MaintenanceBlockSyncService` delegate `RoomBlockService` (không duplicate `ConflictChecker`).
- **API Partner:** Bổ sung `GET /{id}`, `PATCH /{id}`; fix pagination `page`/`per_page`; `RoomMaintenancePolicy` ownership.
- **FE:** Sửa `Maintenances.tsx` (bỏ mock status), `RoomDetail` (block_calendar + conflict UI), `MaintenanceSection` CTA tiếp nhận.
- **Tests:** `PartnerRoomMaintenanceTest` feature + unit state machine.
- **Handoff:** `testcase_014_partner_maintenance.md` qua `stack-testcase`; thực thi qua `stack-task`.

### Phase 1 đã ship (2026-06-18)

- **Migration:** `database/migrations/2026_06_18_120000_extend_room_maintenances_for_partner_lifecycle.php` — chạy thành công trên DB dev.
- **Model:** `RoomMaintenance` → `final`, constants status/type/source, relations `room`, `property`, `roomBlock`, `creator`.
- **Seeder:** `RoomMaintenancesSeeder` seed cột `block_calendar`, `source`, audit timestamps.
- **Docs:** `db_overview_etc_core_schema.md` (section `room_maintenances` + changelog), `data-dictionary.md` §2.3.5.
- **Next:** Phase 2 T2.1 — `RoomMaintenancePolicy` + service lifecycle.

### Phase 2 đã ship (2026-06-18)

- **Policy:** `RoomMaintenancePolicy` — Partner scope qua `property.user_id`; admin bypass.
- **Services:** `RoomMaintenanceService` (create/update/list/detail), `MaintenanceBlockSyncService` → `RoomBlockService`.
- **API:** `GET/PATCH /api/v1/partner/room-maintenances/{id}`; list pagination `page`/`per_page`; enriched `RoomMaintenanceResource`.
- **Tests:** `tests/Feature/Partner/PartnerRoomMaintenanceTest.php` — 3 tests, 20 assertions.
- **Next:** Phase 3 FE — `Maintenances.tsx`, `partnerService.updateMaintenance`, Room Detail.

