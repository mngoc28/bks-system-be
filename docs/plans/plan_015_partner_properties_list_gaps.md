# Implementation Plan: Partner Properties List — Filter, Search & UX Gaps

## Document Information

| Mục | Nội dung |
|-----|----------|
| **Plan ID** | PLAN-PP-015 |
| **Created** | 2026-06-19 |
| **Status** | Phase 1 ✅ · Phase 2 ✅ · Phase 3 ✅ (2026-06-19) · Phase 4 pending |
| **Related PRD** | `docs/SRC/prd_partner_properties_list_gaps.md` |
| **Related SRS** | `docs/SRC/srs_partner_portal_360.md` (YC-R01 — quản lý cơ sở & phòng) |
| **Phụ thuộc nền** | PLAN-PARTNER-PROP-009 (`docs/plans/plan_009_partner_properties_api_optimization.md`) — lazy preview, `with_rooms=0`, React Query |
| **Màn hình** | `http://localhost:5173/partner/properties` |
| **API chính** | `GET /api/v1/partner/properties/searchAll` |
| **Risk level** | Low–Medium — chủ yếu FE + mở rộng query repository; không migration bắt buộc (Must/Should) |

### Executive Summary

Triển khai các gap đã chốt trong PRD `prd_partner_properties_list_gaps.md` cho trang Partner Properties: bổ sung bộ lọc/sort, search `keyword`, CTA thêm phòng, confirm xóa đơn, cover image; phase Could mở rộng filter occupancy/rating và URL persistence.

**Khác biệt so với As-Is:** FE chỉ filter `name` + `property_type_id`; không sort; không confirm xóa đơn; `RoomModal` create không có entry point.

**Ước lượng:** 6.5–10 dev-days (1 FE + 0.5 BE), 4 release slice.

### Timeline đề xuất

| Phase | Thời gian | Deliverable | Release slice |
|-------|-----------|-------------|---------------|
| **Phase 1** — Must (BE keyword) | 0.5–1 ngày | API `keyword` + validation + tests | Slice A |
| **Phase 2** — Must (FE core UX) | 1.5–2 ngày | rent_category, keyword UI, thêm phòng, confirm delete | Slice B |
| **Phase 3** — Should | 1.5–2 ngày | Tỉnh/Phường, sort, cover image | Slice C |
| **Phase 4** — Could + QA | 2–4 ngày | Occupancy/rating filters, URL persist, preview search, export (optional) | Slice D |

---

## Quyết định planning (chốt tạm để unblock dev)

| ID | Quyết địn | Ghi chú |
|----|-----------|---------|
| PD-PP-015-001 | Thêm param **`keyword`** mới; giữ **`name`** hoạt động như cũ (chỉ match `properties.name`) | Giải quyết PRD OQ1; backward-compat |
| PD-PP-015-002 | Sort `reviews_avg_rating`: property **chưa có review đẩy xuống cuối** (`NULLS LAST` semantics) | Giải quyết PRD OQ2 |
| PD-PP-015-003 | Dialog xóa đơn: confirm 2 nút, **không** bắt gõ mã; hiển thị `rooms_count` sẽ bị ảnh hưởng | Giải quyết PRD OQ5 |
| PD-PP-015-004 | Filter Tỉnh/Phường + sort đặt trong panel **"Bộ lọc nâng cao"** (collapse mobile) | Tránh filter bar quá dài |
| PD-PP-015-005 | Phase 4 occupancy filter dùng param `occupancy_filter` ∈ `vacant\|occupied\|maintenance` | Đồng bộ `buildOccupancyStatusSelect` |
| PD-PP-015-006 | **Không migration DB** cho Must/Should; index `properties(name, address_detail)` là task optional perf | Đọc `db_overview` — bảng `properties` đã có `address_detail` |

---

## Phase Overview

| Phase | Tên | Tasks | Dependencies | Song song |
|-------|-----|-------|--------------|-----------|
| 1 | Must — Backend keyword | 4 | None | — |
| 2 | Must — FE core UX | 6 | Phase 1 (T1.2 cho keyword FE) | — |
| 3 | Should — Filter nâng cao + cover | 6 | Phase 2 (hook contract) | T3.6 perf check song song T3.4 |
| 4 | Could + Integration QA | 8 | Phase 3 (cho filter combo) | T4.7 testcase draft song song Phase 3 |

