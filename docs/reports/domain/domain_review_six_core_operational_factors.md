# Báo cáo Nghiệp vụ Lưu trú & Kế hoạch Thực hiện: 6 Nhóm Yếu tố Vận hành Cốt lõi

## Trạng thái Nghiệp vụ của các tính năng (Status Overview)
*   **[PENDING]** 1. Quy định Thú cưng (Pet Policy)
*   **[PENDING]** 2. Nội quy phòng (House Rules: Smoking, Parties, Quiet Hours)
*   **[PENDING]** 3. Hỗ trợ Tiếp cận (Accessibility Features)
*   **[PENDING]** 4. Chính sách Trẻ em & Phụ thu thêm người (Extra Guest & Child Policy)
*   **[PENDING]** 5. Quy trình Nhận/Trả phòng linh hoạt (Check-in/out & Self Check-in)
*   **[PARTIALLY_READY]** 6. Tiền đặt cọc bảo đảm (Security Deposit)

---

## Executive Summary
- **Domain Recommendation**: **CONDITIONALLY APPROVED** (Cần cập nhật các quy tắc nghiệp vụ này vào tài liệu đặc tả hệ thống PRD và cập nhật Database Migrations trước khi phát triển).
- **Summary**: Báo cáo phân tích khoảng trống nghiệp vụ giữa hệ thống `bks-system` hiện tại và các tiêu chuẩn ngành của các nền tảng OTA toàn cầu (Booking.com, Airbnb). Việc chuẩn hóa 6 yếu tố vận hành này sẽ đảm bảo tính pháp lý của giao dịch đặt phòng, phòng ngừa tranh chấp giữa Khách lưu trú (Guest) và Đối tác (Partner), đồng thời tối ưu hóa tỷ lệ chuyển đổi tìm kiếm phòng.

---

## Chi tiết Nghiệp vụ & Kế hoạch Triển khai 6 Yếu tố

### 1. Quy định Thú cưng (Pet Policy) - **[PENDING]**

#### A. Quy tắc nghiệp vụ (Business Rules)
*   Chủ phòng (Partner) khi đăng tin phải chọn 1 trong 3 trạng thái:
    1.  Cho phép mang thú cưng (Pets allowed).
    2.  Không cho phép mang thú cưng (No pets allowed).
    3.  Cho phép mang thú cưng có điều kiện (Pets allowed with conditions - kèm mô tả chi tiết như: giới hạn trọng lượng dưới 10kg, chỉ nhận chó/mèo, phí vệ sinh thú cưng phát sinh).
*   Hệ thống tìm kiếm ở Frontend phải cung cấp bộ lọc Checkbox: `"Cho phép mang thú cưng"`.

#### B. Giải pháp kỹ thuật đề xuất (Technical Spec)
*   **Database:** 
    *   Thêm bản ghi tiện nghi `"Cho phép mang thú cưng"` vào bảng `amenities` (dùng cho tìm kiếm nhanh).
    *   Bổ sung cột `pet_policy` (enum: `allowed`, `not_allowed`, `conditional`) và `pet_policy_note` (text) vào bảng `properties`.
*   **Frontend UI:**
    *   Trang Đối tác (Partner - Thêm mới/Sửa chỗ nghỉ): Thêm nhóm chọn chính sách thú cưng và ô nhập lưu ý.
    *   Trang Chi tiết phòng (Detail): Hiển thị biểu tượng Chó/Mèo kèm mô tả chính sách.
    *   Trang Tìm kiếm (Search): Thêm tùy chọn "Cho phép thú cưng" trong danh sách bộ lọc tiện ích.

---

### 2. Nội quy phòng (House Rules) - **[PENDING]**

#### A. Quy tắc nghiệp vụ (Business Rules)
*   Cấu hình bắt buộc 3 quy định phổ biến nhất để đồng bộ hiển thị và lọc:
    1.  **Hút thuốc (Smoking):** Cho phép hay cấm hút thuốc trong khuôn viên phòng/căn hộ.
    2.  **Tiệc tùng/Sự kiện (Parties/Events):** Cho phép tổ chức tiệc tụ tập hay cấm.
    3.  **Khung giờ yên lặng (Quiet Hours):** Yêu cầu giữ yên lặng tuyệt đối từ khung giờ nào (ví dụ: 22h00 - 06h00).

