# Test Case Specification: Partner Room Maintenance (PLAN-MNT-014)

## Document Information

| Trường | Giá trị |
|---|---|
| **Testcase ID** | TC014 |
| **Related SRS** | [docs/SRC/srs_partner_maintenance.md](../SRC/srs_partner_maintenance.md) |
| **Related Plan** | [docs/plans/plan_014_partner_maintenance.md](../plans/plan_014_partner_maintenance.md) |
| **E2E Script** | [bks-system-fe/business-script/E2E_PARTNER_MAINTENANCE.md](../../../bks-system-fe/business-script/E2E_PARTNER_MAINTENANCE.md) |
| **Status** | Draft |
| **Ngôn ngữ thực thi QC** | Tiếng Việt |

## Scope

### In-scope

- Partner API lifecycle `room_maintenances`: list, create, show, update (planned → in_progress → completed/cancelled).
- Đồng bộ `room_blocks` khi `block_calendar=true`.
- Conflict 409 với booking/block overlap.
- FE Partner: `/partner/maintenances`, Room Detail tab Bảo trì, Dashboard widget, Calendar block hiển thị.
- Occupancy badge **Đang bảo trì** trên Properties preview.

### Out-of-scope

- End User báo sự cố (`source=guest_report`) — phase 2.
- Event `maintenance.changed` realtime riêng (dùng `room_block.changed`).
- Admin UI quản lý phiếu (chỉ API admin hiện có).

## Preconditions

| ID | Điều kiện |
|---|---|
| P-01 | Migration `2026_06_18_120000_extend_room_maintenances_for_partner_lifecycle` đã chạy. |
| P-02 | Partner A có ≥1 property, ≥1 room; Partner B tách biệt để test isolation. |
| P-03 | Token partner hợp lệ (`auth:sanctum`, role partner). |
| P-04 | Có booking confirmed overlap (cho case 409) và khoảng trống (cho case tạo thành công). |

## Traceability Matrix (FR → Test)

| Requirement | Mô tả ngắn | Test case |
|---|---|---|
| MNT-001 | Partner scope isolation | TC014-001 |
| MNT-002 | List + filter + pagination | TC014-002 |
| MNT-003 | POST create `planned` | TC014-003 |
| MNT-004 | GET detail enriched | TC014-004 |
| MNT-005 | PATCH lifecycle hợp lệ | TC014-005, TC014-006 |
| MNT-006 | Cancel + `cancellation_reason` | TC014-007 |
| MNT-007 | `block_calendar` default true | TC014-008 |
| MNT-008 | `room_block_id` liên kết | TC014-009 |
| MNT-009 | Complete/cancel release block | TC014-010 |
| MNT-010 | Conflict 409 | TC014-011 |
| MNT-011 | Upload images JSON | TC014-012 (Should) |
| MNT-012 | Response labels enriched | TC014-013 |
| MNT-013 | Occupancy maintenance badge | TC014-014 |
| MNT-014 | Dashboard urgent sort emergency | TC014-015 |

## Smoke Tests (S-MNT)

| ID | Scenario | Steps tóm tắt | Expected | Priority |
|---|---|---|---|---|
| **S-MNT-01** | Tạo emergency + block | Room Detail → dialog → `block_calendar=true` | 201 + block trên calendar | High |
| **S-MNT-02** | Dashboard CTA | Dashboard → Tiếp nhận → click tên phòng | PATCH in_progress + navigate room | High |
| **S-MNT-03** | List lifecycle | Maintenances → Xong | completed + filter | High |
| **S-MNT-04** | Calendar regression | Complete → calendar | Block biến mất; drag-drop OK | High |
| **S-MNT-05** | Conflict UI | Tạo overlap booking | 409 + highlight conflict | High |
| **S-MNT-06** | Conflict preview | Chọn ngày trùng + khóa lịch | Panel đỏ, chặn submit, CTA hành động | High |

## Test Cases (chi tiết)

| TC ID | Requirement | Module | Scenario | Steps | Expected | Priority |
|---|---|---|---|---|---|---|
| **TC014-001** | MNT-001 | API | Partner isolation | Partner B gọi `GET/PATCH` phiếu của A | 404 Not Found | High |
| **TC014-002** | MNT-002 | API | List filters | `GET` với `property_id`, `status`, `page`, `per_page` | Pagination Laravel chuẩn; filter đúng | High |
| **TC014-003** | MNT-003 | API | Create | `POST` đủ field bắt buộc | `status=planned`, 201 | High |
| **TC014-004** | MNT-004 | API | Show | `GET /{id}` | Có `room_name`, `property_name`, `status_label` | High |
| **TC014-005** | MNT-005 | API | planned→in_progress | `PATCH { status: in_progress }` | 200, `started_at` set | High |
| **TC014-006** | MNT-005 | API | in_progress→completed | `PATCH { status: completed }` | 200, `completed_at` set | High |
| **TC014-007** | MNT-006 | API | Cancel | `PATCH cancelled` không reason / có reason | 422 thiếu reason; 200 khi có reason | High |
| **TC014-008** | MNT-007 | API | block_calendar false | POST không gửi `block_calendar` | Default true; false → không tạo block | High |
| **TC014-009** | MNT-008 | API/DB | room_block_id | POST `block_calendar=true` | Phiếu có `room_block_id` not null | High |
| **TC014-010** | MNT-009 | API/Calendar | Release block | PATCH completed/cancelled | Block sync xóa; GET calendar không còn block | High |
| **TC014-011** | MNT-010 | API | Conflict | POST overlap booking + block_calendar true | 409 `MAINTENANCE_CALENDAR_CONFLICT` | High |
| **TC014-012** | MNT-011 | API | Images | PATCH với `images` array ≤5 URL | 200; lưu JSON | Medium |
| **TC014-013** | MNT-012 | FE/API | Labels | Mở Maintenances list | Hiển thị `status_label`, `room_name` không normalize sai | Medium |
| **TC014-014** | MNT-013 | FE/API | Properties badge | Phiếu active hôm nay | Badge **Đang bảo trì** trên Properties | Medium |
| **TC014-015** | MNT-014 | Dashboard | Urgent sort | 2 phiếu emergency + scheduled | Emergency lên trước | Medium |
| **TC014-016** | MNT-010 | API/FE | Conflict preview | `GET conflict-preview` + dialog FE | `has_conflict`, `current_stay`; chặn submit khi block + conflict | High |

## Negative / Edge Cases

| TC ID | Scenario | Expected |
|---|---|---|
| TC014-N01 | PATCH `completed` → `in_progress` | 422 invalid transition |
| TC014-N02 | PATCH cancel phiếu đã `completed` | 422 |
| TC014-N03 | POST `block_calendar=true` thiếu `end_time` | 422 |
| TC014-N04 | Partner không sở hữu `room_id` | 403/404 |

## Automation Reference

| Layer | File | Coverage |
|---|---|---|
| BE Feature | `tests/Feature/Partner/PartnerRoomMaintenanceTest.php` | List, lifecycle, isolation, conflict preview |
| FE Build | `npm run build` | Typecheck toàn FE |
| Manual E2E | `business-script/E2E_PARTNER_MAINTENANCE.md` | S-MNT-01 → 05 |

## Sign-off Checklist (QC)

- [ ] Smoke S-MNT-01 → S-MNT-06 pass trên staging
- [ ] TC014-001 → TC014-011 pass (API)
- [ ] Không regression Calendar drag-drop (S-MNT-04)
- [ ] Dashboard CTA không ảnh hưởng work queue booking (C5 plan)
