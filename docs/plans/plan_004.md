# Implementation Plan: Gợi ý phòng theo điểm du lịch

## 1. Thông tin kế hoạch
- **Mã plan:** PLAN-RTM-004
- **SRS đầu vào:** [docs/SRC/srs_room_tourist_spot_mapping.md](../SRC/srs_room_tourist_spot_mapping.md)
- **Design đầu vào:** [docs/designs/design_004.md](../designs/design_004.md)
- **Scope:** Backend public/admin cho tourist spot summary + mapping data
- **Mục tiêu:** Cho phép public home/search/detail nhận summary du lịch theo phòng, và admin quản lý master điểm du lịch cùng mapping phòng.

## 1.1. Timeline triển khai
1. Giai đoạn đầu: tạo schema và model nền tảng cho `tourist_spots` và `room_tourist_spot_maps`.
2. Giai đoạn giữa: triển khai public summary service + API resource dùng chung cho home/search/detail.
3. Giai đoạn sau: triển khai admin CRUD, cache invalidation, validation và audit-friendly semantics.
4. Giai đoạn cuối: test, verify fallback, và chốt handoff cho FE / QA / review / report.

## 1.2. Implementation Status
- **Completed in code:** schema foundation, public summary service, public response enrichment, admin CRUD routes/controllers, validation classes.
- **Validated:** static compile/error sweep on touched backend files.
- **Pending:** runtime PHPUnit / feature suite execution and FE integration.

---

## 2. Nguyên tắc triển khai
- Không tích hợp live routing trong plan này; travel time tiếp tục là giá trị ước tính / quản trị.
- Public home/search/detail dùng chung một summary DTO để tránh lệch payload.
- Admin CRUD phải đi qua service/repository layer, không thao tác DB trực tiếp từ controller.
- Mọi thay đổi schema phải merge vào [docs/databases_docs/db_overview_etc_core_schema.md](../databases_docs/db_overview_etc_core_schema.md) và ghi nhật ký thay đổi.
- Giữ backward compatible cho public endpoints: thiếu mapping vẫn trả response hợp lệ.

---

## 3. Phạm vi công việc

### Phase A. Schema foundation và domain model
Mục tiêu: tạo nền tảng lưu điểm du lịch và mapping phòng.

Task A1. Tạo migration cho `tourist_spots`
- File dự kiến:
  - `database/migrations/*_create_tourist_spots_table.php`
  - `app/Models/TouristSpot.php`
- Việc cần làm:
  - Tạo master table cho điểm du lịch.
  - Bổ sung unique slug, featured flag, sort order, active flag.
  - Tạo model và cast/capabilities cơ bản.
- Acceptance Criteria:
  - [ ] Migration chạy và rollback được.
  - [ ] `slug` unique, `is_active/is_featured/sort_order` có default hợp lý.
  - [ ] Model tồn tại và mapping được với timestamps.
- Dependencies: không có.
- Blocks: A2, B1.
- Test Scenarios:
  - Migration up/down.
  - Model can create/read active spot.

Task A2. Tạo migration cho `room_tourist_spot_maps`
- File dự kiến:
  - `database/migrations/*_create_room_tourist_spot_maps_table.php`
  - `app/Models/RoomTouristSpotMap.php`
- Việc cần làm:
  - Tạo bảng mapping phòng-điểm du lịch.
  - Tạo FK tới `rooms` và `tourist_spots`.
  - Thêm index cho room/primary/priority.
  - Mở relation trong model room và tourist spot.
- Acceptance Criteria:
  - [ ] Migration chạy và rollback được.
  - [ ] FK và index khớp canonical schema.
  - [ ] Quan hệ Eloquent room ↔ mapping ↔ spot hoạt động.
- Dependencies: A1.
- Blocks: B1, B2, C1.
- Test Scenarios:
  - Tạo mapping hợp lệ.
  - Từ chối mapping với FK không tồn tại.
  - Kiểm tra relation load được danh sách spot theo room.

Task A3. Cập nhật canonical schema và nhật ký thay đổi
- File chính: [docs/databases_docs/db_overview_etc_core_schema.md](../databases_docs/db_overview_etc_core_schema.md)
- Việc cần làm:
  - Ghi schema mới cho `tourist_spots` và `room_tourist_spot_maps`.
  - Cập nhật ERD/quan hệ nghiệp vụ.
  - Ghi rõ rule `travel_time_minutes` là ước tính / quản trị.
- Acceptance Criteria:
  - [ ] Schema mới được phản ánh trong overview canonical.
  - [ ] Nhật ký thay đổi có ngày và nội dung cụ thể.
