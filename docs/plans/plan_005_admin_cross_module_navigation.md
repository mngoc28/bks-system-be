# Implementation Plan: Điều hướng liên phân hệ Admin (UAT Navigation Issues)

## 1. Thông tin kế hoạch
- **Mã plan:** PLAN-ADM-NAV-005
- **Design đầu vào:** [docs/designs/design_005_admin_cross_module_navigation.md](../designs/design_005_admin_cross_module_navigation.md)
- **Scope:** FE-first + BE filter hardening cho chuỗi điều hướng `Partner -> Property -> Room -> Booking -> User`
- **Mục tiêu:** Loại bỏ toàn bộ nhóm lỗi UAT "workflow navigation gap" trên admin, không đổi business rules lõi.

## 1.1 Timeline triển khai đề xuất
1. **Phase 1 (Quick win, FE-only):** bổ sung CTA/link/context params, contextual chips, reset context.
2. **Phase 2 (FE + BE):** chuẩn hóa server-side filtering theo context IDs.
3. **Phase 3 (Optimization):** telemetry, polish UX và mở rộng quick actions sau duyệt.

## 1.2 Implementation Status
- **Design completed:** Có tài liệu D005.
- **Code status:** Chưa triển khai.
- **Risk level hiện tại:** Medium-High vì ảnh hưởng nhiều màn admin cốt lõi.

---

## 2. Nguyên tắc triển khai
- Thay đổi theo hướng **surgical**: chỉ chạm vào action/navigation/filter UI liên quan.
- Không refactor kiến trúc module ngoài phạm vi điều hướng.
- Mọi link cross-module phải truyền ngữ cảnh nhất quán qua query params.
- Màn đích phải luôn có cơ chế "thoát ngữ cảnh" (`Bỏ lọc ngữ cảnh`).
- Ưu tiên backward-compatible: route cũ vẫn hoạt động nếu không có context params.

---

## 3. Phạm vi công việc theo phase

### Phase A. FE Navigation Foundation (Quick Win)
Mục tiêu: Có thể điều hướng liên phân hệ ngay, dù backend filter chưa hoàn toàn chuẩn hóa.

Task A1. Chuẩn hóa helper điều hướng context
- File dự kiến:
  - `src/utils/adminNavigation.ts` (mới)
  - `src/constant.ts` (nếu cần enum query key)
- Việc cần làm:
  - Tạo helper build URL/query params (`source`, `partner_id`, `property_id`, `room_id`, `user_id`).
  - Chuẩn hóa tên query key dùng chung toàn admin.
- Acceptance Criteria:
  - [ ] Có helper dùng lại được ở Partner/Property/Room/Booking.
  - [ ] Không hard-code query key rải rác trong nhiều component.
- Dependencies: Không có.
- Blocks: A2, A3, A4, A5, A6.

Task A2. Bổ sung quick actions ở Partner Management/Detail
- File dự kiến:
  - `src/pages/Admin/PartnerManagement/components/PartnerTable.tsx`
  - `src/pages/Admin/PartnerManagement/index.tsx`
  - `src/pages/Admin/PartnerDetail/index.tsx`
- Việc cần làm:
  - Thêm action `Xem tài sản`, `Xem phòng`, `Xem booking`.
  - Truyền `partner_id`, `source`.
- Acceptance Criteria:
  - [ ] Từ partner list/detail nhảy đúng sang màn đích với context.
  - [ ] Không ảnh hưởng action cũ (`View/Edit`).
- Dependencies: A1.
- Blocks: A6.

Task A3. Bổ sung cross-links ở Property list/detail
- File dự kiến:
  - `src/pages/Admin/PropertyManager/components/PropertyTableRow.tsx`
  - `src/pages/Admin/PropertyDetail/index.tsx`
  - `src/pages/Admin/PropertyManager/index.tsx`
- Việc cần làm:
  - Thêm action `Xem phòng`, `Xem booking`, `Xem đối tác`.
  - Link context theo `property_id` và partner liên quan.
- Acceptance Criteria:
  - [ ] Điều hướng từ property đi đúng rooms/booking.
  - [ ] Không làm vỡ luồng edit/delete hiện có.
