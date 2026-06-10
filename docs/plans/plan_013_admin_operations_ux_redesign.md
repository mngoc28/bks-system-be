# Implementation Plan: Admin Portal — Operations-first UX Redesign

## Document Information

| Mục | Nội dung |
|-----|----------|
| **Plan ID** | PLAN-ADM-UX-013 |
| **Version** | 1.0 |
| **Status** | Draft |
| **Author** | Senior BA + Senior Hospitality Domain Expert |
| **Date** | 2026-06-08 |
| **Domain Review Input** | Đánh giá UI/UX Admin (2026-06-08) — CONDITIONALLY APPROVED |
| **Related Plans** | [plan_005_admin_cross_module_navigation.md](./plan_005_admin_cross_module_navigation.md) (đã triển khai một phần), [plan_012_partner_dashboard_redesign.md](./plan_012_partner_dashboard_redesign.md) (mẫu operations-first) |
| **Route chính** | `http://localhost:5173/admin/dashboard`, `/admin/bookings`, sidebar layout |
| **Risk level** | Medium — refactor UX admin; BE mở rộng query, **không migration DB** |

---

## 1. Executive Summary

Admin portal hiện có **nền tảng UI nhất quán** (sidebar, PageBar, table/card toggle, drill-down cross-module) nhưng **thiếu tầng vận hành ca trực** so với chuẩn ngành lưu trú. Dashboard mang tính báo cáo/analytics; màn Booking chỉ hiển thị 4 trạng thái nghiệp vụ (`pending/confirmed/cancelled/completed`) mà không phản ánh **trạng thái lưu trú** (`stay_status`: checked-in, checked-out, no-show). Sidebar trộn module vận hành với CMS (News, Chatbot, Newsletter) gây nhiễu nhận thức trong giờ cao điểm.

**Mục tiêu:** Chuyển Admin từ "bảng điều khiển báo cáo" sang **"bảng điều khiển ca trực"** — ưu tiên việc cần làm ngay, sau đó mới analytics — tái sử dụng logic BE đã có ở Partner (`DashboardService::getStatsForPartner`, `stay_status` trên `bookings`).

**Ước lượng:** 8–12 dev-days (1 BE + 1 FE), 4 phase release tăng dần.

**Domain Recommendation:** CONDITIONALLY APPROVED → APPROVED sau Phase 1–2.

---

## 2. Problem Statement

### 2.1 Current State (As-Is)

| Khu vực | Hiện trạng | Nguồn code |
|---------|------------|------------|
| **Admin Dashboard** | KPI user/partner/room/booking, action cards, biểu đồ GMV/booking theo tháng | `bks-system-fe/src/pages/Admin/Dashboard/index.tsx` |
| **API Admin Dashboard** | 7 endpoint: total-user, total-partner, system-room, bookings-per-month, revenue-per-month, properties-bookings-count | `DashboardController.php`, `routes/api.php` L404–411 |
| **API Partner Dashboard** | Đã có `stats` với todayCheckIn, todayCheckOut, inStay, occupancyRate | `DashboardService::getStatsForPartner` |
| **Admin Booking** | Badge 4 trạng thái; **không** hiển thị/lọc `stay_status` | `BookingTable.tsx`, `mapBookingStatus()` |
| **Partner Booking** | Đã có `getPartnerRowDisplayStatus()`, filter stay_status, check-in/out actions | `partnerBookingDisplay.ts`, `Bookings.tsx` |
| **Sidebar** | 6 nhóm menu, 15+ item phẳng | `src/components/layout/index.tsx` |
| **Cross-module nav** | ContextFilterChips, adminNavigation helper — **đã có** | plan_005 đã implement một phần |

### 2.2 Pain Points (Domain + UX)

| # | Pain Point | Ai bị ảnh hưởng | Business Risk |
|---|------------|-----------------|---------------|
| P1 | Không thấy arrival/departure hôm nay trên Admin Dashboard | Admin vận hành, CS | Trễ xử lý booking, khách chờ xác nhận |
| P2 | Không biết occupancy / phòng đang ở (in-house) toàn hệ thống | Admin, quản lý danh mục | Quyết định inventory/ADR chậm |
| P3 | Booking Admin thiếu stay_status | Admin khi điều phối partner | Không phân biệt "đã xác nhận" vs "đang ở" vs "no-show" |
| P4 | Sidebar dài, trộn vận hành + nội dung | Admin ca trực | Tăng thời gian tìm module, sai thao tác |
| P5 | Work queue booking thiếu ngữ cảnh (property, nights, SLA) | Admin duyệt booking | Duyệt chậm, khó ưu tiên |

