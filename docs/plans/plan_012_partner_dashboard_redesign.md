# Implementation Plan: Partner Dashboard Redesign (vận hành-first)

## Document Information

| Mục | Nội dung |
|-----|----------|
| **Plan ID** | PLAN-PP360-DASH-012 |
| **Created** | 2026-06-08 |
| **Status** | Phase 1–3 done (2026-06-08) · Phase 4 QA pending |
| **Related UI Design** | `docs/ui-designs/partner-dashboard/ui_design_v1.md` |
| **Related SRS** | `docs/SRC/srs_partner_portal_360.md` (PP360-DASH-001 → 006) |
| **Route** | `http://localhost:5173/partner/dashboard` |
| **Risk level** | Medium — refactor màn landing partner; không đổi schema DB |

### Executive Summary

Tái cấu trúc Partner Dashboard theo nguyên tắc **operations first, analytics second**: ưu tiên check-in/check-out hôm nay, alert 4 loại, work queue giàu ngữ cảnh, filter theo tài sản. Phần lớn KPI vận hành **đã có sẵn trong API** `GET /partner/dashboard/stats` nhưng chưa render trên FE. Cần bổ sung BE cho **overbooking count** và **mở rộng pending-bookings**; không cần migration DB.

**Ước lượng:** 7–10 dev-days (1 BE + 1 FE), chia 4 phase có thể release tăng dần.

### Timeline đề xuất

| Phase | Thời gian | Deliverable | Release slice |
|-------|-----------|-------------|---------------|
| **Phase 0** — Gate | 0.5 ngày | `UI_APPROVED` + handoff engineer | — |
| **Phase 1** — FE quick wins | 2 ngày | KPI vận hành, việt hóa, refetch, layout 2 tầng (data hiện có) | Slice A (cải thiện ngay, không đổi API) |
| **Phase 2** — BE API | 2–3 ngày | `property_id` filter, pending enriched, overbooking count | Slice B (API backward-compatible) |
| **Phase 3** — FE integration | 2–3 ngày | Property filter, alert 4 ô, work queue SLA/conflict | Slice C (full To-Be) |
| **Phase 4** — QA & polish | 1–2 ngày | Test, responsive, E2E script | Release final |

---

## Phase Overview

| Phase | Tên | Tasks | Dependencies | Song song |
|-------|-----|-------|--------------|-----------|
| 0 | UI Gate | 1 | — | — |
| 1 | FE Quick Wins | 8 | Phase 0 (khuyến nghị) | — |
| 2 | BE API Extensions | 6 | — | Phase 1 (một phần) |
| 3 | FE Full Integration | 7 | Phase 2 (T2.4–T2.6 cho conflict/overbooking) | — |
| 4 | QA & Release | 4 | Phase 1–3 | — |

---

## Dependency Graph

```text
Phase 0: [T0.1 UI_APPROVED]
              │
    ┌─────────┴─────────┐
    ▼                   ▼
Phase 1 (FE)        Phase 2 (BE)
[T1.1]─[T1.8]       [T2.1]─[T2.3] property filter
                         │
                    [T2.4] pending enrich
                         │
                    [T2.5] overbooking count
                         │
                    [T2.6] conflict flag pending
                         │
                         ▼
                    Phase 3 (FE)
                    [T3.1]─[T3.7]
                         │
                         ▼
                    Phase 4 (QA)
                    [T4.1]─[T4.4]
```

---

## Hiện trạng kỹ thuật (As-Is)

### API đã có — FE chưa dùng hết

| Field | Endpoint | FE hiện tại |
|-------|----------|-------------|
| `todayCheckInCount` | `GET /partner/dashboard/stats` | ❌ Không render |
| `todayCheckOutCount` | stats | ❌ |
| `inStayCount` | stats | ❌ |
| `pendingCancellationCount` | stats | ❌ (AlertCenter thiếu ô thứ 4) |
| `gmvMtd`, `netRevenueMtd` | `GET /partner/dashboard/kpis` | ✅ (nhưng lẫn với 7 KPI cũ) |

### Gap BE cần làm

