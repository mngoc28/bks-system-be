# UI Design v1: Admin Portal — Operations-first UX

## Document Information

| Mục | Nội dung |
|-----|----------|
| **Plan reference** | [plan_013_admin_operations_ux_redesign.md](../../plans/plan_013_admin_operations_ux_redesign.md) |
| **Design system** | Tailwind + Shadcn UI + `adminTheme` (navy sidebar, white surfaces) |
| **Status** | Draft — PENDING_UI_APPROVAL |
| **Version** | v1 |
| **Date** | 2026-06-08 |
| **Based on** | Hospitality Expert domain review + BA plan PLAN-ADM-UX-013 |

## Scope

| In scope | Out of scope |
|----------|--------------|
| `/admin/dashboard` — layout 2 tầng, KPI ca trực, work queue | Redesign màn CMS (News, Chatbot) |
| `/admin/bookings` — combined badge, filter stay_status, presets | Admin thực hiện check-in/out |
| Sidebar IA — nhóm collapsible, badge pending | Mobile app admin riêng |
| Header chrome (giữ nguyên) | Thay đổi business rules deposit |

## User journeys

| Journey | Actor | Entry | Success outcome |
|---------|-------|-------|-----------------|
| Ca sáng vận hành | System Admin | Login → Dashboard | Biết check-in/out hôm nay + booking chờ trong ≤10s |
| Duyệt booking nhanh | Admin CS | Dashboard work queue | Mở detail booking với đủ ngữ cảnh, không cần filter thủ công |
| Điều phối no-show | Admin | Bookings → filter "Không đến" | Thấy danh sách no-show toàn hệ thống |
| Quản lý danh mục | Admin | Sidebar thu gọn CMS | Tìm module vận hành trong ≤2 click |

## As-Is vs To-Be

| Region / screen | As-Is | To-Be delta |
|-----------------|-------|-------------|
| Dashboard header | Hero gradient + date range filter | Giữ date filter; thêm timestamp + nút Làm mới (refetch) |
| KPI hàng 1 | 6 action cards (partner/user/booking) | **4 KPI vận hành** (check-in, check-out, in-stay, occupancy) — nổi bật nhất |
| KPI hàng 2 | (không có) | 6 action cards **thu nhỏ** xuống hàng 2 (giữ drill-down) |
| Work queue | 3 dòng text count (partner/user/booking pending) | **Panel booking enriched** (10 item, SLA, conflict) |
| Charts | Cùng tầng với action cards | **Tầng 2** — analytics sau operations |
| Booking badge | 4 status: pending/confirmed/cancelled/completed | **Combined badge** (status + stay_status) |
| Booking filter | status, date, room, price | Thêm **stay_status** + preset "Nhận/Trả phòng hôm nay" |
| Sidebar | 6 header, 15 item phẳng | 3 nhóm collapsible; CMS **collapsed mặc định**; badge `3` trên Bookings |

---

## Screen 1: Admin Dashboard (`/admin/dashboard`)

### Purpose & primary actions

- **Mục đích:** Bảng điều khiển ca trực toàn hệ thống — *operations first, analytics second*.
- **Hành động chính:** Xem KPI hôm nay → drill-down booking; xử lý work queue pending; theo dõi GMV/booking trend (tầng 2).

### Layout (regions top → bottom)

