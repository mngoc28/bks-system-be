# Repository Decisions Log

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