### 2.3 Opportunity

- Tái sử dụng **80% logic BE** từ Partner Dashboard (plan_012) — chỉ cần scope `admin` (toàn hệ thống).
- Tái sử dụng **utility FE** `partnerBookingDisplay.ts` cho nhãn trạng thái lưu trú.
- Không cần migration DB — `bookings.stay_status` đã có từ migration `2026_04_20_221212`.

---

## 3. Goals & Success Metrics

### 3.1 Primary Goal

Admin mở dashboard và **trong ≤ 10 giây** biết: bao nhiêu check-in/check-out hôm nay, bao nhiêu booking chờ duyệt, occupancy toàn hệ thống — click một lần để xử lý.

### 3.2 Success Metrics (Measurable)

| Metric | Baseline (ước lượng) | Target sau Phase 2 |
|--------|----------------------|---------------------|
| Time-to-first-action (dashboard → booking detail) | ~45s (nhiều click/filter thủ công) | ≤ 15s |
| % admin hiểu trạng thái lưu trú từ list booking | 0% (không hiển thị) | 100% (badge kết hợp) |
| Số menu item visible mặc định (sidebar) | 15+ | ≤ 10 (nhóm CMS thu gọn) |
| Booking pending xử lý trong ngày | Không đo | Giảm ≥ 20% sau 2 tuần UAT |

### 3.3 Non-Goals (Won't-have trong plan này)

- Admin **không** thực hiện check-in/check-out thay Partner (chỉ xem + điều phối).
- Không redesign toàn bộ CMS module (News, Chatbot) — chỉ IA sidebar.
- Không thay đổi business rules booking/deposit (xem plan_010).
- Không mobile app admin riêng.

---

## 4. Stakeholders

| Stakeholder | Vai trò | Mức ảnh hưởng |
|-------------|---------|---------------|
| System Admin | Người dùng chính | Cao |
| Partner (gián tiếp) | Admin điều phối booking/partner | Trung bình |
| Guest (gián tiếp) | Thời gian xử lý booking nhanh hơn | Trung bình |
| QC/UAT | Verify scenarios ca trực | Cao |
| Senior Engineer | Implement FE/BE | Cao |

---

## 5. User Stories & Acceptance Criteria

### Epic E1: Bảng điều khiển ca trực (Admin Dashboard)

**US-E1-01:** Là Admin, tôi muốn thấy 4 KPI vận hành hôm nay (check-in, check-out, đang lưu trú, occupancy) ngay khi vào dashboard, để ưu tiên ca trực.

**Acceptance Criteria:**
- [ ] Given admin đã đăng nhập, when mở `/admin/dashboard`, then hiển thị 4 ô KPI vận hành với số liệu thời gian thực.
- [ ] Given KPI "Check-in hôm nay" > 0, when click ô KPI, then điều hướng `/admin/bookings?status=1&stay_status=pending&start_date={today}&source=dashboard`.
- [ ] Given không có dữ liệu, then hiển thị `0` + sub-label "Hôm nay · dd/MM/yyyy".

**US-E1-02:** Là Admin, tôi muốn work queue booking chờ duyệt có đủ ngữ cảnh (khách, phòng, property, số đêm, tổng tiền), để duyệt nhanh không cần mở detail.

**Acceptance Criteria:**
- [ ] Given có booking status=0, when xem work queue trên dashboard, then mỗi dòng có: user_name, room_number, property_name, start_date–end_date, nights, total_amount.
- [ ] Given click một dòng work queue, then mở BookingDetailDialog hoặc điều hướng booking list với highlight.

**US-E1-03:** Là Admin, tôi muốn phần analytics (biểu đồ GMV, booking theo tháng) nằm **dưới** KPI vận hành, để không che khuất việc cần làm ngay.

**Acceptance Criteria:**
- [ ] Layout 2 tầng: Tầng 1 = KPI vận hành + action cards + work queue; Tầng 2 = charts/analytics.
- [ ] Responsive: 4 KPI → 2 → 1 cột trên mobile.