---

## Dependency Graph

```text
Phase 1: Must BE
[T1.1] Repository keyword search (name OR address_detail)
[T1.2] Validation keyword param
[T1.3] Feature tests keyword + backward name
[T1.4] api-doc properties.js (optional delta)
         │
         ▼
Phase 2: Must FE
[T2.1] Extend PartnerPropertiesFilters + fetchPartnerPropertiesList
[T2.2] Filter rent_category + reset/clear all
[T2.3] Search UI keyword + placeholder fix
[T2.4] handleAddRoom + CTA header + empty state
[T2.5] Single delete confirm dialog
[T2.6] FE build + manual smoke
         │
         ▼
Phase 3: Should
[T3.1] Province/Ward filter (SearchableSelect)
[T3.2] Sort control + API sort param
[T3.3] BE sort reviews_avg_rating NULLS LAST (nếu cần)
[T3.4] include=cover + card thumbnail
[T3.5] AdvancedFilterPanel collapse mobile
[T3.6] Benchmark list p95 (< 360ms local)
         │
         ├────────────────────┐
         ▼                    ▼
Phase 4: Could + QA     [T4.8] testcase handoff (draft)
[T4.1] occupancy_filter BE
[T4.2] min_rating + has_rooms BE
[T4.3] FE Could filters
[T4.4] URL query persistence (useSearchParams)
[T4.5] Preview room client-side filter
[T4.6] Export Excel endpoint + FE button (optional slice)
[T4.7] Feature tests Could filters
[T4.8] E2E smoke + user-manual delta
```

---

## Hiện trạng kỹ thuật (As-Is)

### Backend

| Thành phần | File | Ghi chú |
|------------|------|---------|
| List API | `PartnerPropertyController::index` | `searchPropertyValidation` + `handleGetAllPropertiesForPartner` |
| Query | `PropertyRepository::getPropertiesForPartner` | Hỗ trợ `province_name`, `ward_name`, `rent_category`, `sort`, `include=cover`; **chưa** `keyword`, occupancy, rating |
| Validation | `PropertiesValidation::searchPropertyValidation` | Chưa rule `keyword` |
| Tests | `tests/Feature/Partner/PartnerPropertiesListTest.php` | 6 tests — pagination, preview, ownership |

### Frontend

| Thành phần | File | Gap |
|------------|------|-----|
| Page | `bks-system-fe/src/pages/Partner/Properties.tsx` | Filter name+type only; no add room CTA; no single delete confirm |
| Hook | `hooks/Partner/usePartnerPropertiesQuery.ts` | `PartnerPropertiesFilters` thiếu rent, keyword, geo, sort |
| Parser | `utils/partnerPropertyData.ts` | Chưa map `cover_image_url` |
| Pattern tham chiếu | `Admin/PropertyManager/components/PropertySearchSection.tsx` | Province/ward/rent_category |
| Pattern URL | `Partner/PropertyRooms.tsx` | `useSearchParams` |

### DB baseline

- Bảng `properties`: `name`, `address_detail`, `rent_category`, `province_id`, `ward_id`, `property_type_id` — **không cần migration** cho Must/Should.
- Tham chiếu: `docs/databases_docs/db_overview_etc_core_schema.md` § properties.

---

## Phase 1: Must — Backend keyword

**Goal:** API hỗ trợ tìm `keyword` trên tên + địa chỉ chi tiết.  
**Duration:** 0.5–1 ngày  
**Dependencies:** None  
**Parallel With:** None

### Tasks

#### [T1.1] Repository — keyword search
- **Description:** Trong `getPropertiesForPartner` và `getAllOrSearchProperties`, thêm block:
  - `keyword` filled → `WHERE (LOWER(name) LIKE ? OR LOWER(address_detail) LIKE ?)`
  - Giữ nguyên filter `name` hiện tại (chỉ `properties.name`).
