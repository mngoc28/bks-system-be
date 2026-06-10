# Báo cáo Nghiệp vụ Lưu trú: Đề xuất thêm Bộ lọc Đánh giá (Rating Filter) cho trang Tìm kiếm Phòng

## Executive Summary
- **Domain Recommendation**: **APPROVED** (Sẵn sàng Vận hành)
- **Summary**: Việc bổ sung bộ lọc rating (đánh giá từ khách lưu trú) là một chuẩn mực cốt lõi trong ngành hospitality (Booking.com, Airbnb, Agoda). Tính năng này giúp tăng độ tin cậy của khách hàng (social proof), tối ưu tỷ lệ chuyển đổi (conversion rate), giảm tỷ lệ hủy phòng do trải nghiệm thực tế kém, và tạo động lực cho các đối tác (hosts) nâng cao chất lượng dịch vụ. Về mặt kỹ thuật, hệ thống backend đã hỗ trợ sẵn tham số đầu vào `rating_min`, giúp việc triển khai frontend diễn ra nhanh chóng với chi phí tối thiểu.

---

## Hospitality Business Rules & Standards

### 1. Tâm lý Học và Trải nghiệm của Khách hàng (Guest Psychology & Journey)
- **Hành vi lựa chọn chỗ ở**: Khách lưu trú trực tuyến không thể tiếp xúc trực tiếp với phòng trước khi nhận. Đánh giá từ những khách hàng cũ là yếu tố "xác thực xã hội" (social proof) quan trọng thứ hai sau giá cả.
- **Tiết kiệm thời gian & Tránh rủi ro**: Khách hàng thường muốn loại bỏ các phòng có điểm số thấp (ví dụ dưới 3.0 hoặc 4.0 sao) để tránh gặp rủi ro về vệ sinh kém, dịch vụ không đúng mô tả, hoặc thái độ phục vụ của host. Bộ lọc rating giúp khách hàng thu hẹp phạm vi tìm kiếm một cách nhanh chóng và an toàn.

### 2. Tiêu chuẩn Phân loại & Mức Đánh giá trong Ngành (Industry Benchmarks)
Bộ lọc rating nên chia thành các phân khúc chuẩn mực của ngành để khách hàng dễ hiểu:
- **Từ 4.5 sao trở lên**: Phân khúc xuất sắc/tuyệt vời (Excellent/Superb).
- **Từ 4.0 sao trở lên**: Phân khúc rất tốt (Very Good).
- **Từ 3.0 sao trở lên**: Phân khúc tốt/đạt yêu cầu (Good/Pleasant).
- **Tất cả**: Không lọc theo đánh giá.

---

## Phân tích Khoảng trống Nghiệp vụ (Gap Analysis)

*Dù backend đã hỗ trợ sẵn xử lý dữ liệu đầu vào nhưng frontend hiện tại chưa có giao diện tương tác.*

### 1. Thiếu cổng tương tác cho bộ lọc Rating trên Frontend
- **Mô tả**: Trang tìm kiếm phòng [RoomSearch/index.tsx](file:///d:/ASUS/intern/bks-datn/bks-system-fe/src/pages/EndUser/RoomSearch/index.tsx) chứa các bộ lọc: Tỉnh/thành, Phường/xã, Ngày nhận/trả phòng, Số khách, Loại hình phòng (Property Type) và Từ khóa. Tuy nhiên, không có cách nào để khách hàng lọc theo điểm đánh giá trung bình.
- **Rủi ro vận hành & kinh doanh**:
  - **Giảm trải nghiệm khách hàng**: Khách hàng phải lướt qua nhiều phòng có chất lượng thấp hoặc chưa được kiểm chứng, gây mỏi mệt trong quá trình tìm kiếm (choice fatigue).
  - **Lãng phí tài nguyên backend**: Backend đã tốn công sức truy vấn bảng `reviews` để tính `reviews_avg_rating` trong `RoomsRepository` nhưng frontend không khai thác bộ lọc này.
- **Đề xuất nghiệp vụ**:
  - Thêm một bộ lọc rating vào hàng các công cụ lọc hoặc nằm cạnh bộ lọc "Loại hình".
  - Cho phép người dùng chọn mức rating tối thiểu mong muốn (Ví dụ: 3+, 4+, 4.5+ hoặc 5 sao).
  - Khi người dùng click chọn, frontend sẽ gọi API tìm kiếm kèm theo query parameter `rating_min` gửi lên backend.

---

## Collaboration Action Items

### 1. Dành cho Business Analyst (BA)
- Cập nhật tài liệu Yêu cầu Nghiệp vụ (PRD) và Tiêu chí Chấp nhận (Acceptance Criteria) cho màn hình tìm kiếm phòng:
  - **User Story**: "Là khách lưu trú đang tìm phòng, tôi muốn lọc danh sách phòng theo số sao đánh giá trung bình để tôi có thể nhanh chóng tìm thấy những phòng có chất lượng dịch vụ tốt và uy tín."
  - **Acceptance Criteria (AC)**:
    1. Hiển thị UI chọn Rating tối thiểu dưới dạng Dropdown hoặc cụm Button chọn nhanh.
    2. Các tùy chọn bao gồm: "Tất cả đánh giá", "Từ 3.0⭐", "Từ 4.0⭐", "Từ 4.5⭐".
    3. Khi thay đổi bộ lọc rating, URL query parameter sẽ được cập nhật (ví dụ: `&rating_min=4.0`) để hỗ trợ chia sẻ link và lưu trạng thái trang.
    4. Trình bày số lượng phòng tương ứng với kết quả lọc chính xác.

### 2. Dành cho UAT Tester
- Xây dựng các kịch bản kiểm thử tích hợp (End-to-End Test Cases):
  - **Scenario 1**: Xác minh việc hiển thị danh sách phòng khi chọn bộ lọc "Từ 4.0⭐". Đảm bảo không có phòng nào có `reviews_avg_rating < 4.0` hiển thị trong kết quả.
  - **Scenario 2**: Kiểm tra trường hợp phòng chưa có đánh giá nào (`reviews_avg_rating` bằng 0 hoặc null) sẽ bị ẩn đi khi áp dụng bộ lọc rating (kể cả bộ lọc thấp nhất như 3.0⭐).
  - **Scenario 3**: Đảm bảo bộ lọc rating hoạt động đồng bộ với các bộ lọc hiện tại (như tỉnh/thành, khoảng giá, số người lớn/trẻ em).
  - **Scenario 4**: Chia sẻ liên kết kết quả tìm kiếm đã lọc rating sang tab ẩn danh để kiểm tra tính năng khôi phục bộ lọc từ URLSearchParams.

---
**Sign-off Signature:** Senior Hospitality & Accommodation Domain Expert  
**Date:** 2026-06-07