| Gap | Hiện tại | Cần |
|-----|----------|-----|
| Property filter | KPI/chart/stats không nhận `property_id` | Query param optional trên 4 endpoint dashboard |
| Pending bookings | 5 field: id, user_name, room_number, start_date, status | Thêm property_name, end_date, total_amount, created_at, room_id, nights, has_conflict |
| Overbooking count | `AlertCenter` hardcode `overbookingCount={0}` | Count từ BE (logic tương tự `Calendar.tsx` useMemo) |
| Conflict pre-check | Chỉ khi confirm (409) | `has_conflict` trên từng pending item |

### File impact chính

| File | Phase | Loại thay đổi |
|------|-------|---------------|
| `bks-system-fe/src/pages/Partner/Dashboard.tsx` | 1, 3 | Refactor layout lớn |
| `bks-system-fe/src/pages/Partner/components/AlertCenter.tsx` | 1, 3 | Thêm alert hủy; wire overbooking |
| `bks-system-fe/src/hooks/usePartnerDashboardQuery.ts` | 3 | Query key + `propertyId` |
| `bks-system-fe/src/api/partnerDashboardApi.ts` | 3 | Query params |
| `app/Services/DashboardService.php` | 2 | Filter `property_id` |
| `app/Services/PartnerKpiService.php` | 2 | Filter + overbooking count |
| `app/Repositories/BookingRepository/BookingRepository.php` | 2 | Enrich pending query |
| `app/Http/Controllers/Partner/PartnerDashboardController.php` | 2 | Validate `property_id` |
| `routes/api.php` | 2 | (optional) `GET /partner/dashboard/overbooking-count` |

**Không migration DB** — chỉ mở rộng query trên bảng `bookings`, `rooms`, `properties`, `room_blocks` hiện có (xem `docs/databases_docs/db_overview_etc_core_schema.md`).

---

## Phase 0: UI Gate

**Goal:** Chốt phạm vi UI trước khi code full To-Be.  
**Duration:** 0.5 ngày

### [T0.1] Xác nhận UI_APPROVED

- **Description:** User/stakeholder review `ui_design_v1.md` + canvas preview; gửi token `UI_APPROVED`.
- **Acceptance Criteria:**
  - [ ] `ui_design_v1.md` status cập nhật `UI_APPROVED`
  - [ ] `ui_handoff_for_engineer.md` được tạo (stack-ui-design-gate)
- **Dependencies:** None
- **Blocks:** Phase 3 layout final (Phase 1 có thể chạy trước nếu user đồng ý delta-only)

---

## Phase 1: FE Quick Wins (Slice A)

**Goal:** Cải thiện dashboard ngay với API hiện có — không chờ BE.  
**Duration:** 2 ngày  
**Dependencies:** None (khuyến nghị sau T0.1)  
**Parallel With:** Phase 2 (bắt đầu song song từ ngày 2)

### [T1.1] Tái cấu trúc KPI — 2 tầng (data hiện có)

- **Description:** Thay grid 7 cột bằng:
  - Hàng 1: Check-in hôm nay, Check-out hôm nay, Đang lưu trú, Lấp đầy hôm nay (`stats`)
  - Hàng 2: GMV tháng, Doanh thu thực nhận, Thời gian xác nhận TB (`headlineKpis`)
- **Acceptance Criteria:**
  - [ ] 4 metric vận hành hiển thị đúng từ `stats`
  - [ ] Sub-label thời gian: "Hôm nay · dd/MM", "Tháng M/YYYY"
  - [ ] Responsive: 4→2→1 cột
- **Files:** `Dashboard.tsx`, (mới) `components/OperationsKpiGrid.tsx`, `components/FinancialKpiGrid.tsx`
- **SRS:** PP360-DASH-001, -002, -004
- **Est:** 3h

### [T1.2] Việt hóa nhãn & tooltip

- **Description:** Đổi copy theo bảng trong `ui_design_v1.md`; ẩn text `Full: X ₫` (chỉ tooltip hover).
- **Acceptance Criteria:**
  - [ ] Không còn label EN trên dashboard (Pending, Net Revenue, Time-to-confirm…)
  - [ ] Tooltip giải thích GMV vs Net vs occupancy snapshot
- **Files:** `Dashboard.tsx`, `OccupancyChart.tsx`, `GmvChart.tsx`, `AlertCenter.tsx`
- **Est:** 2h

