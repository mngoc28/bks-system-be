# UI Preview Manifest: Đặt Cọc Linh Hoạt & Nghiệp Vụ Tài Chính (Booking Deposit & Financial Rules)

## Document Information
- **Current version:** v3.1 (Host — Quản lý Đặt phòng + Stitch To-Be)
- **Status:** PENDING_UI_APPROVAL
- **Updated:** 2026-06-01

---

## Open Preview (Bắt đầu xem tại đây)

| Loại tài liệu | Đường dẫn (Path) | Ghi chú |
| :--- | :--- | :--- |
| **To-Be v3 — Host (Stitch mock)** | [assets/v3/partner-bookings-to-be.png](assets/v3/partner-bookings-to-be.png) | Mock pixel gần Partner Portal — cột Cọc/HĐ, KPI Chờ cọc, Check-in gate |
| **As-Is — Host** | [assets/baseline/partner-bookings-as-is.png](assets/baseline/partner-bookings-as-is.png) | Tab Chờ duyệt (baseline bạn cung cấp) |
| **Canvas v3 — wireframe** | `canvases/booking-deposit-financial-host-bookings-ui-v3.canvas.tsx` | Cấu trúc + bảng logic trong Cursor |
| **Đặc tả chi tiết v3** | [ui_design_v3.md](ui_design_v3.md) | Spec đầy đủ §3 |
| **Đặc tả Guest v2** | [ui_design_v2.md](ui_design_v2.md) | Booking Success + Stay Portal |
| **Nhật ký thay đổi** | [ui_change_log.md](ui_change_log.md) | v1 → v2 → v3 → v3.1 |
| **SRS** | [docs/SRC/srs_booking_deposit_and_financial_rules.md](../../SRC/srs_booking_deposit_and_financial_rules.md) | |
| **Plan** | [docs/plans/plan_010_booking_deposit_and_financial_rules.md](../../plans/plan_010_booking_deposit_and_financial_rules.md) | T3.1, T4.4 |
| **Stitch project** | [Google Stitch — BKS Deposit](https://stitch.withgoogle.com/projects/9871443953956024804) | Screen ID `ba81ac66b19e4d4bb28079b113c3fbfa` |

---

## Screenshots & Assets

| Màn hình | Đường dẫn | Ghi chú |
| :--- | :--- | :--- |
| As-Is: Partner Bookings | [partner-bookings-as-is.png](assets/baseline/partner-bookings-as-is.png) | Tab Chờ duyệt |
| **To-Be v3: Partner Bookings** | **[partner-bookings-to-be.png](assets/v3/partner-bookings-to-be.png)** | Stitch — tab Đã duyệt, delta cọc |
| As-Is: Booking Success | [booking-success-as-is.png](assets/baseline/booking-success-as-is.png) | Nếu có trong repo |
| To-Be v2: Booking Success | [booking-success-to-be.png](assets/v2/booking-success-to-be.png) | Nếu có trong repo |

---

## How to Review in Cursor

1. Mở **[partner-bookings-to-be.png](assets/v3/partner-bookings-to-be.png)** và đối chiếu **[partner-bookings-as-is.png](assets/baseline/partner-bookings-as-is.png)**.
2. Đọc [ui_design_v3.md](ui_design_v3.md) — §3.5–3.8 (cột Cọc/HĐ, Check-in gate, Lễ tân).
3. (Tuỳ chọn) Mở Canvas wireframe để xem logic bảng.
4. Phản hồi chỉnh sửa hoặc gửi **`UI_APPROVED`** khi chốt màn Host.

---

## Previous Versions

| Version | Focus | Visual | Spec |
| :--- | :--- | :--- | :--- |
| v1 | Guest dark mock | Stitch (deprecated) | [ui_design_v1.md](ui_design_v1.md) |
| v2 | Guest light + Stay | `assets/v2/*` | [ui_design_v2.md](ui_design_v2.md) |
| v3 | Host spec + Canvas | Canvas only | [ui_design_v3.md](ui_design_v3.md) |
| **v3.1** | **Host Stitch mock** | **partner-bookings-to-be.png** | [ui_design_v3.md](ui_design_v3.md) |