---

### Epic E2: Trạng thái lưu trú trên Admin Booking

**US-E2-01:** Là Admin, tôi muốn thấy nhãn trạng thái kết hợp (booking status + stay_status) trên bảng booking, giống Partner portal.

**Acceptance Criteria:**
- [ ] Given booking status=1 và stay_status=checked_in, then badge hiển thị "Đang lưu trú" (màu xanh đậm).
- [ ] Given booking status=1 và stay_status=no_show, then badge hiển thị "Không đến" (màu đỏ).
- [ ] Logic nhãn dùng chung utility (tách từ `partnerBookingDisplay.ts` → `bookingDisplay.ts` shared).

**US-E2-02:** Là Admin, tôi muốn lọc booking theo stay_status, để tìm khách đang ở hoặc no-show.

**Acceptance Criteria:**
- [ ] Filter dropdown thêm: Tất cả / Chờ nhận phòng / Đang lưu trú / Đã trả phòng / Không đến.
- [ ] Given chọn "Đang lưu trú", when apply filter, then API gửi `stay_status=checked_in`.
- [ ] Filter kết hợp được với status, date range, context chips (partner/property/room).

**US-E2-03:** Là Admin, tôi muốn quick filter "Hôm nay check-in" / "Hôm nay check-out" trên Booking Manage, để xử lý ca trực.

**Acceptance Criteria:**
- [ ] 2 chip/preset filter: "Nhận phòng hôm nay", "Trả phòng hôm nay".
- [ ] Click preset → set date + status filter tương ứng, sync URL params.

---

### Epic E3: Sidebar Information Architecture

**US-E3-01:** Là Admin ca trực, tôi muốn sidebar ưu tiên module vận hành, thu gọn nhóm nội dung, để giảm nhiễu.

**Acceptance Criteria:**
- [ ] Nhóm "Vận hành" (luôn mở): Dashboard, Partner, Partner Approval, Properties, Rooms, Bookings, Settlements.
- [ ] Nhóm "Danh mục & Nội dung" (collapsed mặc định): Amenities, Services, Province, News, Chatbot, Newsletter.
- [ ] Nhóm "Hệ thống": User Management.
- [ ] Collapsed sidebar vẫn hiển thị icon + tooltip đầy đủ.

**US-E3-02:** Là Admin, tôi muốn badge số trên menu Bookings khi có pending, để biết việc chờ mà không vào dashboard.

**Acceptance Criteria:**
- [ ] Given pendingBookingsCount > 0, then hiển thị badge đỏ trên menu item Bookings.
- [ ] Số badge cập nhật khi refetch (interval 5 phút hoặc on navigation).

---

## 6. Functional Requirements

| ID | Requirement | Priority | Phase |
|----|-------------|----------|-------|
| FR-001 | API `GET /admin/dashboard/stats` trả KPI vận hành toàn hệ thống | Must | 2 |
| FR-002 | API `GET /admin/dashboard/pending-bookings?limit=10` trả work queue enriched | Must | 2 |
| FR-003 | Dashboard FE: layout 2 tầng (operations → analytics) | Must | 1 |
| FR-004 | Dashboard FE: 4 KPI vận hành + drill-down | Must | 1–3 |
| FR-005 | Dashboard FE: work queue panel | Must | 3 |
| FR-006 | Admin booking list: hiển thị combined status badge | Must | 1 |
| FR-007 | Admin booking list: filter stay_status | Must | 2 |
| FR-008 | Admin booking: preset filter check-in/check-out hôm nay | Should | 3 |
| FR-009 | Sidebar: nhóm collapsible, CMS collapsed mặc định | Should | 1 |
| FR-010 | Sidebar: badge pending trên menu Bookings | Could | 3 |
| FR-011 | Shared utility `bookingDisplay.ts` (tách từ partner) | Must | 1 |
| FR-012 | Admin booking API trả `stay_status` trong response | Must | 2 |

---

## 7. Non-Functional Requirements

| Loại | Yêu cầu |
|------|----------|
| **Performance** | `GET /admin/dashboard/stats` ≤ 500ms (p95), cache Redis 2 phút |
| **Security** | Chỉ `role:admin`; không lộ PII guest ngoài work queue cần thiết |
| **Accessibility** | KPI cards keyboard-focusable; badge có aria-label |
| **i18n** | Nhãn tiếng Việt qua `react-i18next`; key mới trong `vi.json` |
| **Backward compatibility** | API admin cũ không đổi response; endpoint mới là additive |

