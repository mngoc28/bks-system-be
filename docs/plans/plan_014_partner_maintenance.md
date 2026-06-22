# Implementation Plan: Partner Room Maintenance (Quản lý Bảo trì phòng)

## Document Information

| Mục | Nội dung |
|-----|----------|
| **Plan ID** | PLAN-MNT-014 |
| **Created** | 2026-06-18 |
| **Status** | Draft — Phase 1–2 ✅ · Phase 3–4 pending |
| **Related SRS** | `docs/SRC/srs_partner_maintenance.md` |
| **Related Design** | Chưa có `design_*.md` riêng — triển khai trực tiếp từ SRS (có thể bổ sung design nhẹ nếu cần review kiến trúc sync block) |
| **Phụ thuộc nền** | Partner Portal 360 Phase 3 (`room_blocks`, `ConflictChecker`, `RoomBlockService`) — `docs/plans/plan_001.md` |
| **Risk level** | Medium — migration schema + đồng bộ 2 entity + sửa nhiều màn Partner |

### Executive Summary

Hoàn thiện module **Bảo trì phòng** cho Partner: vòng đời phiếu `planned → in_progress → completed | cancelled`, phân quyền ownership, API đầy đủ (list/create/show/patch), đồng bộ tùy chọn với `room_blocks`, và nối FE (`Maintenances.tsx`, `RoomDetail.tsx`, `MaintenanceSection.tsx`) với API thật.

**Khác biệt so với As-Is:** Hiện chỉ có GET/POST; FE đổi status giả local; không scope Partner; pagination lệch contract.

**Ước lượng:** 8–11 dev-days (1 BE + 1 FE), 4 phase release tăng dần.

### Timeline đề xuất

| Phase | Thời gian | Deliverable | Release slice |
|-------|-----------|-------------|---------------|
| **Phase 1** — Foundation | 1 ngày | Migration + model + DB docs | Slice A (schema only, backward-compatible) |
| **Phase 2** — Backend core | 3–4 ngày | Authorize, lifecycle, block sync, tests | Slice B (API usable qua Postman) |
| **Phase 3** — Frontend | 2–3 ngày | Maintenances list, Room Detail, API client | Slice C (Partner UI end-to-end Must) |
| **Phase 4** — Integration & QA | 2 ngày | Dashboard CTA, Calendar verify, testcase | Slice D (production-ready Must) |

---

## Phase Overview

| Phase | Tên | Tasks | Dependencies | Song song |
|-------|-----|-------|--------------|-----------|
| 1 | Foundation | 3 | None | — |
| 2 | Backend core | 10 | Phase 1 | — |
| 3 | Frontend | 8 | Phase 2 (T2.1–T2.6) | Phase 4 prep (testcase draft) |
| 4 | Integration & QA | 5 | Phase 2 + 3 | — |

---

## Dependency Graph

```text
Phase 1: Foundation
[T1.1] Migration room_maintenances extend
[T1.2] Update db_overview + data-dictionary
[T1.3] Model fillable/casts/relations
         │
         ▼ (blocks all Phase 2)
Phase 2: Backend core
[T2.1] Partner scope + policy authorize
[T2.2] Repository: partner filter + pagination fix + enrich
[T2.3] MaintenanceBlockSyncService (wrap RoomBlockService)
[T2.4] RoomMaintenanceService: create + transaction
[T2.5] RoomMaintenanceService: update status machine
[T2.6] Validation + error codes
[T2.7] Controller show/patch + routes
[T2.8] API Resource (enriched response)
[T2.9] Dashboard urgent-maintenances sort
[T2.10] Feature + unit tests
         │
         ├──────────────────┐
         ▼                  ▼
Phase 3: Frontend      Phase 4 (partial)
[T3.1] partnerService API    [T4.5] testcase handoff
[T3.2] types + i18n labels
[T3.3] Maintenances.tsx
[T3.4] RoomDetail maintenance tab
[T3.5] Create dialog block_calendar
[T3.6] Cancel dialog + reason
[T3.7] Filters + pagination
[T3.8] FE build + lint
         │
         ▼
Phase 4: Integration & QA
[T4.1] MaintenanceSection CTA
[T4.2] Calendar regression
[T4.3] Properties room status verify
[T4.4] E2E smoke script
```