### [T1.3] Nút Làm mới — React Query refetch

- **Description:** Thay `window.location.reload()` bằng `queryClient.invalidateQueries` cho tất cả dashboard keys.
- **Acceptance Criteria:**
  - [ ] Bấm Làm mới không reload trang
  - [ ] Scroll position giữ nguyên
  - [ ] Timestamp cập nhật sau refetch
- **Files:** `Dashboard.tsx`
- **Est:** 1h

### [T1.4] AlertCenter — thêm "Yêu cầu hủy"

- **Description:** Ô thứ 4 dùng `stats.pendingCancellationCount`; CTA → `/partner/cancellation-requests`.
- **Acceptance Criteria:**
  - [ ] Hiển thị count từ API stats
  - [ ] Deep link đúng route
- **Files:** `AlertCenter.tsx`, `Dashboard.tsx` (truyền prop)
- **SRS:** PP360-DASH-005
- **Est:** 2h

### [T1.5] Gỡ biểu đồ "Phân tích doanh thu" khỏi dashboard

- **Description:** Xóa `usePartnerRevenueAnalyticsQuery` + AreaChart tháng khỏi `Dashboard.tsx`. Giữ hook/API cho `/partner/reports` (không xóa BE).
- **Acceptance Criteria:**
  - [ ] Dashboard chỉ còn 2 chart 30 ngày
  - [ ] Reports page vẫn hoạt động (nếu đã dùng API đó)
- **Files:** `Dashboard.tsx`
- **SRS:** PP360-DASH-006 (giảm trùng lặp)
- **Est:** 1h

### [T1.6] Portfolio collapsible

- **Description:** Gom 4 metric cũ (cơ sở, tổng phòng, trống, doanh thu dự kiến) vào `Collapsible` mặc định đóng.
- **Acceptance Criteria:**
  - [ ] Mặc định collapsed
  - [ ] Expand hiển thị đủ 4 số liệu từ `stats`
- **Files:** `Dashboard.tsx`, (mới) `components/PortfolioSummary.tsx`
- **Est:** 2h

### [T1.7] Bảo trì — compact khi không có sự cố

- **Description:** Empty state thu thành 1 dòng + link; chỉ render grid card khi `urgentMaintenances.length > 0`.
- **Acceptance Criteria:**
  - [ ] Không có sự cố → 1 dòng, không chiếm 400px chiều cao
  - [ ] Có sự cố → grid như hiện tại
- **Files:** `Dashboard.tsx`
- **Est:** 1h

### [T1.8] Thời gian xác nhận TB — empty state

- **Description:** Khi `avgConfirmSeconds === null`, hiển thị "Chưa đủ dữ liệu" + tooltip thay vì "N/A".
- **Acceptance Criteria:**
  - [ ] Copy tiếng Việt rõ nghĩa
  - [ ] Khi có data: format phút/giờ + màu SLA (xanh ≤5p, vàng 5–15p, đỏ >15p)
- **Files:** `FinancialKpiGrid.tsx` hoặc `Dashboard.tsx`
- **Est:** 1h

---

## Phase 2: BE API Extensions (Slice B)

**Goal:** Hỗ trợ filter property, pending giàu field, overbooking count.  
**Duration:** 2–3 ngày  
**Dependencies:** None  
**Parallel With:** Phase 1

### [T2.1] Quy ước query param `property_id`

- **Description:** Thêm validation optional `property_id` (integer, thuộc partner) cho:
  - `GET /partner/dashboard/stats`
  - `GET /partner/dashboard/kpis`
  - `GET /partner/dashboard/charts/occupancy`
  - `GET /partner/dashboard/charts/gmv`
  - `GET /partner/dashboard/pending-bookings`
- **Acceptance Criteria:**
  - [ ] Không truyền `property_id` → hành vi như hiện tại (all properties)
  - [ ] `property_id` không thuộc partner → 403 hoặc 404
  - [ ] OpenAPI/route doc cập nhật trong comment controller
- **Files:** `PartnerDashboardController.php`, `DashboardValidation.php` (nếu có)
- **SRS:** PP360-DASH-003, -006
- **Est:** 2h

### [T2.2] Filter `property_id` trong DashboardService & PartnerKpiService

