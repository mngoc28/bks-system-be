# Implementation Plan: Gợi ý phòng theo điểm du lịch cụ thể (Homepage)

## 1. Thông tin kế hoạch

| Mục | Nội dung |
|-----|----------|
| **Mã plan** | PLAN-RTM-HP-007 |
| **Ngày tạo** | 2026-05-29 |
| **Trạng thái** | Implemented (2026-05-29) — chờ Ops mapping production |
| **SRS liên quan** | [docs/SRC/srs_room_tourist_spot_mapping.md](../SRC/srs_room_tourist_spot_mapping.md), [docs/SRC/srs_landing_page_prominence.md](../SRC/srs_landing_page_prominence.md) |
| **Plan nền tảng** | [plan_004.md](plan_004.md) (schema + summary + admin CRUD — đã implement) |
| **Design liên quan** | [design_004.md](../designs/design_004.md), [design_003.md](../designs/design_003.md) |
| **Tài liệu hiển thị** | [docs/homepage-display-criteria.md](../../../docs/homepage-display-criteria.md) (cập nhật sau Phase 5) |
| **Canonical DB** | [docs/databases_docs/db_overview_etc_core_schema.md](../databases_docs/db_overview_etc_core_schema.md) |
| **Phạm vi** | Homepage section “Phòng được gợi ý theo từng điểm đến”: tab theo **điểm du lịch** (Sa Pa, Cát Bà, Lý Sơn, Bà Nà Hill, …), API grouped-by-spot, FE refactor, search deep-link, QA/Ops mapping |
| **Out of scope** | Bản đồ tương tác; live routing; redesign toàn landing; migration schema mới (trừ seed data) |

### 1.1. Executive summary

Hiện UI section ghi **“Gợi ý theo điểm du lịch”** nhưng tab và API vẫn gom theo **tỉnh** (`GET home/rooms/rooms-by-province`). Plan này chuyển nguồn dữ liệu sang **`tourist_spots` + `room_tourist_spot_maps`** (đã có từ PLAN-RTM-004), bổ sung API mới, refactor FE, và mở rộng master data cho các điểm user yêu cầu.

**Ước lượng:** ~1.5 MM dev full-stack + ~0.3–0.5 MM nhập liệu Ops (mapping phòng).

**Quyết định đã chốt:** [DEC-260529-RTM-HP-001](../memory/decisions.md) — tab theo spot; ẩn tab khi thiếu phòng mapped; giữ API province một release để rollback.

---

## 2. Hiện trạng (as-is)

| Thành phần | Trạng thái | Ghi chú |
|------------|------------|---------|
| `tourist_spots`, `room_tourist_spot_maps` | ✅ Migration + model | plan_004 |
| `RoomTouristSummaryService` | ✅ Enrich card | `tourist_summary` trên room list |
| Admin CRUD spot/mapping | ✅ | `/api/v1/admin/tourist-spots`, `room-tourist-spot-maps` |
| `GET home/rooms/rooms-by-province` | ✅ | `ROW_NUMBER() PARTITION BY province_id` |
| `SuggestedRoomsByProvince.tsx` | ✅ UI tab tỉnh | Copy “điểm du lịch” nhưng label tỉnh |
| Seeder spot | ⚠️ Thiếu Sa Pa, Cát Bà, Lý Sơn | Có Bà Nà Hill, Fansipan (Lào Cai) |
| API grouped-by-spot | ❌ | Chưa có |
| Search filter `tourist_spot_slug` | ❌ | Chưa có |

### 2.1. Gap so với mockup / yêu cầu

- Tab mong muốn: **Sa Pa · Cát Bà · Lý Sơn · Bà Nà Hill · …** — không phải “Đà Nẵng / Khánh Hòa / Quảng Ninh”.
- CTA “Xem phòng tại {điểm}” cần deep-link search theo spot, không chỉ `RoomByProvince`.

---

## 3. Mục tiêu (to-be)