- Dependencies: A1, A2.
- Blocks: B1, B2.
- Test Scenarios:
  - Review tài liệu canonical không bị thiếu entity.

### Phase B. Public summary API
Mục tiêu: public home/search/detail có thể nhận tourist summary dùng chung.

Task B1. Xây `RoomTouristSummaryService`
- File dự kiến:
  - `app/Services/RoomTouristSummaryService.php`
  - `app/Repositories/RoomTouristSpotMapRepository.php`
  - `app/Repositories/TouristSpotRepository.php`
- Việc cần làm:
  - Chọn primary spot theo `is_primary/is_featured/priority_order`.
  - Build DTO summary gồm name, travel time, distance, spots phụ.
  - Bỏ qua record invalid thay vì fail toàn bộ response.
  - Chuẩn hoá fallback khi thiếu mapping.
- Acceptance Criteria:
  - [ ] Service trả summary ổn định cho room có và không có mapping.
  - [ ] Chọn đúng spot chính theo rule đã chốt.
  - [ ] Không xuất hiện N+1 trong query chính.
- Dependencies: A1, A2.
- Blocks: B2, C1.
- Test Scenarios:
  - Room có 1 mapping.
  - Room có nhiều mapping.
  - Room không có mapping.

Task B2. Enrich public resource / response cho home, search, room detail
- File dự kiến:
  - `app/Http/Resources/*` hoặc transformer hiện có
  - `app/Http/Controllers/*Home*`, `*Room*` public controller hiện có
- Việc cần làm:
  - Gắn tourist summary vào room card payload.
  - Đảm bảo home/search/detail dùng cùng shape.
  - Không làm vỡ response khi mapping thiếu.
- Acceptance Criteria:
  - [ ] Home và search đều nhận được cùng một tourist summary shape.
  - [ ] Room detail trả danh sách spot liên quan.
  - [ ] Fallback vẫn hợp lệ trên public API.
- Dependencies: B1.
- Blocks: FE integration handoff, C1, D1.
- Test Scenarios:
  - Snapshot API response có tourist summary.
  - Fallback response khi room không map spot.

Task B3. Thêm cache layer cho public summary
- File dự kiến:
  - `app/Services/RoomTouristSummaryService.php`
  - `app/Services/*Cache*` nếu repo đã có pattern tương tự
- Việc cần làm:
  - Cache danh sách spot active.
  - Cache summary room theo room_id / scope đủ nhỏ.
  - Dùng version key hoặc TTL ngắn để invalidate sau CRUD.
- Acceptance Criteria:
  - [ ] Public read path có cache cho summary.
  - [ ] Cache invalidation hoạt động sau update master/mapping.
  - [ ] Không trả data stale lâu hơn TTL đã chốt.
- Dependencies: B1, B2.
- Blocks: C2.
- Test Scenarios:
  - Cache hit/miss.
  - Invalidate sau update mapping.

### Phase C. Admin CRUD và validation
Mục tiêu: nội dung có thể quản trị an toàn và nhất quán.

Task C1. Tạo `TouristSpotService` + admin controller
- File dự kiến:
  - `app/Services/TouristSpotService.php`
  - `app/Http/Controllers/Admin/TouristSpotController.php`
  - `app/Http/Requests/Admin/*TouristSpot*Request.php`
- Việc cần làm:
  - CRUD spot master.
  - Validate slug unique, category enum, active/featured/sort order.
  - Bảo vệ bằng auth/role hiện có.
- Acceptance Criteria:
  - [ ] Admin tạo/sửa/xóa spot được.
  - [ ] Validation trả lỗi thân thiện.
  - [ ] API chỉ cho admin truy cập.
- Dependencies: A1.
- Blocks: C3, D2.
- Test Scenarios:
  - Tạo spot hợp lệ.
  - Chặn slug trùng.
  - Chặn role không đủ quyền.

Task C2. Tạo `RoomTouristSpotMapService` + admin controller
- File dự kiến:
  - `app/Services/RoomTouristSpotMapService.php`
  - `app/Http/Controllers/Admin/RoomTouristSpotMapController.php`
  - `app/Http/Requests/Admin/*RoomTouristSpotMap*Request.php`
- Việc cần làm:
  - CRUD mapping phòng-điểm du lịch.
  - Validate room/spot tồn tại, travel time dương, distance không âm, chỉ 1 primary active / room.
  - Ghi note/source_type hợp lệ.
- Acceptance Criteria:
  - [ ] Admin tạo/sửa/xóa mapping được.
  - [ ] Rule primary/priority được enforced ở service.
  - [ ] Validation lỗi rõ ràng khi dữ liệu không hợp lệ.