---

## Hiện trạng kỹ thuật (As-Is)

### Backend

| Thành phần | File | Gap |
|------------|------|-----|
| Controller | `RoomMaintenanceController` | Chỉ `index`, `store` |
| Service | `RoomMaintenanceService` | Không authorize, không sync block, không update |
| Repository | `RoomMaintenanceRepository` | Không filter `partner_id`; pagination param `pagination` ≠ `page`/`per_page` |
| Routes | `routes/api.php` L740–743 | Không middleware partner ownership rõ ràng |
| Dashboard | `DashboardService::getUrgentMaintenancesForPartner` | Sort chưa ưu tiên `emergency` |

### Frontend

| Thành phần | File | Gap |
|------------|------|-----|
| List | `Maintenances.tsx` | `safeHandleUpdate` chỉ setState local |
| Room Detail | `RoomDetail.tsx` | Có create; thiếu PATCH, cancel, ảnh |
| Dashboard | `MaintenanceSection.tsx` | Chỉ navigate, không CTA tiếp nhận |
| API | `partnerService.ts` | Thiếu `updateMaintenance`, `getMaintenanceById` |

### DB baseline

- Bảng `room_maintenances` đã có: `room_id`, `property_id`, `title`, `description`, `images`, `maintenance_type`, `start_time`, `end_time`, `status`, `created_by`.
- Thiếu: `room_block_id`, `block_calendar`, `source`, audit timestamps, `cancellation_reason`.
- Tham chiếu: `docs/architecture/data-dictionary.md` §2.3.5, `docs/databases_docs/db_overview_etc_core_schema.md`.

### Quyết định planning (chốt tạm để unblock dev)

| ID | Quyết định | Ghi chú |
|----|------------|---------|
| PD-MNT-001 | `block_calendar` default `true`; khi `true` bắt buộc `end_time` | Tránh block vô hạn (OQ2 SRS) |
| PD-MNT-002 | Conflict với booking đang ở: **cho phép** tạo phiếu nếu `block_calendar=false`; **từ chối 409** nếu `block_calendar=true` | Giải quyết OQ1 theo hướng linh hoạt |
| PD-MNT-003 | Sync block: gọi nội bộ `RoomBlockService::create` / `delete` thay vì duplicate logic | Tái dùng Phase 3 |
| PD-MNT-004 | Dedup block: nếu overlap block `maintenance` đã có, **link** `room_block_id` thay vì tạo mới | Giảm risk C2 |
| PD-MNT-005 | Không implement MNT-015 (realtime) trong plan này | Could-have phase sau |

---

## Phase 1: Foundation ✅ DONE (2026-06-18)

**Goal:** Schema và model sẵn sàng cho service layer.  
**Duration:** 1 ngày  
**Dependencies:** None

### [T1.1] Migration mở rộng `room_maintenances` ✅ DONE

- **Description:** Tạo migration thêm cột liên kết block, audit, source; index scope Partner.
- **Acceptance Criteria:**
  - [x] Migration chạy `up`/`down` sạch trên DB dev
  - [x] FK `room_block_id` → `room_blocks.id` ON DELETE SET NULL
  - [x] Index `idx_room_maintenances_partner_scope (property_id, status, maintenance_type, start_time)`
- **Files Affected:**
  - `database/migrations/2026_06_18_120000_extend_room_maintenances_for_partner_lifecycle.php`
- **Dependencies:** None
- **Blocks:** T1.3, T2.4, T2.5
- **Completed:** 2026-06-18

### [T1.2] Cập nhật tài liệu DB ✅ DONE

- **Description:** Đồng bộ schema mới vào `db_overview_etc_core_schema.md` và `data-dictionary.md` §2.3.5.
- **Acceptance Criteria:**
  - [x] Mô tả đủ 7 cột mới + index
  - [x] Ghi chú quan hệ `room_maintenances.room_block_id` ↔ `room_blocks`
