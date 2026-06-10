# Báo cáo Nghiệp vụ Lưu trú: Tối ưu hóa Quy trình Đăng ký Nhận Bản tin & Coupon Chào mừng (Welcome Offer)

## Executive Summary
- **Domain Recommendation**: **APPROVED** (Sẵn sàng Vận hành)
- **Summary**: Quy trình đăng ký nhận tin bản (Newsletter Subscription) kết hợp quà tặng chào mừng (Welcome Coupon - 10%) là một trong những phễu chuyển đổi (Conversion Funnel) cốt lõi của các nền tảng OTA và đặt phòng trực tuyến (Booking.com, Agoda, Traveloka). Nó đóng vai trò chuyển hóa khách truy cập vãng lai (visitors) thành khách hàng tiềm năng (leads) và thúc đẩy lượt đặt phòng đầu tiên (First-time Booking). Đề xuất này nâng cấp cơ chế xử lý từ "tạm thời" sang "chuyên nghiệp & thực tế" bằng cách lưu giữ email khách hàng, chặn spam mã giảm giá và tự động hóa email xác nhận.

---

## Hospitality Business Rules & Standards

### 1. Kiểm soát Chi phí Thu hút Khách hàng (Customer Acquisition Cost - CAC)
- **Chống lạm dụng khuyến mãi (Anti-coupon abuse)**: Mã giảm giá chào mừng (Welcome Coupon) là một khoản đầu tư từ ngân sách marketing nhằm thu hút khách hàng mới. Để tối ưu hóa tỷ suất lợi nhuận (ROI), mỗi khách hàng (định danh bằng địa chỉ email) chỉ được phép nhận mã chào mừng **duy nhất một lần**.
- **Tính duy nhất của lượt đăng ký**: Backend phải kiểm tra sự tồn tại của email trong cơ sở dữ liệu trước khi cấp mã mới. Nếu email đã nhận tin trước đó, hệ thống sẽ từ chối cấp thêm mã mới và trả về thông tin cảnh báo rõ ràng.

### 2. Tối ưu hóa Điểm chạm & Trải nghiệm Đặt phòng (Seamless Guest Experience)
- **Cung cấp mã tức thì (Instant Gratification)**: Theo khảo sát hành vi khách hàng của Agoda, việc bắt buộc khách hàng phải rời khỏi website để vào hòm thư cá nhân lấy mã giảm giá trước khi quay lại đặt phòng sẽ làm tăng **35% tỷ lệ bỏ giỏ hàng (Cart Abandonment)**. 
- **Quy trình tối ưu**:
  1. Người dùng nhập email trên màn hình tìm kiếm.
  2. Hệ thống kiểm tra hợp lệ, hiển thị mã giảm giá trực tiếp trên màn hình kèm tính năng "Sao chép nhanh".
  3. Đồng thời, một bản sao của mã giảm giá cùng thư chào mừng sẽ được gửi qua email. Khách hàng có thể sử dụng mã ngay lập tức mà không bị đứt gãy trải nghiệm mua sắm, hoặc lưu lại email để sử dụng trong vòng 30 ngày tới.

### 3. Tiếp thị Giữ chân Khách hàng (Customer Retention & Email Marketing)
- **Bản tin định kỳ (Newsletter)**: Khách lưu trú đăng ký nhận tin không chỉ để lấy coupon, họ mong muốn nhận được thông tin về các chương trình ưu đãi mùa du lịch, các combo phòng nghỉ cuối tuần, và các điểm đến xu hướng. Việc lưu trữ email một cách có cấu trúc là tiền đề xây dựng tệp khách hàng trung thành.
- **Trạng thái đăng ký (Subscription Status)**: Lưu trữ trạng thái (`subscribed` / `unsubscribed`) để tôn trọng quyền riêng tư của khách hàng, cho phép họ hủy nhận tin (Opt-out) khi không còn nhu cầu, tuân thủ các quy định về an toàn thông tin và chống thư rác (GDPR / CAN-SPAM Act).

---

## Phân tích Khoảng trống Nghiệp vụ (Gap Analysis)

### 1. Thất thoát dữ liệu Khách hàng tiềm năng (Lead Leakage)
- **Thực trạng**: API `/api/v1/home/coupons/register` nhận email gửi lên từ giao diện nhưng hoàn toàn bỏ qua email này, không lưu trữ vào bất cứ cơ sở dữ liệu nào.
- **Rủi ro vận hành**: Đội ngũ Marketing của khách sạn không có dữ liệu để thực hiện các chiến dịch chăm sóc, tiếp thị lại (Re-marketing) qua email, lãng phí chi phí hiển thị và lượng truy cập tự nhiên.
- **Đề xuất nghiệp vụ**: Tạo bảng `newsletter_subscriptions` để lưu trữ email, trạng thái đăng ký, và ID của mã coupon đã phân phối cho email đó để đối soát hiệu quả chuyển đổi sau này.