- **Description:** Truyền `?int $propertyId` xuống repository queries (`where properties.id = ?` hoặc skip khi null).
- **Acceptance Criteria:**
  - [ ] Stats/KPI/chart thay đổi đúng khi filter 1 property (unit test)
  - [ ] Cache key bao gồm `property_id` (tránh stale cross-property)
- **Files:** `DashboardService.php`, `PartnerKpiService.php`, `InvalidatePartnerKpiCache` (nếu cần key pattern)
- **Est:** 4h

### [T2.3] Unit test filter property

- **Description:** Mở rộng `PartnerKpiServiceTest` + test stats với 2 property fixture.
- **Acceptance Criteria:**
  - [ ] Test pass: all vs single property occupancy khác nhau
- **Files:** `tests/Unit/Services/PartnerKpiServiceTest.php`, (mới) feature test nhẹ
- **Est:** 3h

### [T2.4] Enrich `GET /partner/dashboard/pending-bookings`

- **Description:** Mở rộng `getPendingBookingsForPartner`:
  ```text
  + properties.name AS property_name
  + bookings.end_date, bookings.created_at, bookings.room_id
  + bookings.total_price (hoặc field amount hiện có)
  + ORDER BY created_at ASC (chờ lâu nhất trước) thay DESC
  + LIMIT tăng lên 10 (configurable query ?limit=10)
  ```
- **Acceptance Criteria:**
  - [ ] Response JSON có đủ field cho work queue spec
  - [ ] Sắp xếp SLA-first
- **Files:** `BookingRepository.php`, `PartnerDashboardController.php`
- **Est:** 3h

### [T2.5] Overbooking count endpoint

- **Description:** Thêm `overbookingCount` vào payload `GET /partner/dashboard/kpis` **hoặc** endpoint riêng `GET /partner/dashboard/alerts-summary`.

  **Logic đề xuất (DEC-260608-DASH-001):** Quét booking active (status ∉ cancelled/completed) trong cửa sổ 30 ngày tới, group theo `room_id`, đếm cặp interval overlap (cùng semantics `ConflictChecker::intervalsOverlap`, end exclusive). Không tính pending vs pending trừ khi cùng room overlap — align với Calendar FE.

- **Acceptance Criteria:**
  - [ ] Trả `overbookingCount: int >= 0`
  - [ ] Filter `property_id` áp dụng
  - [ ] Unit test: 2 booking overlap cùng phòng → count ≥ 1
- **Files:** `PartnerKpiService.php` hoặc service mới `PartnerOverbookingService.php`
- **SRS:** PP360-DASH-005, PP360-CAL-005
- **Est:** 5h

### [T2.6] Conflict flag trên pending items

- **Description:** Với mỗi pending booking, gọi `ConflictChecker::hasConflict(room_id, start, end, excludeBookingId=id)` → field `has_conflict: bool`.
- **Acceptance Criteria:**
  - [ ] Pending có booking khác overlap → `has_conflict: true`
  - [ ] Performance: batch hoặc limit 10 items; cache 60s chung dashboard
- **Files:** `PartnerDashboardController.php` hoặc `DashboardService`
- **SRS:** PP360-BOOK-004
- **Est:** 3h

---

## Phase 3: FE Full Integration (Slice C)

**Goal:** Hoàn thiện To-Be theo `ui_design_v1.md`.  
**Duration:** 2–3 ngày  
**Dependencies:** T2.1–T2.6 (một phần T2.5 có thể stub = 0 tạm thời)

### [T3.1] Property filter component

- **Description:** Dropdown "Tất cả tài sản" + danh sách property (reuse API properties list hoặc lightweight endpoint). Persist `localStorage` key `partner-dashboard-property-id`.
- **Acceptance Criteria:**
  - [ ] Đổi filter → refetch tất cả dashboard queries
  - [ ] Query key: `['partner-stats', propertyId]`, etc.
- **Files:** (mới) `components/DashboardPropertyFilter.tsx`, `usePartnerDashboardQuery.ts`, `partnerDashboardApi.ts`
- **Est:** 4h

### [T3.2] AlertCenter — wire overbooking thật