---

## 8. Technical Design Summary

### 8.1 Backend — Endpoint mới (additive)

```
GET /api/v1/admin/dashboard/stats
Response: {
  todayCheckInCount, todayCheckOutCount, inStayCount,
  occupancyRate, vacantRooms, totalRooms,
  pendingBookingsCount, pendingCancellationCount,
  totalProperties, totalPartners
}

GET /api/v1/admin/dashboard/pending-bookings?limit=10
Response: [{
  id, user_name, room_number, property_name,
  start_date, end_date, nights, total_amount,
  created_at, has_conflict
}]
```

**Implementation:** Mở rộng `DashboardService` — method `getStatsForAdmin()` mirror `getStatsForPartner()` nhưng **không filter partner_id** (scope toàn hệ thống). Tái sử dụng `BookingRepository::countBookingsForPartner` → thêm `countBookingsForAdmin(array $filters)`.

### 8.2 Backend — Admin Booking API

- Đảm bảo `stay_status` có trong select columns admin booking list/detail.
- Thêm query param `stay_status` vào admin booking search (repository đã hỗ trợ ở partner — mirror cho admin controller).

### 8.3 Frontend — File impact

| File | Phase | Thay đổi |
|------|-------|----------|
| `src/pages/Admin/Dashboard/index.tsx` | 1, 3 | Refactor layout 2 tầng |
| `src/pages/Admin/Dashboard/components/OperationsKpiGrid.tsx` | 1 | Mới |
| `src/pages/Admin/Dashboard/components/AdminWorkQueue.tsx` | 3 | Mới |
| `src/api/dashboardApi.ts` | 2, 3 | Thêm stats, pending-bookings |
| `src/hooks/useDashboardQuery.ts` | 2, 3 | Hooks mới |
| `src/utils/bookingDisplay.ts` | 1 | Tách shared từ `partnerBookingDisplay.ts` |
| `src/pages/Admin/BookingManage/components/BookingTable.tsx` | 1, 2 | Badge + filter |
| `src/pages/Admin/BookingManage/components/BookingSearchSection.tsx` | 2, 3 | stay_status filter + presets |
| `src/components/layout/index.tsx` | 1, 3 | Sidebar IA + badge |
| `src/components/ClassSidebar/index.tsx` | 1 | Collapsible groups |
| `app/Services/DashboardService.php` | 2 | `getStatsForAdmin`, `getPendingBookingsForAdmin` |
| `app/Http/Controllers/DashboardController.php` | 2 | 2 action mới |
| `routes/api.php` | 2 | 2 route mới |

### 8.4 Không migration DB

Chỉ query trên `bookings`, `rooms`, `properties`, `users` hiện có.

---

## 9. MoSCoW Prioritization

| Must (Phase 1–2) | Should (Phase 3) | Could (Backlog) | Won't |
|------------------|------------------|-----------------|-------|
| KPI vận hành dashboard | Work queue enriched | Badge pending sidebar | Admin check-in/out action |
| Combined status badge booking | Preset filter ca trực | Occupancy chart admin | Mobile admin app |
| API stats admin | Sidebar collapsible CMS | | Redesign CMS screens |
| stay_status trong API + filter | | | |

---

## 10. Phase Overview & Timeline

| Phase | Tên | Thời gian | Deliverable | Release slice |
|-------|-----|-----------|-------------|---------------|
| **0** | UI Gate | 0.5 ngày | Wireframe/mockup admin dashboard To-Be | — |
| **1** | FE Quick Wins | 2–3 ngày | Layout 2 tầng, shared bookingDisplay, badge stay_status (mock/placeholder nếu API chưa có) | Slice A |
| **2** | BE API Extensions | 2–3 ngày | stats + pending-bookings + stay_status filter admin | Slice B |
| **3** | FE Full Integration | 2–3 ngày | Wire API, work queue, sidebar IA, preset filters | Slice C |
| **4** | QA & UAT | 1–2 ngày | Test scenarios ca trực | Release final |

**Tổng ước lượng:** 8–12 dev-days.

---

## 11. Dependency Graph

