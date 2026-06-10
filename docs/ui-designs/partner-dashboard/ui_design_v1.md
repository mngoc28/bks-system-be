# UI Design v1: Partner Dashboard (Cải tiến vận hành)

## Document Information
- **SRS reference:** `docs/SRC/srs_partner_portal_360.md` — §Dashboard (PP360-DASH-001 → 006)
- **Design system:** Tailwind + Shadcn UI (giữ shell Partner Portal hiện tại)
- **Status:** Draft — PENDING_UI_APPROVAL
- **Version:** v1
- **Date:** 2026-06-08
- **Based on:** Đánh giá UI/UX Designer + Hospitality Expert (2026-06-08)

## Scope
- **In scope:** Màn `/partner/dashboard` — tái cấu trúc hierarchy, KPI vận hành, Alert Center, work queue, biểu đồ
- **Out of scope:** Sidebar/Header chrome, Bookings detail, Calendar, Finance module, RBAC nhân viên

## User journeys (summary)
| Journey | Actor | Entry | Success outcome |
|---------|-------|-------|-----------------|
| Buổi sáng vận hành | Partner owner | Login → Dashboard | Biết ngay check-in/out hôm nay và việc cần xử lý |
| Duyệt booking nhanh | Partner owner | Dashboard work queue | Confirm/reject trong ≤5 phút SLA |
| Theo dõi hiệu quả | Partner owner | Dashboard analytics | Hiểu occupancy & doanh thu 30 ngày theo tài sản |

## As-Is vs To-Be
| Region / screen | As-Is | To-Be delta | SRS trace |
|-----------------|-------|-------------|-----------|
| Header | Tiêu đề + Làm mới (reload trang) | Thêm filter tài sản; Làm mới refetch | PP360-DASH-003, -006 |
| KPI hàng 1 | 7 thẻ ngang (cơ sở, phòng, trống, occupancy, doanh thu, TTC, net) | 4 thẻ **Vận hành hôm nay** (check-in, check-out, in-stay, occupancy) | PP360-DASH-001, -003 |
| KPI hàng 2 | (không có) | 3 thẻ **Tài chính tháng** (GMV MTD, Net MTD, Thời gian xác nhận TB) | PP360-DASH-002, -004 |
| Alert Center | 3 ô: Pending, Overbooking (hardcode 0), Hợp đồng | 4 ô: Pending, Trùng phòng, Yêu cầu hủy, Hợp đồng sắp hết hạn | PP360-DASH-005 |
| Biểu đồ | 3 chart (occupancy, GMV, phân tích tháng) | 2 chart 30 ngày; gỡ chart tháng trùng | PP360-DASH-006 |
| Work queue | Tên, phòng, ngày nhận; Pending EN | Đầy đủ: property, khoảng ngày, giá, SLA timer, conflict badge | PP360-DASH-001, PP360-BOOK-004 |
| Bảo trì | Card full-width cuối trang | Thu gọn 1 dòng + link; chỉ expand khi có sự cố | Out of SRS phase — giảm noise |

---

## Screen: Partner Dashboard (`/partner/dashboard`)

### Purpose & primary actions
- **Mục đích:** Bảng điều khiển vận hành hàng ngày — *operations first, analytics second*.
- **Hành động chính:** Duyệt/Từ chối booking pending; điều hướng nhanh tới Calendar/Bookings/Hợp đồng.

### Layout (regions top → bottom)

```
┌──────────────────────────────────────────────────────────────────────────┐
│ [A] PAGE HEADER                                                          │
│   Dashboard · Chào mừng trở lại                                          │
│   [Filter: Tất cả tài sản ▼]  [Cập nhật 21:11]  [Làm mới ↻]              │
├──────────────────────────────────────────────────────────────────────────┤
│ [B] ALERT BANNER (tổng hợp, không trùng work queue)                      │
│   ⚠ 4 booking chờ · 0 trùng phòng · 1 yêu cầu hủy · 0 HĐ sắp hết hạn    │
├──────────────────────────────────────────────────────────────────────────┤
│ [C] KPI VẬN HÀNH HÔM NAY (4 cột, card lớn)                               │
│   Check-in hôm nay │ Check-out hôm nay │ Đang lưu trú │ Lấp đầy hôm nay   │
├──────────────────────────────────────────────────────────────────────────┤
│ [D] KPI TÀI CHÍNH THÁNG (3 cột)                                          │
│   GMV tháng 6 │ Doanh thu thực nhận │ Thời gian xác nhận TB               │
├───────────────────────────────┬──────────────────────────────────────────┤
│ [E] YÊU CẦU MỚI (work queue)  │ [F] BIỂU ĐỒ (stack dọc)                  │
│   Card booking + SLA + actions│   Occupancy 30 ngày                      │
│   (chiếm 5/12 cột desktop)    │   GMV & Net Revenue 30 ngày              │
├───────────────────────────────┴──────────────────────────────────────────┤
│ [G] TÀI SẢN TỔNG QUAN (collapsible, mặc định thu gọn)                    │
│   17 cơ sở · 82 phòng · 71 trống · Doanh thu dự kiến tháng               │
├──────────────────────────────────────────────────────────────────────────┤
│ [H] BẢO TRÌ (1 dòng, chỉ khi có sự cố mới expand)                        │
└──────────────────────────────────────────────────────────────────────────┘
```