- **Description:** Nhận `overbookingCount` từ KPI/alerts API; bỏ hardcode `0`.
- **Acceptance Criteria:**
  - [ ] Count khớp logic Calendar khi cùng dataset test
  - [ ] CTA → `/partner/calendar`
- **Files:** `AlertCenter.tsx`, `Dashboard.tsx`
- **Est:** 2h

### [T3.3] Work queue card component

- **Description:** Tách `PendingBookingCard` hiển thị: mã, khách, property, phòng, khoảng ngày + đêm, giá, badge chờ (SLA màu), badge conflict/HĐ dài hạn.
- **Acceptance Criteria:**
  - [ ] Badge đỏ khi chờ > 5 phút
  - [ ] Badge "Trùng lịch" khi `has_conflict`
  - [ ] Badge "Sẽ tạo HĐ thuê" khi nights ≥ 30
  - [ ] Nút action min-height 44px mobile
- **Files:** (mới) `components/PendingBookingCard.tsx`, `Dashboard.tsx`
- **Est:** 5h

### [T3.4] Layout 5/12 + 7/12

- **Description:** Desktop: work queue trái, charts phải; tablet/mobile stack theo spec responsive.
- **Acceptance Criteria:**
  - [ ] Khớp wireframe `ui_design_v1.md`
  - [ ] Không duplicate pending detail trong AlertCenter (chỉ banner số)
- **Files:** `Dashboard.tsx`
- **Est:** 3h

### [T3.5] Deep link CTA với property context

- **Description:** Navigate từ alert/booking CTA kèm `?property_id=` khi filter đang chọn.
- **Acceptance Criteria:**
  - [ ] Bookings pending filter + calendar mở đúng property
- **Files:** `AlertCenter.tsx`, `Dashboard.tsx`
- **Est:** 2h

### [T3.6] Cập nhật realtime invalidation

- **Description:** Đảm bảo `useBookingsRealtime` invalidate keys có `propertyId` prefix hoặc invalidate all dashboard queries.
- **Acceptance Criteria:**
  - [ ] Booking mới → work queue + KPI cập nhật không reload
- **Files:** `useBookingsRealtime.ts`, `RealtimeNotifyProvider.tsx`
- **Est:** 2h

### [T3.7] i18n keys (optional)

- **Description:** Chuyển label cứng sang `vi.json` nếu project pattern yêu cầu; tối thiểu giữ VI inline đồng nhất Sidebar.
- **Acceptance Criteria:**
  - [ ] Không regression locale EN nếu có toggle ngôn ngữ
- **Est:** 2h (Could — có thể defer)

---

## Phase 4: QA & Release

**Goal:** Xác minh acceptance criteria UI + regression Partner Portal.  
**Duration:** 1–2 ngày

### [T4.1] BE unit/feature tests

- **Acceptance Criteria:**
  - [ ] `PartnerKpiServiceTest` + overbooking count cases
  - [ ] Pending bookings enriched fields
  - [ ] Property filter authorization
- **Est:** 3h

### [T4.2] FE manual + responsive checklist

- **Scenarios:**
  - Partner 17 property: filter 1 property → KPI đổi
  - 4 pending, 1 chờ >5p → badge đỏ
  - Làm mới không flash reload
  - Mobile: nút Duyệt đủ lớn
- **Est:** 2h

### [T4.3] Cập nhật E2E script

- **Files:** `bks-system-fe/business-script/E2E_PARTNER_PORTAL_360_PHASE*.md`
- **Est:** 2h

### [T4.4] stack-review-branch trước merge

- **Handoff:** Chạy `stack-review-branch` so với `develop`/`main`.
- **Est:** 1h

---

## Conflict Analysis

| ID | Loại | Mô tả | Giải quyết |
|----|------|-------|------------|
| C1 | File | `Dashboard.tsx` Phase 1 và 3 cùng sửa | Phase 1 merge trước; Phase 3 rebase |
| C2 | API | Chart + stats cache key thiếu property | T2.2 bắt buộc trước T3.1 release |
| C3 | UX | AlertCenter vs work queue trùng pending | Alert chỉ số + CTA; chi tiết chỉ ở work queue (spec v1) |
| C4 | Performance | T2.6 N+1 conflict check | Limit 10 pending; cache 60s |

**Không có conflict DB** — zero migration.