```text
Phase 0: [T0.1 UI wireframe approved]
              │
    ┌─────────┴─────────┐
    ▼                   ▼
Phase 1 (FE)        Phase 2 (BE)
[T1.1]─[T1.6]       [T2.1] stats API
                         │
                    [T2.2] pending-bookings API
                         │
                    [T2.3] stay_status admin booking
                         │
                         ▼
                    Phase 3 (FE)
                    [T3.1]─[T3.7]
                         │
                         ▼
                    Phase 4 (QA)
                    [T4.1]─[T4.4]
```

**Song song:** Phase 1 và Phase 2 có thể chạy song song từ ngày 1 (FE dùng mock data cho KPI đến khi BE xong).

---

## 12. Task Breakdown Chi Tiết

### Phase 0: UI Gate (0.5 ngày)

#### [T0.1] Wireframe Admin Dashboard To-Be
- **Mô tả:** Sketch layout 2 tầng: Operations KPI → Action cards → Work queue → Analytics charts.
- **Acceptance:**
  - [ ] Wireframe được stakeholder confirm (có thể dùng stack-ui-design-gate nếu cần canvas).
  - [ ] Ghi rõ vị trí 4 KPI, work queue, vị trí charts hiện có.
- **Est:** 2h

---

### Phase 1: FE Quick Wins — Slice A (2–3 ngày)

#### [T1.1] Tách shared `bookingDisplay.ts`
- **Mô tả:** Extract `getBookingRowDisplayStatus()`, badge colors từ `partnerBookingDisplay.ts` → `src/utils/bookingDisplay.ts`. Partner import lại từ shared.
- **Files:** `src/utils/bookingDisplay.ts` (mới), `src/utils/partnerBookingDisplay.ts`, `src/pages/Partner/Bookings.tsx`
- **Acceptance:**
  - [ ] Partner booking không regression (cùng nhãn như cũ).
  - [ ] Export đủ cho Admin dùng.
- **Est:** 2h
- **Blocks:** T1.2

#### [T1.2] Combined status badge trên Admin BookingTable + BookingCard
- **Mô tả:** Thay `getStatusBadge(status)` bằng `getBookingRowDisplayStatus(status, stay_status)`.
- **Files:** `BookingTable.tsx`, `BookingCard.tsx`, `booking.dataHelper.ts` (thêm field `stay_status`)
- **Acceptance:**
  - [ ] 5 trạng thái hiển thị đúng: Chờ duyệt, Đã xác nhận, Đang lưu trú, Đã trả phòng, Không đến, Đã hủy.
  - [ ] Nếu API chưa trả stay_status → fallback "Đã xác nhận".
- **Est:** 3h
- **Dependencies:** T1.1

#### [T1.3] Dashboard layout 2 tầng (structure only)
- **Mô tả:** Tách `Dashboard/index.tsx` thành `<OperationsSection />` (trên) và `<AnalyticsSection />` (dưới). Giữ charts hiện có, di chuyển xuống tầng 2.
- **Acceptance:**
  - [ ] Action cards + KPI cũ vẫn hoạt động.
  - [ ] Charts không bị mất.
- **Est:** 3h

#### [T1.4] Component `OperationsKpiGrid` (placeholder/mock)
- **Mô tả:** 4 ô: Check-in hôm nay, Check-out hôm nay, Đang lưu trú, Lấp đầy (%). Dùng mock `{0,0,0,0}` hoặc derive từ `useSystemRoom` tạm thời.
- **Acceptance:**
  - [ ] Responsive 4→2→1.
  - [ ] Click drill-down (route chuẩn bị sẵn, có thể chưa có data).
- **Est:** 3h
- **Dependencies:** T1.3

#### [T1.5] Sidebar — nhóm collapsible
- **Mô tả:** `MenuItem` thêm `group`, `defaultCollapsed`. Nhóm "Danh mục & Nội dung" collapsed mặc định.
- **Files:** `layout/index.tsx`, `ClassSidebar`, `shared/types` (MenuItem)
- **Acceptance:**
  - [ ] 3 nhóm: Vận hành (open), Danh mục (collapsed), Hệ thống (open).
  - [ ] Toggle group không mất active state.
- **Est:** 4h

#### [T1.6] i18n keys mới
- **Mô tả:** Thêm keys: `dashboard.today_check_in`, `dashboard.today_check_out`, `dashboard.in_stay`, `dashboard.occupancy_rate`, `bookings.stay_status_*`, `menu.group_operations`, `menu.group_content`.
- **Est:** 1h

