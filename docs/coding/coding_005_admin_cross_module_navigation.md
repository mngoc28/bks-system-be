# Coding Document: Admin Cross-Module Navigation

## 1. Thông tin tài liệu
- **Coding ID:** CODING-ADM-NAV-005
- **Design input:** [docs/designs/design_005_admin_cross_module_navigation.md](../designs/design_005_admin_cross_module_navigation.md)
- **Plan input:** [docs/plans/plan_005_admin_cross_module_navigation.md](../plans/plan_005_admin_cross_module_navigation.md)
- **Mục tiêu coding:** Cung cấp đặc tả implement chi tiết để dev code trực tiếp, không cần trung gian quản lý task.

---

## 2. Phạm vi coding

Triển khai nhóm tính năng điều hướng liên phân hệ cho admin:
- Partner -> Property/Room/Booking
- Property -> Room/Booking/Partner
- Room -> Property/Booking/Partner
- Booking -> User/Room/Property (drill-down)
- Context filter chips + reset context

Không bao gồm:
- Refactor kiến trúc lớn ngoài phạm vi navigation.
- Đổi business rules nghiệp vụ duyệt/booking.

---

## 3. Contract kỹ thuật bắt buộc

## 3.1 Query params chuẩn
- `source`
- `partner_id`
- `property_id`
- `room_id`
- `user_id`
- `booking_id`
- `from_approval`

## 3.2 Quy tắc dùng query params
- Chỉ dùng snake_case cho key.
- Dùng helper chung để build URL; không hardcode từng chỗ.
- Khi vào màn list đích, phải parse params và set filter state tương ứng.
- Luôn có hành động clear context.

## 3.3 UX contract
- Action liên phân hệ đặt trong nhóm hành động rõ ràng (menu hoặc button group).
- Nếu user không có quyền màn đích: ẩn action hoặc disable có tooltip.
- Khi có context filter: hiển thị chips + nút `Bỏ lọc ngữ cảnh`.

---

## 4. File-level implementation (Frontend)

## 4.1 Tạo helper điều hướng dùng chung

### File mới
- `bks-system-fe/src/utils/adminNavigation.ts`

### Yêu cầu code
- Tạo type:
  - `AdminNavigationContext`
  - `AdminNavigationSource`
- Tạo hàm:
  - `buildAdminUrl(pathname: string, context?: AdminNavigationContext): string`
  - `parseAdminContext(search: string): AdminNavigationContext`
  - `clearAdminContext(search: string): string`
  - `toPropertiesByPartner(partnerId: number | string, source: string)`
  - `toRoomsByPartner(partnerId: number | string, source: string)`
  - `toBookingsByPartner(partnerId: number | string, source: string)`
  - `toRoomsByProperty(propertyId: number | string, source: string)`
  - `toBookingsByRoom(roomId: number | string, source: string)`
- Không thêm logic business vào helper này.

### Acceptance
- URL build đúng, không trùng params.
- Parse/clear hoạt động ổn định với URL có sẵn params khác (`page`, `per_page`, ...).

---

## 4.2 Partner Management + Partner Detail

### Files cần sửa
- `bks-system-fe/src/pages/Admin/PartnerManagement/components/PartnerTable.tsx`
- `bks-system-fe/src/pages/Admin/PartnerManagement/index.tsx`
- `bks-system-fe/src/pages/Admin/PartnerDetail/index.tsx`

### Yêu cầu code
- Bổ sung action:
  - `Xem tài sản`
  - `Xem phòng`
  - `Xem booking`
- Dùng helper `adminNavigation.ts` để navigate.
- `source` chuẩn:
  - `partner-management` (list)
  - `partner-detail` (detail)

### Acceptance
- Từ list/detail partner đi đúng màn và mang context `partner_id`.
- Không phá action `View/Edit` hiện có.

---

## 4.3 Property module

### Files cần sửa
- `bks-system-fe/src/pages/Admin/PropertyManager/components/PropertyTableRow.tsx`
- `bks-system-fe/src/pages/Admin/PropertyManager/index.tsx`
- `bks-system-fe/src/pages/Admin/PropertyDetail/index.tsx`

### Yêu cầu code
- Thêm action:
  - `Xem phòng`
  - `Xem booking`
  - `Xem đối tác`
- Trong `PropertyDetail`, thêm CTA header để đi Room/Booking theo `property_id`.
- Nếu dữ liệu property có `user/partner` id, cho phép nhảy về detail đối tác tương ứng.

### Acceptance
- Điều hướng từ property sang room/booking đúng theo context.
- Không ảnh hưởng flow edit images/edit property.

---

## 4.4 Room module