- **Files Affected:**
  - `docs/databases_docs/db_overview_etc_core_schema.md`
  - `docs/architecture/data-dictionary.md`
- **Dependencies:** T1.1
- **Completed:** 2026-06-18

### [T1.3] Cập nhật Model `RoomMaintenance` ✅ DONE

- **Description:** Bổ sung `fillable`, `casts`, quan hệ `room()`, `property()`, `roomBlock()`, `creator()`.
- **Acceptance Criteria:**
  - [x] Model load được record sau migration
  - [x] `images` cast array; datetime casts cho audit fields
- **Files Affected:**
  - `app/Models/RoomMaintenance.php`
  - `database/seeders/RoomMaintenancesSeeder.php` (cột mới cho seed)
- **Dependencies:** T1.1
- **Blocks:** T2.4, T2.5
- **Completed:** 2026-06-18

### Phase 1 Review Notes (2026-06-18)

| Review | Kết quả | Ghi chú |
|--------|---------|---------|
| **Business (BA)** | PASS | Đủ cột lifecycle + liên kết block theo SRS/plan |
| **Technical (TLA)** | PASS | FK nullable SET NULL; index scope Partner; model `final` + relations |
| **QA** | PASS | `php artisan migrate` OK trên DB dev |

**Next:** Phase 2 — bắt đầu T2.1 (Partner authorization)

---

## Phase 2: Backend Core ✅ DONE (2026-06-18)

**Goal:** API Partner đầy đủ lifecycle + đồng bộ `room_blocks` + tests.  
**Dependencies:** Phase 1 complete

| Task | Trạng thái | Ghi chú |
|------|------------|---------|
| T2.1 Policy | ✅ | `RoomMaintenancePolicy` + đăng ký AuthServiceProvider |
| T2.2 Repository | ✅ | Scope `partner_id`, `page`/`per_page`, eager load |
| T2.3 Block sync | ✅ | `MaintenanceBlockSyncService` delegate `RoomBlockService` |
| T2.4 Create | ✅ | Transaction + `block_calendar` + rollback on conflict |
| T2.5 Update lifecycle | ✅ | State machine + audit timestamps + release block |
| T2.6 Validation/i18n | ✅ | `RoomMaintenanceValidation` + `lang/vi|en/room_maintenance.php` |
| T2.7 Controller/routes | ✅ | `show`, `update` (Partner + Admin) |
| T2.8 API Resource | ✅ | `RoomMaintenanceResource` enriched |
| T2.9 Dashboard sort | ✅ | Emergency ưu tiên trong `getUrgentMaintenancesForPartner` |
| T2.10 Tests | ✅ | `PartnerRoomMaintenanceTest` 3/3 pass (20 assertions) |

### Phase 2 Review Notes (2026-06-18)

| Review | Kết quả | Ghi chú |
|--------|---------|---------|
| **Business (BA)** | PASS | MNT-001–010, MNT-012, MNT-014 covered; isolation test 404 |
| **Technical (TLA)** | PASS | Reuse `RoomBlockService`/`ConflictChecker`; transaction on create |
| **QA** | PASS | Feature tests green; conflict-with-booking test deferred to Phase 4 E2E |

**API mới (Partner):**
- `GET /api/v1/partner/room-maintenances/{id}`
- `PATCH /api/v1/partner/room-maintenances/{id}`

**Next:** Phase 3 — FE `Maintenances.tsx`, `partnerService`, Room Detail

---

## Phase 2 (archived task detail)

<details>
<summary>Chi tiết task Phase 2 (đã hoàn thành)</summary>

### [T2.1] Partner authorization ✅ DONE
### [T2.2] Repository ✅ DONE
### [T2.3] MaintenanceBlockSyncService ✅ DONE
### [T2.4] Create service ✅ DONE
### [T2.5] Update status ✅ DONE
### [T2.6] Validation + i18n ✅ DONE
### [T2.7] Controller + routes ✅ DONE
### [T2.8] API Resource ✅ DONE
### [T2.9] Dashboard urgent-maintenances ✅ DONE
### [T2.10] Automated tests ✅ DONE

</details>

---

## Phase 3: Frontend

