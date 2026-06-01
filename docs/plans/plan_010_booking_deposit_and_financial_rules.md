# Implementation Plan: Đặt cọc linh hoạt & Nghiệp vụ tài chính nâng cao (Booking Deposit & Financial Rules)

## Document Information
- **Plan ID:** PLAN-DEP-010
- **Created:** 2026-06-01
- **Status:** 📝 DRAFT (Under Review)
- **Related Design:** N/A (Embedded in Plan)
- **Related SRS:** [docs/SRC/srs_booking_deposit_and_financial_rules.md](../SRC/srs_booking_deposit_and_financial_rules.md)

---

## Executive Summary
Kế hoạch này chi tiết hóa việc triển khai hệ thống **Đặt cọc linh hoạt (Dynamic Deposit Policy)** cho đơn ngắn hạn, **Ký quỹ (Online Escrow)** cho đơn dài hạn, tích hợp kiểm tra điều kiện Check-in cứng, tự động giải phóng phòng khi quá hạn cọc và đồng bộ quỹ phòng thời gian thực (Real-time Inventory Sync) lên các sàn OTA qua Channel Manager.

Mục tiêu cốt lõi là tối thiểu hóa rủi ro bùng phòng (No-Show), tối đa hóa tỷ lệ lấp đầy (Occupancy Rate) trong mùa thấp điểm và tối ưu hóa quy trình thủ công cho Lễ tân tại quầy (Front Desk SOP).

Dự án được chia làm 4 Phase với tổng effort ước tính khoảng **64 giờ lập trình và kiểm thử**.

---

## Phase Overview

| Phase | Name | Tasks | Dependencies | Est. Hours |
|---|---|---|---|---|
| 1 | Foundation (Cơ sở hạ tầng & Database) | 3 | None | 10 |
| 2 | Core Logic (Đặt cọc & Hủy phòng tự động) | 4 | Phase 1 | 18 |
| 3 | Flow Integration & Channel Manager Sync | 4 | Phase 2 | 16 |
| 4 | Frontend Portal & Communication UI | 5 | Phase 3 | 20 |

---

## Dependency Graph

```text
Phase 1: Foundation
├── [T1.1] Migration DB (deposits, config fields) ──┐
├── [T1.2] Eloquent Model (BookingDeposit) ◄────────┤
└── [T1.3] DepositRepository ◄──────────────────────┤ (blocks Phase 2)
                                                    
Phase 2: Core Logic
├── [T2.1] DepositService (Escrow vs Direct) ◄──────┤
├── [T2.2] Dynamic Deposit Policy Ruleset ◄─────────┤
├── [T2.3] Auto-Cancel Job (Grace Period) ◄─────────┤ (blocks Phase 3)
└── [T2.4] DB Transaction & lockForUpdate ──────────┘

Phase 3: Flow Integration & Channel Manager Sync
├── [T3.1] Check-in Gate Verification ◄─────────────┤
├── [T3.2] Room Cancellation & Refund Handler ◄─────┤
├── [T3.3] PMS Real-time Inventory Release Job ◄────┤ (blocks Phase 4)
└── [T3.4] Last-Minute Deals Engine ────────────────┘

Phase 4: Frontend Portal & Communication UI
├── [T4.1] BookingSuccess Checklist Wizard UI
├── [T4.2] Guest PNG Voucher & print media CSS
├── [T4.3] Email room-booking.blade.php Customization
├── [T4.4] Front Desk Confirmation Panel
└── [T4.5] E2E Verification & Test Suite Run
```

---

## Phase 1: Foundation (Cơ sở hạ tầng & Database)

**Goal:** Thiết lập bảng cơ sở dữ liệu cho đặt cọc, mở rộng các bảng hiện tại và xây dựng tầng Model/Repository.
**Duration Estimate:** 10 giờ
**Dependencies:** Không

### Tasks

#### [T1.1] Migration cơ sở dữ liệu (Database Migrations)
- **Description:** 
  - Tạo bảng `booking_deposits` (`id`, `booking_id`, `amount`, `status`, `receipt_path`, timestamps) phục vụ ghi nhận cọc.
  - Bổ sung cột `deposit_amount` vào bảng `bookings`.
  - Bổ sung trường cấu hình cọc động trong bảng cài đặt hoặc bảng hạng phòng.