- **Acceptance Criteria:**
  - [ ] `keyword=nguyen` trả property match tên hoặc `address_detail`
  - [ ] `name=nguyen` vẫn chỉ match tên (regression)
  - [ ] Partner scope `user_id` không đổi
- **Files:** `app/Repositories/PropertyRepository/PropertyRepository.php`
- **Dependencies:** None
- **Blocks:** T2.3
- **Est.:** 2h

#### [T1.2] Validation — keyword param
- **Description:** Thêm rule `keyword` nullable string max:255 vào `searchPropertyValidation`.
- **Acceptance Criteria:**
  - [ ] `keyword` > 255 → 422
  - [ ] Empty string coi như không filter
- **Files:** `app/Http/Validations/PropertiesValidation.php`
- **Dependencies:** None
- **Blocks:** T1.3
- **Est.:** 1h

#### [T1.3] Feature tests — keyword
- **Description:** Mở rộng `PartnerPropertiesListTest`:
  - Test keyword match `address_detail`
  - Test keyword + `rent_category` combo
  - Test partner isolation unchanged
- **Acceptance Criteria:**
  - [ ] ≥ 3 test mới pass
  - [ ] Full `PartnerPropertiesListTest` green
- **Files:** `tests/Feature/Partner/PartnerPropertiesListTest.php`
- **Dependencies:** T1.1, T1.2
- **Blocks:** T2.3
- **Est.:** 2h

#### [T1.4] API doc delta (optional)
- **Description:** Cập nhật `api-doc/properties.js` mô tả `keyword`.
- **Acceptance Criteria:**
  - [ ] Param documented
- **Files:** `api-doc/properties.js`
- **Dependencies:** T1.1
- **Est.:** 0.5h

---

## Phase 2: Must — FE core UX

**Goal:** FR-PP-001 → 004 trên UI.  
**Duration:** 1.5–2 ngày  
**Dependencies:** Phase 1 (T1.1–T1.3)  
**Parallel With:** None

### Tasks

#### [T2.1] Hook — mở rộng filters contract
- **Description:** `PartnerPropertiesFilters` thêm `keyword?`, `rentCategory?`; `fetchPartnerPropertiesList` map query params.
- **Acceptance Criteria:**
  - [ ] Query key thay đổi khi filter đổi → React Query refetch
  - [ ] `with_rooms=0` giữ nguyên (plan 009)
- **Files:** `usePartnerPropertiesQuery.ts`
- **Dependencies:** T1.1
- **Blocks:** T2.2, T2.3
- **Est.:** 2h

#### [T2.2] Filter rent_category
- **Description:** Thêm `Select` hình thức cho thuê; tích hợp nút "Xóa lọc" reset cả `rentCategory`; đổi filter → page 1.
- **Acceptance Criteria:**
  - [ ] US-M01 acceptance criteria pass
  - [ ] Label dùng `t(RENT_CATEGORY.*)`
- **Files:** `Properties.tsx`
- **Dependencies:** T2.1
- **Est.:** 2h

#### [T2.3] Search keyword UI
- **Description:** Đổi state `searchName` → gửi `keyword`; placeholder "Tìm theo tên hoặc địa chỉ…".
- **Acceptance Criteria:**
  - [ ] US-M02 acceptance criteria pass
  - [ ] Debounce 500ms giữ nguyên
- **Files:** `Properties.tsx`, `usePartnerPropertiesQuery.ts`
- **Dependencies:** T2.1, T1.3
- **Est.:** 1.5h

#### [T2.4] CTA Thêm phòng
- **Description:**
  - `handleAddRoom(propertyId)` → `setEditingRoom(null)`, `setTargetPropertyId`, `setIsRoomModalOpen(true)`
  - Nút "Thêm phòng" trên header card property
  - Empty state expand: nút "Thêm phòng ngay"
- **Acceptance Criteria:**
  - [ ] US-M03 acceptance criteria pass
  - [ ] Save success → `invalidatePreview` + `invalidateList`
- **Files:** `Properties.tsx`
- **Dependencies:** None (RoomModal đã có)
- **Est.:** 2h

