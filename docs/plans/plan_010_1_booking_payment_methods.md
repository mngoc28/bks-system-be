# Kế hoạch triển khai: Chọn Phương thức thanh toán khi đặt phòng (Payment Method Selection)

## Document Information
- **Plan ID:** PLAN-PAY-010.1
- **Created:** 2026-06-01
- **Status:** 📝 DRAFT (Under Review)
- **Related Plan:** [plan_010_booking_deposit_and_financial_rules.md](file:///d:/ASUS/intern/bks-datn/bks-system-be/docs/plans/plan_010_booking_deposit_and_financial_rules.md)
- **Related SRS:** [business-domain-rules.md](file:///d:/ASUS/intern/bks-datn/docs/features/business-domain-rules.md)

---

## 1. Executive Summary (Tổng quan)
Tài liệu này đặc tả kế hoạch tích hợp tính năng **Chọn phương thức thanh toán** lúc đặt phòng ngắn hạn cho khách hàng (End-User) giữa hai hình thức: **Thanh toán trực tuyến (Online)** và **Thanh toán tại quầy (Pay at counter)**. 

Quy trình này tích hợp trực tiếp với chính sách đặt cọc linh hoạt (Dynamic Deposit Policy) nhằm đảm bảo:
1. Khách hàng có sự linh hoạt trong việc lựa chọn hình thức thanh toán.
2. Bảo vệ chủ nhà (Host) khỏi rủi ro bùng phòng (No-Show) bằng cách khóa tùy chọn thanh toán tại quầy nếu đơn phòng thuộc diện bắt buộc cọc (ngày lễ, cuối tuần, sát giờ).
3. Cung cấp luồng thanh toán giả lập phía Backend để phục vụ kiểm thử và demo toàn trình trước khi kết nối cổng thanh toán thật.

---

## 2. Luồng Vận Hành Đề Xuất (Workflow)

```text
                  [ Khách đặt phòng ngắn hạn ]
                                │
             API kiểm tra chính sách cọc (Dynamic Policy)
                                │
           ┌────────────────────┴────────────────────┐
     [ Có yêu cầu cọc ]                        [ Không yêu cầu cọc ]
           │                                         │
Disable "Thanh toán tại quầy"               Enable cả 2 phương thức
Bắt buộc chọn "Thanh toán Online"          Khách tùy chọn Online / Tại quầy
           │                                         │
    Tạo đơn PENDING                           Tạo đơn PENDING
           │                                         │
Chuyển hướng Mock Checkout                ┌──────────┴──────────┐
           │                        [Chọn Online]        [Chọn Tại Quầy]
     Nhấn Pay thành công                  │                     │
           │                       Chuyển hướng         Lưu đơn PENDING
           │                       Mock Checkout        Chờ Host duyệt
           │                              │                     │
           │                       Nhấn Pay thành công          │
           │                              │                     │
           └──────────────────────────────┼─────────────────────┘
                                          ▼
                         [ Landing Trang BookingSuccess ]
```

---

## 3. Chi tiết Công việc & Phân công (Tasks)

### 3.1 Backend (Laravel API)

#### [T1] Migration Cơ sở dữ liệu
- **Mô tả:** Bổ sung cột `payment_method` vào bảng `bookings`.
- **Đặc tả trường:** `string('payment_method', 50)->nullable()`. Giá trị lưu trữ: `'online'`, `'pay_at_counter'`.
- **Tệp tin ảnh hưởng:** `database/migrations/2026_06_01_233000_add_payment_method_to_bookings_table.php` (mới).

#### [T2] Cập nhật Validation & Logic Tạo đơn phòng
- **Mô tả:**
  - Thêm validation bắt buộc trường `payment_method` trong `BookingValidation->userCreateBookingValidation()`.
  - Cập nhật logic trong `BookingService->handleUserCreateBooking()`:
    1. Lưu trường `payment_method` vào database.
    2. Gọi `DynamicDepositPolicyService->calculateRequiredDeposit()` để kiểm tra yêu cầu cọc.
    3. Nếu đơn hàng bắt buộc cọc, kiểm tra: Nếu khách gửi lên `payment_method === 'pay_at_counter'`, từ chối tạo đơn và trả về mã lỗi 400 (Validation Error): *"Tùy chọn thanh toán tại quầy không khả dụng vì phòng yêu cầu đặt cọc trong khoảng thời gian này."*
    4. Nếu `payment_method === 'online'`: Trả về trường `payment_url` dẫn đến cổng thanh toán mock-up (`/api/v1/payments/mock-checkout?booking_id={id}`).
- **Tệp tin ảnh hưởng:**
  - `app/Http/Validations/BookingValidation.php`
  - `app/Services/BookingService.php`

#### [T3] Xây dựng Cổng thanh toán giả lập (Mock Payment Gateway)
- **Mô tả:**
  - Tạo `MockPaymentController` xử lý yêu cầu GET `/api/v1/payments/mock-checkout`. Trả về một giao diện HTML đơn giản chứa thông tin đơn hàng và nút "Xác nhận thanh toán (Simulate Success)".
  - Khi click xác nhận:
    1. Cập nhật `bookings.payment_collected_at = now()`.
    2. Cập nhật `bookings.status = 1` (CONFIRMED).
    3. Cập nhật trạng thái cọc tương ứng trong bảng `booking_deposits` thành `confirmed_by_partner`.
    4. Gửi email xác nhận đặt phòng thành công cho khách hàng.
    5. Điều hướng người dùng quay trở lại Frontend thông qua URL: `{frontend_url}/booking-success?bookingId={id}&bookingCode={code}&email={email}&paymentStatus=success`.
- **Tệp tin ảnh hưởng:**
  - `app/Http/Controllers/MockPaymentController.php` (mới)
  - `routes/api.php`

---

### 3.2 Frontend (React Client)

#### [T4] Giao diện Checkout Đặt phòng (`BookingPage.tsx`)
- **Mô tả:**
  - Tại **Bước 1 (Thông tin)**:
    - Bổ sung ô chọn phương thức thanh toán (Radio group hoặc Card Selection) với 2 lựa chọn:
      1. *Thanh toán trực tuyến (Online):* VNPay/Ví điện tử.
      2. *Thanh toán tại quầy (Pay at counter):* Trả trực tiếp khi nhận phòng.
  - Tích hợp logic kiểm tra cọc động:
    - Frontend sẽ tính toán ngày đặt phòng, nếu thuộc cuối tuần (Thứ 7, Chủ nhật) hoặc hệ thống cấu hình cọc, tiến hành:
      - Disable tùy chọn "Thanh toán tại quầy".
      - Hiện cảnh báo màu cam: *"⚠️ Phòng yêu cầu đặt cọc trực tuyến vào cuối tuần / ngày lễ."*
  - Tại **Bước 2 (Xác nhận)**:
    - Cập nhật luồng nhấn nút đặt phòng:
      - Gửi `payment_method` lên API tạo đơn.
      - Nếu kết quả trả về chứa `payment_url`, thực hiện chuyển hướng trình duyệt (`window.location.href = data.payment_url`) đến trang thanh toán.
      - Nếu không có `payment_url` (đối với thanh toán tại quầy), đi trực tiếp đến trang `/booking-success` qua router nội bộ và truyền state.
- **Tệp tin ảnh hưởng:**
  - `bks-system-fe/src/pages/EndUser/Booking/BookingPage.tsx`

#### [T5] Giao diện Đặt phòng thành công (`BookingSuccess`)
- **Mô tả:**
  - Thêm cơ chế đọc URL query parameters để hứng dữ liệu trả về từ redirect Mock Payment (`bookingId`, `bookingCode`, `email`, `paymentStatus`).
  - Nếu phát hiện có tham số trong URL, gọi API `bookingApi.lookupBooking` để lấy thông tin chi tiết của đơn vừa thanh toán.
  - Cập nhật hiển thị banner động:
    - **Trường hợp đã thanh toán online thành công:** Hiển thị banner màu xanh lá cây với thông điệp: *"Đặt phòng đã được thanh toán & xác nhận thành công!"*, đồng thời ẩn countdown grace period.
    - **Trường hợp thanh toán tại quầy:** Hiển thị banner màu vàng: *"Đặt phòng tạm thời đã được ghi nhận. Chờ Host xác nhận phòng trống."*
    - **Trường hợp chờ đóng cọc (chuyển khoản ngoài):** Hiển thị banner đỏ kèm countdown thanh toán.
- **Tệp tin ảnh hưởng:**
  - `bks-system-fe/src/pages/EndUser/BookingSuccess/index.tsx`

## 4. Tùy chọn Tối ưu: Thanh toán QR Động qua VietQR (Dynamic QR Code)

Nhằm tối ưu hóa luồng thanh toán trực tuyến (Online) mà không cần tích hợp các cổng thanh toán doanh nghiệp phức tạp (như VNPay/GMO), hệ thống đề xuất phương án sử dụng **VietQR Động** hiển thị tại trang thanh toán giả lập hoặc trang thông tin cọc:

### 4.1 Cơ chế tạo QR động (Dynamic QR Generation)
Thay vì sử dụng QR tĩnh cố định (dẫn đến lỗi chuyển khoản sai tiền hoặc sai nội dung), Frontend hoặc Backend sẽ sinh mã QR động theo chuẩn Napas 247 thông qua dịch vụ công cộng miễn phí (ví dụ: `vietqr.io`):
- **Cấu trúc URL sinh mã:**
  `https://img.vietqr.io/image/<BANK_ID>-<ACCOUNT_NO>-<TEMPLATE>.jpg?amount=<AMOUNT>&addInfo=<DESCRIPTION>&accountName=<ACCOUNT_NAME>`
- **Các tham số động:**
  - `BANK_ID`: Mã ngân hàng của Host/Hệ thống (ví dụ: `MB`, `VCB`, `ICB`).
  - `ACCOUNT_NO`: Số tài khoản nhận tiền.
  - `TEMPLATE`: Giao diện QR (`qr_only` để ẩn thông tin text thừa, `compact` hoặc `print`).
  - `amount`: Số tiền thanh toán cọc hoặc toàn bộ tiền phòng (`total_amount` hoặc `deposit_amount`).
  - `addInfo`: Nội dung chuyển khoản định danh (khớp chính xác với `booking_code` để phục vụ đối soát, ví dụ: `RM-2026-000617`).
  - `accountName`: Tên tài khoản ngân hàng (viết hoa không dấu, ví dụ: `NGUYEN VAN A`).

### 4.2 Lợi ích vận hành
- App ngân hàng của khách quét mã sẽ tự điền: Số tài khoản, số tiền chính xác, nội dung chuyển khoản chính xác $\rightarrow$ Loại bỏ hoàn toàn lỗi gõ sai thông tin của người dùng.
- Chi phí bằng 0 và không cần đăng ký API ngân hàng phức tạp.

### 4.3 Khả năng tự động hóa đối soát (Auto-Reconciliation)
Hệ thống có thể nâng cấp trong tương lai bằng cách kết nối với các dịch vụ đồng bộ số dư tài khoản cá nhân (như PayOS, Casso, SePay) qua webhook để tự động chuyển trạng thái đơn hàng sang `CONFIRMED` mà không cần Host duyệt thủ công.

---

## 5. Kế hoạch kiểm thử (Verification Plan)

### Kiểm thử tự động (Automated Tests)
- **Unit Test Backend:** Viết test case trong `tests/Feature/BookingPaymentTest.php` để đảm bảo:
  1. Gửi request đặt phòng ngày lễ với hình thức tại quầy sẽ bị từ chối (HTTP 400).
  2. Gửi request ngày lễ với hình thức online sẽ trả về `payment_url` (HTTP 201).
  3. Gọi webhook/nhấn nút giả lập thanh toán sẽ cập nhật đúng trạng thái booking (`CONFIRMED`) và trạng thái cọc (`confirmed_by_partner`).

### Kiểm thử thủ công (Manual UAT)
1. **Case 1: Đặt ngày thường chọn Thanh toán tại quầy**
   - Đặt phòng từ thứ 2 đến thứ 3. Chọn "Thanh toán tại quầy" và tiến hành đặt.
   - Kết quả: Không bị chặn cọc, đi thẳng đến `BookingSuccess` dạng màu vàng chờ duyệt.
2. **Case 2: Đặt ngày thường chọn Thanh toán Online**
   - Đặt phòng từ thứ 2 đến thứ 3. Chọn "Thanh toán trực tuyến".
   - Kết quả: Chuyển hướng sang trang mock, click thành công, quay về `BookingSuccess` màu xanh lá cây.
3. **Case 3: Đặt cuối tuần**
   - Đặt phòng vào thứ Bảy.
   - Kết quả: Tùy chọn "Thanh toán tại quầy" bị khóa. Chỉ cho chọn "Thanh toán trực tuyến". Đi qua luồng mock và hoàn tất.