- Dependencies: A2, C1.
- Blocks: C3, D2.
- Test Scenarios:
  - Tạo mapping hợp lệ.
  - Chặn travel time âm.
  - Chặn 2 primary cho cùng một room.

Task C3. Cache invalidation và transaction semantics cho CRUD
- File dự kiến:
  - `app/Services/TouristSpotService.php`
  - `app/Services/RoomTouristSpotMapService.php`
  - listener/helper invalidation nếu repo có pattern
- Việc cần làm:
  - Invalidate cache sau commit.
  - Bao CRUD trong transaction.
  - Ghi log khi invalid data bị bỏ qua.
- Acceptance Criteria:
  - [ ] Update spot/map làm refresh public summary đúng lúc.
  - [ ] Không có state trung gian trên public API.
- Dependencies: B1, B2, C1, C2.
- Blocks: D1.
- Test Scenarios:
  - CRUD xong public response đổi đúng.

### Phase D. Verification, QA handoff và release readiness
Mục tiêu: xác nhận ổn định và bàn giao downstream rõ ràng.

Task D1. Test cho public summary path
- File dự kiến:
  - `tests/Unit/Services/RoomTouristSummaryServiceTest.php`
  - `tests/Feature/*TouristSpot*Test.php`
- Việc cần làm:
  - Test primary selection, fallback, many spots, cache behavior.
  - Test response shape cho home/search/detail.
- Acceptance Criteria:
  - [ ] Happy path và negative path đều có test.
  - [ ] Không test implementation detail không cần thiết.
- Dependencies: B1, B2, B3.
- Blocks: D3.
- Test Scenarios:
  - Spot featured priority.
  - Fallback khi không có mapping.

Task D2. Test cho admin CRUD và validation
- File dự kiến:
  - `tests/Feature/Admin/*TouristSpot*Test.php`
  - `tests/Feature/Admin/*RoomTouristSpotMap*Test.php`
- Việc cần làm:
  - Test role/permission.
  - Test rule primary/priority/travel time.
  - Test invalid payload trả lỗi đúng.
- Acceptance Criteria:
  - [ ] Admin endpoints được bảo vệ.
  - [ ] Validation rule đủ rõ để QA dùng lại.
- Dependencies: C1, C2, C3.
- Blocks: D3.
- Test Scenarios:
  - Unauthorized access.
  - Duplicate primary mapping.

Task D3. Handoff tài liệu và release notes
- File liên quan:
  - `docs/SRC/srs_room_tourist_spot_mapping.md`
  - `docs/designs/design_004.md`
  - `docs/memory/*`
- Việc cần làm:
  - Chốt note cho FE integration contract.
  - Ghi lại quyết định triển khai / fallback.
  - Chuẩn bị báo cáo cho bước review và release.
- Acceptance Criteria:
  - [ ] Có thể trace requirement -> design -> plan -> test.
  - [ ] Downstream handoff rõ cho `stack-task`, `stack-testcase`, `stack-review-branch`, `report-writer`.
- Dependencies: D1, D2.
- Blocks: none.

---

## 4. Thứ tự thực thi khuyến nghị
1. A1
2. A2
3. A3
4. B1
5. B2
6. B3
7. C1
8. C2
9. C3
10. D1
11. D2
12. D3

---

## 5. Phân tích phụ thuộc và xung đột

### 5.1. Dependency Graph

```text
Phase A: Schema foundation
├── A1 tourist_spots migration/model ──────────────┐
├── A2 room_tourist_spot_maps migration/model ──────┤
└── A3 canonical DB doc update ────────────────────┘
                                                  │
                                                  v
Phase B: Public summary API                       
├── B1 summary service ◄────────────── A1, A2      
├── B2 public resource enrichment ◄──── B1         
└── B3 cache layer ◄─────────────────── B1, B2     
                                                  │
                                                  v
Phase C: Admin CRUD
├── C1 tourist spot CRUD ◄───────────── A1         
├── C2 mapping CRUD ◄───────────────── A2, C1     
└── C3 invalidation/transaction ◄────── B1, B2, C1, C2
                                                  │
                                                  v
Phase D: Verification
├── D1 public tests ◄────────────────── B1, B2, B3
├── D2 admin tests ◄─────────────────── C1, C2, C3
└── D3 handoff / release prep ◄──────── D1, D2
```

### 5.2. Identified Conflicts