```
┌──────────────────────────────────────────────────────────────────────────────┐
│ [A] PAGE HEADER                                                              │
│   Tổng quan vận hành · Dashboard điều hành                                   │
│   [Từ ngày] [Đến ngày] [Đặt lại]              Cập nhật 08:42  [Làm mới ↻]   │
├──────────────────────────────────────────────────────────────────────────────┤
│ [B] KPI VẬN HÀNH HÔM NAY (4 cột — accent, clickable)                         │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐            │
│  │ Check-in    │ │ Check-out   │ │ Đang lưu trú│ │ Lấp đầy     │            │
│  │    12       │ │     8       │ │    156      │ │   68.4%     │            │
│  │ Hôm nay     │ │ Hôm nay     │ │ Phòng có KH │ │ Toàn hệ thống│           │
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘            │
├──────────────────────────────────────────────────────────────────────────────┤
│ [C] ALERT TỔNG HỢP (1 dòng banner)                                           │
│   3 booking chờ · 2 partner chờ · 1 user chờ · 0 yêu cầu hủy              │
├───────────────────────────────┬──────────────────────────────────────────────┤
│ [D] BOOKING CHỜ DUYỆT         │ [E] VIỆC CẦN XỬ LÝ (action cards 2×3)      │
│   (work queue — 5/12 cột)     │   Partner chờ │ Partner khóa │ User chờ     │
│                               │   User khóa   │ Booking chờ  │ Phòng trống  │
│   #BK-1042  Nguyễn A          │   (compact cards, giữ màu hiện tại)          │
│   Villa Sơn Trà · P.201       │                                              │
│   08/06 → 10/06 · 2 đêm       │                                              │
│   2.400.000 ₫ · chờ 12 phút   │                                              │
│   [!] Trùng lịch              │                                              │
│   ─────────────────────────   │                                              │
│   #BK-1038  Trần B  ...       │                                              │
│   [Xem tất cả booking chờ →]  │                                              │
├───────────────────────────────┴──────────────────────────────────────────────┤
│ [F] PHÂN TÍCH (Tầng 2 — collapsible section "Phân tích & Báo cáo")           │
│  ┌────────────────────────────┐  ┌──────────────────┐  ┌──────────────────┐  │
│  │ Booking theo tháng (chart)│  │ GMV theo ngày    │  │ Health donut     │  │
│  └────────────────────────────┘  └──────────────────┘  └──────────────────┘  │
│  ┌────────────────────────────────────────────────────────────────────────┐  │
│  │ Top property theo booking (bar chart)                                   │  │
│  └────────────────────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────────────────────┘
```

### Components & data

#### [B] KPI Vận hành hôm nay

| Thẻ | API field | Sub-label | Click → |
|-----|-----------|-----------|---------|
| Check-in hôm nay | `stats.todayCheckInCount` | Hôm nay · dd/MM | `/admin/bookings?status=1&stay_status=pending&start_date={today}` |
| Check-out hôm nay | `stats.todayCheckOutCount` | Hôm nay · dd/MM | `/admin/bookings?status=1&stay_status=checked_in&end_date={today}` |
| Đang lưu trú | `stats.inStayCount` | Phòng có khách | `/admin/bookings?stay_status=checked_in` |
| Lấp đầy hôm nay | `stats.occupancyRate` | % toàn hệ thống | `/admin/rooms` (future: filter occupied) |

**Visual:** Card `admin-card`, số `text-3xl font-bold`, icon góc phải. Hover: `hover:-translate-y-0.5`. Accent border-top 3px theo metric.

#### [D] Work queue — Booking chờ duyệt

| Field | Hiển thị | Bắt buộc |
|-------|----------|----------|
| Mã | `#BK-{id}` | Có |
| Khách | `user_name` | Có |
| Cơ sở | `property_name` | Có |
| Phòng | `room_number` | Có |
| Khoảng ngày | `start_date → end_date` + `{nights} đêm` | Có |
| Giá | `total_amount` VND | Có |
| Thời gian chờ | Từ `created_at` | Có — đỏ nếu > 15 phút |
| Conflict | Pill "Trùng lịch" cam | Khi `has_conflict=true` |

**Actions:** Click row → `BookingDetailDialog`. Footer link → `/admin/bookings?status=0`.

**Sắp xếp:** `created_at ASC` (chờ lâu nhất trước).

#### [E] Action cards (thu nhỏ)