#### [T2.5] Confirm dialog xóa property đơn
- **Description:** State `deleteTargetProperty`; Dialog cảnh báo (tên property, `rooms_count`, hậu quả cascade); Cancel / Xác nhận xóa.
- **Acceptance Criteria:**
  - [ ] US-M04 acceptance criteria pass
  - [ ] Không gọi API khi Cancel
  - [ ] Lỗi BE hiển thị `toastError` message
- **Files:** `Properties.tsx`
- **Dependencies:** None
- **Est.:** 2h

#### [T2.6] Smoke + lint
- **Description:** `npm run build` FE; manual checklist Slice B.
- **Acceptance Criteria:**
  - [ ] Build pass
  - [ ] Filter + add room + delete confirm hoạt động local
- **Est.:** 1h

**Release Slice B checklist:** Partner login → filter rent → search địa chỉ → thêm phòng từ overview → confirm xóa (cancel + confirm).

---

## Phase 3: Should — Filter nâng cao + cover

**Goal:** FR-PP-005 → 007.  
**Duration:** 1.5–2 ngày  
**Dependencies:** Phase 2  
**Parallel With:** T4.8 testcase draft

### Tasks

#### [T3.1] Province/Ward filter
- **Description:** Tái dùng `partnerService.getProvinces` / `getWardsByProvince`; gửi `province_name`, `ward_name` (label từ option selected); đổi tỉnh reset ward.
- **Acceptance Criteria:**
  - [ ] US-S01 acceptance criteria pass
- **Files:** `Properties.tsx`, `usePartnerPropertiesQuery.ts`
- **Dependencies:** T2.1
- **Est.:** 3h

#### [T3.2] Sort control FE
- **Description:** Select "Sắp xếp" 4 option; map `sort[0][field]` + `sort[0][order]`; default `id desc`.
- **Acceptance Criteria:**
  - [ ] US-S02 acceptance criteria pass (trừ rating nếu T3.3 chưa xong)
- **Files:** `Properties.tsx`, `usePartnerPropertiesQuery.ts`
- **Dependencies:** T2.1
- **Blocks:** T3.3
- **Est.:** 2h

#### [T3.3] BE sort reviews_avg_rating NULLS LAST
- **Description:** Trong `attachPartnerPropertyReviewAggregates` flow hoặc `orderBy` custom: khi sort field `reviews_avg_rating`, NULL cuối danh sách.
- **Acceptance Criteria:**
  - [ ] Property không review ở cuối khi sort rating desc
  - [ ] Không SQL error trên MySQL
- **Files:** `PropertyRepository.php`
- **Dependencies:** T3.2
- **Est.:** 2h

#### [T3.4] Cover image on card
- **Description:** Request `include=cover`; parse `cover_image_url`; thumbnail 64×64 + fallback `Building2`.
- **Acceptance Criteria:**
  - [ ] US-S03 acceptance criteria pass
  - [ ] Property không ảnh vẫn render card đẹp
- **Files:** `usePartnerPropertiesQuery.ts`, `partnerPropertyData.ts`, `Properties.tsx`
- **Dependencies:** T2.1
- **Est.:** 2h

#### [T3.5] AdvancedFilterPanel mobile
- **Description:** Wrap Tỉnh/Phường/Sort vào `AdvancedFilterPanel` hoặc component tương tự Admin; filter cơ bản (keyword, type, rent) luôn hiện.
- **Acceptance Criteria:**
  - [ ] Mobile 375px không overflow ngang
  - [ ] PD-PP-015-004 đạt
- **Files:** `Properties.tsx`, có thể tách `PartnerPropertyFilterPanel.tsx`
- **Dependencies:** T3.1, T3.2
- **Est.:** 2h

#### [T3.6] Performance benchmark
- **Description:** Đo p95 `searchAll` với `include=cover` + joins province khi filter; ghi vào plan completion note.
- **Acceptance Criteria:**
  - [ ] p95 local < 360ms với seed data hiện tại
  - [ ] Nếu vượt → document và đề xuất index (không block release)
- **Dependencies:** T3.1, T3.4
- **Est.:** 1h

---

## Phase 4: Could + Integration QA

