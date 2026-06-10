# Repository Decisions Log

## 2026-06-08 - Partner Dashboard overbooking count (plan)

| Field | Decision |
|---|---|
| Decision ID | DEC-260608-DASH-001 |
| Context | Alert Center hardcode `overbookingCount={0}`; Calendar FE đã có logic đếm overlap client-side nhưng Dashboard cần số liệu server-side cho CTA và filter property |
| Decision | Tính `overbookingCount` trên BE bằng cách quét booking active trong cửa sổ 30 ngày, group theo `room_id`, đếm cặp interval overlap với semantics `ConflictChecker::intervalsOverlap` (end_date exclusive). Expose qua payload `GET /partner/dashboard/kpis` (field mới) thay vì endpoint riêng — giảm round-trip |
| Rationale | Đồng bộ semantics với confirm conflict và Calendar; tái sử dụng service hiện có; không cần migration |
| Related artifact | `docs/plans/plan_012_partner_dashboard_redesign.md`, `docs/ui-designs/partner-dashboard/ui_design_v1.md`, `app/Services/ConflictChecker.php` |
| Status | Planned |

## 2026-06-08 - Partner Dashboard property filter (plan)

| Field | Decision |
|---|---|
| Decision ID | DEC-260608-DASH-002 |
| Context | SRS PP360-DASH-003/006 yêu cầu filter theo property; KPI/chart hiện aggregate toàn partner |
| Decision | Thêm query param optional `property_id` trên `stats`, `kpis`, `charts/*`, `pending-bookings`; validate ownership; cache key KPI bao gồm property scope |
| Rationale | Backward-compatible (không param = all); align Calendar filter pattern |
| Related artifact | `docs/plans/plan_012_partner_dashboard_redesign.md`, `app/Services/PartnerKpiService.php` |
| Status | Planned |

## 2026-05-31 - Đối soát doanh thu Admin

| Field | Decision |
|---|---|
| Decision ID | DEC-20260531-REC-001 |
| Context | Hệ thống chưa có luồng đối soát công nợ thực tế để thu 5% hoa hồng từ Partner và theo dõi trạng thái thanh toán. Dashboard Admin nhầm lẫn GMV toàn sàn với doanh thu nền tảng. FAQ và hợp đồng mẫu hiển thị sai tỷ lệ hoa hồng (10%, 12% thay vì 5%). |
| Decision | Chọn Model A - Đối soát định kỳ, Partner nộp phí 5% hoa hồng trên tổng GMV (phòng + dịch vụ) của các đơn COMPLETED check-out thực tế. Chu kỳ ngày 05 và ngày 20 hàng tháng. Tạo 2 bảng mới `partner_settlement_periods` và `settlement_line_items`, liên kết khóa booking qua `settlement_period_id` khi chốt kỳ. Đồng bộ copy 5% trên FAQ và Onboarding Wizard. |
| Rationale | Model A không yêu cầu payment gateway, phù hợp dòng tiền trực tiếp tại quầy của Partner ở Việt Nam. Chỉ tính đối soát khi đơn COMPLETED để tránh rủi ro công nợ ảo của các đơn CONFIRMED nhưng bị cancel/no-show thực tế. |
| Related artifact | `docs/SRC/srs_admin_revenue_reconciliation.md`, `docs/designs/design_006.md`, `docs/plans/plan_006_admin_revenue_reconciliation.md`, `docs/databases_docs/db_overview_etc_core_schema.md` |
| Status | Implemented (2026-05-31) |

## 2026-05-29 - Homepage suggestions by tourist spot (plan)

| Field | Decision |
|---|---|
| Decision ID | DEC-260529-RTM-HP-001 |
| Context | UI homepage ghi "gợi ý theo điểm du lịch" nhưng tab/API vẫn theo tỉnh; user yêu cầu Sa Pa, Cát Bà, Lý Sơn, Bà Nà Hill |
| Decision | Thêm API `GET home/rooms/rooms-by-tourist-spot` grouped by `tourist_spot_id`; FE tab theo spot; ẩn tab khi không đủ phòng mapped (mặc định, không fallback region trừ khi bật env); giữ `rooms-by-province` một release với feature flag rollback |
| Rationale | Tận dụng schema plan_004; đúng ngữ nghĩa sản phẩm và mockup; tránh hiển thị phòng không gắn điểm |
| Related artifact | `docs/plans/plan_007_homepage_suggested_rooms_by_tourist_spot.md` |
| Status | Implemented in code (2026-05-29); Ops mapping production pending |

## 2026-05-21 - Room-tourist mapping implementation

| Field | Decision |
|---|---|
| Decision ID | DEC-260521-RTM-004 |
| Context | Stack-task implementation for room-tourist mapping is now wired into backend code |
| Decision | Keep public tourist summary embedded on existing home/search/detail room payloads and expose admin CRUD under `/api/v1/admin/tourist-spots` and `/api/v1/admin/room-tourist-spot-maps` |
| Rationale | Reuses current room endpoints, minimizes FE changes, and keeps management UI separate from public flow |
| Status | Implemented in code; runtime test execution pending |