---

### Phase 2: BE API Extensions — Slice B (2–3 ngày)

#### [T2.1] `DashboardService::getStatsForAdmin()`
- **Mô tả:** Mirror `getStatsForPartner` nhưng scope toàn hệ thống (không `partner_id`). Đếm todayCheckIn (status=1, start_date=today), todayCheckOut (status=1, end_date=today), inStay (stay_status=checked_in), occupancyRate.
- **Files:** `DashboardService.php`, `BookingRepository.php`, `RoomsRepository.php`
- **Acceptance:**
  - [ ] Unit test: 3 case (0 booking, có check-in hôm nay, occupancy 50%).
  - [ ] Response format khớp FR-001.
- **Est:** 4h

#### [T2.2] `DashboardService::getPendingBookingsForAdmin()`
- **Mô tả:** Top N booking status=0, enriched: property_name, nights, total_amount, has_conflict (dùng ConflictChecker có sẵn).
- **Files:** `DashboardService.php`, `BookingRepository.php`
- **Acceptance:**
  - [ ] Trả đúng thứ tự `created_at ASC` (cũ nhất trước — ưu tiên SLA).
  - [ ] `has_conflict` đúng với logic partner.
- **Est:** 4h

#### [T2.3] Admin Booking API — expose `stay_status` + filter
- **Mô tả:** Admin booking list/detail select thêm `stay_status`. Search nhận query `stay_status`.
- **Files:** Admin BookingController, BookingRepository (admin search method), Form Request validation
- **Acceptance:**
  - [ ] `GET /admin/bookings?stay_status=checked_in` trả đúng tập.
  - [ ] Response JSON có field `stay_status`.
- **Est:** 3h

#### [T2.4] Routes + Controller actions
- **Mô tả:** Thêm route `GET /admin/dashboard/stats`, `GET /admin/dashboard/pending-bookings`. Controller delegate service.
- **Files:** `routes/api.php`, `DashboardController.php`, `DashboardValidation.php`
- **Est:** 2h
- **Dependencies:** T2.1, T2.2

#### [T2.5] Cache layer (optional, Should)
- **Mô tả:** Cache stats 2 phút key `admin:dashboard:stats`.
- **Est:** 1h

#### [T2.6] Feature tests
- **Mô tả:** `AdminDashboardStatsTest`, `AdminBookingStayStatusFilterTest`
- **Est:** 3h

---

### Phase 3: FE Full Integration — Slice C (2–3 ngày)

#### [T3.1] Wire `OperationsKpiGrid` với API stats
- **Mô tả:** `useAdminDashboardStatsQuery()` → `dashboardApi.getStats()`. Refetch interval 5 phút.
- **Acceptance:**
  - [ ] Số liệu khớp BE.
  - [ ] Click KPI → drill-down đúng query params.
- **Est:** 3h
- **Dependencies:** T2.4, T1.4

#### [T3.2] Component `AdminWorkQueue`
- **Mô tả:** Panel danh sách pending enriched. Click → BookingDetailDialog.
- **Acceptance:**
  - [ ] Hiển thị tối đa 10 item.
  - [ ] `has_conflict=true` → icon cảnh báo cam.
  - [ ] Empty state: "Không có booking chờ duyệt".
- **Est:** 4h
- **Dependencies:** T2.2

#### [T3.3] BookingSearchSection — filter stay_status
- **Mô tả:** Dropdown + sync URL `stay_status`.
- **Est:** 3h
- **Dependencies:** T2.3, T1.2

#### [T3.4] Preset filter "Nhận phòng hôm nay" / "Trả phòng hôm nay"
- **Mô tả:** 2 chip trong BookingSearchSection hoặc PageBar.
- **Est:** 2h

#### [T3.5] Sidebar badge pending bookings
- **Mô tả:** Gọi `useAdminDashboardStatsQuery` hoặc lightweight count endpoint; badge trên menu Bookings.
- **Est:** 2h

#### [T3.6] Loading/error states
- **Mô tả:** Skeleton cho KPI grid, work queue; toast khi API fail.
- **Est:** 2h