**Goal:** FR-PP-008 → 012 + handoff QA.  
**Duration:** 2–4 ngày (export optional)  
**Dependencies:** Phase 3  
**Parallel With:** None (export T4.6 có thể defer)

### Tasks

#### [T4.1] BE occupancy_filter
- **Description:** Param `occupancy_filter` ∈ `vacant|occupied|maintenance`; `WHERE EXISTS` subquery room với logic occupancy (reuse `buildOccupancyStatusSelect` hoặc extract helper).
- **Acceptance Criteria:**
  - [ ] US-C01 filter trả đúng property có ≥1 phòng match
  - [ ] Validation reject giá trị invalid
- **Files:** `PropertyRepository.php`, `PropertiesValidation.php`
- **Est.:** 4h

#### [T4.2] BE min_rating + has_rooms
- **Description:** `min_rating` (nullable numeric 0–5), `has_rooms` (0|1).
- **Acceptance Criteria:**
  - [ ] US-C02 acceptance criteria pass
- **Files:** `PropertyRepository.php`, `PropertiesValidation.php`
- **Est.:** 3h

#### [T4.3] FE Could filters
- **Description:** Thêm controls trong advanced panel; badge tóm tắt occupancy (optional nếu API trả aggregate — else defer badge).
- **Acceptance Criteria:**
  - [ ] Kết hợp filter Must+Should+Could không conflict
- **Files:** `Properties.tsx`, hook
- **Dependencies:** T4.1, T4.2
- **Est.:** 3h

#### [T4.4] URL query persistence
- **Description:** `useSearchParams` hydrate/sync filter state; `replace: true` on change.
- **Acceptance Criteria:**
  - [ ] US-C04 acceptance criteria pass
  - [ ] Reload giữ filter
- **Files:** `Properties.tsx`
- **Dependencies:** T2.1, T3.1
- **Est.:** 3h

#### [T4.5] Preview room client-side filter
- **Description:** Khi expand: local filter tên + status trên `previewRooms` (data đã load ≤6).
- **Acceptance Criteria:**
  - [ ] US-C03 pass với client-side only (không gọi API thêm)
  - [ ] Banner "xem toàn bộ" vẫn hiện khi `rooms_count > 6`
- **Files:** `Properties.tsx`
- **Est.:** 2h

#### [T4.6] Export Excel (optional — Slice D+)
- **Description:** `GET /partner/properties/export` trả xlsx; FE nút export; max 500 rows; apply filters hiện tại.
- **Acceptance Criteria:**
  - [ ] US-C05 pass
  - [ ] Timeout guard
- **Files:** BE controller/service mới, `partnerService.ts`, `Properties.tsx`
- **Est.:** 6h — **có thể Won't nếu Product chốt OQ4**

#### [T4.7] Feature tests Could
- **Description:** Tests occupancy, has_rooms, min_rating.
- **Acceptance Criteria:**
  - [ ] ≥ 4 test mới pass
- **Files:** `PartnerPropertiesListTest.php`
- **Dependencies:** T4.1, T4.2
- **Est.:** 3h

#### [T4.8] E2E smoke + docs
- **Description:** Cập nhật `business-script/E2E_PARTNER_MAINTENANCE.md` hoặc tạo `E2E_PARTNER_PROPERTIES.md`; delta `docs/user-manual/partner-portal.md` §2.
- **Acceptance Criteria:**
  - [ ] Script cover Must+Should flows
- **Est.:** 2h

---

## Conflict Analysis

| Conflict ID | Type | Description | Resolution |
|-------------|------|-------------|------------|
| C1 | File | `Properties.tsx` sửa ở Phase 2, 3, 4 | Sequential phases; tách `PartnerPropertyFilterPanel.tsx` ở T3.5 |
| C2 | API | `name` vs `keyword` | Giữ cả hai; FE chuyển sang `keyword` only (PD-PP-015-001) |
| C3 | Perf | `include=cover` + province join | Lazy join chỉ khi `province_name` filled (đã có trong repository) |
| C4 | Branch | Song song maintenance/plan 014 | Không đụng `RoomMaintenanceService`; chỉ `Properties.tsx` |

---

## Parallelization Opportunities