## 2026-05-21 - Room-tourist mapping plan

| Field | Decision |
|---|---|
| Decision ID | DEC-260521-RTM-003 |
| Context | Need an implementation sequence for room-tourist mapping after design completion |
| Decision | Execute in 4 phases with summary DTO freeze at public API phase, version-based cache invalidation, and downstream QA/review/report handoffs explicitly defined |
| Rationale | Reduces contract churn for FE/QA and keeps admin/public concerns separated |
| Related artifact | `docs/plans/plan_004.md` |

## 2026-05-21 - Room-tourist mapping design

| Field | Decision |
|---|---|
| Decision ID | DEC-260521-RTM-002 |
| Context | Design phase for room-tourist mapping needs a concrete implementation path |
| Decision | Use a shared public tourist-summary DTO for home/search/detail, admin service/repository CRUD for master data, and cache versioning with short TTLs for read-heavy public responses |
| Rationale | Keeps FE simple, reduces N+1, and allows content updates without live routing dependency |
| Related artifact | `docs/designs/design_004.md` |

## 2026-05-21 - Room-tourist mapping analysis

| Field | Decision |
|---|---|
| Decision ID | DEC-260521-RTM-001 |
| Context | User wants room cards to show relation to tourist spots (e.g. Bà Nà Hill) on home and search results |
| Decision | Scope uses maintained / estimated travel time and new master-mapping schema: `tourist_spots` + `room_tourist_spot_maps`; FE should render at most one primary spot plus a small number of secondary spots |
| Rationale | Fits existing room/area data without forcing live routing; keeps UI readable and API payload reusable across screens |
| Out of scope | Full map engine, route directions, and redesign of homepage |

## 2026-05-10 - Partner Portal 360 Scope

| Field | Decision |
|---|---|
| Decision ID | DEC-260510-PP360-001 |
| Context | Lead `lead_260510_partner-portal-360.md` yêu cầu chuyển sang `stack-analyze` cho role Partner |
| Decision | Phase đầu chỉ tập trung Dashboard, Bookings và Calendar cho Serviced Apartment + Homestay |
| Rationale | Đây là nhóm pain/KPI có tác động trực tiếp tới occupancy, GMV, time-to-confirm và Partner retention |
| Out of scope | Channel Manager, native mobile, payment/payout, AI, Chat, RBAC nội bộ Partner, onboarding/KYC |

## 2026-05-10 - Realtime Direction

| Field | Decision |
|---|---|
| Decision ID | DEC-260510-PP360-002 |
| Context | Partner cần nhận booking mới và calendar update nhanh |
| Decision | SRS yêu cầu WebSocket cho booking/calendar event, có fallback polling 30 giây |
| Rationale | User đã chọn WebSocket/Pusher; scale nhỏ nên fallback polling giữ hệ thống ổn định nếu socket lỗi |
| Pending | `stack-design` cần chốt Laravel Reverb hay Pusher |

## 2026-05-10 - Database Canonical Path

| Field | Decision |
|---|---|
| Decision ID | DEC-260510-PP360-003 |
| Context | Skill `stack-analyze` yêu cầu DB schema canonical tại `docs/databases_docs/db_overview_etc_core_schema.md` |
| Decision | Tạo baseline canonical ở đúng path trong repo BE và ghi schema delta của SRS Partner Portal 360 vào đó |
| Rationale | Tránh tản mạn schema trong từng SRS riêng; các bước design/plan sau có một nguồn sự thật |
| Related artifact | `docs/databases_docs/db_overview_etc_core_schema.md` |

## 2026-05-10 - Realtime Stack chốt cho Partner Portal 360

| Field | Decision |
|---|---|
| Decision ID | DEC-260510-PP360-004 |
| Context | Backend đang dùng Laravel 9.19; FE đã cài sẵn `laravel-echo` và `pusher-js`; OQ9 cần chốt Reverb hay Pusher |
| Decision | Dùng Pusher protocol: Soketi tự host cho dev/staging, Pusher Cloud cho prod. Thêm dependency `pusher/pusher-php-server`. Channel `private-partner.{partnerId}` và `private-property.{propertyId}`, authorize qua `BroadcastAuthController` mới (JWT-aware) |
| Rationale | Reverb yêu cầu Laravel 11 nên không khả dụng; Soketi/Pusher tương thích `pusher-js` đã có; tránh upgrade framework lớn |
| Related artifact | `docs/designs/design_001.md` mục 1.3, 3.1, 6.1 |

## 2026-05-10 - SLA, Overbooking và Bulk Action

