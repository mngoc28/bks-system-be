# Repository Memory Index

## Đối soát doanh thu Admin

| Date | Entry | Related artifacts |
|---|---|---|
| 2026-05-31 | SRS đối soát doanh thu Admin (Model A): Chu kỳ đối soát ngày 05 và 20 hàng tháng, tính hoa hồng 5% trên GMV (phòng + dịch vụ) của các đơn COMPLETED check-out thực tế; quản lý công nợ Partner thủ công. | `docs/SRC/srs_admin_revenue_reconciliation.md`, `docs/databases_docs/db_overview_etc_core_schema.md` |
| 2026-05-31 | Design D006 đối soát doanh thu Admin: Thiết kế kiến trúc Service-Repository, Scheduler Job tự động quét đơn, 3 bảng đề xuất mới (`partner_settlement_periods`, `settlement_line_items`, `settlement_adjustments`), phân quyền RBAC và cơ chế khóa đơn chống thay đổi sau khi chốt kỳ. | `docs/designs/design_006.md`, `docs/databases_docs/db_overview_etc_core_schema.md` |
| 2026-05-31 | Kế hoạch triển khai PLAN-REC-006 đối soát doanh thu Admin: Chia thành 4 Phase (Foundation, Core Backend, REST APIs & Export, Frontend UI) với 20+ task chi tiết dài ~62 giờ lập trình, bao gồm test tự động và kế hoạch rollback. | `docs/plans/plan_006_admin_revenue_reconciliation.md` |
| 2026-05-31 | Hoàn thành Module Đối soát doanh thu Admin (Phase 1-4): Hiện thực hóa toàn bộ database schema, logic job chốt kỳ, logic khóa booking đã đối soát, các endpoints API Admin/Partner, tính năng xuất báo cáo Excel/PDF, đồng bộ copy phí hoa hồng 5% và hoàn thiện UI dashboard Admin/Partner. Toàn bộ tests chạy qua 100%. | `docs/plans/plan_006_admin_revenue_reconciliation.md`, `bks-system-fe/src/pages/Partner/Finance/index.tsx`, `bks-system-fe/src/pages/Admin/SettlementManage/index.tsx` |

## Homepage suggested rooms by tourist spot

| Date | Entry | Related artifacts |
|---|---|---|
| 2026-05-29 | Plan PLAN-RTM-HP-007: chuyển section homepage từ gom theo tỉnh (`rooms-by-province`) sang gom theo `tourist_spot` (Sa Pa, Cát Bà, Lý Sơn, Bà Nà Hill); API mới `rooms-by-tourist-spot`, FE `SuggestedRoomsByTouristSpot`, search deep-link, Ops mapping ≥8 phòng/spot | `docs/plans/plan_007_homepage_suggested_rooms_by_tourist_spot.md`, `docs/plans/plan_004.md`, `docs/SRC/srs_room_tourist_spot_mapping.md`, `docs/SRC/srs_landing_page_prominence.md`, `bks-system-fe/src/pages/EndUser/Home/components/SuggestedRoomsByProvince.tsx` |

## Cơ chế đánh giá kỳ nghỉ Stay

| Date | Entry | Related artifacts |
|---|---|---|
| 2026-05-25 | Documented cơ chế đánh giá kỳ nghỉ của BKS Stay: chỉ mở sau khi booking hoàn thành / checked-out, một lần submit có thể gửi đồng thời đánh giá phòng và đối tác, review cũ hiển thị read-only trong `BookingDetail`, dữ liệu review được tái sử dụng cho landing / room detail / partner detail | `docs/memory/knowledge_base.md`, `docs/memory/decisions.md`, `bks-system-fe/src/pages/EndUser/BksStay/BookingDetail.tsx`, `bks-system-fe/src/hooks/useReviewQuery.ts`, `bks-system-fe/src/api/reviewApi.ts` |

## Room-tourist mapping test cases

| Date | Entry | Related artifacts |
|---|---|---|
| 2026-05-21 | QC testcase TC004 (Room-tourist mapping): `docs/test-cases/testcase_004.md` — kịch bản test API public (enrichment/fallback/eager load) và Admin CRUD (validation/cache/transaction) | `docs/test-cases/testcase_004.md`, `docs/SRC/srs_room_tourist_spot_mapping.md`, `docs/plans/plan_004.md` |