1. Tab homepage = **tên điểm du lịch** (`tourist_spot.name`), phụ đề có thể hiện `region_label` (vd: “Lào Cai · Sa Pa”).
2. Mỗi tab carousel **4–12 phòng** có mapping tới spot đó; card giữ nhãn `tourist_summary` (thời gian di chuyển).
3. Sort phòng trong tab: `is_primary DESC` → `reviews_avg_rating DESC` → `reviews_count DESC` → `travel_time_minutes ASC` → `rooms.updated_at DESC`.
4. Tab không đủ dữ liệu → **ẩn tab** (mặc định). Fallback theo `region_label` chỉ khi `HOMEPAGE_SPOT_FALLBACK_REGION=true` (BE) / `VITE_HOMEPAGE_SPOT_FALLBACK_REGION=true` (FE).
5. API cũ `rooms-by-province` **giữ** ít nhất 1 release; FE chuyển qua flag `VITE_HOMEPAGE_SUGGESTIONS_BY_SPOT=true`.

### 3.1. Danh mục điểm giai đoạn 1 (MVP tab)

| Thứ tự tab | Tên hiển thị | `slug` đề xuất | `region_label` | Ghi chú seed |
|------------|---------------|----------------|----------------|--------------|
| 1 | Sa Pa | `sa-pa` | Lào Cai | Spot mới; tách khỏi chỉ Fansipan |
| 2 | Cát Bà | `cat-ba` | Hải Phòng | Spot mới |
| 3 | Lý Sơn | `ly-son` | Quảng Ngãi | Spot mới |
| 4 | Bà Nà Hill | `ba-na-hill` | Đà Nẵng | Đã có seeder |
| 5 | Vịnh Hạ Long | `vinh-ha-long` | Quảng Ninh | Tùy chọn tab thứ 5 |
| 6 | VinWonders Nha Trang | `vinwonders-nha-trang` | Khánh Hòa | Tùy chọn tab thứ 6 |

**FE constant (đề xuất):**

```ts
// bks-system-fe/src/constant.ts
export const SUGGESTED_ROOM_SPOT_PRIORITY = [
  "Sa Pa",
  "Cát Bà",
  "Lý Sơn",
  "Bà Nà Hill",
  "Vịnh Hạ Long",
  "VinWonders Nha Trang",
] as const;
```

**Ngưỡng tối thiểu hiển thị tab:** ≥ **4** phòng `PUBLIC` có mapping tới spot (cấu hình được qua env `HOMEPAGE_SPOT_MIN_ROOMS=4`).

---

## 4. Tổng quan 5 phase

| Phase | Tên | Mục tiêu | Ước lượng | Phụ thuộc |
|-------|-----|---------|-----------|-----------|
| **1** | Master data & chuẩn bị phạm vi | Seed spot, checklist Ops, cập nhật tài liệu tiêu chí | 1–2 ngày | plan_004 schema |
| **2** | API gợi ý theo điểm | Endpoint grouped-by-spot, validation, test | 2–3 ngày | Phase 1 (spot id/slug) |
| **3** | Frontend homepage | Component tab spot, hook, feature flag | 2–3 ngày | Phase 2 contract |
| **4** | Search & deep link | Filter search theo spot; CTA từ homepage | 1–2 ngày | Phase 2–3 |
| **5** | QA, Ops & phát hành | Test case, mapping ≥8 phòng/spot, docs, rollout | 1–2 ngày | Phase 1–4 |

### 4.1. Dependency graph

```
Phase 1 (Master data + Ops checklist)
    │
    ├──────────────────┐
    v                  v
Phase 2 (API)     Phase 1b Ops mapping (song song, kéo dài)
    │
    v
Phase 3 (FE homepage) ──► Phase 4 (Search deep-link)
    │
    v
Phase 5 (QA + docs + release)
```

### 4.2. Song song hóa

| Nhóm | Công việc | Điều kiện |
|------|-----------|-----------|
| P1 + Ops | Seed spot (dev) + map phòng (Ops) | Sau khi slug spot chốt |
| P2 + P3 | BE API + FE component skeleton | Sau khi chốt response DTO (task 2.2) |
| P4 | Search filter | Sau P2 merge; có thể overlap cuối P3 |
| P5 | Testcase + UAT | Sau P3 deploy staging |

---

## Phase 1 — Master data & chuẩn bị phạm vi

