# Domain Review: Quy trình Thanh toán Online sau Đặt phòng

## Executive Summary
- **Domain Recommendation**: **REJECTED (High Operational Risk)** (Bị bác bỏ do rủi ro vận hành cao)
- **Summary**: Quy trình thanh toán online mô phỏng hiện tại gặp các lỗ hổng nghiệp vụ nghiêm trọng về kiểm soát trạng thái phòng và bảo mật dữ liệu khách lưu trú. Việc cho phép thanh toán công khai không qua xác thực quyền sở hữu và tự động xác nhận đơn đặt phòng đã bị hủy/quá hạn mà không kiểm tra xung đột phòng trống sẽ trực tiếp dẫn đến tình trạng overbooking (trùng phòng), gây tổn hại uy tín thương hiệu và phát sinh chi phí đền bù lớn cho đối tác vận hành.

---

## Hospitality Business Rules & Standards

1. **Nguyên tắc giữ chỗ & Thời gian ân hạn (Hold & Grace Period)**:
   - Các đơn đặt phòng ở trạng thái `PENDING` (0) chỉ được giữ chỗ tạm thời trong thời gian ân hạn (grace period từ 2 đến 12 tiếng tùy thuộc vào độ cận ngày check-in). 
   - Khi quá hạn, hệ thống tự động hủy đơn (`CANCELLED`) để đưa phòng trở lại kho phòng trống nhằm tối ưu hóa tỷ lệ lấp đầy (occupancy rate).

2. **Nguyên tắc Chuyển trạng thái Thanh toán (Payment State Transitions)**:
   - Hệ thống chỉ được phép chuyển trạng thái đơn hàng sang `CONFIRMED` (1) khi đơn hàng đó đang ở trạng thái hợp lệ là `PENDING` (0) và phòng tương ứng vẫn còn trống.
   - Tuyệt đối nghiêm cấm việc trực tiếp "hồi sinh" các đơn hàng đã ở trạng thái `CANCELLED` (2), `COMPLETED` (3), hoặc `PENDING_CANCELLATION` (4) về lại `CONFIRMED` (1) thông qua cổng thanh toán mà không có sự kiểm tra và phê duyệt từ hệ thống quản lý phòng.

3. **Bảo mật Dữ liệu Khách lưu trú (Guest Data Privacy)**:
   - Thông tin đặt phòng chứa thông tin định danh cá nhân nhạy cảm (PII) như Họ tên, Email, số điện thoại, thời gian lưu trú và số tiền giao dịch.
   - Trang thanh toán chỉ được phép hiển thị cho chính khách hàng thực hiện đặt phòng. Truy cập phải được xác thực qua session đăng nhập hoặc mã băm bảo mật duy nhất (secure hash/signature) đi kèm URL thanh toán.

---

## Gap Analysis (Domain Perspective)

*Quy trình hiện tại trong code `MockPaymentController` đang có những khoảng trống lớn so với tiêu chuẩn vận hành thực tế của ngành khách sạn:*

### 1. Lỗ hổng IDOR & Rò rỉ thông tin cá nhân khách lưu trú
- **Mô tả kỹ thuật**: API GET `/api/v1/payments/mock-checkout` nhận tham số `booking_id` dạng số nguyên tăng dần và trả về giao diện thanh toán chứa toàn bộ thông tin chi tiết đơn phòng mà không kiểm tra quyền sở hữu của người dùng hiện tại (không có middleware `auth`, không kiểm tra `booking->user_id`).
- **Business Risk**: 
  - Kẻ xấu có thể quét (harvest) các ID đơn hàng để thu thập dữ liệu cá nhân của toàn bộ khách hàng trên hệ thống (vi phạm luật dữ liệu cá nhân).
  - Người dùng lạ có thể vô tình hoặc cố ý thực hiện lệnh thanh toán hoặc hủy giao dịch của khách hàng khác bằng cách thay đổi ID trên URL thanh toán, làm xáo trộn nghiêm trọng dữ liệu vận hành của Host.
- **Domain Recommendation**: 
  - Đóng bảo mật URL thanh toán bằng cách tạo chuỗi mã băm token thanh toán dùng một lần (e.g. `payment_token` lưu trong DB kèm thời gian hết hạn). 
  - Thay vì dùng `booking_id={int}`, URL thanh toán sẽ có dạng `/payments/checkout?token={hash}`. 
  - Nếu vẫn dùng `booking_id`, bắt buộc phải áp dụng middleware xác thực người dùng và kiểm tra tính chính danh: `$booking->user_id === Auth::id()`.

### 2. Nguy cơ Overbooking (Trùng phòng) do "hồi sinh" Đơn đặt phòng đã Hủy
- **Mô tả kỹ thuật**: Trong logic `handlePayment`, hệ thống thực hiện cập nhật thẳng trạng thái đơn đặt phòng sang `CONFIRMED` (1):
  ```php
  $booking->update([
      'status' => 1,
      'payment_collected_at' => $now,
  ]);
  ```
  Hành động này diễn ra trực tiếp mà không kiểm tra trạng thái trước đó của đơn hàng và không gọi `ConflictChecker` để kiểm tra lại tính sẵn sàng của phòng trống.