## Room-tourist mapping implementation

| Date | Entry | Related artifacts |
|---|---|---|
| 2026-05-21 | RTM implemented in code: migrations, Room tourist summary service, admin CRUD wiring, compile validation clean; runtime tests still pending | `database/migrations/2026_05_21_120001_create_tourist_spots_table.php`, `database/migrations/2026_05_21_120002_create_room_tourist_spot_maps_table.php`, `app/Services/RoomTouristSummaryService.php`, `app/Services/TouristSpotService.php`, `app/Services/RoomTouristSpotMapService.php`, `routes/api.php` |

## Room-tourist mapping plan

| Date | Entry | Related artifacts |
|---|---|---|
| 2026-05-21 | Plan room-tourist mapping: 4 phases (schema, public summary API, admin CRUD, verification), DTO contract freeze at B2, version-based cache invalidation | `docs/plans/plan_004.md`, `docs/designs/design_004.md`, `docs/SRC/srs_room_tourist_spot_mapping.md` |

## Room-tourist mapping design

| Date | Entry | Related artifacts |
|---|---|---|
| 2026-05-21 | Design room-tourist mapping: shared public summary DTO, admin CRUD layer, cache versioning, 3-phase rollout | `docs/designs/design_004.md`, `docs/SRC/srs_room_tourist_spot_mapping.md`, `docs/databases_docs/db_overview_etc_core_schema.md` |

## Room-tourist mapping

| Date | Entry | Related artifacts |
|---|---|---|
| 2026-05-21 | SRS room-tourist mapping: home/search room cards hiển thị tên điểm du lịch + travel time ước tính; thêm master `tourist_spots` và mapping `room_tourist_spot_maps`; fallback khi thiếu dữ liệu | `docs/leads/lead_260521_room-tourist-mapping.md`, `docs/SRC/srs_room_tourist_spot_mapping.md`, `docs/databases_docs/db_overview_etc_core_schema.md` |

## Chính sách hủy đặt phòng (BCP)

| Date | Entry | Related artifacts |
|---|---|---|
| 2026-05-14 | QC testcase TC002 (BCP): `docs/test-cases/testcase_002.md` — 36+ TC API/FE, validation matrix, traceability BCP/F-BCP, smoke S-01–S-05 | `docs/test-cases/testcase_002.md`, `docs/SRC/srs_booking_cancellation_policy.md`, `docs/plans/plan_002.md` |
| 2026-05-14 | Phase B5 BE (P002): policy tier resolve + seed % placeholder + metrics SLA/stale + route admin `booking-cancellation-metrics`; unit test matcher không DB | `app/Services/{CancellationPolicyResolver,CancellationPolicyResolution,BookingCancellationMetricsService}.php`, `app/Support/Bcp/CancellationPolicyTierMatcher.php`, `app/Models/CancellationPolicyTier.php`, `database/seeders/CancellationPolicyBaselineSeeder.php`, `app/Http/Controllers/BookingCancellationReportController.php`, `routes/api.php`, `tests/Unit/Support/Bcp/CancellationPolicyTierMatcherTest.php`, `docs/plans/plan_002.md`, `docs/databases_docs/db_overview_etc_core_schema.md` |
| 2026-05-14 | Phase B3 FE (Partner BCP inbox): `bks-system-fe` route `/partner/cancellation-requests`, `partnerService` BCP endpoints, `CancellationRequests.tsx`, Echo `.cancellation_request.updated` qua `useBookingsRealtime` + `RealtimeNotifyProvider` | `bks-system-fe/src/pages/Partner/CancellationRequests.tsx`, `bks-system-fe/src/hooks/Partner/useBookingsRealtime.ts`, `bks-system-fe/src/services/partnerService.ts`, `docs/plans/plan_002.md` |
| 2026-05-14 | Phase B3 BE (Partner inbox BCP): repository + service approve/reject + controller + policy + broadcast `CancellationRequestUpdated` + KPI/calendar; unit tests policy/timeline; api-doc `partner-cancellation-requests.js` | `api-doc/partner-cancellation-requests.js`, `app/Services/PartnerCancellationRequestService.php`, `docs/databases_docs/db_overview_etc_core_schema.md` |
| 2026-05-14 | Implementation Plan P002: phase B1–B5 (schema, Stay cancel/cancel-request, Partner inbox, T6 sync, policy/B7), flag `BCP_CANCELLATION_V1`, handoff stack-task/testcase_002/review/report | `docs/plans/plan_002.md`, `docs/designs/design_002.md`, `docs/SRC/srs_booking_cancellation_policy.md` |
| 2026-05-14 | System Design D002: kiến trúc API Stay+Partner, service layer, enum status 4, ConflictChecker, cooldown/env, bảng `cancellation_reason_codes`, cột `previous_booking_status` trên request | `docs/designs/design_002.md`, `docs/databases_docs/db_overview_etc_core_schema.md`, `docs/SRC/srs_booking_cancellation_policy.md` |
| 2026-05-14 | SRS hủy/yêu cầu hủy theo bậc trạng thái; `pending_cancellation`; Partner duyệt; T6 sync local; T7 cooldown; B7 metric; DB đề xuất `booking_cancellation_requests` + policy tiers | `docs/leads/lead_260513_booking-cancellation-policy.md`, `docs/SRC/srs_booking_cancellation_policy.md`, `docs/databases_docs/db_overview_etc_core_schema.md`, `docs/SRC/srs_partner_portal_360.md` |