**Goal:** Partner UI gọi API thật, lifecycle hoàn chỉnh.  
**Duration:** 2–3 ngày  
**Dependencies:** Phase 2 (T2.7 API stable)

### [T3.1] API client

- **Description:** Thêm `getMaintenanceById`, `updateMaintenance` vào `partnerService.ts`; types response.
- **Acceptance Criteria:**
  - [ ] PATCH gửi `{ status, cancellation_reason?, end_time?, images? }`
- **Files Affected:**
  - `bks-system-fe/src/services/partnerService.ts`
  - `bks-system-fe/src/pages/Partner/types.ts`
- **Dependencies:** T2.7
- **Blocks:** T3.3, T3.4
- **Est. Hours:** 2

### [T3.2] i18n status/type labels

- **Description:** Map enum EN → VI trong `utils/partnerMaintenanceDisplay.ts` hoặc `locales/vi.json`.
- **Acceptance Criteria:**
  - [ ] Label thống nhất list + detail + dashboard
- **Files Affected:**
  - `bks-system-fe/src/utils/partnerMaintenanceDisplay.ts` (mới)
  - `bks-system-fe/src/locales/vi.json`
- **Dependencies:** None
- **Blocks:** T3.3
- **Est. Hours:** 1

### [T3.3] Refactor `Maintenances.tsx`

- **Description:** Wire PATCH thật cho "Tiếp nhận"/"Hoàn thành"; thêm filter status/type/date; fix pagination params; loading/error states.
- **Acceptance Criteria:**
  - [ ] US-02 acceptance criteria pass
  - [ ] Không còn `safeHandleUpdate` mock
- **Files Affected:**
  - `bks-system-fe/src/pages/Partner/Maintenances.tsx`
- **Dependencies:** T3.1, T3.2
- **Blocks:** T4.4
- **Est. Hours:** 4

### [T3.4] `RoomDetail.tsx` tab bảo trì

- **Description:** Sau create refresh list; hiển thị `started_at`/`completed_at`; action buttons theo status.
- **Acceptance Criteria:**
  - [ ] US-01 partial (create đã có, thêm refresh + actions)
- **Files Affected:**
  - `bks-system-fe/src/pages/Partner/RoomDetail.tsx`
- **Dependencies:** T3.1
- **Blocks:** T4.4
- **Est. Hours:** 3

### [T3.5] Create dialog: `block_calendar` checkbox

- **Description:** Checkbox "Khóa lịch trong thời gian bảo trì" (default checked); validate `end_time` khi checked; hiển thị lỗi 409 conflict.
- **Acceptance Criteria:**
  - [ ] US-01: block tạo khi checked
  - [ ] Conflict UI tương tự `RoomBlockDialog`
- **Files Affected:**
  - `bks-system-fe/src/pages/Partner/RoomDetail.tsx`
- **Dependencies:** T3.1
- **Blocks:** T4.2
- **Est. Hours:** 2

### [T3.6] Cancel maintenance dialog

- **Description:** Dialog hủy với `cancellation_reason` bắt buộc; gọi PATCH `cancelled`.
- **Acceptance Criteria:**
  - [ ] US-05 pass
- **Files Affected:**
  - `bks-system-fe/src/pages/Partner/components/MaintenanceCancelDialog.tsx` (mới)
  - `Maintenances.tsx`, `RoomDetail.tsx`
- **Dependencies:** T3.1
- **Blocks:** T4.4
- **Est. Hours:** 2

### [T3.7] Hook `usePartnerMaintenancesQuery` (optional nhưng khuyến nghị)

- **Description:** React Query hook cho list + invalidate sau mutation (pattern `usePartnerDashboardQuery`).
- **Acceptance Criteria:**
  - [ ] Refetch sau create/update không reload manual
- **Files Affected:**
  - `bks-system-fe/src/hooks/Partner/usePartnerMaintenancesQuery.ts` (mới)
- **Dependencies:** T3.1
- **Blocks:** T3.3
- **Est. Hours:** 2

### [T3.8] FE build verify

- **Description:** `npm run build` pass; fix lint/type errors.
- **Acceptance Criteria:**
  - [ ] Build sạch
