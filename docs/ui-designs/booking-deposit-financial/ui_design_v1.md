# UI Design v1: Đặt Cọc Linh Hoạt & Nghiệp Vụ Tài Chính (Booking Deposit & Financial Rules)

## Document Information
- **Version:** v1
- **Status:** Draft (Reviewing)
- **Related SRS:** [docs/SRC/srs_booking_deposit_and_financial_rules.md](../../SRC/srs_booking_deposit_and_financial_rules.md)
- **Based on:** [ui_baseline.md](ui_baseline.md)

---

## 1. Design Goals (Mục Tiêu Thiết Kế)
1.  **Minh bạch chính sách cọc & Giảm thiểu rào cản (Booking Friction):** Hiển thị rõ số tiền cọc cần đóng và thông tin thanh toán trực quan ngay tại trang đặt phòng thành công.
2.  **Chốt chặn an toàn vận hành (Operational Security):** Thiết kế nút Check-in có điều kiện ràng buộc ở Frontend (disabled và có tooltip giải thích khi chưa đóng cọc hoặc chưa ký hợp đồng).
3.  **Tự động hóa và đếm ngược thời gian chờ cọc (Grace Period Visibility):** Hiển thị bộ đếm ngược thời gian giữ phòng chờ cọc trực quan giúp thúc đẩy khách thanh toán nhanh.
4.  **Tối ưu hóa quy trình buồng phòng & check-out:** Thiết kế biểu mẫu kiểm kho/nghiệm thu hư hại tài sản và hoàn trả cọc trực tiếp tại màn hình check-out của Host.

---

## 2. User Flows (Luồng Người Dùng Giao Diện)

### Luồng A: Khách đặt phòng ngắn hạn cần cọc (Mùa cao điểm/Đặt sát ngày)
1.  Khách hàng tạo đơn đặt phòng thành công $\rightarrow$ Chuyển hướng tới `/booking-success`.
2.  Trang hiện thông báo chúc mừng + Mã VietQR động (chứa số tài khoản, số tiền cọc, và nội dung chuyển khoản `BKSSETTLE[ID_BOOKING]`).
3.  Đồng hồ đếm ngược Grace Period chạy (Ví dụ: `Còn lại 01 giờ 59 phút 59 giây để hoàn tất cọc`).
4.  Khách hàng quét QR thanh toán $\rightarrow$ Webhook phản hồi thành công $\rightarrow$ Giao diện tự động cập nhật sang trạng thái "Đã xác nhận đặt phòng" và hiển thị nút **"Tải ảnh Stay Voucher (PNG)"**.

### Luồng B: Khách đặt phòng ngắn hạn không cần cọc (Ngày thường/Mùa thấp điểm)
1.  Khách hàng tạo đơn đặt phòng thành công $\rightarrow$ Chuyển hướng tới `/booking-success`.
2.  Trang hiện thông báo: "Phòng được giữ miễn phí đến 18:00 ngày [Ngày check-in]".
3.  Trước 12:00 ngày check-in, Lễ tân Host mở `Front Desk SOP Panel` $\rightarrow$ Thấy trạng thái đơn là "Chờ xác nhận" $\rightarrow$ Click nút "Gửi nhắc nhở xác nhận qua Zalo/SMS" $\rightarrow$ Hệ thống tự động gửi tin nhắn kèm link xác nhận nhanh cho khách.
4.  Nếu khách không bấm xác nhận trước 18:00 $\rightarrow$ Hệ thống tự động hủy đơn $\rightarrow$ Đồng bộ tồn kho trống (+1) lên Agoda/Booking.com và kích hoạt flash sale giờ chót.

---

## 3. Screen Specifications (Đặc Tả Giao Diện)

### 3.1 Giao diện Đặt phòng thành công (`/booking-success`)
*   **Bố cục (Layout):** Thêm khối **"Thanh toán đặt cọc" (Payment Gate Block)** nằm nổi bật bên cạnh Checklist 3 bước hướng dẫn.
*   **Thành phần Component:**
    *   *Trường hợp bắt buộc cọc:* 
        *   Mã **VietQR động** được sinh tự động theo chuẩn Napas.
        *   Nút copy nhanh Số tài khoản, Số tiền cọc, Cú pháp chuyển khoản.
        *   Đồng hồ đếm ngược Grace Period dạng progress bar giảm dần (màu vàng nhạt chuyển dần sang đỏ khi sắp hết giờ).
    *   *Trường hợp giữ phòng không cọc:*
        *   Banner thông báo màu xanh dương: *"Đã giữ chỗ miễn phí. Vui lòng xác nhận trước 18:00 ngày nhận phòng để tránh bị hủy đơn tự động."*
*   **UI States:**
    *   *Loading:* Spinner hiển thị khi đang sinh mã VietQR động.
    *   *Success:* Sau khi quét QR thành công, màn hình hiển thị hiệu ứng pháo hoa nhẹ, ẩn khối QR và hiển thị thông điệp: *"Đã xác nhận cọc - Stay Voucher đã sẵn sàng!"* kèm nút tải ảnh PNG.

### 3.2 Giao diện Stay Portal - Chi tiết booking của khách (`/bks-stay/bookings/:id`)
*   **Bố cục (Layout):** 
    *   Thanh tiến trình đặt phòng dạng Step Tracker ở trên cùng: `Đặt phòng thành công` $\rightarrow$ `Ký hợp đồng` $\rightarrow$ `Thanh toán cọc` $\rightarrow$ `Sẵn sàng Check-in` $\rightarrow$ `Đang lưu trú` $\rightarrow$ `Hoàn tất Check-out`.