**Goal:** Có danh mục spot MVP trong DB, quy tắc nghiệp vụ được ghi nhận, Ops biết cách map phòng.

**Duration:** 1–2 ngày  
**Dependencies:** plan_004 hoàn tất schema  
**Parallel:** Ops bắt đầu mapping ngay khi spot seed xong

### Task 1.1 — Chốt phạm vi & tiêu chí (workshop ngắn)

- **Mô tả:** Xác nhận danh sách tab MVP, ngưỡng ẩn tab, có/không fallback region.
- **Acceptance:**
  - [ ] Danh sách 4–6 spot trong mục 3.1 được PM/BA sign-off.
  - [ ] `HOMEPAGE_SPOT_MIN_ROOMS` và fallback env được ghi trong plan + `.env.example`.
  - [ ] Quyết định giữ `rooms-by-province` 1 release — documented.
- **Files:** Plan này; `docs/memory/decisions.md` (nếu đổi).
- **Est.:** 2h

### Task 1.2 — Bổ sung `TouristSpotsTableSeeder`

- **Mô tả:** Thêm spot Sa Pa, Cát Bà, Lý Sơn; đặt `is_featured=true`, `sort_order` theo bảng 3.1; đảm bảo slug unique.
- **Acceptance:**
  - [ ] `php artisan db:seed --class=TouristSpotsTableSeeder` chạy không lỗi trên DB sạch.
  - [ ] Slug `sa-pa`, `cat-ba`, `ly-son`, `ba-na-hill` query được.
  - [ ] Không truncate mất mapping production nếu seeder chỉ dùng local — ghi chú trong seeder/README.
- **Files:**
  - `database/seeders/TouristSpotsTableSeeder.php`
- **Dependencies:** None  
- **Blocks:** 1.3, 2.1  
- **Est.:** 3h

### Task 1.3 — (Tùy chọn) `RoomTouristSpotMapsTableSeeder` demo

- **Mô tả:** Gán vài phòng public mẫu tới spot MVP để dev/QA không section trống.
- **Acceptance:**
  - [ ] Mỗi spot MVP có ≥ 4 phòng sau seed local.
  - [ ] Mỗi phòng có tối đa 1 `is_primary=true` cho spot chính.
- **Files:**
  - `database/seeders/RoomTouristSpotMapsTableSeeder.php`
- **Dependencies:** 1.2  
- **Est.:** 4h

### Task 1.4 — Checklist Ops & hướng dẫn Admin

- **Mô tả:** Tài liệu ngắn cho Ops: tạo/sửa spot, map phòng, điền `travel_time_minutes`, đánh dấu primary.
- **Acceptance:**
  - [ ] Checklist: ≥ **8 phòng/spot** trên staging/production trước go-live.
  - [ ] Có ví dụ map “phòng gần Bà Nà Hill 45 phút”.
- **Files:**
  - `docs/ops/homepage-tourist-spot-mapping-checklist.md` (tạo mới, ngắn)
- **Est.:** 2h

### Task 1.5 — Cập nhật canonical DB doc (nếu chỉ seed, không đổi schema)

- **Mô tả:** Ghi chú danh mục spot MVP vào overview (phần ví dụ), không tạo migration mới.
- **Acceptance:**
  - [ ] `db_overview_etc_core_schema.md` phản ánh ví dụ spot Sa Pa / Cát Bà / Lý Sơn.
- **Files:** `docs/databases_docs/db_overview_etc_core_schema.md`  
- **Est.:** 1h

**Phase 1 exit criteria:** Spot MVP tồn tại trong DB; Ops checklist sẵn sàng; dev có seed demo đủ 4 phòng/spot.

---

## Phase 2 — API gợi ý phòng theo điểm du lịch

**Goal:** Public API trả nhóm phòng theo `tourist_spot_id`, cùng pattern enrich như province API.

**Duration:** 2–3 ngày  
**Dependencies:** Phase 1 (spot ids/slugs)  
**Parallel:** Không — FE Phase 3 chờ contract task 2.2

### Task 2.1 — Repository `getSuggestedRoomsByTouristSpot`