Giữ 6 card hiện tại nhưng:
- Grid `sm:grid-cols-2 xl:grid-cols-3` (nhỏ hơn KPI)
- Font số `text-2xl` (thay vì 3xl)
- Vị trí: bên phải work queue trên desktop; stack dưới work queue trên tablet

#### [F] Analytics tầng 2

- Wrap trong `<CollapsibleSection defaultOpen={false}>` — label "Phân tích & Báo cáo"
- Giữ nguyên charts hiện có (AreaChart booking, ComposedChart GMV, Pie health, Bar property)
- Date range filter ở header **chỉ áp dụng** cho charts tầng 2

### States

| State | Hành vi |
|-------|---------|
| Loading KPI | Skeleton 4 ô độc lập |
| Loading work queue | Skeleton 3 dòng |
| Empty work queue | "Không có booking chờ duyệt" + icon Calendar |
| Error stats | Callout đỏ nhỏ + "Thử lại" |
| Non-admin | Giữ block permission hiện tại |

### Responsive

| Breakpoint | Layout |
|------------|--------|
| Desktop ≥1280px | KPI 4 cột; Work queue 5/12 + Action cards 7/12 |
| Tablet 768–1279px | KPI 2×2; Work queue full width; Action cards 2×3 |
| Mobile <768px | Stack 1 cột; KPI 1 cột; work queue card full width |

---

## Screen 2: Admin Booking Manage (`/admin/bookings`)

### Layout

```
┌──────────────────────────────────────────────────────────────────────────────┐
│ Header: Quản lý Booking                                                      │
├──────────────────────────────────────────────────────────────────────────────┤
│ PageBar: subtitle + [Grid/Table toggle] [+ Tạo booking] [Bộ lọc]            │
├──────────────────────────────────────────────────────────────────────────────┤
│ Context chips (nếu có): [Partner: ABC ×] [Property: Villa ×] [Bỏ lọc]       │
├──────────────────────────────────────────────────────────────────────────────┤
│ PRESET CHIPS (mới):                                                          │
│   [Nhận phòng hôm nay]  [Trả phòng hôm nay]  [Đang lưu trú]  [Không đến]   │
├──────────────────────────────────────────────────────────────────────────────┤
│ Filter panel (expandable):                                                   │
│   Tìm kiếm | Phòng | Trạng thái | Trạng thái lưu trú (mới) | Ngày | Giá    │
├──────────────────────────────────────────────────────────────────────────────┤
│ TABLE                                                                        │
│  ID | Khách | Phòng/Tài sản | Thời gian | Giá | Trạng thái (combined) | ⋮  │
│  42 | Ng.A  | P.201/Villa   | 08-10/06  | 2.4M| [Đang lưu trú]        |    │
│  41 | Tr.B  | P.105/Homestay| 07-09/06  | 1.8M| [Không đến]           |    │
└──────────────────────────────────────────────────────────────────────────────┘
```

### Combined status badge

| status | stay_status | Nhãn | Màu |
|--------|-------------|------|-----|
| 0 | * | Chờ duyệt | Vàng |
| 1 | pending | Đã xác nhận | Xanh nhạt |
| 1 | checked_in | Đang lưu trú | Xanh đậm |
| 1 | checked_out | Đã trả phòng | Xanh dương |
| 1 | no_show | Không đến | Đỏ |
| 2 | * | Đã hủy | Đỏ nhạt |
| 3 | * | Hoàn thành | Xám |

**Utility:** `bookingDisplay.ts` — shared với Partner.

### Filter stay_status (dropdown mới)

| Option | Query param |
|--------|-------------|
| Tất cả | (không gửi) |
| Chờ nhận phòng | `stay_status=pending` + `status=1` |
| Đang lưu trú | `stay_status=checked_in` |
| Đã trả phòng | `stay_status=checked_out` |
| Không đến | `stay_status=no_show` |

### Preset chips