### Files cần sửa
- `bks-system-fe/src/pages/Admin/RoomManager/components/RoomTable.tsx`
- `bks-system-fe/src/pages/Admin/RoomManager/index.tsx`
- `bks-system-fe/src/pages/Admin/RoomDetail/components/RoomDetailView.tsx`

### Yêu cầu code
- Thêm action:
  - `Xem tài sản`
  - `Xem booking`
  - `Xem đối tác`
- `RoomDetailView`: property name hiển thị dạng clickable (khi có `property_id`).

### Acceptance
- Từ room đi được sang property/booking nhanh.
- Không ảnh hưởng logic image modal.

---

## 4.5 Booking module (drill-down)

### Files cần sửa
- `bks-system-fe/src/pages/Admin/BookingManage/components/BookingTable.tsx`
- `bks-system-fe/src/pages/Admin/BookingManage/index.tsx`

### Yêu cầu code
- Cột `Khách hàng`, `Phòng`, `Tài sản` chuyển thành link drill-down.
- Dùng route/detail tương ứng theo id thực tế từ API.
- Truyền `source=booking-manage` và `booking_id` để trace.

### Acceptance
- Click vào text ở 3 cột mở đúng detail page.
- Action row menu cũ giữ nguyên.

---

## 4.6 Context chips component dùng chung

### File mới
- `bks-system-fe/src/components/admin/ContextFilterChips.tsx`

### Props đề xuất
- `context: AdminNavigationContext`
- `onClear: () => void`
- `onRemoveKey?: (key: keyof AdminNavigationContext) => void`

### Yêu cầu code
- Render chips khi có `partner_id/property_id/room_id/user_id`.
- Hiển thị `source` dạng human-readable.
- Có nút `Bỏ lọc ngữ cảnh`.

### Nơi gắn component
- `PropertyManager/index.tsx`
- `RoomManager/index.tsx`
- `BookingManage/index.tsx`

### Acceptance
- Chips luôn phản ánh đúng query params hiện tại.
- Clear context đưa màn về trạng thái lọc mặc định.

---

## 5. Backend filter hardening (Phase 2)

> Nếu backend đã hỗ trợ đủ filter thì chỉ cần mapping FE query params tương ứng.

## 5.1 Properties API
- Hỗ trợ filter `partner_id`.
- Đảm bảo pagination/sort vẫn đúng khi lọc.

## 5.2 Rooms API
- Hỗ trợ `partner_id`, `property_id`.

## 5.3 Bookings API
- Hỗ trợ `partner_id`, `property_id`, `room_id`.

## 5.4 Backend acceptance
- Không có N+1 rõ rệt khi filter.
- Trả đúng `total/current_page/last_page` theo context.
- Validation kiểu dữ liệu id (integer-like).

---

## 6. Quyền truy cập

- FE:
  - Chỉ hiển thị action khi role có thể truy cập route đích.
- BE:
  - Không nới quyền endpoint hiện có.
  - Filter không làm lộ dữ liệu ngoài phạm vi role.

---

## 7. Test checklist cho dev trước khi bàn giao QA

## 7.1 Manual smoke bắt buộc
- Partner list -> Xem tài sản/phòng/booking.
- Partner detail -> Xem tài sản/phòng/booking.
- Property list/detail -> Xem phòng + booking.
- Room list/detail -> Xem property + booking.
- Booking table -> click customer/room/property.
- Context chips hiển thị đúng và clear đúng.

## 7.2 Regression bắt buộc
- Sorting, pagination, filter cũ của từng module vẫn hoạt động.
- Edit/delete/view chi tiết cũ không lỗi.
- Không xuất hiện loop navigate hoặc mất state bất thường.

## 7.3 UAT mapping
- Map trực tiếp theo `UAT-NAV-01` -> `UAT-NAV-06` trong design D005.

---

## 8. Definition of Done

- [ ] Hoàn thành toàn bộ thay đổi FE phase quick win.
- [ ] Context params chuẩn hóa bằng helper dùng chung.
- [ ] Có contextual chips + clear context tại các list đích.
- [ ] Booking drill-down links chạy đúng.
- [ ] Backend filter support đầy đủ (hoặc đã xác nhận sẵn có).
- [ ] Manual smoke + regression pass.
- [ ] Không phát sinh blocker mới trên admin core flows.

---

## 9. Ghi chú triển khai

- Ưu tiên merge theo cụm nhỏ để dễ review:
  1) helper + chips
  2) partner/property
  3) room/booking
  4) backend filters
- Nếu gặp thiếu dữ liệu id trong payload booking/property/room:
  - Bổ sung từ API response trước khi gắn drill-down link.