- **Mô tả:** Clone pattern `getSuggestedRoomsByProvince` — join `room_tourist_spot_maps`, filter `tourist_spots.is_active`, `rooms.status = PUBLIC`.
- **SQL ordering trong partition:**
  1. `room_tourist_spot_maps.is_primary DESC`
  2. `reviews_avg_rating DESC`
  3. `reviews_count DESC`
  4. `room_tourist_spot_maps.travel_time_minutes ASC`
  5. `rooms.updated_at DESC`
- **Window:** `ROW_NUMBER() OVER (PARTITION BY tourist_spot_id ORDER BY ...)`.
- **Acceptance:**
  - [ ] Method trên `RoomsRepository` + interface.
  - [ ] `limit` áp dụng per spot (default 12, max 20).
  - [ ] Chỉ phòng có mapping tới spot trong `tourist_spot_ids` filter.
- **Files:**
  - `app/Repositories/RoomsRepository/RoomsRepository.php`
  - `app/Repositories/RoomsRepository/RoomsRepositoryInterface.php`
- **Dependencies:** 1.2  
- **Blocks:** 2.2, 2.3  
- **Test:** Unit/integration query với DB seed — ít nhất 2 spot, kiểm tra limit và sort primary.  
- **Est.:** 6h

### Task 2.2 — Service + response shape (contract freeze)

- **Mô tả:** `RoomsService::handleSuggestedRoomsByTouristSpot()` group collection; enrich `RoomTouristSummaryService`.
- **Response DTO (mỗi group):**

```json
{
  "tourist_spot_id": 5,
  "tourist_spot_name": "Bà Nà Hill",
  "tourist_spot_slug": "ba-na-hill",
  "region_label": "Đà Nẵng",
  "rooms": [ /* room card fields + tourist_summary */ ]
}
```

- **Acceptance:**
  - [ ] Shape ổn định — ghi vào `bks-system-fe/src/dataHelper/EU/room.dataHelper.ts` type `SuggestedRoomsByTouristSpotGroup`.
  - [ ] Group không có phòng → không xuất hiện trong `data` (hoặc empty array tùy chốt — mặc định: **omit**).
  - [ ] `tourist_summary` nhất quán với top-rated / search.
- **Files:**
  - `app/Services/RoomsService.php`
  - `app/Services/RoomTouristSummaryService.php` (chỉ nếu cần mở rộng)
- **Dependencies:** 2.1  
- **Blocks:** 2.3, Phase 3  
- **Est.:** 4h

### Task 2.3 — Validation, route, controller

- **Mô tả:**
  - `RoomsValidation::suggestedRoomsByTouristSpotValidation`
  - Params: `tourist_spot_ids[]` (required, array int), `limit` (optional int 1–20)
  - Route: `GET /api/v1/home/rooms/rooms-by-tourist-spot`
  - `HomeController::getRoomsByTouristSpot`
- **Acceptance:**
  - [ ] Validation lỗi trả 422 chuẩn project.
  - [ ] Route đăng ký trong `routes/api.php` cùng group `home/rooms`.
  - [ ] OpenAPI/api-doc cập nhật nếu repo đang maintain (`api-doc/`).
- **Files:**
  - `app/Http/Validations/RoomsValidation.php`
  - `app/Http/Controllers/EU/HomeController.php`
  - `routes/api.php`
- **Dependencies:** 2.2  
- **Est.:** 3h

### Task 2.4 — (Tùy chọn) Fallback region trong service

- **Mô tả:** Khi `config('homepage.spot_fallback_region')` true và spot < min rooms, bổ sung phòng cùng `region_label` chưa map (giới hạn chặt).
- **Acceptance:**
  - [ ] Mặc định `false`; bật qua env.
  - [ ] Phòng fallback không ghi đè phòng đã mapped.
- **Dependencies:** 2.2  
- **Est.:** 4h (optional — có thể defer post-MVP)

### Task 2.5 — Feature test API

- **Mô tả:** `tests/Feature/EU/SuggestedRoomsByTouristSpotTest.php`
- **Scenarios:**
  - Happy path: 2 spot ids → 2 groups, đúng limit.
  - Spot không có mapping → không có group / group rỗng omitted.
  - Invalid `tourist_spot_ids` → 422.
  - Primary room xếp trước khi cùng rating.