---

## Risk Register

| ID | Rủi ro | L | I | Mitigation |
|----|--------|---|---|------------|
| R1 | Overbooking count BE khác Calendar FE | M | M | Dùng chung `ConflictChecker::intervalsOverlap`; test cùng fixture |
| R2 | UI_APPROVED chưa có, scope creep layout | M | L | Phase 1 ship trước; Phase 3 sau approval |
| R3 | Pending enrich thiếu `total_price` column | L | M | Kiểm tra migration bookings; fallback `estimated_amount` |
| R4 | Property list API chậm trên header | L | L | Cache property dropdown; default "Tất cả" |

---

## Testing Strategy

### Unit Tests (BE)
- `PartnerKpiService`: property filter, overbooking count
- `ConflictChecker`: overlap edge cases (back-to-back không count)
- Pending repository: sort SLA-first

### Feature Tests
- `GET /partner/dashboard/stats?property_id=X` authorized vs foreign property

### QC Test-case Handoff
- **Output:** `docs/test-cases/testcase_012_partner_dashboard.md` (stack-testcase)
- **Scenarios từ SRS:** PP360-DASH-001 → 006, PP360-BOOK-003/004

---

## Rollback Strategy

| Phase | Rollback |
|-------|----------|
| Phase 1 | Revert FE commit; API không đổi |
| Phase 2 | Revert BE; FE Phase 1 vẫn chạy (ignore new fields) |
| Phase 3 | Feature flag `VITE_PARTNER_DASHBOARD_V2=false` → render layout cũ (optional) |

---

## Downstream Handoffs

| Skill | Input | Output |
|-------|-------|--------|
| **stack-task** | Plan này | Implement từng task T1.x–T4.x theo thứ tự dependency |
| **stack-testcase** | SRS + plan Phase 4 | `testcase_012_partner_dashboard.md` |
| **stack-review-branch** | Branch feature | Security, performance, regression report |
| **report-writer** | Plan completed + screenshots | Mục triển khai trong `docs/report/` |

---

## Appendix A — Task Quick Reference

| Task | Tên | Phase | Deps | Est (h) |
|------|-----|-------|------|---------|
| T0.1 | UI_APPROVED gate | 0 | — | 2 |
| T1.1 | KPI 2 tầng | 1 | — | 3 |
| T1.2 | Việt hóa | 1 | — | 2 |
| T1.3 | Refetch | 1 | — | 1 |
| T1.4 | Alert hủy | 1 | — | 2 |
| T1.5 | Gỡ chart tháng | 1 | — | 1 |
| T1.6 | Portfolio collapse | 1 | — | 2 |
| T1.7 | Maintenance compact | 1 | — | 1 |
| T1.8 | TTC empty state | 1 | — | 1 |
| T2.1 | property_id validation | 2 | — | 2 |
| T2.2 | Service filter | 2 | T2.1 | 4 |
| T2.3 | Unit test filter | 2 | T2.2 | 3 |
| T2.4 | Pending enrich | 2 | — | 3 |
| T2.5 | Overbooking count | 2 | — | 5 |
| T2.6 | Conflict flag | 2 | — | 3 |
| T3.1 | Property filter FE | 3 | T2.1 | 4 |
| T3.2 | Wire overbooking | 3 | T2.5 | 2 |
| T3.3 | PendingBookingCard | 3 | T2.4,T2.6 | 5 |
| T3.4 | Layout 5/12 | 3 | T1.1 | 3 |
| T3.5 | Deep link | 3 | T3.1 | 2 |
| T3.6 | Realtime keys | 3 | T3.1 | 2 |
| T3.7 | i18n (Could) | 3 | T1.2 | 2 |
| T4.1–T4.4 | QA | 4 | Phase 1–3 | 8 |

**Tổng ước lượng:** ~56 giờ ≈ **7–8 dev-days** (buffer 10% → 10 ngày).

---

## Checklist trước khi bắt đầu

- [ ] `UI_APPROVED` (khuyến nghị; Phase 1 có thể bắt đầu sớm)
- [ ] BE + FE dev server chạy local
- [ ] Partner test account có ≥2 property + pending bookings
- [ ] Branch: `feature/partner-dashboard-v2`
