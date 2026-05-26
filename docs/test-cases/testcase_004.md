# Test Case Specification: Gợi ý phòng theo điểm du lịch

## Document Information

| Trường | Giá trị |
|---|---|
| **Testcase ID** | TC004 |
| **Related SRS** | [docs/SRC/srs_room_tourist_spot_mapping.md](../SRC/srs_room_tourist_spot_mapping.md) |
| **Related Design** | [docs/designs/design_004.md](../designs/design_004.md) |
| **Related Plan** | [docs/plans/plan_004.md](../plans/plan_004.md) |
| **Canonical DB** | [docs/databases_docs/db_overview_etc_core_schema.md](../databases_docs/db_overview_etc_core_schema.md) |
| **Status** | Draft |
| **Ngôn ngữ thực thi QC** | Tiếng Việt |

## Scope

### In-scope (theo SRS + plan P004)

- **Public discovery API**:
  - Enrich public room card response (homepage `/api/v1/home` và search `/api/v1/rooms`) với tourist summary.
  - API room detail `/api/v1/rooms/{id}` trả thêm danh sách các điểm du lịch liên quan (gồm cả điểm chính và điểm phụ).
  - Logic chọn điểm du lịch chính theo thứ tự ưu tiên (`is_primary`, `is_featured`, `priority_order`).
  - Fallback an toàn khi phòng không có mapping điểm du lịch.
- **Admin CRUD & Validation**:
  - CRUD điểm du lịch master (`tourist_spots`).
  - CRUD mapping phòng - điểm du lịch (`room_tourist_spot_maps`).
  - Validation rules cho điểm du lịch (tên, unique slug, category enum, sort order).
  - Validation rules cho mapping (FK room/spot, travel time dương, distance không âm, duy nhất 1 primary active cho mỗi phòng).
- **Cache & Transaction**:
  - Cache invalidation cho active spots và room summary sau khi thêm/sửa/xóa điểm du lịch hoặc mapping.
  - Transaction rollback khi có lỗi trong quá trình ghi dữ liệu mapping.

### Out-of-scope

- Không kiểm thử UI/UX frontend cho chức năng này (chỉ kiểm thử payload API backend).
- Không kiểm thử live routing / google map API tích hợp thời gian thực.
- Không thay đổi luồng booking hiện tại.

## Preconditions

| ID | Điều kiện |
|---|---|
| P-01 | Hệ thống Backend Laravel đang chạy ở local / staging với DB được migrate đầy đủ các bảng `tourist_spots` và `room_tourist_spot_maps`. |
| P-02 | Có dữ liệu mẫu của bảng `rooms` trong DB phục vụ mapping. |
| P-03 | Người dùng admin có tài khoản và token hợp lệ để gọi Admin API. |
| P-04 | User public truy cập được các endpoint public mà không cần authentication. |

## Test Data (gợi ý)

| Mã | Mô tả |
|---|---|
| TD-TS-01 | Điểm du lịch 1: name="Bà Nà Hill", slug="ba-na-hill", category="attraction", is_featured=true, sort_order=1, is_active=true. |
| TD-TS-02 | Điểm du lịch 2: name="Bãi biển Mỹ Khê", slug="bai-bien-my-khe", category="beach", is_featured=true, sort_order=2, is_active=true. |
| TD-TS-03 | Điểm du lịch 3 (không active): name="Điểm nháp", slug="diem-nhap", category="other", is_featured=false, sort_order=100, is_active=false. |
| TD-MAP-01 | Mapping 1 (Primary): room_id=1, tourist_spot_id=TD-TS-01, distance_km=25.0, travel_time_minutes=45, priority_order=1, is_primary=true, source_type="manual". |
| TD-MAP-02 | Mapping 2 (Secondary): room_id=1, tourist_spot_id=TD-TS-02, distance_km=5.5, travel_time_minutes=15, priority_order=2, is_primary=false, source_type="estimated". |

## Test Cases