- **Dependencies:** T3.3–T3.7
- **Blocks:** Phase 4
- **Est. Hours:** 1

---

## Phase 4: Integration & QA

**Goal:** Dashboard, Calendar regression, testcase handoff, release readiness.  
**Duration:** 2 ngày  
**Dependencies:** Phase 2 + 3

### [T4.1] Dashboard `MaintenanceSection` CTA

- **Description:** Nút "Tiếp nhận" trên card khẩn cấp gọi PATCH; navigate room detail on click title.
- **Acceptance Criteria:**
  - [ ] US-03 pass
- **Files Affected:**
  - `bks-system-fe/src/pages/Partner/components/MaintenanceSection.tsx`
- **Dependencies:** T2.9, T3.1
- **Est. Hours:** 2

### [T4.2] Calendar regression

- **Description:** Verify block từ maintenance hiển thị trên Calendar; complete maintenance → block biến mất; `room_block.changed` invalidate cache.
- **Acceptance Criteria:**
  - [ ] US-04 pass
  - [ ] Không regression drag-drop booking
- **Files Affected:**
  - (verify only) `Calendar.tsx`, `useBookingsRealtime.ts`
- **Dependencies:** T2.3, T3.5
- **Est. Hours:** 2

### [T4.3] Properties room status

- **Description:** Verify `RoomsRepository` occupancy `maintenance` khi phiếu active; FE `Properties.tsx` badge "Đang bảo trì".
- **Acceptance Criteria:**
  - [ ] MNT-013 pass (nếu BE đã có logic — chỉ verify/wire FE nếu thiếu)
- **Dependencies:** T2.4
- **Est. Hours:** 1

### [T4.4] E2E smoke script

- **Description:** Script manual/E2E: tạo emergency → dashboard → tiếp nhận → complete → calendar mở.
- **Acceptance Criteria:**
  - [ ] 1 flow khẩn cấp end-to-end documented
- **Files Affected:**
  - `bks-system-fe/business-script/E2E_PARTNER_MAINTENANCE.md` (mới)
- **Dependencies:** T3.3, T4.1, T4.2
- **Est. Hours:** 2

### [T4.5] QC testcase handoff

- **Description:** Chuẩn bị input cho `stack-testcase` → `docs/test-cases/testcase_014_partner_maintenance.md`.
- **Acceptance Criteria:**
  - [ ] Traceability matrix FR MNT-001 → MNT-014
  - [ ] Smoke S-MNT-01 đến S-MNT-05
- **Dependencies:** Plan complete
- **Est. Hours:** 2

---

## Conflict Analysis

### Identified Conflicts

| Conflict ID | Type | Description | Affected | Resolution |
|-------------|------|-------------|----------|------------|
| C1 | File | `RoomMaintenanceService` vs `RoomBlockService` cùng ghi `room_blocks` | Phase 2 | `MaintenanceBlockSyncService` delegate, không duplicate ConflictChecker |
| C2 | Database | Migration `room_maintenances` + có thể sửa seeder | Phase 1 | Single migration; cập nhật `RoomMaintenancesSeeder` sau T1.1 |
| C3 | File | `RoomDetail.tsx` + `Maintenances.tsx` cùng maintenance dialog logic | Phase 3 | Extract `MaintenanceFormDialog` shared component (chỉ nếu duplicate > 30 dòng) |
| C4 | Interface | FE gửi `page`/`per_page`, BE cũ dùng `pagination` | Phase 2 T2.2 | BE accept cả hai; deprecate `pagination` trong doc |
| C5 | Product | Dashboard plan_012 `MaintenanceSection` vs plan_014 CTA | Phase 4 | Coordinate: CTA bảo trì không trùng work queue booking |

### Conflict Resolution Strategy

1. **Block sync:** Luôn đi qua `RoomBlockService` để calendar cache invalidation và event `room_block.changed` hoạt động.
2. **Migration:** Deploy Slice A trước; cột mới nullable/default — không break API cũ.
3. **FE shared dialog:** Surgical — extract chỉ khi implement T3.4/T3.5 thấy duplicate.

---

## Parallelization Opportunities