#### B. Giải pháp kỹ thuật đề xuất (Technical Spec)
*   **Database:** Tạo bảng mới `property_rules` để lưu nội quy cấu trúc:
    ```sql
    CREATE TABLE property_rules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT,
        rule_key VARCHAR(50), -- 'smoking_allowed', 'parties_allowed', 'quiet_hours_start', 'quiet_hours_end'
        rule_value VARCHAR(255),
        FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
    );
    ```
*   **Frontend UI:**
    *   Trang Chi tiết phòng: Tạo khối **"Nội quy chỗ ở"** hiển thị danh sách các icon trực quan (Không hút thuốc, Không tiệc tùng...).

---

### 3. Tiện ích hỗ trợ Tiếp cận (Accessibility Features) - **[PENDING]**

#### A. Quy tắc nghiệp vụ (Business Rules)
*   Đặc biệt quan trọng đối với các tòa nhà căn hộ dịch vụ cao tầng ở Việt Nam (nhiều nhà phố cải tạo không có thang máy).
*   Hệ thống cần phân loại rõ:
    1.  Có thang máy (Elevator in building).
    2.  Lối vào không có bậc thềm (Step-free entrance) - hỗ trợ xe lăn/xe đẩy.
    3.  Phòng ở tầng trệt (Ground floor room) - cho người cao tuổi ngại leo cầu thang.

#### B. Giải pháp kỹ thuật đề xuất (Technical Spec)
*   **Database:** Bổ sung các tiện nghi tiếp cận vào bảng `amenities`: `"Có thang máy"`, `"Lối đi xe lăn"`, `"Phòng tầng trệt"`.
*   **Frontend UI:**
    *   Đưa các tiện ích này vào danh mục bộ lọc lọc phòng nâng cao (Advanced Filters) để khách hàng thuộc nhóm gia đình hoặc người khuyết tật dễ tìm chỗ ở an toàn.

---

### 4. Chính sách Trẻ em & Phụ thu thêm người (Extra Guest & Child Policy) - **[PENDING]**

#### A. Quy tắc nghiệp vụ (Business Rules)
*   Mỗi phòng cần cấu hình:
    *   `base_guests`: Số lượng khách tiêu chuẩn bao gồm trong giá gốc (ví dụ: Phòng 2 giường cho 2 người).
    *   `max_guests`: Sức chứa tối đa (ví dụ: tối đa 4 người).
    *   `extra_guest_price`: Giá phụ thu cho mỗi khách phát sinh vượt quá số khách tiêu chuẩn (ví dụ: từ người thứ 3 phụ thu 150.000đ/đêm).
    *   `child_policy_age`: Độ tuổi trẻ em được miễn phí (ví dụ dưới 6 tuổi). Trẻ từ 6-12 tuổi tính phí trẻ em (ví dụ 50.000đ/đêm).

#### B. Giải pháp kỹ thuật đề xuất (Technical Spec)
*   **Database:** Cập nhật bảng `rooms`:
    *   Cột `people` đổi tên/hoặc giữ nguyên làm `max_people`.
    *   Thêm cột `base_people` (mặc định bằng `max_people` nếu không cấu hình phụ thu).
    *   Thêm cột `extra_people_fee` (decimal).
*   **Frontend UI:**
    *   Form tìm kiếm: Khi khách chọn 3 Người lớn cho phòng có `base_people = 2`, hệ thống tự động cộng phụ thu `extra_people_fee` nhân với số đêm vào tổng tiền phòng hiển thị.

---

### 5. Quy trình Nhận/Trả phòng linh hoạt (Check-in/out & Self Check-in) - **[PENDING]**

