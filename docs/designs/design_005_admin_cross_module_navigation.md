# System Design: Liên kết điều hướng liên phân hệ Admin

## Document Information
- **Design ID:** D005
- **Created:** 2026-05-28
- **Status:** Draft
- **Input nguồn:** Audit UAT issue nhóm "workflow navigation gap" trên phân hệ Admin
- **Persona áp dụng:** Senior BA + UAT Tester (dịch vụ đặt phòng)

## 1. Mục tiêu thiết kế

Thiết kế tính năng điều hướng liên phân hệ để loại bỏ lỗi "đứt luồng nghiệp vụ" khi admin vận hành chuỗi:

`Partner -> Property -> Room -> Booking -> User`

### 1.1 Business Goals
- Giảm số lần click thủ công khi chuyển module.
- Giảm nguy cơ mất ngữ cảnh (nhầm partner/property/room).
- Tăng tốc độ xử lý nghiệp vụ duyệt đối tác và vận hành booking.
- Chuẩn hóa trải nghiệm drill-down/drill-through trên toàn admin.

### 1.2 Non-goals (ngoài phạm vi)
- Không thay đổi business rule phê duyệt/đặt phòng hiện tại.
- Không thay đổi schema DB trong phase đầu.
- Không làm mới toàn bộ UI, chỉ bổ sung CTA/link/filter context.

## 2. Phạm vi chức năng

### 2.1 Liên kết cần bổ sung (theo màn hình)

1) `admin/partner-information`
- Thêm action:
  - `Xem tài sản`
  - `Xem phòng`
  - `Xem booking`
- Điều hướng kèm context:
  - `partner_id`
  - `partner_name` (optional, phục vụ badge/header)

2) `admin/partner-information/detail/:id`
- Thêm CTA vùng header:
  - `Tài sản của đối tác`
  - `Phòng của đối tác`
  - `Booking của đối tác`

3) `admin/properties` và `admin/properties/detail/:property_id`
- Thêm action:
  - `Xem phòng của tài sản`
  - `Xem booking của tài sản` (nếu backend filter hỗ trợ qua room/property)
  - `Xem đối tác sở hữu`

4) `admin/rooms` và `admin/rooms/detail/:id`
- Thêm action:
  - `Xem tài sản`
  - `Xem booking của phòng`
  - `Xem đối tác`

5) `admin/booking-manage`
- Cột `Khách hàng`, `Phòng`, `Tài sản` thành link drill-down:
  - Khách hàng -> `admin/user-management/detail/:id`
  - Phòng -> `admin/rooms/detail/:id`
  - Tài sản -> `admin/properties/detail/:id`

6) `admin/partner-approval`
- Sau `Phê duyệt` thành công, hiển thị quick actions:
  - `Mở hồ sơ đối tác`
  - `Tạo tài sản đầu tiên`
  - `Tạo phòng đầu tiên`

### 2.2 Nguyên tắc UX bắt buộc
- Mọi liên kết cross-module phải giữ ngữ cảnh qua query params.
- Màn đích phải hiển thị "bộ lọc đang áp dụng" rõ ràng.
- Có CTA `Xóa ngữ cảnh`/`Reset filter`.
- Breadcrumb phải thể hiện nguồn đi tới (optional state `source`).

## 3. Điều hướng chuẩn hóa (Navigation Contract)

## 3.1 Query Params chuẩn

- `source`: màn nguồn (`partner-management`, `property-detail`, ...)
- `partner_id`
- `property_id`
- `room_id`
- `user_id`
- `from_approval` (boolean)

## 3.2 Mapping điều hướng chính

| From | Action | To | Query Params |
|------|--------|----|--------------|
| Partner list/detail | Xem tài sản | `/admin/properties` | `partner_id`, `source` |
| Partner list/detail | Xem phòng | `/admin/rooms` | `partner_id`, `source` |
| Partner list/detail | Xem booking | `/admin/booking-manage` | `partner_id`, `source` |
| Property list/detail | Xem phòng | `/admin/rooms` | `property_id`, `source` |
| Property list/detail | Xem booking | `/admin/booking-manage` | `property_id`, `source` |
| Room list/detail | Xem booking | `/admin/booking-manage` | `room_id`, `source` |
| Booking table | Click khách hàng | `/admin/user-management/detail/:id` | `source`, `booking_id` |
| Booking table | Click phòng | `/admin/rooms/detail/:id` | `source`, `booking_id` |
| Booking table | Click tài sản | `/admin/properties/detail/:id` | `source`, `booking_id` |

## 3.3 Yêu cầu backend/filter
- Các API list cần hỗ trợ filter theo `partner_id`, `property_id`, `room_id` nếu chưa có.
- Nếu chưa hỗ trợ full ở BE phase đầu:
  - FE fallback filter client-side có cảnh báo "dữ liệu lọc tạm thời".
  - Phase sau bắt buộc chuyển về server-side filter để đảm bảo hiệu năng.

## 4. Thiết kế UI chi tiết (mức implementation-ready)