| Group | Tasks | Điều kiện |
|-------|-------|-----------|
| A | T1.2 ∥ T1.3 | Sau T1.1 |
| B | T2.8 ∥ T2.9 | Sau T2.2 |
| C | T3.2 ∥ T2.7 | Không phụ thuộc API |
| D | T4.5 draft ∥ Phase 3 | BA/QC đọc SRS song song dev |

### Must Be Sequential

1. Phase 1 → Phase 2 (schema trước service)
2. T2.3 → T2.4/T2.5 (sync service trước lifecycle)
3. T2.7 → Phase 3 (API trước FE)
4. T3.3 → T4.4 (UI trước E2E)

---

## Risk Register

| Risk ID | Description | L | I | Mitigation |
|---------|-------------|---|---|------------|
| R1 | Double block (manual + auto) | M | M | PD-MNT-004 dedup link |
| R2 | `end_time` null gây block vô hạn | L | H | PD-MNT-001 bắt buộc end_time khi block |
| R3 | Partner isolation leak | M | H | Feature test T2.10 bắt buộc |
| R4 | Calendar cache stale sau complete | M | M | Reuse `RoomBlockChanged` listener |
| R5 | OQ1/OQ2 stakeholder đổi ý | M | M | Ghi PD-MNT-001/002; flag env nếu cần |

---

## Testing Strategy

### Unit Tests

- State machine transitions hợp lệ / invalid
- `MaintenanceBlockSyncService` map datetime → date
- `ConflictChecker` integration mock

### Feature Tests (`PartnerRoomMaintenanceTest`)

- CRUD lifecycle Partner scoped
- 409 conflict khi `block_calendar=true`
- Complete releases block
- Cancel requires reason

### Integration / Manual

- Calendar hiển thị block maintenance stripe
- Dashboard urgent sort emergency first
- Properties badge maintenance

### QC Test-case Handoff

- **Output:** `docs/test-cases/testcase_014_partner_maintenance.md`
- **Source:** `docs/SRC/srs_partner_maintenance.md` + plan tasks
- **Owner skill:** `stack-testcase`
- **Smoke:** S-MNT-01 create+block, S-MNT-02 conflict, S-MNT-03 accept, S-MNT-04 complete, S-MNT-05 cancel

---

## Rollback Strategy

| Phase | Rollback |
|-------|----------|
| 1 | `migrate:rollback` 1 step; API cũ vẫn chạy (cột mới ignored) |
| 2 | Revert service/controller; giữ migration |
| 3 | Revert FE; BE backward-compatible |
| 4 | Disable CTA feature flag nếu cần hotfix |

---

## Downstream Handoffs

### stack-task

- **Input:** Plan này + `docs/SRC/srs_partner_maintenance.md`
- **Execution order:** Phase 1 → 2 → 3 → 4; mỗi task đánh dấu `[x]` khi xong
- **Persona:** `stack-personas/senior-engineer.md`
- **Definition of Done:** Task acceptance criteria + PHPUnit/FE build pass

### stack-testcase