- **Acceptance Criteria:**
  - [ ] Chạy thành công `php artisan migrate` và rollback sạch sẽ `php artisan migrate:rollback`.
  - [ ] Các chỉ mục (Index) trên `booking_id` và `status` được thiết lập chính xác.
- **Files Affected:**
  - `database/migrations/2026_06_01_130001_create_booking_deposits_table.php` (mới)
  - `database/migrations/2026_06_01_130002_add_deposit_fields_to_bookings_table.php` (mới)
- **Test Scenarios:** Chạy và rollback migrations trên môi trường local.

#### [T1.2] Xây dựng Eloquent Models & Relations
- **Description:** Tạo Model `BookingDeposit` và thiết lập các quan hệ: `Booking->hasOne(BookingDeposit)`, `BookingDeposit->belongsTo(Booking)`.
- **Acceptance Criteria:**
  - [ ] Khai báo đúng các thuộc tính `$fillable` và `$casts` (amount -> decimal, status -> string).
- **Files Affected:**
  - `app/Models/BookingDeposit.php` (mới)
  - `app/Models/Booking.php` (chỉnh sửa)
- **Dependencies:** [T1.1]

#### [T1.3] Xây dựng DepositRepository
- **Description:** Tạo các interface và implementation repository để quản lý truy vấn liên quan đến cọc. Đăng ký trong `RepositoryServiceProvider`.
- **Files Affected:**
  - `app/Repositories/Contracts/BookingDepositRepositoryInterface.php` (mới)
  - `app/Repositories/Eloquent/EloquentBookingDepositRepository.php` (mới)
  - `app/Providers/RepositoryServiceProvider.php` (chỉnh sửa)
- **Dependencies:** [T1.2]

---

## Phase 2: Core Logic (Đặt cọc & Hủy phòng tự động)

**Goal:** Xây dựng nghiệp vụ xử lý đặt cọc trực tuyến/chuyển khoản và công cụ tự động quét hủy phòng khi hết hạn cọc.
**Duration Estimate:** 18 giờ
**Dependencies:** Phase 1 complete

### Tasks

#### [T2.1] Xây dựng DepositService
- **Description:** Xử lý nghiệp vụ đặt cọc: tạo yêu cầu ký quỹ (`held_in_escrow`), nhận webhook cổng thanh toán, khách hàng tải biên lai chuyển khoản (`payment_submitted`), Host phê duyệt biên lai (`confirmed_by_partner`), khấu trừ tài sản và hoàn cọc khi check-out.
- **Acceptance Criteria:**
  - [ ] Đổi trạng thái cọc chính xác theo quy trình.
  - [ ] Ném ra các exception nghiệp vụ phù hợp khi trạng thái sai lệch.
- **Files Affected:**
  - `app/Services/DepositService.php` (mới)
- **Dependencies:** [T1.3]

#### [T2.2] Xây dựng Dynamic Deposit Policy Ruleset
- **Description:** Hiện thực hóa logic kiểm tra xem phòng này, vào ngày này có yêu cầu cọc hay không (mùa thấp điểm/ngày thường: không cọc; cuối tuần/ngày lễ: bắt buộc cọc). Căn cứ theo cấu hình hạng phòng và thời điểm tạo đơn (Lead Time).
- **Acceptance Criteria:**
  - [ ] Trả về số tiền cọc cần đóng ($0$ hoặc một phần tỷ lệ % hoặc 100%) tùy thuộc cấu hình đầu vào.
- **Files Affected:**
  - `app/Services/DynamicDepositPolicyService.php` (mới)
- **Dependencies:** [T1.3]

#### [T2.3] Job tự động quét và hủy đơn quá hạn đặt cọc (Grace Period Daemon)
- **Description:** Tạo Console Command/Job chạy định kỳ (mỗi 5-10 phút) quét các đơn booking ở trạng thái `PENDING` nhưng quá hạn thanh toán cọc (2 tiếng cho booking sát ngày, 12-24 tiếng cho booking xa ngày) để tự động chuyển sang trạng thái `cancelled`.
- **Acceptance Criteria:**
  - [ ] Gom đúng các đơn quá hạn và chuyển trạng thái đơn sang `cancelled`.
  - [ ] Ghi log chi tiết lý do hủy.
- **Files Affected:**
  - `app/Jobs/CancelExpiredUnpaidBookingsJob.php` (mới)
  - `app/Console/Kernel.php` (chỉnh sửa)