| Field | Decision |
|---|---|
| Decision ID | DEC-260510-PP360-005 |
| Context | OQ3, OQ8, OQ10 trong SRS chưa được trả lời |
| Decision | SLA time-to-confirm = 5 phút. Overbooking xử lý theo block tuyệt đối (không cho confirm khi conflict). Bulk confirm/cancel giới hạn 20 booking/lần |
| Rationale | Phù hợp benchmark Booking.com Extranet/MRB; an toàn cho dữ liệu; tránh khoá DB lâu khi bulk |
| Related artifact | `docs/designs/design_001.md` mục 1.4, 4.3, 7.1 |

## 2026-05-10 - Caching Strategy KPI Dashboard

| Field | Decision |
|---|---|
| Decision ID | DEC-260510-PP360-006 |
| Context | Dashboard cần response nhanh khi nhiều Partner cùng F5; tránh query DB lặp |
| Decision | Cache Redis 60 giây cho KPI per partner và 30 giây cho calendar response per property + date range; invalidate khi event `BookingConfirmed/Cancelled/NoShow/RoomBlockChanged` thuộc partner đó |
| Rationale | Đủ tươi cho vận hành thực tế; giảm tải DB; chưa cần materialized snapshot ở scale nhỏ |
| Related artifact | `docs/designs/design_001.md` mục 7.2 |

## 2026-05-10 - Conflict Check & Lock Strategy

| Field | Decision |
|---|---|
| Decision ID | DEC-260510-PP360-007 |
| Context | MySQL không hỗ trợ exclusion constraint cho khoảng ngày |
| Decision | Conflict check ở app layer với `ConflictChecker` chia sẻ giữa confirm booking và create room block. Trong transaction confirm/move dùng `SELECT ... FOR UPDATE` theo `room_id`. Index DB `bookings(room_id, start_date, end_date, status)` và `room_blocks(room_id, start_date, end_date)` |
| Rationale | Tránh race condition mà không cần đổi engine DB; chuẩn hoá cảnh kiểm tra trùng giữa booking và room block |
| Related artifact | `docs/designs/design_001.md` mục 4.3, 7.1 |

## 2026-05-10 - Sprint Slicing Partner Portal 360

| Field | Decision |
|---|---|
| Decision ID | DEC-260510-PP360-008 |
| Context | Plan cần chia thành sprint khả thi và giảm conflict file giữa các phase |
| Decision | 5 phase tuần tự, mỗi phase ≈ 1 sprint 1 tuần. Không gộp Phase 4 vào Phase 3 dù cùng chạm Calendar/Bookings; bulk action xếp Phase 4 thay vì Phase 2 vì cần `ConflictChecker` của Phase 3 |
| Rationale | Calendar + Room Block (Phase 3) đã chiếm trọn 1 sprint; gộp Phase 4 sẽ phình PR khó review. Bulk action có race condition cao, cần lock đã ổn định ở Phase 3 |
| Related artifact | `docs/plans/plan_001.md` Phase Overview |

## 2026-05-10 - Branching và Conflict Resolution Strategy

| Field | Decision |
|---|---|
| Decision ID | DEC-260510-PP360-009 |
| Context | `BookingService.php` và `routes/api.php` được sửa ở mọi phase nên rủi ro merge conflict cao |
| Decision | Mỗi phase = 1 feature branch `feature/pp360-phase{N}` từ `develop`, mỗi phase 1 PR; rebase lên `develop` trước khi mở PR phase kế. Tập trung KPI cache invalidation tại 1 listener `InvalidatePartnerKpiCache` để tránh logic phân mảnh |
| Rationale | Đảm bảo review tuần tự, chủ động giải quyết 10 conflict đã xác định trong Conflict Analysis của plan |
| Related artifact | `docs/plans/plan_001.md` Conflict Analysis |

## 2026-05-10 - Backfill `confirmed_at`

| Field | Decision |
|---|---|
| Decision ID | DEC-260510-PP360-010 |
| Context | Cần baseline KPI time-to-confirm nhưng dữ liệu cũ không có `confirmed_at` |
| Decision | Phase 1 chạy artisan command idempotent `partner:backfill-confirmed-at` set `confirmed_at = updated_at` cho `status = confirmed`; ghi `metadata.backfilled = true` vào timeline để KPI time-to-confirm trung bình loại trừ booking backfill trong 30 ngày đầu rollout. Signature rút gọn từ `partner-portal:backfill-confirmed-at` (plan ban đầu) thành `partner:backfill-confirmed-at` để khớp Laravel convention |
| Rationale | Có baseline để so sánh KPI mà không làm méo dữ liệu thật |
| Related artifact | `docs/plans/plan_001.md` task T1.4, `app/Console/Commands/BackfillBookingConfirmedAt.php` |

## 2026-05-10 - Hoãn event broadcasting skeleton sang Phase 2