| Group | Tasks | Điều kiện |
|-------|-------|-----------|
| A | T1.2 ∥ T1.1 (sau khi T1.1 spec rõ) | Cùng dev BE |
| B | T2.4 ∥ T2.5 | Cùng file nhưng vùng UI khác — 1 PR |
| C | T3.4 ∥ T3.1 | FE khác nhau — 1 dev |
| D | T4.8 testcase draft ∥ Phase 3 | QA/BA |

**Bắt buộc tuần tự:** Phase 1 → 2 → 3 → 4.

---

## Risk Register

| Risk ID | Mô tả | L | I | Mitigation |
|---------|-------|---|---|------------|
| R1 | Sort rating SQL phức tạp | M | M | Subquery aggregate + order; test seed |
| R2 | Filter bar quá dài mobile | H | M | PD-PP-015-004 AdvancedFilterPanel |
| R3 | Keyword search chậm scale lớn | L | M | Optional index migration follow-up |
| R4 | Export Excel scope creep | M | L | T4.6 optional / Won't theo OQ4 |

---

## Testing Strategy

### Unit / Feature (BE)

- Mở rộng `PartnerPropertiesListTest`: keyword, rent_category, province, sort, could filters.
- Regression: `with_rooms=0`, ownership isolation, legacy `with_rooms=1`.

### FE

- Manual smoke per release slice B/C/D.
- Lint + `npm run build`.

### Integration

- Filter combo: keyword + rent + province + sort.
- Add room từ overview → hiện trong preview expand.
- Delete confirm → cancel không xóa; confirm xóa property test (seed không booking).

### QC Test-case Handoff

- **Output:** `docs/test-cases/testcase_015_partner_properties_list.md`
- **Source:** PRD + plan tasks
- **Owner skill:** `stack-testcase`
- **Traceability:** US-M01→M04, US-S01→S03, US-C01→C05

| TC ID | Kịch bản |
|-------|----------|
| PP-015-01 | Lọc rent_category |
| PP-015-02 | Keyword match address_detail |
| PP-015-03 | Thêm phòng từ overview |
| PP-015-04 | Confirm xóa đơn — cancel |
| PP-015-05 | Confirm xóa đơn — success |
| PP-015-06 | Lọc tỉnh/phường |
| PP-015-07 | Sort rating — null last |
| PP-015-08 | Cover image hiển thị |
| PP-015-09 | URL persist reload |
| PP-015-10 | Occupancy filter (Could) |

---

## Rollback Strategy

| Phase | Rollback |
|-------|----------|
| 1 | Revert repository + validation; `name` vẫn hoạt động |
| 2 | Revert FE Properties.tsx + hook; API backward-compat |
| 3 | Revert UI filter/sort/cover; bỏ `include=cover` param |
| 4 | Feature-flag Could filters qua env `PARTNER_PROP_ADVANCED_FILTERS=false` (optional) |

---

## Downstream Handoffs

### stack-task

- **Input:** Plan này + `docs/SRC/prd_partner_properties_list_gaps.md`
- **Execution order:** Phase 1 → 2 → 3 → 4; Slice B có thể release trước Slice D
- **Persona:** `stack-personas/senior-engineer.md`
- **Definition of Done:** Task acceptance + tests/build pass

### stack-testcase

- **Trigger:** Sau Phase 2 (Must stable) hoặc song song Phase 3
- **Output:** `docs/test-cases/testcase_015_partner_properties_list.md`

### stack-review-branch

- **Trigger:** Trước merge
- **Scope:** Partner scope API, không leak property partner khác; UX delete confirm; perf list API
- **Base branch:** `develop` hoặc branch chính team

### report-writer

- **Trigger:** Sau Slice C hoặc D
- **Output:** `docs/reports/implementation/impl_partner_properties_list_YYYY-MM-DD.md`

---

## Checklist

### Before Starting

- [ ] Chốt PD-PP-015-001 → 006 (hoặc chấp nhận default trong plan)
- [ ] Branch `feature/partner-properties-list-gaps`
- [ ] Xác nhận plan 009 đã deploy (`with_rooms=0` + preview endpoint)

### Phase Completion

