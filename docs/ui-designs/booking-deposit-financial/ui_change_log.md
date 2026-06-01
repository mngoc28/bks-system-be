# UI Change Log: Đặt Cọc Linh Hoạt & Nghiệp Vụ Tài Chính (Booking Deposit & Financial Rules)

## v1 (2026-06-01)
- **Added (Bổ sung mới):**
  - Giao diện khối thanh toán VietQR động và bộ đếm ngược Grace Period ở màn hình `/booking-success`.
  - Banner thông báo giữ chỗ miễn phí ngày thường/mùa thấp điểm cho khách ở màn hình `/booking-success`.
  - Giao diện tải ảnh biên lai chuyển khoản cọc (Direct Transfer) trên Stay Portal của khách hàng.
  - Tab "Quản lý Đặt cọc & Ký quỹ (Escrow & Deposits)" tại màn hình tài chính của đối tác `/partner/finance` để theo dõi tiền cọc ký quỹ và duyệt biên lai chuyển khoản cọc thủ công.
  - Biểu mẫu check-out khấu trừ cọc hao mòn tài sản dành cho Host.
  - Chốt chặn kiểm duyệt điều kiện Check-in ở Frontend (Voucher đã ký + cọc đã đóng) bằng cách disable nút Check-in kèm hiển thị Tooltip cảnh báo.
- **Updated (Cập nhật):**
  - Tích hợp tính năng tải ảnh PNG Stay Voucher bằng thư viện `html2canvas`.
  - Tối ưu hóa CSS In ấn (`@media print`) cho Phiếu xác nhận lưu trú và Hợp đồng mẫu.
- **Rationale (Lý do thiết kế):**
  - Việc đưa QR thanh toán cọc trực tiếp lên trang Đặt phòng thành công giúp giảm bớt rào cản thao tác cho khách hàng, đẩy nhanh tiến trình chuyển cọc.
  - Chốt chặn nút Check-in có tooltip cảnh báo giúp Host tránh lỗi vận hành check-in sai quy trình pháp lý và tài chính.

## v2 (2026-06-01) - Tích hợp đồng bộ Bối cảnh hiện tại
- **Updated (Cập nhật):**
  - **Tái thiết kế giao diện sang Light Theme:** Chuyển đổi toàn bộ wireframe và bản vẽ từ Chế độ Tối sang Chế độ Sáng đồng bộ 100% với Header, Hero Banner xanh dương đậm, và Footer xanh đen của BKS System hiện trạng.
  - **Tích hợp khối cọc vào Card trắng:** Thay vì làm một trang riêng biệt, lồng ghép trực tiếp cụm VietQR và đếm ngược Grace Period vào bên trong lòng thẻ Card trắng hiện có của màn `/booking-success`, đặt ngay dưới mã đơn phòng và phía trên Hướng dẫn 3 bước tiếp theo.
  - **Tái thiết lập màn hình Chi tiết đặt phòng (Stay Booking Detail):** Cấu trúc lại giao diện đề xuất (To-Be) của trang `/bks-stay/bookings/:id` trên Stitch. Khôi phục hoàn toàn khung sườn (Shell) hiện tại bao gồm: thanh menu bên trái (Sidebar) đầy đủ 7 mục Việt hóa với avatar/tên Ngọc Hổ phía trên cùng, banner ảnh homestay thực tế góc rộng chèn đè tiêu đề phòng, và bố cục cột widget bên phải. Tích hợp thanh tiến trình 4 bước và khối quét mã VietQR + Dropzone nhận biên lai cọc vào lòng Card nội dung chính.
- **Rationale (Lý do thiết kế):**
  - Đảm bảo tính nhất quán của thiết kế giao diện (UI consistency) toàn hệ thống BKS. Việc thiết kế trang mới tinh bằng Dark Mode làm lệch trải nghiệm người dùng so với bối cảnh các trang tìm kiếm/đặt phòng khác.
  - Kế thừa tối đa cấu trúc giao diện Portal cũ (menu sidebar, avatar, banner) giúp giảm thiểu sai lệch UX và chi phí phát triển frontend, đồng thời làm nổi bật các thành phần nghiệp vụ mới bổ sung (cọc, hợp đồng, biên lai).

## v3 (2026-06-01) — Host Quản lý Đặt phòng (`/partner/bookings`)
- **Added:**
  - Đặc tả To-Be đầy đủ `ui_design_v3.md` cho màn Host: KPI Chờ cọc hôm nay, tab lọc Chờ cọc, cột Cọc/HĐ, Check-in gate (disabled + popover đỏ nhạt), drawer Cọc & Hợp đồng, tab Lễ tân hôm nay (Front Desk SOP).
  - Canvas wireframe `booking-deposit-financial-host-bookings-ui-v3.canvas.tsx`.
- **Updated:**
  - Mở rộng §2.3 trong `ui_design_v2.md` thành spec chi tiết v3 (v2 vẫn giữ Option 1 cho Guest).
- **Preserved (As-Is shell):**
  - Layout Partner Portal, header trang, 4 KPI gốc, bảng + pagination, modal Check-in/out, bulk actions Partner 360.
- **Rationale:**
  - Plan T4.4 + REQ-DEP-002 yêu cầu Host thấy trạng thái cọc/HĐ và không check-in sai quy trình; tách tab Lễ tân phục vụ REQ-SDEP-005 mà không đổi theme trang.

## v3.1 (2026-06-01) — Stitch To-Be Host (`partner-bookings-to-be`)
- **Added:**
  - Export ảnh Stitch To-Be: `assets/v3/partner-bookings-to-be.png` (screen `ba81ac66b19e4d4bb28079b113c3fbfa`, project `9871443953956024804`).
  - Liên kết As-Is baseline `assets/baseline/partner-bookings-as-is.png` trong `ui_baseline.md` và `ui_preview.md`.
- **Updated:**
  - `ui_design_v3.md` §3.9 Visual preview; `ui_preview.md` trỏ mock Stitch làm preview chính.
- **Rationale:**
  - Sau khi có baseline PNG, chạy Stitch với prompt alignment (giữ shell Partner light, chỉ delta cọc/Check-in gate) để có mock pixel trước `UI_APPROVED`.