| Field | Decision |
|---|---|
| Decision ID | DEC-260510-PP360-011 |
| Context | Plan T1.9/T1.10 ghi tạo skeleton class `BookingConfirmed`/`BookingCancelled` event ở Phase 1, broadcasting bật Phase 2 |
| Decision | Hoãn skeleton event class sang Phase 2; Phase 1 chỉ ghi timeline đồng bộ trong cùng transaction |
| Rationale | Broadcasting infra (Pusher driver, `BroadcastServiceProvider` config, channel auth) tập trung Phase 2; tạo skeleton rỗng ở Phase 1 sẽ là dead code không value, vi phạm "Simplicity First". Khi Phase 2 bật broadcasting, có thể dispatch event ngay tại điểm gọi `BookingTimelineService::recordConfirmed/Cancelled` |
| Related artifact | `docs/plans/plan_001.md` task T1.9, T1.10 |

## 2026-05-10 - KPI dashboard endpoint MTD-only ở Phase 1

| Field | Decision |
|---|---|
| Decision ID | DEC-260510-PP360-012 |
| Context | Plan T1.13 ghi yêu cầu validate `property_id`, `from`, `to` query params với rule range > 365 ngày |
| Decision | Phase 1 ship endpoint `/dashboard/kpis` chỉ trả KPI cho tháng hiện tại (Asia/Ho_Chi_Minh), không nhận query params. Filter theo `property_id`/`from`/`to` hoãn sang Phase 4 cùng `T4.1` (analytics drill-down) |
| Rationale | Phase 1 mục tiêu là baseline KPI cho dashboard chính; multi-property filter và range tuỳ ý là tính năng analytics nâng cao thuộc Phase 4. Giữ scope hẹp tránh over-engineering |
| Related artifact | `docs/plans/plan_001.md` task T1.13, T4.1 |

## 2026-05-10 - PartnerKpiService không declare final

| Field | Decision |
|---|---|
| Decision ID | DEC-260510-PP360-013 |
| Context | Rule `php-laravel-rule.mdc` yêu cầu service classes nên `final`. Tuy nhiên unit test `PartnerKpiServiceTest` cần override `computeAvgConfirmSeconds` để bypass DB facade |
| Decision | Bỏ `final` cho `PartnerKpiService`, đổi `computeAvgConfirmSeconds` từ private sang protected. Document rõ trong class docblock rằng class có thể override để testable nhưng public methods vẫn stable |
| Rationale | Trade-off: testability quan trọng hơn strict immutability ở scope phase 1. Không có production subclass; rủi ro thấp |
| Related artifact | `app/Services/PartnerKpiService.php`, `tests/Unit/Services/PartnerKpiServiceTest.php` |

## 2026-05-21 - Landing Page Prominence Docs

| Field | Decision |
|---|---|
| Decision ID | DEC-260521-LP-001 |
| Context | Tài liệu landing page prominence trước đó nằm trong repo FE và cần đưa lại về repo BE theo yêu cầu người dùng |
| Decision | Chuyển lead/SRS/design/plan về `bks-system-be/docs/{leads,SRC,designs,plans}`; rename design thành `design_003.md` và plan thành `plan_003.md` để tránh đụng file sẵn có |
| Rationale | Giữ tài liệu cùng repo với backend feature work, đồng thời tuân thủ convention đặt tên hiện có của BE docs |

## 2026-05-10 - Listener Phase 2 không thay timeline inline Phase 1

| Field | Decision |
|---|---|
| Decision ID | DEC-260510-PP360-014 |
| Context | Plan T2.5 đề xuất `RecordBookingTimeline` listener (queued) ghi timeline cho 3 event Created/Confirmed/Cancelled, có sync fallback nếu queue down. Tuy nhiên Phase 1 đã ghi timeline INLINE trong cùng transaction với booking update (Phase 1 DEC-260510-PP360-011) |
| Decision | Phase 2 KHÔNG thay timeline inline bằng listener async. Listener chỉ ghi marker phụ trợ `event_type='broadcast_dispatched'` để debug realtime; timeline transition vẫn do `BookingService` ghi đồng bộ trong transaction |
| Rationale | Giữ tính transactional cho timeline (rollback đồng nhất với booking khi exception), không phá Phase 1 unit tests, đồng thời vẫn có audit cho realtime path. Nếu chuyển sang listener async sẽ mất "Surgical changes" nguyên tắc + cần migrate dữ liệu Phase 1 |
| Related artifact | `app/Listeners/RecordBookingTimeline.php`, `app/Services/BookingService.php`, `docs/plans/plan_001.md` task T2.5 |

## 2026-05-10 - Custom /broadcasting/auth endpoint qua jwt.auth