- **Acceptance:**
  - [ ] Test xanh trong CI local.
- **Est.:** 4h

**Phase 2 exit criteria:** Endpoint deploy staging; contract DTO frozen; feature test pass.

---

## Phase 3 — Frontend homepage

**Goal:** Section tab hiển thị đúng tên điểm du lịch; gọi API mới; feature flag rollback province.

**Duration:** 2–3 ngày  
**Dependencies:** Phase 2 task 2.2 (DTO)  
**Parallel:** UI polish sau khi API staging sẵn

### Task 3.1 — API client & hook

- **Mô tả:**
  - `roomApi.getSuggestedRoomsByTouristSpot({ tourist_spot_ids, limit })`
  - `useSuggestedRoomsByTouristSpotQuery` trong `useRoomQuery.ts`
  - Type `SuggestedRoomsByTouristSpotGroup` trong `room.dataHelper.ts`
- **Acceptance:**
  - [ ] React Query cache key ổn định.
  - [ ] `enabled` khi đã resolve được `tourist_spot_ids`.
- **Files:**
  - `bks-system-fe/src/api/EU/roomApi.ts`
  - `bks-system-fe/src/hooks/EU/useRoomQuery.ts`
  - `bks-system-fe/src/dataHelper/EU/room.dataHelper.ts`
- **Est.:** 3h

### Task 3.2 — Resolve spot ids từ priority list

- **Mô tả:** Trong `Home/index.tsx`:
  - Gọi API public list spot **hoặc** embed map slug→id từ config (ưu tiên: endpoint `GET tourist-spots/featured` nếu có; nếu chưa có — dùng ids từ response grouped API lần đầu hoặc constant slug + lookup nhẹ).
  - **Đề xuất tối giản MVP:** Truyền `tourist_spot_ids` từ BE config endpoint mới **hoặc** hardcode slug list → BE trả ids trong meta (task 2.3 mở rộng nhỏ `?slugs[]=sa-pa` — optional).
  - Map `SUGGESTED_ROOM_SPOT_PRIORITY` → ordered groups.
- **Acceptance:**
  - [ ] Thứ tự tab khớp constant, không phụ thuộc thứ tự API raw.
  - [ ] Tab không đủ phòng (theo `HOMEPAGE_SPOT_MIN_ROOMS`) không render — reuse filter như `orderedGroups.filter(length > 0)`.
- **Files:**
  - `bks-system-fe/src/constant.ts`
  - `bks-system-fe/src/pages/EndUser/Home/index.tsx`
- **Est.:** 4h

### Task 3.3 — Component `SuggestedRoomsByTouristSpot`

- **Mô tả:** Refactor từ `SuggestedRoomsByProvince.tsx`:
  - Props: `groups`, `prioritySpotNames`, `loading`
  - Tab label: `tourist_spot_name`
  - Subtitle: `region_label` (uppercase nhỏ phía trên title)
  - CTA: `Xem phòng tại {spot}` → route Phase 4
  - `sectionId`: `suggested-spot-{slug}`
- **Acceptance:**
  - [ ] UI khớp mockup: badge xanh, tab pill, carousel 4 cột.
  - [ ] `toRoomCard` giữ `tourist_summary`, `property_type_name` cho label HOMESTAY/KHÁCH SẠN.
  - [ ] Loading skeleton tương đương component cũ.
- **Files:**
  - `bks-system-fe/src/pages/EndUser/Home/components/SuggestedRoomsByTouristSpot.tsx` (mới)
  - `SuggestedRoomsByProvince.tsx` — giữ cho rollback hoặc deprecate sau 1 release
- **Est.:** 5h

### Task 3.4 — Feature flag & tích hợp Home

- **Mô tả:**
  - `VITE_HOMEPAGE_SUGGESTIONS_BY_SPOT=true|false`
  - `false` → giữ `useSuggestedRoomsByProvinceQuery` (hành vi cũ).
- **Acceptance:**
  - [ ] Toggle không cần rebuild BE.
  - [ ] `.env.example` FE ghi chú.