*   **Thành phần Component:**
    *   *Khối tải biên lai cọc (đối với Direct Transfer ngoài hệ thống):* Một khu vực Drag-and-drop cho phép khách hàng kéo thả file ảnh biên lai chuyển khoản ngân hàng (.jpg, .png) kèm nút gửi. Trạng thái hiển thị sang *"Đang chờ Host xác thực cọc"*.
    *   *Khối Stay Confirmation Voucher:*
        *   Nút **"In phiếu xác nhận (A4)"** kích hoạt `window.print()`. Sử dụng CSS `@media print` ẩn hoàn toàn sidebar BKS Stay, header trang web và các nút bấm, căn lề đúng khổ giấy A4.
        *   Nút **"Tải ảnh phiếu lưu trú (PNG)"** sử dụng thư viện `html2canvas` để render card phiếu voucher dưới dạng ảnh sắc nét.

### 3.3 Giao diện Lễ tân cho Host - Bảng Front Desk (`/partner/bookings`)
*   **Bố cục (Layout):** Thêm bộ lọc nhanh "Hôm nay" và tab chuyên dụng **"Bảng Lễ Tân (Front Desk Panel)"**.
*   **Thành phần Component:**
    *   *Cảnh báo kiểm duyệt điều kiện Check-in (Gate Check):* 
        *   Nút **"Check-in"** bình thường có màu xanh dương. Nếu đơn dài hạn chưa đóng cọc (`held_in_escrow` hoặc `confirmed_by_partner` = false) hoặc chưa ký hợp đồng (`status` của hợp đồng = 0), nút **Check-in sẽ bị disable (màu xám)**.
        *   Khi hover vào nút Check-in bị disabled, hiển thị **Tooltip cảnh báo màu đỏ**: *"Chặn Check-in: Hợp đồng thuê nhà chưa ký (Chờ ký) hoặc Tiền cọc chưa được xác thực."*
    *   *Nút hành động khẩn cấp (Emergency actions):* Nút bấm nhanh để lễ tân gọi điện hoặc nhắn tin Zalo xác nhận phòng cho khách không cọc trước khi đến hạn giải phóng 18:00.

### 3.4 Giao diện Tài chính Đối tác - Tab Ký quỹ cọc (`/partner/finance`)
*   **Bố cục (Layout):** Bổ sung Tab chuyên dụng **"Quản lý Đặt cọc & Ký quỹ (Escrow & Deposits)"**.
*   **Thành phần Component:**
    *   *Thẻ KPI cọc:* Tiền cọc đang ký quỹ (Held in Escrow), Tiền cọc đã hoàn, Tiền cọc khấu trừ tài sản.
    *   *Bảng kiểm duyệt chuyển khoản cọc thủ công:* Hiển thị danh sách khách tải biên lai chuyển tiền, nút phóng to ảnh biên lai và nút **"Xác nhận đã nhận tiền"** (để chuyển trạng thái cọc sang `confirmed_by_partner`).
    *   *Biểu mẫu check-out khấu trừ cọc:* Khi bấm check-out, hiển thị form phụ cho lễ tân nhập: Số tiền khấu trừ (do hao mòn, hỏng tài sản), lý do khấu trừ, ảnh chụp hiện trạng hỏng tài sản. Số tiền cọc còn lại tự động tính để hoàn trả cho khách.

---

## 4. UX Rules (Quy Tắc Trải Nghiệm Người Dùng)
*   **Tìm kiếm & Lọc:** Bộ lọc Front Desk Panel phải hỗ trợ tìm kiếm tức thì theo số điện thoại khách hàng và mã phòng/loại phòng.
*   **Thông báo & Toasts:**
    *   Khi khách nộp biên lai cọc thành công: Hiển thị Toast thông báo *"Biên lai của bạn đã được gửi tới Host. Vui lòng chờ xác thực trong vòng 30 phút."*
    *   Khi Host duyệt cọc: Gửi thông báo đẩy (Push notification) tức thời cho khách.

---

## 5. Bản Đồ Chuyển Đổi Giao Diện (As-Is to To-Be Mapping)

| Màn hình hiện tại | Thay đổi thiết kế (To-Be) | Loại thay đổi |
| :--- | :--- | :--- |
| **BookingSuccess** | Tích hợp khối thanh toán VietQR động và đếm ngược Grace Period. | Nâng cấp (Update) |
| **Stay Booking Detail** | Thêm thanh tiến trình đặt phòng, khu vực tải ảnh biên lai chuyển khoản cọc. | Nâng cấp (Update) |
| **Partner Booking Table** | Thêm chốt chặn disabled nút Check-in kèm tooltip cảnh báo điều kiện cọc & hợp đồng. Thêm tab Front Desk Panel. | Nâng cấp (Update) |
| **Partner Finance** | Bổ sung tab theo dõi tiền cọc ký quỹ (Escrow) của khách và duyệt biên lai cọc thủ công. | Bổ sung mới (New) |

---

## 6. Các Điểm Cần Làm Rõ (Open Points)
- [ ] *Cổng thanh toán VietQR:* API của hệ thống đã hỗ trợ sinh mã VietQR động tích hợp giá trị tiền cọc chưa? (Giả định: đã có API hỗ trợ).
- [ ] *Thời gian đếm ngược (Grace Period):* Thời gian đếm ngược hiển thị ở trang Success sẽ được đồng bộ theo thời gian thực (Server-sent events hoặc polling) hay chỉ đếm ngược client-side? (Đề xuất: Client-side đếm ngược kết hợp gọi API kiểm tra trạng thái mỗi 30 giây).