- Dependencies: A1.
- Blocks: A6.

Task A4. Bổ sung cross-links ở Room list/detail
- File dự kiến:
  - `src/pages/Admin/RoomManager/components/RoomTable.tsx`
  - `src/pages/Admin/RoomDetail/components/RoomDetailView.tsx`
  - `src/pages/Admin/RoomManager/index.tsx`
- Việc cần làm:
  - Thêm action `Xem tài sản`, `Xem booking`, `Xem đối tác`.
  - Từ room detail, property name chuyển thành link click được.
- Acceptance Criteria:
  - [ ] Có thể từ room đi tới property/booking đúng context.
  - [ ] Không ảnh hưởng modal ảnh và action edit room.
- Dependencies: A1.
- Blocks: A6.

Task A5. Booking drill-down links
- File dự kiến:
  - `src/pages/Admin/BookingManage/components/BookingTable.tsx`
  - `src/pages/Admin/BookingManage/index.tsx`
- Việc cần làm:
  - Chuyển cột `Khách hàng`, `Phòng`, `Tài sản` thành link drill-down.
  - Truyền `source`, `booking_id` (nếu cần trace ngược).
- Acceptance Criteria:
  - [ ] Click customer/room/property mở đúng detail page.
  - [ ] Hành vi row actions cũ không đổi.
- Dependencies: A1.
- Blocks: A6.

Task A6. Contextual chips + reset context trên các trang list đích
- File dự kiến:
  - `src/pages/Admin/PropertyManager/index.tsx`
  - `src/pages/Admin/RoomManager/index.tsx`
  - `src/pages/Admin/BookingManage/index.tsx`
  - Shared component mới: `src/components/admin/ContextFilterChips.tsx`
- Việc cần làm:
  - Parse query params context và hiển thị chips.
  - Bổ sung `Bỏ lọc ngữ cảnh`.
- Acceptance Criteria:
  - [ ] Context hiển thị rõ ràng khi đến từ quick action.
  - [ ] Bỏ lọc xong list quay về trạng thái mặc định.
- Dependencies: A2, A3, A4, A5.
- Blocks: B1, C1.

### Phase B. Backend Filter Hardening
Mục tiêu: Tất cả lọc context chuyển về server-side để đúng dữ liệu và hiệu năng.

Task B1. Chuẩn hóa API filter cho Properties
- File dự kiến:
  - `bks-system-be` backend: controller/request/repository/service liên quan properties
- Việc cần làm:
  - Hỗ trợ `partner_id` trong endpoint list properties.
- Acceptance Criteria:
  - [ ] `GET /admin/properties?partner_id=X` trả đúng dữ liệu.
- Dependencies: Không bắt buộc phụ thuộc A-phase, nhưng cần contract thống nhất.
- Blocks: B4.

Task B2. Chuẩn hóa API filter cho Rooms
- Việc cần làm:
  - Hỗ trợ `partner_id`, `property_id` trong endpoint list rooms.
- Acceptance Criteria:
  - [ ] `GET /admin/rooms?partner_id=X&property_id=Y` lọc chính xác.
- Dependencies: Không có.
- Blocks: B4.

Task B3. Chuẩn hóa API filter cho Booking
- Việc cần làm:
  - Hỗ trợ `partner_id`, `property_id`, `room_id` trong endpoint list booking.
- Acceptance Criteria:
  - [ ] Filter booking theo context IDs hoạt động đúng.
- Dependencies: Không có.
- Blocks: B4.

Task B4. FE chuyển toàn bộ context filter sang server-side
- File dự kiến:
  - `src/pages/Admin/PropertyManager/index.tsx`
  - `src/pages/Admin/RoomManager/index.tsx`
  - `src/pages/Admin/BookingManage/index.tsx`
- Việc cần làm:
  - Đọc context params và map vào query call API.
  - Loại bỏ fallback client-side tạm thời (nếu có).
- Acceptance Criteria:
  - [ ] Dữ liệu list nhất quán với context ngay cả khi phân trang lớn.
- Dependencies: B1, B2, B3.
- Blocks: C1.