| TC ID | Requirement Ref | Screen/Module | Scenario | Steps | Test Data | Expected Result | Priority |
|------|-----------------|---------------|----------|-------|-----------|-----------------|----------|
| **TC004-001** | FR-02, FR-07 | Public API | Lấy thông tin phòng có 1 mapping | 1) Gọi GET `/api/v1/home` hoặc `/api/v1/rooms`.<br>2) Tìm phòng có duy nhất 1 mapping. | TD-TS-01, TD-MAP-01 | Response chứa object `tourist_summary` gồm:<br>- `tourist_spot_name`: "Bà Nà Hill"<br>- `travel_time_label`: "45 phút di chuyển"<br>- `has_tourist_mapping`: true. | High |
| **TC004-002** | FR-03, FR-04 | Public API | Lấy thông tin phòng có nhiều mapping (lọc primary) | 1) Gắn 2 mapping cho room_id=1 (1 primary, 1 secondary).<br>2) Gọi GET `/api/v1/rooms`. | TD-MAP-01, TD-MAP-02 | Response trả về `tourist_summary` ứng với điểm chính (TD-MAP-01): `tourist_spot_name` là "Bà Nà Hill", `travel_time_label` là "45 phút di chuyển". | High |
| **TC004-003** | FR-05 | Public API | Lấy thông tin phòng không có mapping (fallback) | 1) Gọi GET `/api/v1/rooms`.<br>2) Tìm phòng chưa được gắn mapping nào. | Không có mapping | Response trả về `tourist_summary` rỗng hoặc `has_tourist_mapping` là `false`, không crash API, layout hoặc các trường khác hiển thị bình thường. | High |
| **TC004-004** | FR-04 | Public API | Lấy chi tiết phòng (Room Detail) | 1) Gọi GET `/api/v1/rooms/{id}` với room_id=1.<br>2) Kiểm tra list `tourist_spots`. | TD-MAP-01, TD-MAP-02 | API trả về danh sách chi tiết các điểm du lịch liên quan gồm cả Bà Nà Hill và Bãi biển Mỹ Khê với khoảng cách, thời gian di chuyển đầy đủ. | High |
| **TC004-005** | FR-06 | Admin API | CRUD Tourist Spot Master | 1) POST `/api/v1/admin/tourist-spots` tạo mới.<br>2) GET danh sách kiểm tra.<br>3) PUT cập nhật thông tin.<br>4) DELETE điểm vừa tạo. | Dữ liệu hợp lệ | Các bước CRUD chạy thành công, trả status 201/200/204 tương ứng. Điểm du lịch được lưu/cập nhật/xóa đúng trong DB. | High |
| **TC004-006** | Validation | Admin API | Trùng slug trong Tourist Spot | 1) Gửi POST tạo spot mới với slug đã tồn tại. | Slug trùng | Trả về lỗi 422 Unprocessable Entity, báo lỗi slug đã tồn tại. | High |
| **TC004-007** | Validation | Admin API | Sai enum category trong Tourist Spot | 1) Gửi POST tạo spot với category="invalid_category". | Category sai | Trả về lỗi 422 Unprocessable Entity, báo lỗi category không hợp lệ. | Medium |
| **TC004-008** | FR-06 | Admin API | CRUD Room-Tourist Mapping | 1) POST `/api/v1/admin/room-tourist-spot-maps` để map room và spot.<br>2) GET kiểm tra.<br>3) PUT thay đổi travel time.<br>4) DELETE mapping. | Dữ liệu hợp lệ | Các bước CRUD chạy thành công. Mapping được cập nhật đúng trong DB. | High |
| **TC004-009** | Validation | Admin API | Travel time âm hoặc bằng 0 | 1) Gửi POST tạo mapping với `travel_time_minutes` = 0 hoặc -5. | travel_time <= 0 | Trả về lỗi 422 Unprocessable Entity, báo lỗi travel time phải lớn hơn 0. | High |
| **TC004-010** | Validation | Admin API | Khoảng cách distance_km âm | 1) Gửi POST tạo mapping với `distance_km` = -1.5. | distance_km < 0 | Trả về lỗi 422 Unprocessable Entity, báo lỗi distance không được âm. | High |
| **TC004-011** | Validation | Admin API | Ràng buộc duy nhất 1 primary mapping | 1) Tạo mapping thứ hai cho room 1 với `is_primary` = true (mapping cũ đang là true). | 2 primary maps | Trả về lỗi 422 hoặc logic service tự động cập nhật mapping cũ thành `is_primary = false` hoặc từ chối (tùy thiết kế chi tiết), đảm bảo chỉ có tối đa 1 primary active. | High |
| **TC004-012** | Permission | Admin API | Bảo vệ quyền Admin | 1) Gửi request Admin API không kèm Token hoặc dùng token của User thường. | Thiếu/sai auth | Trả về lỗi 401 Unauthorized hoặc 403 Forbidden. | High |
| **TC004-013** | Cache | Cache logic | Invalidate cache sau khi CRUD | 1) Gọi GET `/api/v1/rooms` và thấy cache hit.<br>2) Update/Delete mapping qua Admin API.<br>3) Gọi lại GET `/api/v1/rooms`. | TD-MAP-01 | Dữ liệu ở lần gọi thứ 3 lập tức được cập nhật theo thay đổi mới ở bước 2 (cache cũ đã bị invalidate). | Medium |
| **TC004-014** | Transaction | Integrity | Rollback khi mapping lỗi | 1) Giả lập lưu mapping hàng loạt hoặc có kèm quan hệ phức tạp mà bước cuối lỗi. | DB fail | Transaction rollback hoàn toàn, không lưu record rác vào database. | Medium |