- **Trigger:** Sau Phase 2 API stable hoặc song song Phase 3
- **Output:** `docs/test-cases/testcase_014_partner_maintenance.md`
- **Coverage:** MNT-001–MNT-014 (trừ MNT-015 Won't)

### stack-review-branch

- **Trigger:** Trước merge feature branch
- **Scope:** Security (ownership), transaction integrity, FE không mock status
- **Base branch:** `develop` hoặc branch chính của team

### report-writer

- **Trigger:** Sau Phase 4 QA pass
- **Output:** `docs/reports/implementation/impl_partner_maintenance_YYYY-MM-DD.md`
- **Nội dung:** Summary, files changed, test results, known limitations

---

## Checklist

### Before Starting

- [ ] Chốt PD-MNT-001/002 với stakeholder (hoặc chấp nhận default trong plan)
- [ ] Branch `feature/partner-maintenance` từ base hiện tại
- [ ] DB dev có seed `room_maintenances` + `room_blocks`

### Phase Completion

- [ ] Phase 1: migration + docs synced
- [ ] Phase 2: API Postman collection / feature tests green
- [ ] Phase 3: Partner UI lifecycle không mock
- [ ] Phase 4: E2E script + testcase handoff

---

## Appendix

### A. File Impact Summary

| File | Phase | Change |
|------|-------|--------|
| `database/migrations/*_extend_room_maintenances_table.php` | 1 | Add columns + index |
| `app/Services/MaintenanceBlockSyncService.php` | 2 | New |
| `app/Services/RoomMaintenanceService.php` | 2 | Major refactor |
| `app/Policies/RoomMaintenancePolicy.php` | 2 | New |
| `app/Http/Resources/Partner/RoomMaintenanceResource.php` | 2 | New |
| `app/Http/Controllers/RoomMaintenanceController.php` | 2 | show, update |
| `routes/api.php` | 2 | New routes |
| `tests/Feature/Partner/PartnerRoomMaintenanceTest.php` | 2 | New |
| `bks-system-fe/src/pages/Partner/Maintenances.tsx` | 3 | Major |
| `bks-system-fe/src/pages/Partner/RoomDetail.tsx` | 3 | Medium |
| `bks-system-fe/src/services/partnerService.ts` | 3 | New methods |
| `bks-system-fe/src/pages/Partner/components/MaintenanceSection.tsx` | 4 | CTA |

### B. Task Quick Reference

| Task ID | Name | Phase | Deps | Est. h |
|---------|------|-------|------|--------|
| T1.1 | Migration | 1 | — | 2 |
| T1.2 | DB docs | 1 | T1.1 | 1 |
| T1.3 | Model | 1 | T1.1 | 1 |
| T2.1 | Policy | 2 | T1.3 | 3 |
| T2.2 | Repository | 2 | T2.1 | 3 |
| T2.3 | Block sync service | 2 | T2.1 | 4 |
| T2.4 | Create service | 2 | T2.3 | 3 |
| T2.5 | Update status | 2 | T2.3 | 3 |
| T2.6 | Validation | 2 | T2.4–5 | 2 |
| T2.7 | Controller/routes | 2 | T2.4–6 | 2 |
| T2.8 | API Resource | 2 | T2.2 | 2 |
| T2.9 | Dashboard sort | 2 | T2.2 | 1 |
| T2.10 | Tests | 2 | T2.7 | 4 |
| T3.1 | FE API client | 3 | T2.7 | 2 |
| T3.2 | i18n labels | 3 | — | 1 |
| T3.3 | Maintenances page | 3 | T3.1 | 4 |
| T3.4 | RoomDetail tab | 3 | T3.1 | 3 |
| T3.5 | block_calendar UI | 3 | T3.1 | 2 |
| T3.6 | Cancel dialog | 3 | T3.1 | 2 |
| T3.7 | React Query hook | 3 | T3.1 | 2 |
| T3.8 | FE build | 3 | T3.3–7 | 1 |
| T4.1 | Dashboard CTA | 4 | T2.9, T3.1 | 2 |
| T4.2 | Calendar regression | 4 | T2.3 | 2 |
| T4.3 | Properties status | 4 | T2.4 | 1 |
| T4.4 | E2E script | 4 | T3.3 | 2 |
| T4.5 | Testcase handoff | 4 | Plan | 2 |

**Tổng ước lượng:** ~52 giờ (~8–11 dev-days tùy parallel).

### C. SRS Requirement Traceability

| SRS FR | Task |
|--------|------|
| MNT-001 | T2.1, T2.10 |
| MNT-002 | T2.2, T3.3, T3.7 |
| MNT-003 | T2.4 |
| MNT-004 | T2.7, T2.8 |
| MNT-005 | T2.5, T3.3 |
| MNT-006 | T2.5, T3.6 |
| MNT-007 | T2.4, T3.5 |
| MNT-008 | T1.1, T2.3 |
| MNT-009 | T2.3, T2.5 |
| MNT-010 | T2.3, T3.5 |
| MNT-011 | T2.8 (Should) |
| MNT-012 | T2.8 (Should) |
| MNT-013 | T4.3 (Should) |
| MNT-014 | T2.9, T4.1 (Should) |