## 4.1 Mẫu CTA row action chuẩn

- Group `Quản trị liên quan` trong menu `RowActions`:
  - `Xem tài sản`
  - `Xem phòng`
  - `Xem booking`
- Rule hiển thị:
  - Ẩn action không có ý nghĩa (vd: ở Room không hiện `Xem phòng`).
  - Disable nếu thiếu ID context.

## 4.2 Header contextual chips

Trên các trang list đích (`properties`, `rooms`, `booking-manage`) hiển thị chips:
- `Partner: #{id}`
- `Tài sản: #{id}`
- `Phòng: #{id}`
- `Nguồn: Partner Management`

Kèm nút:
- `Bỏ lọc ngữ cảnh`

## 4.3 Booking table drill-down

Chuyển text thuần thành link:
- `booking.user.name` -> user detail
- `booking.room.room_number` -> room detail
- `booking.room.property.name` -> property detail

Kèm tooltip:
- `Mở chi tiết người dùng/phòng/tài sản`

## 5. Quyền và kiểm soát truy cập

- Chỉ render action nếu role hiện tại có quyền màn đích.
- Nếu không quyền:
  - Ẩn action hoặc disable + tooltip `Bạn không có quyền truy cập`.
- Không để lộ ID nhạy cảm qua UI khi role không đủ quyền.

## 6. Theo dõi hiệu quả (KPI sau release)

- **KPI-01:** Giảm median click để xử lý một case từ Partner -> Booking ít nhất 30%.
- **KPI-02:** Giảm thời gian xử lý một case duyệt đối tác -> tạo tài sản/phòng ít nhất 25%.
- **KPI-03:** Giảm lỗi thao tác sai ngữ cảnh (manual report từ vận hành) ít nhất 40%.
- **KPI-04:** Tỷ lệ dùng quick action >= 60% sau 2 tuần rollout.

## 7. Test Acceptance Criteria (UAT-ready)

### UAT-NAV-01: Partner -> Properties context
- Khi bấm `Xem tài sản` tại partner id X:
  - Điều hướng đúng `admin/properties?partner_id=X`.
  - Danh sách chỉ hiển thị tài sản thuộc partner X.
  - Có chip context và nút bỏ lọc.

### UAT-NAV-02: Property -> Rooms context
- Khi bấm `Xem phòng` ở property id Y:
  - Điều hướng đúng `admin/rooms?property_id=Y`.
  - Danh sách phòng lọc đúng property Y.

### UAT-NAV-03: Room -> Booking context
- Khi bấm `Xem booking` ở room id Z:
  - Điều hướng đúng `admin/booking-manage?room_id=Z`.
  - Bảng booking chỉ còn booking thuộc room Z.

### UAT-NAV-04: Booking drill-down
- Click vào customer/room/property ở table booking mở đúng detail page tương ứng.

### UAT-NAV-05: Permission guard
- User không có quyền màn đích không thấy action hoặc action bị disable đúng chuẩn.

### UAT-NAV-06: Source backtracking
- Điều hướng từ nguồn có `source`, màn đích hiển thị được context nguồn.

## 8. Rủi ro và phương án giảm thiểu

| Risk | Impact | Mitigation |
|------|--------|------------|
| BE chưa hỗ trợ filter ID-context | High | Tạm filter FE phase đầu + plan BE phase 2 |
| Route detail không đồng nhất ID param | Medium | Chuẩn hóa helper build route tập trung |
| User bị "kẹt filter context" | Medium | Hiển thị chip rõ + nút "Bỏ lọc ngữ cảnh" |
| Quá nhiều action gây rối | Medium | Nhóm action liên quan + ưu tiên top 2 action chính |

## 9. Kế hoạch triển khai theo phase

### Phase 1 (Quick win - FE only, 1 sprint)
- Bổ sung CTA/link liên phân hệ.
- Truyền query params context.
- Hiển thị contextual chips + reset context.
- Booking table drill-down links.

### Phase 2 (FE + BE filter hardening, 1 sprint)
- Chuẩn hóa API filter `partner_id/property_id/room_id`.
- Chuyển toàn bộ lọc context về server-side.
- Bổ sung telemetry event cho quick actions.

### Phase 3 (Optimization, optional)
- Smart suggestions sau duyệt đối tác (`Tạo tài sản đầu tiên`).
- Shortcut keyboard cho điều hướng tác vụ thường dùng.

## 10. Deliverables cho team

- Tài liệu design này (D005).
- Danh sách task implementation theo module:
  - PartnerManagement
  - PropertyManager
  - RoomManager
  - BookingManage
  - PartnerApproval
- Bộ UAT test case nhóm `UAT-NAV-*`.

## 11. Quyết định thiết kế chính

- Chọn hướng **context-driven navigation** thay vì thêm dashboard mới.
- Ưu tiên **surgical changes** vào actions/filter hiện có để giảm rủi ro regression.
- Tách triển khai thành 2 phase để không block release bởi backend filter.