### Phase C. QA/UAT & Rollout Readiness
Mục tiêu: Chốt release an toàn, đo được hiệu quả vận hành.

Task C1. Bộ test case UAT nhóm Navigation
- Việc cần làm:
  - Viết test theo `UAT-NAV-01..06` từ design D005.
- Acceptance Criteria:
  - [ ] Có đầy đủ bước, expected result, mức độ severity.
- Dependencies: A6 (tối thiểu), tốt nhất hoàn tất B4.
- Blocks: C2.

Task C2. Smoke test regression đa module
- Việc cần làm:
  - Rà lại Partner, Property, Room, Booking, User flows.
  - Đảm bảo không phá action cũ (view/edit/delete/filter/pagination).
- Acceptance Criteria:
  - [ ] Không có blocker mới.
  - [ ] Không có regression major trên luồng cũ.
- Dependencies: C1.
- Blocks: C3.

Task C3. Release checklist và KPI instrumentation
- Việc cần làm:
  - Bổ sung tracking event cho quick actions (nếu hệ thống có analytics).
  - Chốt KPI đo sau release.
- Acceptance Criteria:
  - [ ] Có dashboard/metric đầu vào để theo dõi adoption.
- Dependencies: C2.

---

## 4. Thứ tự thực thi khuyến nghị
1. A1
2. A2
3. A3
4. A4
5. A5
6. A6
7. B1
8. B2
9. B3
10. B4
11. C1
12. C2
13. C3

---

## 5. Dependency Graph

```text
Phase A (FE Quick Win)
A1 -> A2 -> A6
   -> A3 -> A6
   -> A4 -> A6
   -> A5 -> A6

Phase B (BE Hardening)
B1 -> B4
B2 -> B4
B3 -> B4

Phase C (QA & Rollout)
A6/B4 -> C1 -> C2 -> C3
```

---

## 6. Phân tích xung đột

| Conflict ID | Type | Description | Resolution |
|-------------|------|-------------|------------|
| C-ADMNAV-01 | UI | Nhiều module cùng chỉnh `RowActions` pattern | Chuẩn hóa helper + tái sử dụng component action group |
| C-ADMNAV-02 | Contract | Query params FE và API filter BE có thể lệch | Chốt contract key trong A1 trước khi làm BE |
| C-ADMNAV-03 | Regression | Drill-down link có thể ảnh hưởng click behavior table/card | Thêm regression checklist bắt buộc ở C2 |
| C-ADMNAV-04 | Permission | Link mở được nhưng không có quyền màn đích | Guard hiển thị action theo role + fallback UX |

---

## 7. Ước lượng effort (tương đối)

| Nhóm | Effort |
|------|--------|
| Phase A | 5-7 dev days |
| Phase B | 4-6 dev days |
| Phase C | 2-3 QA/dev days |
| **Tổng** | **11-16 dev days** |

> Estimate này dành cho 1 squad nhỏ (1 FE + 1 BE + QA shared), chưa tính buffer release governance.

---

## 8. Acceptance Checklist cuối plan

- [ ] Partner list/detail có quick actions liên phân hệ.
- [ ] Property list/detail có quick actions liên phân hệ.
- [ ] Room list/detail có quick actions liên phân hệ.
- [ ] Booking table có drill-down links.
- [ ] Context chips + reset context hoạt động ổn định.
- [ ] API filter context IDs chạy server-side.
- [ ] UAT-NAV-01..06 pass.
- [ ] Không có regression major trên module admin hiện hữu.

---

## 9. Handoff downstream

### Handoff cho dev (stack-task)
- Thực thi theo thứ tự A -> B -> C.
- Chia PR theo module để review nhỏ:
  - PR1: Partner + helper context
  - PR2: Property + Room
  - PR3: Booking drill-down + chips
  - PR4: BE filter hardening

### Handoff cho QA (stack-testcase)
- Dùng trực tiếp `UAT-NAV-01..06`.
- Bổ sung test regression: sorting/filter/pagination/action menu mỗi module.

### Handoff cho reviewer (stack-review-branch)
- Ưu tiên review:
  - consistency query params
  - permission guard
  - broken navigation risk
  - regression table click targets