- **Files:** `Home/index.tsx`, `bks-system-fe/.env.example`  
- **Est.:** 2h

### Task 3.5 — Đa dạng loại hình trên carousel (nice-to-have trong MVP)

- **Mô tả:** Sau khi query 12 phòng, FE (hoặc BE) ưu tiên tối đa 1 phòng mỗi `property_type_name` trong 4 slot đầu carousel nếu đủ dữ liệu.
- **Acceptance:**
  - [ ] Carousel 4 card đầu không trùng hết một loại hình khi DB có mix.
- **Est.:** 3h (optional)

**Phase 3 exit criteria:** Staging homepage hiển thị tab Sa Pa / Cát Bà / … với phòng thật hoặc seed; flag rollback hoạt động.

---

## Phase 4 — Search & deep link

**Goal:** CTA từ homepage mở kết quả tìm kiếm đúng ngữ cảnh điểm du lịch.

**Duration:** 1–2 ngày  
**Dependencies:** Phase 2 (filter BE), Phase 3 (CTA href)

### Task 4.1 — BE: filter `rooms/search` theo spot

- **Mô tả:** Thêm query `tourist_spot_id` hoặc `tourist_spot_slug` vào public search.
- **Acceptance:**
  - [ ] Chỉ trả phòng có mapping tới spot (join maps).
  - [ ] Tương thích backward: không truyền param → hành vi cũ.
  - [ ] Validation slug/id.
- **Files:**
  - `app/Repositories/RoomsRepository/*` (search method)
  - `app/Http/Validations/RoomsValidation.php`
  - `app/Services/RoomsService.php`
- **Est.:** 5h

### Task 4.2 — FE: `RoomSearch` đọc query param

- **Mô tả:**
  - URL: `/rooms/search?tourist_spot_slug=sa-pa`
  - Chip/filter hiển thị “Gần Sa Pa”; clear filter được.
  - CTA homepage trỏ đúng URL.
- **Acceptance:**
  - [ ] Reload trang giữ filter.
  - [ ] Kết hợp filter ngày/giá không crash.
- **Files:**
  - `bks-system-fe/src/pages/EndUser/RoomSearch/index.tsx`
  - `bks-system-fe/src/constant.ts` (`ROUTERS` nếu cần)
- **Est.:** 4h

### Task 4.3 — (Defer) Landing SEO `/diem-den/:slug`

- **Out of MVP** — ghi backlog nếu PM cần SEO riêng.

**Phase 4 exit criteria:** Click CTA tab Sa Pa → search có kết quả filtered; ít nhất 1 E2E manual pass.

---

## Phase 5 — QA, Ops, tài liệu & phát hành

**Goal:** Chất lượng release, dữ liệu production đủ, tài liệu đồng bộ.

**Duration:** 1–2 ngày (+ Ops song song từ Phase 1)  
**Dependencies:** Phase 1–4 trên staging

### Task 5.1 — Cập nhật `homepage-display-criteria.md`

- **Mô tả:** Thay mục “Suggested by Province” bằng “Suggested by Tourist Spot”; ghi API, sort, min rooms, flag.
- **Acceptance:**
  - [ ] Doc khớp implementation thực tế.
- **Files:** `docs/homepage-display-criteria.md`  
- **Est.:** 2h

### Task 5.2 — Test case QC (`stack-testcase`)

- **Mô tả:** Tạo `docs/test-cases/testcase_007.md` (hoặc mở rộng TC003/TC004).
- **Scenarios tối thiểu:**
  - TC007-01: Tab spot đúng thứ tự priority.
  - TC007-02: Đổi tab → carousel đổi phòng.
  - TC007-03: Spot không đủ phòng → tab ẩn.
  - TC007-04: CTA → search filtered.
  - TC007-05: Card hiển thị `tourist_summary`.
  - TC007-06: Flag rollback province.
  - TC007-07: Mobile responsive tab scroll.
- **Est.:** 4h (QC owner)

### Task 5.3 — Ops hoàn tất mapping production

- **Acceptance:**
  - [ ] Mỗi spot MVP ≥ 8 phòng public, có ảnh, giá, primary + travel time.
  - [ ] Mix property type (KS/NN/Homestay) trên mỗi spot nếu có trong DB.