#### [T3.7] Xóa mock/placeholder Phase 1
- **Mô tả:** Cleanup mock data, đảm bảo không còn hardcode.
- **Est:** 1h

---

### Phase 4: QA & UAT (1–2 ngày)

#### [T4.1] Test cases vận hành (UAT scenarios)

| # | Scenario | Expected |
|---|----------|----------|
| UAT-01 | Admin mở dashboard lúc 8:00, có 3 check-in hôm nay | KPI hiển thị 3, click → list đúng 3 booking |
| UAT-02 | Booking confirmed + checked_in | Badge "Đang lưu trú" |
| UAT-03 | Booking confirmed + no_show | Badge "Không đến" |
| UAT-04 | 5 booking pending, 2 có conflict | Work queue hiển thị 5, 2 có icon conflict |
| UAT-05 | Sidebar: nhóm CMS collapsed | Chỉ thấy 7 item vận hành + hệ thống |
| UAT-06 | Drill-down từ dashboard → booking → user | Context chips + back OK (plan_005) |

#### [T4.2] Regression Partner portal
- **Mô tả:** Đảm bảo tách `bookingDisplay.ts` không break Partner Bookings/Dashboard.

#### [T4.3] Performance check
- **Mô tả:** stats API p95 ≤ 500ms với dataset seeder hiện có.

#### [T4.4] Cập nhật docs
- **Files:** `docs/databases_docs/` (nếu cần ghi API contract), domain review sign-off.

---

## 13. Risks & Mitigations

| Risk | Mức | Mitigation |
|------|-----|------------|
| Scope creep sang CMS redesign | Medium | Chỉ IA sidebar, không đổi màn CMS |
| Performance stats toàn hệ thống | Medium | Cache 2 phút; index `bookings.start_date`, `stay_status` |
| Partner regression sau tách utility | Low | Unit test + UAT-04 regression |
| Admin kỳ vọng check-in/out action | Medium | Ghi rõ Non-Goals; chỉ view + drill-down |

---

## 14. Assumptions

1. Chỉ role `admin` dùng các endpoint mới (không mở cho manager khác trong phase này).
2. `stay_status` enum hiện tại đủ: `pending`, `checked_in`, `checked_out`, `no_show`.
3. plan_005 (cross-module navigation) **đã hoạt động** — không re-implement.
4. Partner Dashboard plan_012 chạy độc lập — không block plan này.

---

## 15. Open Questions (đã quyết định — không hỏi user)

| # | Câu hỏi | Quyết định |
|---|---------|------------|
| Q1 | Admin có được check-in/out thay partner? | **Không** — Won't-have; Admin chỉ giám sát |
| Q2 | Occupancy tính toàn hệ thống hay theo property? | **Toàn hệ thống** phase 1; filter property → backlog |
| Q3 | Work queue bao nhiêu item? | **10** — đủ cho ca trực, không scroll dài |
| Q4 | Sidebar CMS ẩn hay xóa? | **Thu gọn (collapsed)** — không xóa route |

---

## 16. Collaboration Action Items

### Cho UAT Tester
- Chạy 6 scenarios UAT-01 → UAT-06 (mục 12, Phase 4).
- Edge case: booking check-in 2:00 AM, no-show sau 24h, pending + conflict cùng phòng.
- Data setup: seeder ≥ 20 booking với mix stay_status.

### Cho Senior Engineer
- Bắt đầu Phase 1 (FE) và Phase 2 (BE) song song.
- Ưu tiên T1.1 → T1.2 (badge) và T2.1 (stats API) trước.
- Tham chiếu plan_012 Partner Dashboard cho pattern reuse.

### Cho UI Designer (nếu có)
- Wireframe T0.1 trước Phase 3 layout final.

---

## 17. Definition of Done (toàn plan)

- [ ] Admin Dashboard hiển thị 4 KPI vận hành từ API thật.
- [ ] Work queue pending enriched hoạt động.
- [ ] Booking list Admin hiển thị + lọc stay_status.
- [ ] Sidebar nhóm CMS collapsed mặc định.
- [ ] Không regression Partner portal.
- [ ] Feature tests BE pass.
- [ ] UAT scenarios pass.
- [ ] Domain Review cập nhật: **APPROVED (Operational Ready)**.

---

**Sign-off:** Senior Business Analyst + Senior Hospitality & Accommodation Domain Expert  
**Date:** 2026-06-08