- **Dependencies:** [T2.1], [T2.2]

#### [T2.4] Tích hợp Cơ chế Khóa đồng thời (Concurrency Safeguards)
- **Description:** Sử dụng `lockForUpdate()` trong giao dịch DB (Database Transaction) ở quá trình kiểm tra phòng trống và tự động hủy đơn nhằm tránh xảy ra tình trạng lấp trùng phòng (Double-booking) hoặc xung đột dữ liệu.
- **Acceptance Criteria:**
  - [ ] Thực hiện kiểm tra bằng unit test mô phỏng race conditions.
- **Files Affected:**
  - `app/Services/BookingService.php` (chỉnh sửa)
- **Dependencies:** [T1.2]

---

## Phase 3: Flow Integration & Channel Manager Sync

**Goal:** Chốt chặn quy trình Check-in, Hủy cọc tự động, đồng bộ dữ liệu phòng thời gian thực lên OTA và tự động tạo Deal giờ chót.
**Duration Estimate:** 16 giờ
**Dependencies:** Phase 2 complete

### Tasks

#### [T3.1] Check-in Gate Verification
- **Description:** Sửa đổi API `check-in` của Host. Thêm middleware hoặc validator kiểm tra điều kiện cứng: Cọc phải ở trạng thái `held_in_escrow` hoặc `confirmed_by_partner` ĐỒNG THỜI hợp đồng thuê nhà phải ở trạng thái `Signed` (đối với thuê dài hạn). Nếu không thỏa mãn, trả về lỗi 422 Unprocessable Entity.
- **Acceptance Criteria:**
  - [ ] Chặn đứng hành vi check-in sai luật.
- **Files Affected:**
  - `app/Http/Controllers/Partner/PartnerBookingController.php` (chỉnh sửa)
  - `app/Http/Validations/CheckInValidation.php` (chỉnh sửa)
- **Dependencies:** [T2.1]

#### [T3.2] Room Cancellation & Refund Handler
- **Description:** Logic tính toán tiền hoàn và phạt cọc khi hủy phòng:
  - Hủy miễn phí: Hoàn 100% cọc cho khách.
  - Hủy mất phí: Chuyển tiền phạt cho Host, ghi nhận GMV từ tiền phạt cọc này và thu 5% hoa hồng cho hệ thống.
- **Files Affected:**
  - `app/Services/BookingService.php` (chỉnh sửa)
  - `app/Services/SettlementService.php` (chỉnh sửa)
- **Dependencies:** [T2.1]

#### [T3.3] PMS Real-time Inventory Release Job
- **Description:** Khi đơn đặt phòng không cọc bị hủy tự động (ví dụ lúc 18:00), hệ thống lập tức kích hoạt Event `RoomInventoryReleased` để đẩy thông tin cập nhật quỹ phòng (+1 phòng khả dụng) sang các kênh OTA thông qua Channel Manager trong vòng tối đa 5 giây.
- **Files Affected:**
  - `app/Events/RoomInventoryReleased.php` (mới)
  - `app/Listeners/SyncInventoryToChannelManager.php` (mới)
  - `app/Providers/EventServiceProvider.php` (chỉnh sửa)
- **Dependencies:** [T2.3]

#### [T3.4] Last-Minute Deals Engine
- **Description:** Cung cấp cơ chế tự động cấu hình giảm giá phòng trống (Flash Sale 20-30%) cho các đơn phòng được giải phóng muộn sau 16:00/18:00 hàng ngày trên kênh OTA/Website trực tuyến.
- **Files Affected:**
  - `app/Services/DynamicPricingService.php` (chỉnh sửa)
- **Dependencies:** [T3.3]

---

## Phase 4: Frontend Portal & Communication UI

**Goal:** Xây dựng màn hình đặt phòng thành công, tích hợp tính năng tải ảnh PNG Stay Voucher, tối ưu CSS Print, chỉnh sửa mẫu email và kiểm thử E2E.
**Duration Estimate:** 20 giờ
**Dependencies:** Phase 3 complete

### Tasks

#### [T4.1] Tối ưu hóa giao diện Đặt phòng thành công (BookingSuccess Checklist)
- **Description:** 
  - Thiết kế trang thành công tối giản (không hiển thị bảng chi tiết giá để chống nhiễu).
  - Tích hợp **Checklist Hướng dẫn 3 bước**: Mở email $\rightarrow$ Đăng nhập/Kích hoạt $\rightarrow$ Nhận Voucher/Ký hợp đồng.
  - Sử dụng mã màu xanh lá cây `#10b981` cho từ khóa thành công.