#### A. Quy tắc nghiệp vụ (Business Rules)
*   Khung giờ nhận phòng (Check-in time) và trả phòng (Check-out time) tiêu chuẩn (thường là nhận sau 14h00, trả trước 12h00).
*   Phương thức nhận phòng:
    *   Gặp trực tiếp chủ nhà / Bảo vệ giao chìa khóa.
    *   Tự nhận phòng (Self check-in) qua: Khóa thông minh (Smartlock) hoặc Hộp lấy khóa số (Key lockbox). Khách thuê homestay rất chuộng điều này để linh hoạt giờ giấc.

#### B. Giải pháp kỹ thuật đề xuất (Technical Spec)
*   **Database:** Bổ sung vào bảng `properties`:
    *   `standard_checkin_start` (time - vd: '14:00:00').
    *   `standard_checkout_end` (time - vd: '12:00:00').
    *   `checkin_method` (enum: `meet_host`, `smart_lock`, `lockbox`, `reception_24h`).
*   **Frontend UI:**
    *   Bộ lọc tìm kiếm: Cho phép chọn bộ lọc nhanh `"Tự nhận phòng"` (Self check-in).
    *   Trang Chi tiết phòng: Hiển thị rõ khung giờ nhận trả phòng và phương thức nhận chìa khóa.

---

### 6. Tiền đặt cọc bảo đảm (Security Deposit) - **[PARTIALLY_READY]**

#### A. Quy tắc nghiệp vụ (Business Rules)
*   Bảo vệ chủ phòng trước các tổn thất hư hỏng đồ đạc hoặc trốn tiền điện nước (đối với thuê tháng).
*   Quy định rõ số tiền cọc (`deposit_amount`), hình thức nộp cọc (Chuyển khoản trực tiếp cho chủ phòng hay hệ thống giữ hộ - escrow), và thời hạn hoàn cọc (ví dụ: hoàn trả trong vòng 24 giờ sau khi trả phòng nếu không phát sinh hư hỏng).

#### B. Giải pháp kỹ thuật đề xuất (Technical Spec)
*   **Trạng thái hiện tại:**
    *   Bảng `rooms` đã có trường `deposit` (float).
    *   Bảng `room_prices` đã có trường `deposit_amount` (float).
    *   Tuy nhiên, chưa có quy trình quản lý cọc trực tuyến hay hoàn trả cọc trên hệ thống thanh toán.
*   **Nâng cấp:**
    *   Cung cấp cấu hình `"Chính sách đặt cọc"` trên giao diện Đối tác.
    *   Khi khách đặt phòng, thông báo rõ số tiền cọc cần đóng và hình thức nộp (Nộp trực tiếp cho Host khi nhận phòng hay Chuyển khoản trước).

---

## Kịch bản Kiểm thử Nghiệp vụ (Verification & UAT Scenarios)

### Scenario 1: Kiểm thử Lọc Thú cưng kết hợp khoảng giá
*   **Mục tiêu:** Đảm bảo khách tìm được phòng cho phép mang chó mèo trong tầm ngân sách.
*   **Thực hiện:**
    1.  Chọn bộ lọc giá tối đa 1.000.000đ/đêm.
    2.  Tích chọn tiện ích "Cho phép mang thú cưng".
    3.  Kết quả trả về chỉ gồm các phòng có giá trị `cheapest_daily_price <= 1.000.000` và có tiện ích thú cưng trong DB.

### Scenario 2: Tính toán phụ thu thêm người lớn
*   **Mục tiêu:** Xác minh tổng tiền phòng tăng chính xác khi vượt quá số người tiêu chuẩn.
*   **Thực hiện:**
    1.  Phòng A giá 1.000.000đ/đêm, sức chứa tiêu chuẩn 2 người (`base_people = 2`), phụ thu thêm người là 200.000đ/người/đêm.
    2.  Khách đặt phòng A cho 3 người lớn, thời gian ở 2 đêm.
    3.  Hệ thống tính toán tổng tiền chính xác: `(1.000.000 + 200.000) * 2 = 2.400.000đ`.

---
**Chữ ký xác nhận nghiệp vụ:** Chuyên gia Nghiệp vụ Lưu trú & Khách sạn (Senior Hospitality Domain Expert)  
**Ngày phát hành:** 2026-06-07