### Components & data displayed

#### [A] Page Header
| Thành phần | Nguồn dữ liệu | Ghi chú |
|------------|---------------|---------|
| Filter tài sản | `GET /partner/properties` (list) | Giá trị mặc định: "Tất cả tài sản"; persist `localStorage` |
| Timestamp | Client `new Date()` hoặc `calculatedAt` từ KPI API | |
| Nút Làm mới | React Query `refetchQueries` | Không `window.location.reload()` |

#### [B] Alert Banner
| Alert | API / logic | CTA |
|-------|-------------|-----|
| Booking chờ duyệt | `headlineKpis.pendingCount` | `/partner/bookings?status=pending` |
| Trùng phòng | `GET /partner/calendar/conflicts` (mới) hoặc count từ calendar service | `/partner/calendar` |
| Yêu cầu hủy | `stats.pendingCancellationCount` | `/partner/cancellation-requests` |
| Hợp đồng sắp hết hạn | `GET /partner/contracts/expiring-soon` | `/partner/contracts` |

**Quy tắc UX:** Banner chỉ hiển thị **tổng số + CTA**; không lặp lại card booking chi tiết.

#### [C] KPI Vận hành hôm nay
| Thẻ | Field API | Sub-label |
|-----|-----------|-----------|
| Check-in hôm nay | `stats.todayCheckInCount` | "Hôm nay · dd/MM" |
| Check-out hôm nay | `stats.todayCheckOutCount` | "Hôm nay · dd/MM" |
| Đang lưu trú | `stats.inStayCount` | "Phòng có khách" |
| Lấp đầy hôm nay | `stats.occupancyRate` | "Phòng có khách / Tổng phòng" |

#### [D] KPI Tài chính tháng
| Thẻ | Field API | Tooltip |
|-----|-----------|---------|
| GMV tháng | `headlineKpis.gmvMtd` | Tổng giá trị booking confirmed/completed trong tháng |
| Doanh thu thực nhận | `headlineKpis.netRevenueMtd` | GMV × (1 − 5% hoa hồng) |
| Thời gian xác nhận TB | `headlineKpis.avgConfirmSeconds` | Từ tạo đơn → xác nhận (30 ngày gần nhất) |

**SLA màu (Thời gian xác nhận):**
- Xanh: ≤ 5 phút
- Vàng: 5–15 phút
- Đỏ: > 15 phút
- Xám + copy: "Chưa đủ dữ liệu" khi `null`

#### [E] Work queue — Yêu cầu mới
Mỗi item hiển thị:

| Field | Hiển thị | Bắt buộc |
|-------|----------|----------|
| Mã booking | `#BK-12345` | Có |
| Tên khách | `user_name` | Có |
| Cơ sở | `property_name` | Có (khi ≥2 property) |
| Phòng | `room_number` | Có |
| Khoảng ngày | `start_date` → `end_date` (số đêm) | Có |
| Giá trị | `total_amount` VND | Có |
| Thời gian chờ | Tính từ `created_at` | Có — badge đỏ nếu > 5 phút |
| Conflict | Badge "Còn trống" / "Trùng lịch" | Có (pre-check) |
| Hợp đồng dài hạn | Badge "Sẽ tạo HĐ thuê" nếu ≥30 đêm | Khi áp dụng |

**Actions:** Từ chối (mở dialog lý do) · Duyệt (quick confirm + hoàn tác 15s) — giữ flow hiện tại.

**Sắp xếp:** Ưu tiên SLA (chờ lâu nhất trước), sau đó `start_date` gần nhất.

#### [F] Biểu đồ
- Giữ `OccupancyChart` + `GmvChart` (30 ngày).
- Khi filter property: truyền `property_id` query param xuống API chart.
- **Gỡ** card "Phân tích doanh thu" theo tháng khỏi dashboard → chuyển sang `/partner/reports`.