- **Business Risk**:
  - Khi đơn hàng `PENDING` bị hủy tự động (do quá hạn nộp cọc), kho phòng trống của phòng đó sẽ được giải phóng. Một khách hàng mới hoàn toàn có thể đặt và xác nhận thành công phòng này cho cùng một khoảng thời gian.
  - Nếu khách hàng cũ (đã bị hủy đơn) mở lại link thanh toán cũ và nhấn "Xác nhận thanh toán", đơn đặt phòng cũ sẽ tự động được hồi sinh thành `CONFIRMED`.
  - Kết quả: **Hai khách hàng khác nhau cùng sở hữu đơn đặt phòng CONFIRMED cho cùng một phòng thực tế**. Khi cả hai đến nhận phòng cùng lúc, đối tác vận hành bắt buộc phải từ chối một bên, chịu phạt đền bù đặt phòng, mất khách hàng trung thành, và nhận đánh giá 1 sao trên hệ thống.
- **Domain Recommendation**:
  - Ràng buộc chặt chẽ trạng thái đơn hàng trước khi thanh toán: Chỉ cho phép thanh toán nếu đơn hàng hiện tại có trạng thái `bookings.status == 0` (PENDING).
  - Tích hợp `ConflictChecker::findConflicts(useLock=true)` vào giao dịch thanh toán trong `MockPaymentController::handlePayment` để đảm bảo tại thời điểm nhận tiền, phòng vẫn chưa bị lấp bởi đơn phòng khác hoặc room block của Host.
  - Trong trường hợp phòng đã bị lấp hoặc đơn hàng đã hủy quá lâu, hệ thống phải trả về trang lỗi thanh toán quá hạn, đề xuất khách đặt phòng khác và chuyển tiền cọc sang trạng thái "chờ hoàn trả tự động" hoặc thông báo cho bộ phận Chăm sóc khách hàng (CS) xử lý thủ công.

### 3. Race Condition trong quá trình thanh toán song song
- **Mô tả kỹ thuật**: Việc thiếu cơ chế khóa dòng dữ liệu (`lockForUpdate`) trên phòng khi thực hiện thanh toán trực tuyến mô phỏng tạo ra khe hở thời gian (time-of-check to time-of-use).
- **Business Risk**: Hai khách hàng cùng đang mở màn hình thanh toán cho một phòng trống cuối cùng trong mùa cao điểm. Cả hai cùng nhấn "Xác nhận thanh toán" gần như đồng thời. Do thiếu khóa dòng, cả hai đơn hàng đều có thể ghi nhận trạng thái thành công và chuyển sang `CONFIRMED`, dẫn đến overbooking ngoài ý muốn.
- **Domain Recommendation**: Áp dụng cơ chế Pessimistic Lock (`lockForUpdate`) lên phòng và kiểm tra xung đột trong một database transaction duy nhất khi cập nhật trạng thái thanh toán thành công.

---

## Collaboration Action Items

### For Business Analyst (BA)
- [ ] Cập nhật tài liệu luồng nghiệp vụ thanh toán (PRD): Quy định rõ ràng liên kết thanh toán phải được mã hóa bằng token bảo mật dùng một lần, có thời hạn sống.
- [ ] Bổ sung kịch bản xử lý nghiệp vụ khi **"Thanh toán thành công đối với đơn đặt phòng đã bị hệ thống hủy do hết hạn"** (Quy trình hoàn tiền tự động hoặc gửi email thông báo kèm voucher đền bù).

### For UAT Tester
- [ ] **Scenario 1 (IDOR Verification)**:
  - Sử dụng hai tài khoản khách hàng khác nhau (Khách A và Khách B).
  - Tạo đơn đặt phòng cho Khách A để lấy `booking_id_A`.
  - Dùng tài khoản của Khách B (hoặc không đăng nhập) truy cập trực tiếp URL `/api/v1/payments/mock-checkout?booking_id={booking_id_A}`.
  - *Kỳ vọng*: Hệ thống phải báo lỗi `403 Unauthorized` hoặc redirect về trang chủ, tuyệt đối không hiển thị thông tin đặt phòng của Khách A.
- [ ] **Scenario 2 (Double-Booking Verification via Expired Cancellation)**:
  - Tạo đơn đặt phòng A cho Phòng X (ngày 15/06 - 18/06). Trạng thái hiện tại: `PENDING` (0).
  - Chạy job quét hết hạn hoặc chủ động cập nhật trực tiếp DB đơn đặt phòng A sang `CANCELLED` (2) (giả lập việc quá hạn thanh toán).
  - Tạo tiếp đơn đặt phòng B cho Phòng X cùng ngày (15/06 - 18/06) và chuyển sang `CONFIRMED` (1).
  - Mở URL thanh toán của đơn đặt phòng A và bấm nút "Xác nhận thanh toán".
  - *Kỳ vọng*: Hệ thống phải báo lỗi giao dịch không hợp lệ do đơn đặt phòng đã bị hủy hoặc phòng không còn khả dụng, không được tự động chuyển trạng thái đơn A sang `CONFIRMED`.
- [ ] **Scenario 3 (Double Click Test)**:
  - Bấm nút "Xác nhận thanh toán" liên tục nhiều lần trên giao diện thanh toán để kiểm tra xem hệ thống có gửi thừa email xác nhận hay tạo ra các log giao dịch trùng lặp hay không.

---
**Sign-off Signature:** Senior Hospitality & Accommodation Domain Expert  
**Date:** 2026-06-03