| Chip | Sets |
|------|------|
| Nhận phòng hôm nay | `start_date=today`, `status=1`, `stay_status=pending` |
| Trả phòng hôm nay | `end_date=today`, `status=1`, `stay_status=checked_in` |
| Đang lưu trú | `stay_status=checked_in` |
| Không đến | `stay_status=no_show` |

Active chip: `bg-primary/10 ring-1 ring-primary`.

---

## Screen 3: Sidebar IA (`ClassSidebar`)

### Layout To-Be

```
┌─────────────────────┐
│ [BKS] ADMIN         │
├─────────────────────┤
│ ▼ VẬN HÀNH          │  ← luôn mở
│   ● Dashboard       │
│   ○ Đối tác         │
│   ○ Duyệt đối tác   │
│   ○ Tài sản         │
│   ○ Phòng           │
│   ○ Bookings [3]    │  ← badge đỏ pending count
│   ○ Thanh toán      │
├─────────────────────┤
│ ▶ DANH MỤC & NỘI DUNG│  ← collapsed mặc định
│   (ẩn khi đóng)     │
├─────────────────────┤
│ ▼ HỆ THỐNG          │  ← luôn mở
│   ○ Quản lý user    │
└─────────────────────┘
```

**Khi expand "Danh mục & Nội dung":**
- Tiện ích, Dịch vụ, Tỉnh/Thành, Tin tức, Chatbot, Đăng ký coupon

### Interaction rules

1. Toggle group → lưu state `localStorage` key `admin_sidebar_groups`.
2. Badge Bookings: `stats.pendingBookingsCount`; ẩn khi = 0.
3. Collapsed sidebar: chỉ icon + tooltip; badge hiển thị dot đỏ nhỏ trên icon Calendar.

---

## Copy (VI)

| Key | Copy |
|-----|------|
| dashboard.today_check_in | Check-in hôm nay |
| dashboard.today_check_out | Check-out hôm nay |
| dashboard.in_stay | Đang lưu trú |
| dashboard.occupancy_rate | Lấp đầy hôm nay |
| dashboard.work_queue_title | Booking chờ duyệt |
| dashboard.analytics_section | Phân tích & Báo cáo |
| bookings.stay_status_pending | Chờ nhận phòng |
| bookings.stay_status_checked_in | Đang lưu trú |
| bookings.stay_status_checked_out | Đã trả phòng |
| bookings.stay_status_no_show | Không đến |
| bookings.preset_checkin_today | Nhận phòng hôm nay |
| bookings.preset_checkout_today | Trả phòng hôm nay |
| menu.group_operations | Vận hành |
| menu.group_content | Danh mục & Nội dung |
| menu.group_system | Hệ thống |

---

## UI Acceptance Criteria

- [ ] Given admin login, when mở dashboard, then 4 KPI vận hành hiển thị trên cùng action cards.
- [ ] Given `todayCheckInCount=12`, when click KPI Check-in, then navigate đúng query params.
- [ ] Given 3 booking pending, when xem work queue, then hiển thị đủ property/room/nights/amount.
- [ ] Given `has_conflict=true`, when xem work queue item, then pill "Trùng lịch" màu cam.
- [ ] Given booking status=1 + stay_status=checked_in, when xem bảng booking, then badge "Đang lưu trú".
- [ ] Given click preset "Nhận phòng hôm nay", when apply, then URL sync đúng 3 params.
- [ ] Given sidebar load lần đầu, then nhóm "Danh mục & Nội dung" collapsed.
- [ ] Given `pendingBookingsCount=3`, then badge `3` trên menu Bookings.
- [ ] Given bấm "Làm mới", when refetch, then không reload trang.

---

## Approval Gate

Gửi token **`UI_APPROVED`** để handoff engineer triển khai Phase 1–3 theo plan_013.

**Preview:** [admin-operations-ux-wireframe.canvas.tsx](../../../../../../.cursor/projects/d-ASUS-intern-bks-datn-bks-system-fe/canvases/admin-operations-ux-wireframe.canvas.tsx) (mở bên cạnh chat trong Cursor)