- [x] Phase 1: keyword API + tests green (11/11 `PartnerPropertiesListTest`, ~257s)
- [x] Phase 2: Slice B — rent filter, keyword UI, thêm phòng, confirm xóa đơn; FE build pass
- [x] Phase 3: Slice C — Tỉnh/Phường, sort, cover image, advanced filter panel; FE build pass
- [x] Phase 4: Could filters + URL persist + preview filter + testcase handoff (export deferred — T4.6)

---

## Appendix

### A. File Impact Summary

| File | Phase | Change |
|------|-------|--------|
| `app/Repositories/PropertyRepository/PropertyRepository.php` | 1,3,4 | keyword, sort rating, could filters |
| `app/Http/Validations/PropertiesValidation.php` | 1,4 | New params |
| `tests/Feature/Partner/PartnerPropertiesListTest.php` | 1,4 | Extend tests |
| `bks-system-fe/src/hooks/Partner/usePartnerPropertiesQuery.ts` | 2,3 | Filters contract |
| `bks-system-fe/src/utils/partnerPropertyData.ts` | 3 | cover_image_url |
| `bks-system-fe/src/pages/Partner/Properties.tsx` | 2,3,4 | Major UI |
| `bks-system-fe/src/pages/Partner/components/PartnerPropertyFilterPanel.tsx` | 3 | New (optional) |
| `docs/user-manual/partner-portal.md` | 4 | Delta §2 |
| `docs/SRC/prd_partner_properties_list_gaps.md` | — | Source PRD |

### B. Task Quick Reference

| Task ID | Name | Phase | Deps | Est. h |
|---------|------|-------|------|--------|
| T1.1 | Repository keyword | 1 | — | 2 |
| T1.2 | Validation keyword | 1 | — | 1 |
| T1.3 | Feature tests keyword | 1 | T1.1–2 | 2 |
| T1.4 | API doc | 1 | T1.1 | 0.5 |
| T2.1 | Hook filters | 2 | T1.1 | 2 |
| T2.2 | rent_category UI | 2 | T2.1 | 2 |
| T2.3 | keyword UI | 2 | T2.1, T1.3 | 1.5 |
| T2.4 | Add room CTA | 2 | — | 2 |
| T2.5 | Delete confirm | 2 | — | 2 |
| T2.6 | Smoke | 2 | T2.2–5 | 1 |
| T3.1 | Province/Ward | 3 | T2.1 | 3 |
| T3.2 | Sort FE | 3 | T2.1 | 2 |
| T3.3 | Sort rating BE | 3 | T3.2 | 2 |
| T3.4 | Cover image | 3 | T2.1 | 2 |
| T3.5 | Advanced panel | 3 | T3.1–2 | 2 |
| T3.6 | Benchmark | 3 | T3.4 | 1 |
| T4.1 | occupancy BE | 4 | Phase 3 | 4 |
| T4.2 | rating/has_rooms BE | 4 | Phase 3 | 3 |
| T4.3 | Could FE | 4 | T4.1–2 | 3 |
| T4.4 | URL persist | 4 | Phase 2–3 | 3 |
| T4.5 | Preview filter | 4 | — | 2 |
| T4.6 | Export Excel | 4 | T4.1–2 | 6 |
| T4.7 | Could tests | 4 | T4.1–2 | 3 |
| T4.8 | E2E + manual | 4 | — | 2 |

**Tổng ước lượng:** ~48h (~6.5–10 dev-days tùy parallel và có T4.6).

### C. Traceability PRD → Plan

| FR ID | Tasks |
|-------|-------|
| FR-PP-001 | T2.2 |
| FR-PP-002 | T1.1–T1.3, T2.3 |
| FR-PP-003 | T2.4 |
| FR-PP-004 | T2.5 |
| FR-PP-005 | T3.1 |
| FR-PP-006 | T3.2, T3.3 |
| FR-PP-007 | T3.4 |
| FR-PP-008 | T4.1, T4.3 |
| FR-PP-009 | T4.2, T4.3 |
| FR-PP-010 | T4.5 |
| FR-PP-011 | T4.4 |
| FR-PP-012 | T4.6 |