### 2. Lạm dụng Mã giảm giá (Coupon Abuse / Promotional Fraud)
- **Thực trạng**: Không có cơ chế kiểm tra email đã đăng ký chưa. Người dùng có thể nhập cùng một email hàng trăm lần để nhận mã, hoặc nhập các email không tồn tại mà vẫn nhận được mã giảm giá thực tế.
- **Rủi ro vận hành**: 
  - Thất thoát doanh thu do một khách hàng sử dụng mã chào mừng nhiều lần cho nhiều đơn đặt phòng khác nhau bằng cách thay đổi email rác.
  - Quá tải hệ thống do các robot tự động đăng ký spam.
- **Đề xuất nghiệp vụ**: Ràng buộc thuộc tính `unique` cho trường `email` trong bảng đăng ký bản tin. Trả về thông báo lỗi thân thiện nhưng dứt khoát nếu phát hiện email trùng lặp.

### 3. Đứt gãy luồng giao tiếp thương hiệu (Communication Disconnect)
- **Thực trạng**: Khách hàng bấm nhận mã, mã hiện lên màn hình nhưng không có email nào gửi về. Nếu khách hàng vô tình tải lại trang hoặc tắt trình duyệt, họ sẽ mất mã giảm giá này và mất đi động lực hoàn tất đặt phòng.
- **Rủi ro vận hành**: Giảm tỷ lệ chuyển đổi đơn đặt phòng thành công của khách hàng mới.
- **Đề xuất nghiệp vụ**: Xây dựng Class Mail `NewsletterWelcomeMail` để tự động hóa gửi email chào mừng chính thức chứa mã giảm giá trực quan ngay khi đăng ký thành công.

---

## Collaboration Action Items

### 1. Dành cho Business Analyst (BA)
- Cập nhật tài liệu Nghiệp vụ và luồng hành trình người dùng (User Flow) cho tính năng "Đăng ký nhận Coupon chào mừng":
  - **Actor**: Khách truy cập chưa đăng nhập hoặc đã đăng nhập nhưng chưa nhận coupon chào mừng.
  - **Acceptance Criteria (AC)**:
    1. Giao diện Banner Đăng ký hiển thị ô nhập Email và nút "Nhận Coupon".
    2. Email nhập vào phải được validate đúng định dạng trước khi gửi lên backend.
    3. Nếu email đã từng đăng ký trước đó, hiển thị thông báo: `"Email này đã được sử dụng để nhận mã ưu đãi. Vui lòng kiểm tra lại hộp thư của bạn."`.
    4. Nếu đăng ký thành công, lưu thông tin vào bảng `newsletter_subscriptions` với trạng thái `subscribed`, gửi email chào mừng chứa mã giảm giá và hiển thị mã giảm giá trực quan trên màn hình kèm thông báo hướng dẫn.
    5. Đảm bảo coupon chào mừng được phát ra phải là coupon đang hoạt động (`status = active`, còn hạn sử dụng, và chưa vượt quá hạn ngạch sử dụng `usage_limit`).

### 2. Dành cho UAT Tester
- Xây dựng các ca kiểm thử tích hợp (End-to-End Test Cases) phục vụ đánh giá nghiệm thu sản phẩm:
  - **Scenario 1 (Happy Path - Đăng ký mới)**: Nhập một email hợp lệ mới tinh (ví dụ: `guest.new@bksstay.com`).
    - Kết quả mong muốn: Giao diện hiển thị thành công kèm mã coupon. Cơ sở dữ liệu ghi nhận email ở bảng `newsletter_subscriptions`. Email chào mừng được gửi đi (hoặc log thành công trong `laravel.log`).
  - **Scenario 2 (Duplicate Check - Trùng lặp)**: Nhập lại email `guest.new@bksstay.com` lần thứ hai.
    - Kết quả mong muốn: Hệ thống chặn lại, hiển thị thông báo lỗi chi tiết trên màn hình. Không ghi nhận thêm dữ liệu trùng lặp.
  - **Scenario 3 (Invalid Email - Định dạng sai)**: Nhập email dạng `guest.new@invalid` hoặc để trống.
    - Kết quả mong muốn: Giao diện hoặc API phản hồi lỗi validation, không tiến hành xử lý sâu.
  - **Scenario 4 (Mail Delivery Verification - Kiểm thử Email)**: Kiểm tra nội dung email chào mừng được gửi.
    - Kết quả mong muốn: Email chứa đúng tiêu đề chào mừng BKS Stay, hiển thị rõ ràng mã giảm giá đã xuất hiện trên giao diện, có định dạng HTML chuyên nghiệp và hấp dẫn.

---
**Sign-off Signature:** Senior Hospitality & Accommodation Domain Expert  
**Date:** 2026-06-08
