# UI Design v2: Đặt Cọc Linh Hoạt & Nghiệp Vụ Tài Chính (BKS System Integration)

## Document Information
- **Version:** v2
- **Status:** Draft (Reviewing)
- **Related SRS:** [docs/SRC/srs_booking_deposit_and_financial_rules.md](../../SRC/srs_booking_deposit_and_financial_rules.md)
- **Based on:** [ui_baseline.md](ui_baseline.md), [ui_design_v1.md](ui_design_v1.md)

---

## 1. Design Goals (Mục Tiêu Thiết Kế v2)
1.  **Đồng bộ 100% nhận diện thương hiệu BKS System:** Chuyển đổi toàn bộ thiết kế đề xuất sang **chế độ sáng (light theme)**, giữ nguyên Header trắng, Banner xanh dương đậm, và cấu trúc thẻ Card trắng bo góc bo tròn đặc trưng.
2.  **Đảm bảo an toàn UX & Tin cậy giao dịch (Option 1):** Thay vì hiển thị trực tiếp QR chuyển khoản cho Host ngay tại trang đặt phòng thành công (khiến người dùng lo lắng về độ uy tín), chúng ta chỉ hiển thị bảng **Cảnh báo chờ cọc** và **Đếm ngược thời gian giữ phòng** tại màn hình `/booking-success`. Toàn bộ cổng thanh toán cọc VietQR chi tiết sẽ được chuyển vào trang chi tiết đơn hàng trong BKS Stay Portal của khách hàng.
3.  **Tối ưu luồng điều hướng:** Nút hành động chính đổi thành **"Tới BKS Stay Portal"** để dẫn dắt khách hàng đăng nhập, kiểm tra thông tin đối tác và thanh toán cọc an toàn.

---

## 2. Screen Specifications (Đặc Tả Giao Diện Tích Hợp)

### 2.1 Giao diện Đặt phòng thành công (`/booking-success`) - Tích hợp mới

Giao diện giữ nguyên cấu trúc khung của BKS System (Header trắng, Banner xanh dương đậm, Footer tối). Thay đổi bên trong Card trắng chính như sau:

```text
┌────────────────────────────────────────────────────────┐
│                   MÃ ĐẶT PHÒNG CỦA BẠN                 │
│                      RM-2826-008576                    │
│      Thông tin chi tiết đã được gửi tới: guest@email.com │
├────────────────────────────────────────────────────────┤
│ ⚠️ CẢNH BÁO: Đơn đặt phòng đang chờ thanh toán đặt cọc  │
│                                                        │
│                  CÒN LẠI ĐỂ GIỮ PHÒNG                  │
│                        01:59:42                        │
│                                                        │
│  Để đảm bảo đặt phòng không bị hủy tự động, vui lòng   │
│  truy cập BKS Stay Portal của bạn để kiểm tra thông    │
│  tin và thực hiện đặt cọc giữ chỗ.                     │
├────────────────────────────────────────────────────────┤
│ ✔ HƯỚNG DẪN CÁC BƯỚC TIẾP THEO                         │
│ 01. Kiểm tra Email                                     │
│ 02. Kích hoạt tài khoản & Đăng nhập                    │
│ 03. Thanh toán cọc & Tải Voucher (Lên Portal cọc)      │
├────────────────────────────────────────────────────────┤
│   [Tới BKS Stay Portal (Blue)]    [Tiếp tục tìm phòng] │
└────────────────────────────────────────────────────────┘
```

*   **Khối cảnh báo cọc (Notice Box):** Nền màu xám nhạt (`#f8fafc`), cảnh báo dạng viền đỏ nhạt (`#fef2f2`), đếm ngược màu đỏ đậm (`#dc2626`) hiển thị to rõ để nhấn mạnh tính khẩn cấp của Grace Period (2 tiếng).

---

### 2.2 Giao diện BKS Stay Portal - Chi tiết lưu trú (`/bks-stay/bookings/:id`)
*   **Vị trí tích hợp cọc:** Sau khi khách hàng nhấn "Tới BKS Stay Portal" và đăng nhập, tại màn hình chi tiết đơn đặt phòng sẽ tích hợp **Khối thanh toán cọc VietQR chi tiết**:
    *   **Thanh tiến trình (Step Tracker):** Hiển thị trực quan: *Đặt thành công* $\rightarrow$ *Ký hợp đồng* $\rightarrow$ *Thanh toán cọc (Đang thực hiện)* $\rightarrow$ *Sẵn sàng Check-in*.
    *   **Khối VietQR thanh toán:**
        *   Bên trái: Mã QR Code được sinh tự động khớp với số tiền cọc.
        *   Bên phải: Thông tin ngân hàng nhận tiền (Số tiền cọc: 1,500,000 VND, Số tài khoản: 1234567890 (BIDV), Nội dung CK: BKSSETTLE10) kèm các nút Copy nhanh bên cạnh.
    *   **Receipt Dropzone:** Khung tải ảnh biên lai chuyển khoản (dành cho hình thức chuyển khoản trực tiếp).
    *   **Nút in ấn:** Nút "Tải Voucher (PNG)" sử dụng thư viện `html2canvas` và "In phiếu xác nhận" sử dụng `@media print` mặc định của trình duyệt.

### 2.3 Giao diện Lễ tân cho Host (`/partner/bookings`)

> **Chi tiết To-Be đầy đủ:** xem [ui_design_v3.md](ui_design_v3.md) (cột Cọc/HĐ, tab Lễ tân hôm nay, drawer, KPI Chờ cọc). **Mock Stitch:** [assets/v3/partner-bookings-to-be.png](assets/v3/partner-bookings-to-be.png).

*   **Tích hợp mới (tóm tắt):**
    *   Nút Check-in chuyển sang màu xám nhạt (`#e2e8f0`) và chữ xám (`#94a3b8`) khi bị khóa.
    *   Tooltip cảnh báo xuất hiện dạng hộp thoại popover màu đỏ nhạt (`#fef2f2`), chữ màu đỏ đậm (`#991b1b`), viền đỏ mảnh (`#fee2e2`) để chặn check-in khi chưa cọc/ký hợp đồng.

---

## 3. Bản Đồ Chuyển Đổi Giao Diện (v2)

| Màn hình | As-Is (Hiện trạng BKS) | To-Be (v2 Tích hợp - Option 1) |
| :--- | :--- | :--- |
| **Booking Success** | Chỉ có mã đơn phòng + hướng dẫn 3 bước tĩnh trên nền sáng. | Lồng ghép khối thông báo **Chờ cọc** & đếm ngược, thay nút chính thành "Tới BKS Stay Portal". |
| **Stay Booking Detail** | Chỉ có thông tin phòng cơ bản. | Thêm thanh tiến trình đặt cọc, **khối thanh toán VietQR cọc chi tiết**, và khung upload biên lai cọc. |
| **Partner Booking Table** | Bấm Check-in trực tiếp không kiểm duyệt. | Nút Check-in bị disabled nếu chưa cọc/ký hợp đồng + Popover cảnh báo màu đỏ nhạt. |