| Field | Decision |
|---|---|
| Decision ID | DEC-260510-PP360-015 |
| Context | Laravel mặc định đăng ký route `Broadcast::routes()` với middleware `web` (cookie session). FE Partner Portal dùng JWT Bearer (no cookie) nên route mặc định trả 401 |
| Decision | Disable `Broadcast::routes()` trong `BroadcastServiceProvider`, tự đăng ký `POST /api/v1/broadcasting/auth` trong `routes/api.php` với middleware `jwt.auth`, controller `BroadcastAuthController@authenticate` delegate cho `Broadcast::auth()` để sinh signature |
| Rationale | Giữ stack auth thuần JWT, không phải maintain 2 cơ chế (session + JWT); có thể cấu hình rate limit, log riêng cho route này nếu cần ở phase sau |
| Related artifact | `app/Http/Controllers/BroadcastAuthController.php`, `app/Providers/BroadcastServiceProvider.php`, `routes/api.php`, `docs/runbooks/realtime_setup.md` |

## 2026-05-10 - safeDispatch wrap dispatch trong BookingService

| Field | Decision |
|---|---|
| Decision ID | DEC-260510-PP360-016 |
| Context | Phase 2 dispatch `BookingCreated/Confirmed/Cancelled` SAU khi DB::commit(). Nếu Pusher cluster down hoặc credentials sai, ShouldBroadcast event sẽ throw từ HTTP client |
| Decision | Bọc dispatch trong `safeDispatch(string $name, callable $factory)` — try/catch + log warning. Booking đã commit thành công, không revert do lỗi realtime |
| Rationale | Realtime là trải nghiệm phụ trợ, không được phép phá business flow. Polling fallback FE đảm bảo user vẫn nhận update tối đa 30s. Logged warning đủ để ops monitor |
| Related artifact | `app/Services/BookingService.php` (`safeDispatch`, `resolveBroadcastScope`) |

## 2026-05-10 - Polling fallback gắn liền useBookingsRealtime

| Field | Decision |
|---|---|
| Decision ID | DEC-260510-PP360-017 |
| Context | Plan T2.13 đề xuất file mới `RealtimeStatusBanner.tsx` riêng và logic polling tách hook |
| Decision | Gộp logic polling fallback + status banner vào trong cùng `useBookingsRealtime` hook + `RealtimeNotifyProvider` provider duy nhất. Không tạo file `RealtimeStatusBanner.tsx` riêng |
| Rationale | Simplicity first: polling activation gắn liền connection state, ít state cross-component, dễ debug. Banner chỉ là 1 div nhỏ trong provider → không cần component file riêng |
| Related artifact | `src/hooks/Partner/useBookingsRealtime.ts`, `src/pages/Partner/components/RealtimeNotifyProvider.tsx` |

## 2026-05-10 - Calendar cache invalidation: version-pointer thay vì Redis tags

| Field | Decision |
|---|---|
| Decision ID | DEC-260510-PP360-018 |
| Context | Plan T3.10 đề xuất Redis cache 30s + invalidate qua tag/key delete. Repo chạy CACHE_DRIVER mix (file/redis tuỳ env), không bảo đảm Redis tags hoạt động trên file driver |
| Decision | Cache key `calendar:{partnerId}:v{version}:{scope}:{room}:{from}:{to}` với version pointer riêng `calendar:{partnerId}:version`. Listener `InvalidateCalendarCache::bumpVersion(partnerId)` chỉ cần `Cache::increment` (driver-agnostic). Key cũ tự stale + TTL 30s đảm bảo sạch sau cùng |
| Rationale | Driver-agnostic; không phụ thuộc Redis tags; logic gọn — không cần track key list. Chấp nhận trade-off một số entry "rác" giữ trong cache đến khi TTL hết — không ảnh hưởng correctness vì version mismatch ⇒ cache miss |
| Related artifact | `app/Services/PartnerCalendarService.php`, `app/Listeners/InvalidateCalendarCache.php` |

## 2026-05-10 - Conflict semantics: end_date exclusive (back-to-back NOT conflict)

| Field | Decision |
|---|---|
| Decision ID | DEC-260510-PP360-019 |
| Context | Booking và RoomBlock đều có `start_date` và `end_date`. SRS không nêu rõ end là inclusive hay exclusive. `BookingRepository::checkRoomConflict` cũ có nhầm lẫn flag back-to-back là conflict |
| Decision | Quy ước `[start_date, end_date)` exclusive trên `end_date`. Two intervals overlap ⇔ `a.start < b.end ∧ b.start < a.end`. Back-to-back (a.end == b.start) ⇒ NOT conflict (cho phép check-out sáng và check-in cùng ngày) |
| Rationale | Khớp practice ngành khách sạn (check-out trước check-in cùng ngày là chuẩn). Đơn giản hoá toán học, dễ unit test (`ConflictChecker::intervalsOverlap`). Verified trong `ConflictCheckerTest::test_back_to_back_intervals_are_not_conflicts` |
| Related artifact | `app/Services/ConflictChecker.php`, `tests/Unit/Services/ConflictCheckerTest.php` |

## 2026-05-10 - Calendar payload enrich vs separate detail endpoint

