# UI Baseline: Đặt Cọc Linh Hoạt & Nghiệp Vụ Tài Chính (Booking Deposit & Financial Rules)

## Document Information
- **Module:** booking-deposit-financial
- **Status:** Draft (As-Is Analysis)
- **Related SRS:** [docs/SRC/srs_booking_deposit_and_financial_rules.md](../../SRC/srs_booking_deposit_and_financial_rules.md)
- **Related Routes:** 
  - `/booking-success` (EndUser Booking Success)
  - `/bks-stay/bookings/:id` (BKS Guest Stay Portal Detail)
  - `/partner/bookings` (Partner Portal Booking Management)
  - `/partner/finance` (Partner Finance Dashboard)
- **Roles:** Khách hàng (Guest), Đối tác (Partner/Host), Quản trị viên (Admin)

---

## 1. As-Is Screens (Hiện Trạng Giao Diện)

| Screen | Route | Purpose | Main Components | Observed Gaps (Khoảng trống giao diện) |
| :--- | :--- | :--- | :--- | :--- |
| **Booking Success** | `/booking-success` | Thông báo đặt phòng thành công cho khách hàng sau khi tạo đơn. | Card thông điệp, mã đặt phòng, địa chỉ email nhận tin, checklist 3 bước hướng dẫn mở hộp thư. | Chưa hiển thị hướng dẫn đặt cọc trực tiếp đối với các đơn phòng thuộc chính sách bắt buộc cọc (ví dụ: ngày lễ/cuối tuần). Khách hàng phải mở email để lấy thông tin cọc gây chậm trễ hành động. |
| **Stay Booking Detail** | `/bks-stay/bookings/:id` | Hiển thị chi tiết đơn phòng của khách hàng trên Stay Portal. | Thông tin thời gian check-in/out, tên phòng, thông tin liên lạc, liên kết tới hợp đồng/phiếu voucher. | Thiếu khu vực đăng tải ảnh minh chứng chuyển khoản cọc (Receipt/Invoice upload) cho hình thức chuyển khoản trực tiếp (Direct Transfer) và thiếu hiển thị trạng thái cọc thời gian thực (Ký quỹ, Chờ duyệt cọc, Đã confirm). |
| **Partner Booking Management** | `/partner/bookings` | Quản lý danh sách đơn đặt phòng, thực hiện duyệt, từ chối và Check-in/out. | Bộ lọc trạng thái (Chờ duyệt, Đang ở, Đã hủy, Hoàn thành), nút duyệt nhanh, nút Check-in/out. | Nút Check-in hoạt động trực tiếp khi đơn đã duyệt mà không hề kiểm tra ràng buộc cứng về cọc (đã đóng cọc chưa?) và hợp đồng (đã ký chưa?), dễ dẫn đến sai phạm quy trình check-in. |
| **Partner Finance** | `/partner/finance` | Quản lý tài chính, nợ phí hoa hồng 5% và lịch sử kỳ đối soát của đối tác. | Thẻ KPI dư nợ, tổng đã nộp, bảng lịch sử kỳ đối soát, hướng dẫn chuyển khoản phí hệ thống. | Hiện trạng màn hình tài chính chỉ phục vụ cho việc đối soát phí hoa hồng 5% của hệ thống. Hoàn toàn thiếu tính năng theo dõi dòng tiền đặt cọc ký quỹ (Escrow) của khách và biên bản nghiệm thu khấu trừ cọc tài sản khi check-out. |

---

## 2. Evidence (Bằng Chứng Phân Tích)
*   **Screenshot(s):**
    *   [Partner Bookings As-Is](assets/baseline/partner-bookings-as-is.png) — tab Chờ duyệt, KPI 4 thẻ, bảng Duyệt/Từ chối.
*   **Snapshot notes:** 
    *   Phân tích cấu trúc file định tuyến [Router.tsx](file:///d:/ASUS/intern/bks-datn/bks-system-fe/src/Router.tsx).
    *   Phân tích chi tiết giao diện [BookingSuccess.tsx](file:///d:/ASUS/intern/bks-datn/bks-system-fe/src/pages/EndUser/BookingSuccess/index.tsx) - chứa checklist 3 bước tĩnh.
    *   Phân tích mã nguồn [Bookings.tsx](file:///d:/ASUS/intern/bks-datn/bks-system-fe/src/pages/Partner/Bookings.tsx) - Lễ tân thực hiện duyệt và bấm Check-in trực tiếp mà không có Gate check điều kiện cọc & hợp đồng.
    *   Phân tích mã nguồn [Finance.tsx](file:///d:/ASUS/intern/bks-datn/bks-system-fe/src/pages/Partner/Finance/index.tsx) - tập trung vào settlement nộp phí hoa hồng 5%.

---

## 3. Gap Summary (Tổng Hợp Khoảng Trống UX/UI)
1.  **Thiếu cổng thanh toán/thông tin cọc tại màn Success:** Khách hàng đặt phòng mùa cao điểm cần cọc ngay nhưng trang Success lại quá tối giản, không hiển thị mã QR thanh toán hoặc thông tin chuyển khoản cọc động, làm giảm tốc độ thanh toán cọc và tăng tỷ lệ hủy đơn tự động.
2.  **Lễ tân Check-in "mù":** Host/Lễ tân có thể bấm Check-in cho khách thuê dài hạn ngay cả khi khách chưa đóng cọc và chưa ký hợp đồng (không có cảnh báo hoặc chặn nút bấm ở Frontend).
3.  **Khách không thể tự tải minh chứng cọc:** Khi chọn hình thức chuyển khoản trực tiếp ngoài hệ thống cho Host, khách không có nút bấm để tải ảnh chụp biên lai chuyển tiền lên Stay Portal.
4.  **Thiếu tính năng hoàn cọc tài sản trên UI:** Không có giao diện nhập biên bản khấu trừ tài sản hao tổn và phê duyệt hoàn cọc cho khách tại màn hình Check-out của Host.

---

## 4. Assumptions (Giả Định Thiết Kế)
*   Mã nguồn Frontend React sử dụng TailwindCSS và Shadcn UI cho các component.
*   PMS đã được tích hợp Channel Manager từ trước để đồng bộ quỹ phòng trực tuyến.
*   Cổng thanh toán (VNPay hoặc cổng khác) đã sẵn sàng để tích hợp luồng ký quỹ trực tuyến.