## Validation Matrix

| Field | Rule | Valid Case | Invalid Case | Ghi chú |
|---|---|---|---|---|
| `tourist_spots.slug` | Unique | `ba-na-hill` (chưa có) | `ba-na-hill` (đã tồn tại) | Trả lỗi 422 |
| `tourist_spots.category` | Enum | `attraction`, `beach`, `mountain`, `culture`, `entertainment`, `other` | `resort`, `hotel`, `unknown` | Trả lỗi 422 |
| `room_tourist_spot_maps.travel_time_minutes` | Integer > 0 | `15`, `120` | `0`, `-10`, `abc` | Trả lỗi 422 |
| `room_tourist_spot_maps.distance_km` | Decimal >= 0 | `0`, `12.50` | `-1.50`, `xyz` | Trả lỗi 422 |
| `room_tourist_spot_maps.is_primary` | Tối đa 1 primary/room | Chỉ 1 record có `is_primary = true` cho room_id=1 | 2 record cùng có `is_primary = true` cho room_id=1 | Hệ thống báo lỗi / tự xử lý |

## Traceability Matrix

| Requirement ID | Covered By Test Case | Ghi chú |
|---|---|---|
| **FR-01** (Gắn phòng với điểm du lịch) | TC004-008, TC004-011 | Mapping CRUD và ràng buộc primary |
| **FR-02** (Nhãn du lịch trên card phòng) | TC004-001, TC004-002 | Trả summary định dạng đúng |
| **FR-03** (Ưu tiên điểm nổi tiếng) | TC004-002 | Lọc chọn điểm chính theo priority |
| **FR-04** (Hỗ trợ nhiều điểm đến) | TC004-004 | Room detail trả danh sách các điểm phụ |
| **FR-05** (Fallback thiếu dữ liệu) | TC004-003 | Phòng không có mapping vẫn hoạt động |
| **FR-06** (Quản trị danh mục & mapping) | TC004-005, TC004-008 | Admin CRUD cho spots và maps |
| **FR-07** (Dữ liệu tái sử dụng) | TC004-001, TC004-002 | Định dạng DTO thống nhất cho home và search |

## Execution Notes for QC

1. **Khởi tạo dữ liệu**: Chạy seeder / factory để có sẵn danh mục các điểm du lịch tiêu chuẩn trước khi chạy test case public API.
2. **Kiểm tra Performance**: Khi gọi GET `/api/v1/rooms` (lấy danh sách phòng), theo dõi query log của Laravel để đảm bảo không xảy ra vấn đề N+1 query (dữ liệu maps và spots cần được eager load thông qua `with`).
3. **Môi trường Test**: Cần cấu hình driver cache khác `array` (ví dụ `redis` hoặc `file`) để kiểm thử chính xác hành vi Cache Invalidation.

## Smoke Regression

| # | Mô tả ngắn | Kỳ vọng |
|---|---|---|
| S-01 | GET `/api/v1/rooms` | Trả danh sách phòng kèm nhãn `tourist_summary` (nếu có mapping) |
| S-02 | GET `/api/v1/rooms/{id}` | Trả chi tiết phòng kèm mảng `tourist_spots` các địa điểm xung quanh |
| S-03 | POST `/api/v1/admin/tourist-spots` | Admin tạo được điểm du lịch mới hợp lệ |
| S-04 | POST `/api/v1/admin/room-tourist-spot-maps` | Admin map được phòng với điểm du lịch mới |

*Tài liệu sinh theo skill `stack-testcase`, đồng bộ plan P004 và SRS gợi ý phòng theo điểm du lịch.*