- **Owner:** Ops / Content  
- **Est.:** 2–5 ngày wall-clock (không chặn dev merge nếu staging dùng seed)

### Task 5.4 — Review branch (`stack-review-branch`)

- **Focus:** N+1 query; contract DTO; permission admin không ảnh hưởng public; feature flag; không leak phòng non-public.
- **Est.:** 2h review

### Task 5.5 — Rollout & rollback

| Bước | Hành động |
|------|-----------|
| 1 | Deploy BE API + giữ `rooms-by-province` |
| 2 | Deploy FE với `VITE_HOMEPAGE_SUGGESTIONS_BY_SPOT=false` trên prod |
| 3 | Ops xác nhận mapping ≥ ngưỡng |
| 4 | Bật flag `true` trên prod |
| 5 | Rollback: set flag `false` (không cần revert BE) |

**Phase 5 exit criteria:** TC007 pass trên staging; prod bật flag; `homepage-display-criteria.md` cập nhật.

---

## 5. Conflict analysis

| ID | Loại | Mô tả | Phase | Giải pháp |
|----|------|-------|-------|-----------|
| C-HP-01 | File | `Home/index.tsx` đổi cả province và spot flow | 3 | Feature flag tách nhánh render |
| C-HP-02 | Contract | FE đổi shape group province → spot | 2–3 | Freeze task 2.2 trước FE |
| C-HP-03 | Data | Ít mapping → section trống | 1, 5 | Seed demo + Ops checklist; ẩn tab |
| C-HP-04 | SRS | SRS-LP-001 nói tránh contract mới | 2 | Ghi amendment: grouped-by-spot thay province cho section này |
| C-HP-05 | Performance | 6 spot × 12 phòng payload | 2 | Một query window; limit default 8 trên mobile (tùy chọn) |

---

## 6. Risk register

| ID | Rủi ro | L | I | Giảm thiểu |
|----|--------|---|---|------------|
| R-HP-01 | Production thiếu mapping | H | H | Ops checklist; ẩn tab; seed staging |
| R-HP-02 | Tab “Sa Pa” nhưng phòng xa | M | M | Bắt buộc travel_time; copy rõ trên card |
| R-HP-03 | Trùng phòng nhiều tab | M | L | Chấp nhận MVP; defer dedupe |
| R-HP-04 | PM muốn vừa tỉnh vừa spot | M | M | Flag + giữ province API 1 release |
| R-HP-05 | Không có API list spot | M | M | Task 3.2: thêm param `slugs[]` hoặc endpoint featured nhỏ |

---

## 7. Testing strategy

| Lớp | Nội dung |
|-----|----------|
| **Unit** | Sort primary trong repository (mock query hoặc sqlite nếu hỗ trợ window) |
| **Feature** | `SuggestedRoomsByTouristSpotTest`; search filter spot |
| **FE manual** | Tab switch, CTA, mobile, empty tab |
| **Regression** | Top rated carousel không đổi; province API vẫn hoạt động khi flag off |
| **QC** | `testcase_007.md` từ SRS + plan |

---

## 8. Rollback strategy

| Phase | Cách rollback |
|-------|----------------|
| 1 | Revert seeder commit (không ảnh hưởng prod nếu chỉ chạy local) |
| 2 | Bỏ route mới; FE không gọi |
| 3 | `VITE_HOMEPAGE_SUGGESTIONS_BY_SPOT=false` |
| 4 | Bỏ query param search (optional param không phá client cũ) |
| 5 | Tắt flag prod |

---

## 9. Handoff downstream

### `stack-task`

- Thực thi tuần tự Phase 1 → 2 → 3; Phase 4 sau 2.2; Phase 5 song song Ops từ Phase 1.
- Cập nhật trạng thái task trong plan khi hoàn thành từng phase.
- Không đổi schema ngoài seed trừ khi mở task endpoint `featured spots`.

### `stack-testcase`

- Sinh `docs/test-cases/testcase_007.md` từ mục Task 5.2.
- Liên kết TC004 cho regression `tourist_summary`.

### `stack-review-branch`