| Conflict ID | Type | Description | Affected Phases | Resolution |
|-------------|------|-------------|-----------------|------------|
| C-RTM-01 | File | A1/A2/C1/C2 đều chạm model/repository/service domain mới | A, B, C | Sequence theo domain: schema trước, service sau |
| C-RTM-02 | Database | `tourist_spots` và `room_tourist_spot_maps` cần canonical update đồng bộ với migration | A, C | Gộp nhật ký schema và không tạo file mapping riêng |
| C-RTM-03 | Interface | Public summary DTO phải ổn định trước khi FE integrate | B, D | Chốt B1/B2 trước, sau đó mới handoff FE |
| C-RTM-04 | Cache | CRUD admin và public read path cùng đụng invalidation | B, C | Đưa versioning/invalidation vào service chung |

### 5.3. Conflict Resolution Strategy
1. **File Conflicts:** Các file domain mới có thể làm một nhánh riêng theo phase; tránh vừa viết model vừa đổi resource/public DTO trong cùng một task nếu chưa có schema ổn định.
2. **Database Conflicts:** Migrations tạo trước, canonical schema update ngay sau để tránh design drift.
3. **Interface Conflicts:** Chốt shape summary DTO ở B2 trước khi FE hoặc QA viết test case chi tiết.
4. **Resource Conflicts:** Cache invalidation dùng một cơ chế chung để không phân tán logic giữa spot CRUD và mapping CRUD.

---

## 6. Cơ hội song song hóa

### Có thể chạy đồng thời

| Group | Phases/Tasks | Condition |
|-------|--------------|-----------|
| A | A1 và A3 | Sau khi thống nhất schema naming |
| B | C1 và B3 | Sau khi B1/B2 chốt summary shape |
| C | D1 và D2 | Sau khi Phase B/C hoàn tất |

### Phải tuần tự
1. A1 → A2 → B1: schema trước, service sau.
2. B1 → B2: có summary service rồi mới enrich response.
3. B1/B2 → B3: cache chỉ có ý nghĩa khi summary đã ổn định.
4. C1 → C2: spot master CRUD trước, mapping CRUD sau.
5. C1/C2 → C3: invalidation phụ thuộc vào CRUD semantics.
6. D1/D2 → D3: chỉ bàn giao sau khi tests pass.

---

## 7. Risk Register

| Risk ID | Description | Likelihood | Impact | Mitigation |
|---------|-------------|------------|--------|------------|
| R-RTM-01 | Travel time ước tính không nhất quán giữa các spot | M | H | Validation rule + admin guidance + seed conventions |
| R-RTM-02 | FE nhận summary shape chưa ổn định | M | H | Khóa contract sau B2 và bàn giao sớm |
| R-RTM-03 | Cache stale sau cập nhật mapping | M | M | Version-based invalidation và TTL ngắn |
| R-RTM-04 | N+1 query khi enrich summary | M | H | Eager loading + repository boundary + test query count nếu cần |
| R-RTM-05 | Primary spot trùng cho một room | L | M | Enforce ở service transaction + validation |

---

## 8. Chiến lược testing

- Unit test cho service chọn primary, fallback và ordering.
- Feature test cho public endpoint response shape.
- Feature test cho admin CRUD, permission và validation.
- Regression test cho cache invalidation sau update mapping.
- Test case phải theo hướng requirement-centric, không test implementation detail của repository nội bộ.

---

## 9. Handoff downstream

### Handoff cho `stack-task`
- Thực thi theo thứ tự A → B → C → D.
- Không chạm FE route nếu chỉ làm backend plan này.
- Mỗi phase hoàn thành phải cập nhật trạng thái task trong plan.

### Handoff cho `stack-testcase`
- Tạo testcase cho:
  - summary public home/search/detail.
  - fallback khi thiếu mapping.
  - admin CRUD và validation.
  - cache invalidation sau thay đổi dữ liệu.

### Handoff cho `stack-review-branch`
- Review các rủi ro chính:
  - contract summary DTO có nhất quán không.
  - admin permission có kín không.
  - cache invalidation có đúng không.
  - schema canonical có đồng bộ migration không.

### Handoff cho `report-writer`
- Ghi báo cáo release bằng tiếng Việt, tập trung vào:
  - public room summary theo điểm du lịch.
  - admin quản lý dữ liệu du lịch.
  - rủi ro còn lại và khuyến nghị phát hành.

---

## 10. Tiêu chí hoàn thành plan
- Có schema, service, public resource, admin CRUD, cache, và test path rõ ràng.
- Có dependency graph và conflict analysis trước khi coding.
- Có downstream handoff rõ ràng cho task/testcase/review/report.
- Không chạm live routing hoặc redesign ngoài scope design.