- **Files Affected:**
  - `bks-system-fe/src/pages/EndUser/BookingSuccess/index.tsx` (chỉnh sửa)
- **Dependencies:** [T3.1]

#### [T4.2] Tải ảnh Stay Voucher PNG & Tối ưu hóa CSS In ấn (@media print)
- **Description:**
  - Tích hợp thư viện `html2canvas` để cho phép khách hàng tải ảnh PNG Stay Voucher chất lượng cao về điện thoại.
  - Loại bỏ hoàn toàn nút tải PDF client-side để tránh vỡ font.
  - Cấu hình CSS Print (`@media print`) trong tệp stylesheet nhằm ẩn hoàn toàn sidebar, header và các nút thao tác khi người dùng dùng tổ hợp phím `Ctrl + P` để in phiếu xác nhận A4.
- **Files Affected:**
  - `bks-system-fe/src/pages/EndUser/StayVoucher/index.tsx` (mới)
  - `bks-system-fe/src/styles/print.css` (mới)

#### [T4.3] Email `room-booking.blade.php` Customization
- **Description:**
  - Khách hàng mới: Gửi email gồm 2 liên kết (Thiết lập mật khẩu và Xem chi tiết booking).
  - Khách hàng cũ: Gửi email chỉ gồm 1 liên kết (Xem chi tiết booking).
  - Che giấu URL trần để tránh bị đánh dấu Spam.
- **Files Affected:**
  - `resources/views/emails/room-booking.blade.php` (chỉnh sửa)

#### [T4.4] Front Desk Confirmation Panel for Host
- **Description:** 
  - Giao diện dành cho Lễ tân tại Partner Portal: Màn hình danh sách đơn chưa cọc trong ngày, nút gọi nhanh/Zalo, và nút xác thực thủ công trạng thái cọc hoặc hủy đơn trực tiếp.
- **Files Affected:**
  - `bks-system-fe/src/pages/Partner/Bookings/FrontDeskPanel.tsx` (mới)

#### [T4.5] Kiểm thử E2E & Chạy Test Suite
- **Description:** Viết Unit/Feature test cho APIs đặt cọc, chạy test tự động và build dự án Frontend.
- **Acceptance Criteria:**
  - [ ] Toàn bộ test suite PHPUnit chạy thành công.
  - [ ] Chạy lệnh `npm run build` trên Frontend không phát sinh lỗi compiler.
- **Files Affected:**
  - `tests/Feature/DepositFlowTest.php` (mới)

---

## Conflict Analysis & Mitigation
*   **Conflict 1 (Hủy phòng chồng chéo):** Hệ thống tự động hủy đơn chờ cọc trùng thời điểm khách đang chuyển tiền ngoài.
    *   *Mitigation:* Thiết lập thời gian đệm (Buffer Time 5-10 phút) trước khi thực hiện Job hủy để lễ tân có thời gian cập nhật tay nếu có biên lai.
*   **Conflict 2 (Đồng bộ tồn kho):** Đồng bộ tồn kho lên OTA bị lag gây Overbooking.
    *   *Mitigation:* Sử dụng hàng đợi có độ ưu tiên cao (High-priority Queue) cho Listener đồng bộ tồn kho.

---

## Testing Strategy
*   **Kiểm thử Đơn vị (Unit Tests):** Đảm bảo class `DynamicDepositPolicyService` tính đúng số tiền cọc theo mùa vụ/cuối tuần.
*   **Kiểm thử Tích hợp (Integration Tests):** Mô phỏng khách hàng thanh toán cọc trực tuyến qua webhook cổng thanh toán và cập nhật trạng thái `held_in_escrow`.
*   **Kiểm thử Toàn trình (E2E):** Khách đặt phòng ngắn hạn $\rightarrow$ Chờ quá 2 tiếng không đóng cọc $\rightarrow$ Hệ thống tự động hủy phòng $\rightarrow$ Hệ thống đẩy tồn kho lên sàn trực tuyến $\rightarrow$ Khách hàng mới đặt phòng giờ chót thành công.

---
**Duyệt kế hoạch triển khai (Approved by Lead Architect):**
*Chữ ký điện tử đã xác nhận*
**Ngày:** 2026-06-01