| Field | Decision |
|---|---|
| Decision ID | DEC-260510-PP360-020 |
| Context | Design Section 7 mô tả `/partner/calendar` trả tối thiểu booking + block. FE Calendar dialog cần `room_label`, `guest_name`, `phone`, `total_amount` để hiển thị chi tiết khi click event. Hai option: (A) enrich payload calendar; (B) FE fetch GET booking detail riêng |
| Decision | Chọn (A): enrich `serializeBooking` với `room_label`, `room_title`, `property_id`, `guest_name`, `guest_phone`, `total_amount` (tính = price × số đêm). Eager-load `with(['room','user','price'])` để tránh N+1. PII chấp nhận được vì endpoint REST authenticated cho partner-owner (không phải broadcast) |
| Rationale | Giảm round-trip thứ hai khi user click event; UX tốt hơn (không loading flash). N+1 đã handle bằng eager-load với column whitelist. Broadcast event vẫn không gồm PII (giữ DEC-260510-PP360-006/008) |
| Related artifact | `app/Services/PartnerCalendarService.php` |

## 2026-05-10 - ConflictChecker non-final + static intervalsOverlap helper

| Field | Decision |
|---|---|
| Decision ID | DEC-260510-PP360-021 |
| Context | T3.16 yêu cầu unit test `ConflictChecker` + `RoomBlockService`. ConflictChecker cần mock được trong RoomBlockServiceTest. Logic interval trong `findConflicts` build query Eloquent, khó test thuần |
| Decision | (1) Bỏ `final` khỏi class `ConflictChecker` để PHPUnit/Mockery có thể mock. (2) Tách logic interval thành public static `intervalsOverlap(string $a1, $a2, $b1, $b2): bool` — cho phép unit test thuần thuật toán mà không cần DB |
| Rationale | Mặc dù `php-laravel-rule.mdc` khuyến nghị `service classes should be final and read-only`, ngoại lệ cho service được phụ thuộc qua interface là phổ biến để testability. ConflictChecker không có state nên non-final không tăng rủi ro. Static helper giữ logic tập trung và testable |
| Related artifact | `app/Services/ConflictChecker.php`, `tests/Unit/Services/ConflictCheckerTest.php` |

## 2026-05-12 - KPI cache invalidation explicit keys

| Field | Decision |
|---|---|
| Decision ID | DEC-260512-PP360-022 |
| Context | Phase 4 T4.3 ban đầu đề xuất xoá Redis key pattern `partner:{id}:kpi:*`, nhưng repo/test/dev đang dùng nhiều cache driver (`array`, `file`, `redis`) và Laravel Cache facade không hỗ trợ wildcard/tag thống nhất giữa các driver |
| Decision | `InvalidatePartnerKpiCache` clear danh sách key hữu hạn do `PartnerKpiService::cacheKeysForPartner()` trả về: `dashboard`, `charts:occupancy`, `charts:gmv`. Không dùng Redis wildcard/tag |
| Rationale | Driver-agnostic, test được với `CACHE_DRIVER=array`, tránh coupling Redis-only trong business listener. Phase 4 chỉ có 3 cache slot KPI nên explicit list đủ đơn giản |
| Related artifact | `app/Listeners/InvalidatePartnerKpiCache.php`, `app/Services/PartnerKpiService.php`, `docs/plans/plan_001.md` Phase 4 |

## 2026-05-12 - ContractService repository-only writes for unit testability

| Field | Decision |
|---|---|
| Decision ID | DEC-260512-PP360-023 |
| Context | Phase 5 T5.1 cần `setRenewalReminder`/`terminate` viết trực tiếp lên model. Ban đầu thử `$contract->save()` nhưng unit test với cache driver `array` không có DB connection sẵn ⇒ save() hit DB và fail trong PHPUnit |
| Decision | Tất cả write trong `ContractService` đi qua `$this->contractRepository->update($id, $attrs)`. Sau update repository, đồng bộ attribute trên in-memory model để event payload đầy đủ. Không dùng `$model->save()` hoặc `$model->refresh()` trong service |
| Rationale | (1) Cho phép mock repository và assert chính xác attribute set được persist (test `withArgs(fn($id,$attrs) => …)`). (2) Tuân thủ pattern Repository hiện hữu của project (BookingRepository, RoomBlockRepository). (3) Service không cần DB cho unit test ⇒ phpunit chạy được trên array cache mà không cần seeder/migration |
| Related artifact | `app/Services/ContractService.php`, `tests/Unit/Services/ContractServiceTest.php` |

## 2026-05-12 - PARTNER_360 feature flag scope only on Phase 3+ endpoints