## Partner Portal 360

| Date | Entry | Related artifacts |
|---|---|---|
| 2026-05-10 | Partner Portal 360 SRS: Dashboard, Bookings, Calendar, realtime notification, room block, booking timeline | `docs/leads/lead_260510_partner-portal-360.md`, `docs/SRC/srs_partner_portal_360.md`, `docs/databases_docs/db_overview_etc_core_schema.md` |
| 2026-05-10 | Partner Portal 360 System Design: kiến trúc, API, data model + migration, security/performance, 5 phase triển khai | `docs/designs/design_001.md`, `docs/SRC/srs_partner_portal_360.md`, `docs/databases_docs/db_overview_etc_core_schema.md` |
| 2026-05-10 | Partner Portal 360 Implementation Plan: 58 task chia 5 phase, dependency graph, conflict matrix, handoff cho stack-task/testcase/review/report | `docs/plans/plan_001.md`, `docs/designs/design_001.md`, `docs/SRC/srs_partner_portal_360.md` |
| 2026-05-10 | Phase 1 IMPLEMENTED: schema (3 migrations), audit timeline, BookingPolicy, confirm/cancel/no-show với timeline, backfill command, PartnerKpiService + endpoint `/dashboard/kpis`. 16 unit tests xanh (46 assertions). | `docs/plans/plan_001.md` (Phase 1 ✅), `app/Services/BookingTimelineService.php`, `app/Services/PartnerKpiService.php`, `app/Policies/BookingPolicy.php`, `tests/Unit/Services/*Test.php` |
| 2026-05-10 | Phase 2 IMPLEMENTED: realtime end-to-end. BE: `pusher/pusher-php-server ^7.2`, `BroadcastAuthController` (JWT), 3 events `ShouldBroadcast` (BookingCreated/Confirmed/Cancelled, payload no PII), listener marker `RecordBookingTimeline`, channel auth `private-partner.{id}` + `private-property.{id}`, Soketi compose, runbook. FE: `echoClient.ts` JWT authorizer, `useBookingsRealtime` hook (subscribe + cleanup + polling 30s fallback), `RealtimeNotifyProvider` (toast + banner), `useQuickConfirm` (undo 30s + 409 conflict), `CancelBookingDialog` (5–500 reason). E2E qua MCP chrome-devtools: TC-2.10/2.11/2.12/2.13 pass. | `docs/plans/plan_001.md` (Phase 2 ✅), `app/Events/Booking{Created,Confirmed,Cancelled}.php`, `app/Listeners/RecordBookingTimeline.php`, `app/Http/Controllers/BroadcastAuthController.php`, `routes/channels.php`, `docker-compose.soketi.yml`, `docs/runbooks/realtime_setup.md`, `bks-system-fe/src/lib/echoClient.ts`, `bks-system-fe/src/hooks/Partner/useBookingsRealtime.ts`, `bks-system-fe/src/hooks/Partner/useQuickConfirm.ts`, `bks-system-fe/src/pages/Partner/components/{RealtimeNotifyProvider,CancelBookingDialog}.tsx`, `bks-system-fe/business-script/E2E_PARTNER_PORTAL_360_PHASE2.md` |
| 2026-05-10 | Phase 3 IMPLEMENTED: Calendar + Room Block. BE: migration `room_blocks` (CHECK constraints qua raw SQL), `RoomBlock` model + repo + binding, `RoomBlockPolicy` (ownership qua `Room.property.user_id`), `ConflictChecker` (interval `[a,b)` exclusive, `useLock=true` cho pessimistic select cùng `room_id`, static `intervalsOverlap` cho test), `RoomBlockService` (create/delete trong DB transaction + dispatch `RoomBlockChanged`), `PartnerCalendarService` (cache 30s qua version-pointer key `calendar:{partnerId}:v{version}:...`, enrich booking với `room_label/guest_name/guest_phone/total_amount`), `InvalidateCalendarCache` listener (bumpVersion cho 4 event), `BookingService::handleConfirmBooking` refactor sang ConflictChecker + lock, `handleMove` mới cho drag-drop, `HttpStatus::CONFLICT=409`. Endpoints: `GET/POST/DELETE /partner/room-blocks`, `GET /partner/calendar` (max 31 ngày), `PUT /partner/bookings/{id}/move`. FE: `useCalendar` hook + `useInvalidatePartnerCalendar`, `useBookingsRealtime` mở rộng listen `room_block.changed` + invalidate calendar prefix, refactor `Calendar.tsx` (option "Tất cả tài sản", render block stripe pattern, banner overbooking, drag-drop với revert 409, dialog detail mode booking/block với gỡ block), `RoomBlockDialog` component. Tests: 12 unit tests mới (ConflictChecker logic 6 + RoomBlockService 6) — toàn suite Unit 28/28 xanh. | `docs/plans/plan_001.md` (Phase 3 ✅), `app/Services/{ConflictChecker,RoomBlockService,PartnerCalendarService,BookingService}.php`, `app/Models/RoomBlock.php`, `app/Repositories/RoomBlockRepository/*`, `app/Policies/RoomBlockPolicy.php`, `app/Http/Controllers/Partner/{PartnerRoomBlockController,PartnerCalendarController,PartnerBookingController}.php`, `app/Http/Validations/RoomBlockValidation.php`, `app/Events/RoomBlockChanged.php`, `app/Listeners/InvalidateCalendarCache.php`, `database/migrations/2026_05_10_120004_create_room_blocks_table.php`, `routes/api.php`, `tests/Unit/Services/{ConflictCheckerTest,RoomBlockServiceTest}.php`, `bks-system-fe/src/hooks/Partner/{useCalendar,useBookingsRealtime}.ts`, `bks-system-fe/src/services/partnerService.ts`, `bks-system-fe/src/pages/Partner/{Calendar.tsx,components/RoomBlockDialog.tsx}` |
| 2026-05-12 | Phase 4 IMPLEMENTED: Dashboard KPI nâng cao + Bulk action. BE: chart endpoints `GET /partner/dashboard/charts/{occupancy,gmv}`, KPI cache invalidation explicit keys qua `InvalidatePartnerKpiCache`, event domain `BookingNoShow`, bulk endpoints `POST /partner/bookings/{bulk-confirm,bulk-cancel}` với `BulkBookingActionRequest` max 20 và partial success `{succeeded, failed}`. FE: dashboard thêm cards `Time-to-confirm TB`/`Net Revenue`, `OccupancyChart`, `GmvChart`, `AlertCenter`; bookings thêm checkbox bulk action, cap 20, result summary. Verify: Unit 31/31 xanh; FE build pass. | `docs/plans/plan_001.md` (Phase 4 ✅), `app/Services/{PartnerKpiService,BookingService}.php`, `app/Listeners/InvalidatePartnerKpiCache.php`, `app/Events/BookingNoShow.php`, `app/Http/Requests/Partner/BulkBookingActionRequest.php`, `app/Http/Controllers/Partner/{PartnerDashboardController,PartnerBookingController}.php`, `routes/api.php`, `tests/Unit/Http/Requests/BulkBookingActionRequestTest.php`, `tests/Unit/Services/PartnerKpiServiceTest.php`, `bks-system-fe/src/pages/Partner/{Dashboard,Bookings}.tsx`, `bks-system-fe/src/pages/Partner/components/{OccupancyChart,GmvChart,AlertCenter}.tsx`, `bks-system-fe/src/hooks/usePartnerDashboardQuery.ts`, `bks-system-fe/src/api/partnerDashboardApi.ts`, `bks-system-fe/src/services/partnerService.ts` |
| 2026-05-12 | Phase 5 IMPLEMENTED: Long-term contract subset. BE: ContractService mở rộng `setRenewalReminder`/`terminate`/`processDueReminders` (idempotent, repository-only writes), ContractPolicy (admin bypass, partner ownership), event `ContractRenewalReminderQueued` (broadcast `partner.{id}` + `property.{id}`), console command + scheduler `dailyAt('06:00')` Asia/Ho_Chi_Minh, middleware `EnsurePartner360Enabled` (config + env fallback), apply vào route Phase 3-5. Endpoints contract Phase 5: `GET /partner/contracts/expiring-soon`, `PUT /partner/contracts/:id/renewal-reminder`, `POST /partner/contracts/:id/terminate`. FE: trang `ContractDetail.tsx` route `/partner/contracts/:id` (utility_fees + 2 CTA), Calendar badge "Contract" cho booking nights ≥ 30, AlertCenter dùng hook `useExpiringContracts` (số thật + booking sớm nhất), realtime listener `.contract.renewal_reminder`, `lib/featureFlags.ts` ẩn nút "Tạo block"/bulk toolbar. Verify: Unit 34/34 xanh (96 assertions), FE build pass. | `docs/plans/plan_001.md` (Phase 5 ✅), `app/Services/ContractService.php`, `app/Repositories/ContractRepository/*`, `app/Models/Contract.php`, `app/Policies/ContractPolicy.php`, `app/Events/ContractRenewalReminderQueued.php`, `app/Console/Commands/SendContractRenewalReminders.php`, `app/Console/Kernel.php`, `app/Http/Middleware/EnsurePartner360Enabled.php`, `app/Http/Kernel.php`, `config/app.php`, `routes/api.php`, `app/Http/Controllers/Partner/PartnerContractController.php`, `tests/Unit/Services/ContractServiceTest.php`, `tests/Unit/Http/Middleware/EnsurePartner360EnabledTest.php`, `bks-system-fe/src/pages/Partner/ContractDetail.tsx`, `bks-system-fe/src/pages/Partner/Calendar.tsx`, `bks-system-fe/src/pages/Partner/Bookings.tsx`, `bks-system-fe/src/pages/Partner/components/AlertCenter.tsx`, `bks-system-fe/src/hooks/Partner/useExpiringContracts.ts`, `bks-system-fe/src/hooks/Partner/useBookingsRealtime.ts`, `bks-system-fe/src/services/partnerService.ts`, `bks-system-fe/src/lib/featureFlags.ts`, `bks-system-fe/src/Router.tsx` |

| 2026-05-21 | Landing page prominence docs moved back to BE repo and renamed for BE conventions; lead/SRS/design/plan now capture the timeline from initial homepage prominence to grouped-by-province suggestions and FE fallback | `docs/leads/lead_260521_landing-page-prominence.md`, `docs/SRC/srs_landing_page_prominence.md`, `docs/designs/design_003.md`, `docs/plans/plan_003.md` |
| 2026-05-21 | Landing page testcase TC003 added for homepage prominence / city priorities / grouped suggestions / fallback / CTA / responsive | `docs/test-cases/testcase_003.md`, `docs/SRC/srs_landing_page_prominence.md`, `docs/plans/plan_003.md` |

## Quick Links

- Core domain facts: `docs/memory/knowledge_base.md`
- Architecture/requirement decisions: `docs/memory/decisions.md`
- Canonical DB overview: `docs/databases_docs/db_overview_etc_core_schema.md`