#### [G] Tài sản tổng quan (collapsible)
Thu gọn 4 metric cũ từ hàng 7 thẻ:
- Cơ sở lưu trú, Tổng phòng, Phòng trống, Doanh thu dự kiến tháng
- Mặc định **đóng** để giảm cognitive load; partner mở khi cần portfolio snapshot.

#### [H] Bảo trì
- Không có sự cố: 1 dòng xanh "Không có sự cố khẩn cấp" + link "Quản lý bảo trì".
- Có sự cố: expand grid 3 card như hiện tại.

### Interactions & validations
1. Đổi filter property → invalidate & refetch tất cả dashboard queries.
2. Duyệt booking → optimistic UI 15s → API confirm; 409 conflict → toast + revert + badge đỏ "Trùng lịch".
3. Click alert banner item → deep link đúng màn hình + filter tương ứng.

### States
| State | Hành vi |
|-------|---------|
| Loading | Skeleton cho từng region độc lập (không block cả trang) |
| Empty work queue | Illustration + "Chưa có yêu cầu mới" + link Calendar |
| Error KPI | Card đỏ nhỏ "Không tải được dữ liệu" + nút Thử lại |
| No data TTC | Text "Chưa đủ dữ liệu" thay N/A |
| Permission denied | Redirect onboarding (giữ logic `PartnerLayout`) |

### Role visibility
- Chỉ `role=partner` + `status=1` (active).
- Single owner — không phân quyền property.

### Responsive notes
| Breakpoint | Layout |
|------------|--------|
| Desktop (≥1024px) | Work queue 5/12 + Charts 7/12; KPI 4 cột |
| Tablet (768–1023px) | KPI 2×2; work queue full width trên charts |
| Mobile (<768px) | Stack 1 cột; alert banner scroll ngang; nút action min-height 44px |

### Copy (VI) — labels, errors, CTAs
| Key (EN cũ) | Copy mới (VI) |
|-------------|---------------|
| Dashboard | Bảng điều khiển |
| Pending booking | Booking chờ duyệt |
| Overbooking | Trùng phòng |
| Net Revenue | Doanh thu thực nhận |
| Time-to-confirm TB | Thời gian xác nhận TB |
| Occupancy 30 ngày | Tỷ lệ lấp phòng 30 ngày |
| GMV & Net Revenue 30 ngày | Doanh thu gross & thực nhận 30 ngày |
| Full: X ₫ | (ẩn — chỉ tooltip hover trên số compact) |
| Làm mới | Làm mới |
| Duyệt / Từ chối | Giữ nguyên |

**Toast lỗi conflict:** "Không thể duyệt: phòng đã có booking trong khoảng ngày này."

---

## Cross-screen dependencies
- Filter property trên Dashboard **đồng bộ** với Calendar (`?property_id=`) và Bookings khi navigate từ CTA.
- Quick confirm dùng chung hook `useQuickConfirm` với `/partner/bookings`.
- Realtime invalidate: `partner/dashboard/kpis`, `partner-stats`, `partner-pending-bookings`.

---

## UI acceptance criteria
- [ ] Given partner có ≥2 property, when chọn 1 property trên filter, then KPI vận hành và biểu đồ chỉ reflect property đó.
- [ ] Given có booking pending > 5 phút, when mở dashboard, then work queue item hiển thị badge SLA màu đỏ.
- [ ] Given `todayCheckInCount > 0`, when load dashboard, then thẻ "Check-in hôm nay" hiển thị đúng số (không cần vào Bookings).
- [ ] Given overbooking conflict tồn tại, when load dashboard, then Alert "Trùng phòng" > 0 và CTA mở Calendar.
- [ ] Given `pendingCancellationCount > 0`, when load dashboard, then Alert "Yêu cầu hủy" hiển thị và link đúng route.
- [ ] Given bấm "Làm mới", when refetch xong, then trang không reload, scroll position giữ nguyên.
- [ ] Given work queue trống, when xem dashboard, then không có duplicate pending count ở 2 vùng chi tiết (chỉ banner tổng hoặc empty state).

---

## Open questions (owner)
- [ ] API conflict count cho Alert Center — tái sử dụng calendar endpoint hay endpoint mới? — **Owner:** Architect
- [ ] Property filter trên chart API — thêm query param hay endpoint riêng? — **Owner:** Architect
- [ ] Có giữ card "Phân tích doanh thu" dạng tab trong Reports không? — **Owner:** BA

---

## Preview
- Canvas: `canvases/partner-dashboard-ui-v1.canvas.tsx`
- Trạng thái: **PENDING_UI_APPROVAL** — reply `UI_APPROVED` khi chốt để handoff engineer.