| Field | Decision |
|---|---|
| Decision ID | DEC-260512-PP360-024 |
| Context | T5.6 yêu cầu middleware bật/tắt Phase 3+ endpoints. Hai option: (A) gắn middleware lên TOÀN BỘ `/api/v1/partner/*` rồi exclude vài route Phase 1-2; (B) opt-in middleware cho từng group route Phase 3+. Phương án (A) gây breaking change cho user đang dùng booking-CRUD/dashboard cũ |
| Decision | Chọn (B). Middleware `partner360` chỉ apply với endpoint Phase 3 (`/calendar`, `/room-blocks/*`, `/bookings/{id}/move`), Phase 4 (`/dashboard/charts/*`, `/bookings/bulk-*`), và Phase 5 (`/contracts/expiring-soon`, `/contracts/:id/renewal-reminder`, `/contracts/:id/terminate`). Phase 1-2 endpoints (CRUD booking, `/dashboard/{kpis,stats,pending-bookings,urgent-maintenances,revenue-analytics}`) KHÔNG bị flag chặn |
| Rationale | Backwards-compatible cho rollback nhanh: tắt flag = ẩn UI Phase 3+, BE trả 403 với code rõ ràng cho FE handle; Phase 1-2 vẫn hoạt động bình thường. FE `lib/featureFlags.ts` mirror flag qua `VITE_PARTNER_REALTIME` để ẩn UI thay vì để partner chạm 403 |
| Related artifact | `app/Http/Middleware/EnsurePartner360Enabled.php`, `routes/api.php`, `config/app.php`, `bks-system-fe/src/lib/featureFlags.ts` |

## 2026-05-14 - B5: Unit test tier matcher không phụ thuộc DB

| Field | Decision |
|---|---|
| Decision ID | DEC-260514-BCP-006 |
| Context | `RefreshDatabase` + PHPUnit trên máy dev/CI dễ fail (credential `.env.testing`, hoặc full migrate trên SQLite lỗi CHECK `room_blocks`) |
| Decision | Tách logic chọn tier + tính giờ/`stay_kind` vào `App\Support\Bcp\CancellationPolicyTierMatcher`; unit test `tests/Unit/Support/Bcp/CancellationPolicyTierMatcherTest.php` extends `PHPUnit\Framework\TestCase` (không bootstrap DB). `CancellationPolicyResolver` vẫn đọc DB nhưng chỉ map row → mảng rồi gọi matcher. |
| Rationale | Đảm bảo quy tắc nghiệp vụ tier được kiểm thử ổn định trong CI; giảm phụ thuộc MySQL cho phần “pure rules” |
| Related artifact | `app/Support/Bcp/CancellationPolicyTierMatcher.php`, `app/Services/CancellationPolicyResolver.php`, `tests/Unit/Support/Bcp/CancellationPolicyTierMatcherTest.php` |

## 2026-05-14 - Luồng hủy khách: `cancel` vs `cancel-request` và trạng thái `pending_cancellation`

| Field | Decision |
|---|---|
| Decision ID | DEC-260514-BCP-001 |
| Context | Lead `lead_260513_booking-cancellation-policy.md` chốt T8: phân biệt thao tác theo **bậc trạng thái đơn**, không theo role read/cancel tách riêng |
| Decision | **Bậc thấp** (Partner chưa xác nhận theo nghĩa nghiệp vụ đã chốt): khách dùng **`cancel`** trực tiếp. **Bậc cao** (đã confirmed trở đi): khách chỉ **`cancel-request`**; booking vào **`pending_cancellation`** cho đến khi Partner **approve/reject**. **Đang ở/check-in:** không cho hủy đặt. |
| Rationale | Giảm ma sát trước confirm; bảo vệ cam kết sau confirm; đồng bộ metric SLA trên bảng yêu cầu |
| Pending | Map enum “chờ thanh toán”; **% `cancellation_policy_tiers`:** đã seed placeholder qua `CancellationPolicyBaselineSeeder` (B5); vẫn cần điều chỉnh sau research OTA + pháp lý VN |
| Related artifact | `docs/SRC/srs_booking_cancellation_policy.md`, `docs/databases_docs/db_overview_etc_core_schema.md` |

## 2026-05-14 - D002: Conflict semantics với `pending_cancellation` (status 4)

| Field | Decision |
|---|---|
| Decision ID | DEC-260514-BCP-002 |
| Context | Booking ở trạng thái chờ Partner duyệt hủy vẫn chiếm lịch phòng trong thực tế vận hành |
| Decision | `BookingStatus::PENDING_CANCELLATION` (**4**) **không** được `ConflictChecker` xếp vào nhóm loại trừ như `CANCELLED`/`COMPLETED`; coi như đặt chỗ còn hiệu lực cho đến khi approve (→ cancelled) hoặc reject (→ khôi phục status trước đó) |
| Rationale | Tránh overbooking khi khách đã gửi yêu cầu hủy nhưng Partner chưa chấp nhận |
| Related artifact | `docs/designs/design_002.md` §4.3, `app/Services/ConflictChecker.php` |

## 2026-05-14 - D002: Khôi phục trạng thái khi Partner reject