- Review: query performance, DTO ổn định, feature flag, không expose admin fields trên public API.

### `report-writer`

- Báo cáo release (VI): chuyển section homepage sang spot; danh sách spot MVP; rủi ro dữ liệu Ops còn lại.

---

## 10. File impact summary

| File | Phase | Thay đổi |
|------|-------|----------|
| `database/seeders/TouristSpotsTableSeeder.php` | 1 | +spot MVP |
| `app/Repositories/RoomsRepository/RoomsRepository.php` | 2, 4 | +query spot group & search filter |
| `app/Services/RoomsService.php` | 2 | +handler grouped by spot |
| `app/Http/Controllers/EU/HomeController.php` | 2 | +action |
| `routes/api.php` | 2 | +route |
| `bks-system-fe/src/pages/EndUser/Home/components/SuggestedRoomsByTouristSpot.tsx` | 3 | Mới |
| `bks-system-fe/src/pages/EndUser/Home/index.tsx` | 3 | Wire API + flag |
| `bks-system-fe/src/constant.ts` | 1, 3 | `SUGGESTED_ROOM_SPOT_PRIORITY` |
| `bks-system-fe/src/pages/EndUser/RoomSearch/index.tsx` | 4 | Query param |
| `docs/homepage-display-criteria.md` | 5 | Cập nhật tiêu chí |

---

## 11. Task quick reference

| ID | Task | Phase | Deps | Giờ |
|----|------|-------|------|-----|
| T1.1 | Chốt phạm vi | 1 | — | 2 |
| T1.2 | Seed tourist spots | 1 | — | 3 |
| T1.3 | Seed demo maps | 1 | T1.2 | 4 |
| T1.4 | Ops checklist | 1 | T1.2 | 2 |
| T1.5 | DB doc ví dụ | 1 | T1.2 | 1 |
| T2.1 | Repository query | 2 | T1.2 | 6 |
| T2.2 | Service + DTO | 2 | T2.1 | 4 |
| T2.3 | Route + validation | 2 | T2.2 | 3 |
| T2.4 | Fallback region (opt) | 2 | T2.2 | 4 |
| T2.5 | Feature test | 2 | T2.3 | 4 |
| T3.1 | FE API + hook | 3 | T2.2 | 3 |
| T3.2 | Resolve spot ids | 3 | T3.1 | 4 |
| T3.3 | Component spot tabs | 3 | T3.1 | 5 |
| T3.4 | Feature flag | 3 | T3.3 | 2 |
| T3.5 | Mix property types (opt) | 3 | T3.3 | 3 |
| T4.1 | Search filter BE | 4 | T2.1 | 5 |
| T4.2 | Search filter FE + CTA | 4 | T4.1, T3.3 | 4 |
| T5.1 | Doc criteria | 5 | P1–4 | 2 |
| T5.2 | Testcase 007 | 5 | P3–4 | 4 |
| T5.3 | Ops mapping prod | 5 | T1.4 | — |
| T5.4 | Code review | 5 | P2–4 | 2 |
| T5.5 | Rollout | 5 | T5.3 | 2 |

**Tổng dev ước tính:** ~56h (~7 ngày làm việc) chưa gồm Ops.

---

## 12. Checklist trước khi bắt đầu

- [ ] Đọc plan_004 và xác nhận summary service đang chạy trên staging
- [ ] PM sign-off danh sách spot MVP (mục 3.1)
- [ ] `.env.example` BE/FE có biến flag/fallback/min rooms
- [ ] Nhánh git/feature name thống nhất (vd: `feature/homepage-suggestions-by-spot`)

---

## 13. Tiêu chí hoàn thành toàn plan

- [ ] API `rooms-by-tourist-spot` live; test pass
- [ ] Homepage tab Sa Pa / Cát Bà / Lý Sơn / Bà Nà Hill (và spot đủ dữ liệu)
- [ ] CTA mở search filtered
- [ ] Ops ≥ 8 phòng/spot trên production (hoặc ngưỡng đã chốt)
- [ ] `homepage-display-criteria.md` + `testcase_007.md` cập nhật
- [ ] Rollback bằng feature flag đã kiểm chứng