| Field | Decision |
|---|---|
| Decision ID | DEC-260514-BCP-003 |
| Context | Sau `cancel-request`, booking = 4; Partner từ chối cần trả đơn về đúng trạng thái trước yêu cầu (có thể `PENDING` hoặc `CONFIRMED`) |
| Decision | Lưu **`previous_booking_status`** (tinyint) trên row `booking_cancellation_requests` tại thời điểm tạo request; reject set `bookings.status = previous_booking_status`, xóa/cập nhật `pending_cancellation_since`, đóng request `rejected` |
| Rationale | Không suy luận cứng `CONFIRMED` — mở rộng sau cho luồng “chờ thanh toán” vẫn an toàn |
| Related artifact | `docs/designs/design_002.md` §4.1.4, `docs/databases_docs/db_overview_etc_core_schema.md` |

## 2026-05-14 - D002: Cấu hình cooldown, SLA “treo”, ngưỡng đêm

| Field | Decision |
|---|---|
| Decision ID | DEC-260514-BCP-004 |
| Context | Lead yêu cầu N cấu hình được; SRS đề xuất 60 phút; metric B7 cần ngưỡng “treo” |
| Decision | `.env` / config: `CANCEL_REQUEST_COOLDOWN_SECONDS` default **3600**; `BCP_STALE_REQUEST_HOURS` default **48** (request `pending` quá giờ = treo trong báo cáo); `BCP_LONG_STAY_MIN_NIGHTS` default **30** (khớp PP360) |
| Rationale | Tách khỏi code magic number; prod điều chỉnh không deploy |
| Related artifact | `docs/designs/design_002.md` Appendix C |

## 2026-05-14 - P002: Thứ tự triển khai và nhánh feature BCP

| Field | Decision |
|---|---|
| Decision ID | DEC-260514-P002-001 |
| Context | Cần giảm conflict merge trên `routes/api.php`, `BookingService.php`, `ConflictChecker` |
| Decision | Triển khai tuần tự **B1 → B2 → B3**; **B4** sau khi API Stay (B2.5+) ổn định; nhánh gợi ý `feature/bcp-cancellation` hoặc `feature/bcp-phase{N}`; mọi route mới sau B1 gắn flag `BCP_CANCELLATION_V1` |
| Rationale | Schema phải có trước khi service dùng status 4; Partner inbox phụ thuộc guest tạo request; FE song song chỉ sau contract API |
| Related artifact | `docs/plans/plan_002.md` Conflict Analysis, Downstream Handoffs |

## 2026-05-14 - B3: Broadcast cancellation request (no PII)

| Field | Decision |
|---|---|
| Decision ID | DEC-260514-BCP-005 |
| Context | Partner inbox realtime cần cập nhật khi request đổi trạng thái; tránh lộ dữ liệu khách trên socket |
| Decision | Event `CancellationRequestUpdated` broadcast payload tối thiểu: `request_id`, `booking_id`, `property_id`, `partner_id`, `status` (string). **Không** gửi `reason_text`, email, tên khách. Chi tiết lý do guest chỉ trong REST inbox (`GET cancellation-requests`). |
| Rationale | Khớp pattern Phase 2 `BookingCancelled` (`has_reason` thay vì full reason); đồng bộ checklist security |
| Related artifact | `app/Events/CancellationRequestUpdated.php`, `docs/designs/design_002.md`, `api-doc/partner-cancellation-requests.js` |

## 2026-05-25 - Stay review: mở sau lưu trú và gộp room + partner trong một lần gửi

| Field | Decision |
|---|---|
| Decision ID | DEC-260525-REV-001 |
| Context | `BookingDetail` của BKS Stay cần một luồng đánh giá ngắn gọn sau lưu trú, tránh bắt user đi qua nhiều màn hoặc gửi review rời rạc cho phòng và đối tác |
| Decision | Chỉ hiển thị khối đánh giá khi booking **đã hoàn thành** (`status = 3`) hoặc `stay_status = checked_out`. FE dùng **một form duy nhất** để gửi đồng thời **đánh giá phòng** và **đánh giá đối tác/chủ nhà** qua `POST stay/reviews`. Nếu booking đã có review, UI chuyển sang **read-only list** và không hiển thị form nhập mới trong cùng màn `BookingDetail`. |
| Rationale | Giảm friction sau lưu trú, gắn review với booking có thật, và tránh duplicate UX giữa room review với partner review. Cách hiển thị read-only khi đã có review cũng giúp giảm rủi ro gửi lặp từ cùng booking trong FE hiện tại. |
| Pending | Chưa có luồng FE để **edit/delete** review; nếu sau này mở rộng moderation hoặc cho phép cập nhật review, cần chốt contract riêng. |
| Related artifact | `bks-system-fe/src/pages/EndUser/BksStay/BookingDetail.tsx`, `bks-system-fe/src/hooks/useReviewQuery.ts`, `bks-system-fe/src/api/reviewApi.ts` |

