# Implementation Plan: Tối ưu API trang Partner Properties

## 1. Thông tin kế hoạch

| Mục | Nội dung |
|-----|----------|
| **Mã plan** | PLAN-PARTNER-PROP-009 |
| **Màn hình** | `http://localhost:5173/partner/properties` |
| **API chính** | `GET /api/v1/partner/properties/searchAll` |
| **Phạm vi** | Backend query/index/cache + Frontend lazy-load/React Query + contract API gọn |
| **Mục tiêu** | Giảm TTFB API chính từ ~1–2s xuống **p95 < 300ms**; payload list **< 50 KB**; LCP trang **< 1s** (local) |
| **Trạng thái** | Phase 1 hoàn thành (2026-05-30); Phase 2 hoàn thành (2026-05-30) |
| **Risk level** | Medium — ảnh hưởng màn list partner cốt lõi; cần backward-compat `with_rooms=1` |

### 1.1 Timeline đề xuất

| Phase | Thời gian | Deliverable |
|-------|-----------|-------------|
| **Phase 1** — Quick wins | 1–2 ngày | Index DB + giảm eager-load + FE lazy expand |
| **Phase 2** — API contract | 2–3 ngày | Endpoint preview + Resource + React Query |
| **Phase 3** — Cache | 1–2 ngày | Redis cache + invalidation events |
| **Phase 4** — QA & rollout | 1 ngày | Test + benchmark + deprecate path cũ |

**Tổng ước lượng:** 5–8 dev days (1 BE + 1 FE).

---

## 2. Hiện trạng (as-is)

### 2.1 Luồng request khi mở trang

```text
PartnerLayout
  └─ GET /user/profile                    (blocking onboarding check)

Partner Header
  └─ GET /partner/profile                 (company name — song song)

Partner Properties (Properties.tsx)
  ├─ GET /partner/properties/types        (React Query, cache nhẹ)
  ├─ GET /user/profile                    (trùng layout — chỉ cần user_id khi create)
  └─ GET /partner/properties/searchAll
       ?page=1&per_page=5&with_rooms=1     ← BOTTLENECK
       [&name=...&property_type_id=...]
```

### 2.2 Backend — `PropertyRepository::getPropertiesForPartner`

**File:** `app/Repositories/PropertyRepository/PropertyRepository.php` (L141–251)

| Hạng mục | Chi tiết | FE có dùng ở list? |
|----------|----------|-------------------|
| `withCount('rooms')` | Đếm phòng | ✅ (nhưng FE bỏ qua, tự đếm từ `rooms[]`) |
| Subquery `cover_image_url` | Ảnh cover property | ❌ |
| Subquery `reviews_count/avg` (property) | Rating tổng | ✅ |
| `with_rooms=1` → load **tất cả** phòng | Không giới hạn | Chỉ cần preview **6** |
| Eager `amenities`, `services`, `prices` | Quan hệ N-N | ✅ (slice 5 tên) |
| Eager `utilityFees` | Full relation | ❌ |
| Eager `images` (sort=1) | Ảnh phòng | ❌ |
| Subquery reviews/room | 2 query correlated/room | ✅ (rating card) |

**Index DB hiện có:**

| Bảng | Index liên quan | Thiếu |
|------|-----------------|-------|
| `properties` | `province_id`, `ward_id`, `property_type_id`, `name` | **`user_id`** (filter partner ownership) |
| `rooms` | `property_id` | OK |
| `reviews` | `room_id` | OK |
| `property_images` | `property_id`, `(property_id, image_type)` | **`(property_id, sort, id)`** cho cover lookup |

### 2.3 Frontend — `Properties.tsx`

**File:** `bks-system-fe/src/pages/Partner/Properties.tsx`

| Vấn đề | Mô tả |
|--------|-------|
| Luôn `with_rooms: 1` | Payload tối đa mọi lần load/filter/paginate |
| Expand-all mặc định | `useEffect` set expanded = tất cả property → render hết room cards |
| Không React Query | `useEffect` + `partnerService` thủ công; không cache/staleTime |
| `normalizeProperties` | Tính `totalRooms` từ mảng rooms thay vì `rooms_count` |
| Profile trùng | `useGetUserProfileQuery` trong page + layout |

### 2.4 Field UI thực sự cần

**Property card (collapsed):**

```
id, name, address_detail, property_type_id, rent_category,
rooms_count, reviews_count, reviews_avg_rating
```

**Room preview card (expanded, max 6):**

```
id, property_id, title, status, area,
amenities[].name (≤5 hiển thị),
services[].name (≤5 hiển thị),
prices[].{id, unit, price, price_package_id},
reviews_count, reviews_avg_rating
```

**Không cần ở list:** `utilityFees`, `images`, `cover_image_url`, `deposit_amount`, `minimum_stay`, toàn bộ phòng ngoài preview.

### 2.5 Baseline KPI (đo trước khi code)

| Chỉ số | Cách đo | Target |
|--------|---------|--------|
| TTFB `searchAll` | Network tab / Laravel Telescope | p95 < 300ms |
| Payload JSON | Response size | < 50 KB (list không rooms) |
| Query count | Debugbar/Telescope | ≤ 8 queries/request |
| LCP trang | Chrome DevTools | < 1s local |
| Số request first paint | Network waterfall | ≤ 3 (properties + types + layout profile) |

---

## 3. Thiết kế mục tiêu (to-be)

### 3.1 Chiến lược load 2 tầng

```text
[Tầng 1 — First paint]
GET /partner/properties/searchAll
  ?page=1&per_page=5
  &with_rooms=0                    ← default mới
  → property metadata + rooms_count + reviews aggregate

[Tầng 2 — On expand property]
GET /partner/properties/{id}/rooms/preview?limit=6
  → tối đa 6 phòng, field tối thiểu
  (hoặc tạm: searchAll?id=X&with_rooms=preview&rooms_limit=6)
```

### 3.2 Query param contract (mở rộng)

| Param | Type | Default | Mô tả |
|-------|------|---------|-------|
| `with_rooms` | `0\|preview\|full\|1` | `0` | `1` = alias `full` (backward compat) |
| `rooms_limit` | int 1–20 | `6` | Chỉ áp dụng khi `preview` |
| `include` | string CSV | — | Optional: `cover` nếu sau này UI cần ảnh |
| `id` | int | — | Filter 1 property (dùng cho lazy expand) |

### 3.3 Response shape mục tiêu (Phase 2)

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 12,
        "name": "Homestay ABC",
        "address_detail": "123 Nguyễn Trãi",
        "property_type_id": 3,
        "rent_category": 2,
        "province_name": "Đà Nẵng",
        "ward_name": "Hải Châu",
        "rooms_count": 15,
        "reviews_count": 42,
        "reviews_avg_rating": 4.5,
        "rooms_preview": []
      }
    ],
    "total": 20,
    "per_page": 5,
    "last_page": 4
  }
}
```

Endpoint preview riêng:

```json
{
  "success": true,
  "data": {
    "property_id": 12,
    "rooms": [
      {
        "id": 101,
        "title": "Phòng 201",
        "status": 1,
        "area": 25,
        "reviews_count": 3,
        "reviews_avg_rating": 4.0,
        "amenities": ["Wifi", "Máy lạnh"],
        "services": ["Giặt ủi"],
        "prices": [
          { "id": 1, "unit": "day", "price": 350000, "package_name": "Standard" }
        ]
      }
    ],
    "total_rooms": 15,
    "preview_limit": 6
  }
}
```

---

## 4. Phạm vi công việc theo phase

### Phase 1 — Quick wins (BE + FE, không đổi route)

**Mục tiêu:** Giảm 60–70% thời gian load ban đầu trong 1–2 ngày.

#### Task P1-B1. Migration index DB

- **File mới:** `database/migrations/2026_05_30_000001_add_partner_properties_list_indexes.php`
- **Việc cần làm:**
  - `$table->index('user_id')` trên `properties`
  - `$table->index(['property_id', 'sort', 'id'])` trên `property_images`
- **Acceptance:**
  - [ ] Migration up/down pass trên local
  - [ ] `EXPLAIN` filter `properties.user_id = ?` dùng index
  - [ ] Cập nhật `docs/databases_docs/db_overview_etc_core_schema.md` (delta index)
- **Dependencies:** Không
- **Blocks:** P1-B3

#### Task P1-B2. Giảm eager-load thừa + mode `preview`

- **File:**
  - `app/Repositories/PropertyRepository/PropertyRepository.php`
  - `app/Http/Validations/PropertiesValidation.php`
- **Việc cần làm:**
  - Parse `with_rooms`: `0` (default), `preview`, `full`/`1`
  - Khi `preview`: bỏ `utilityFees`, `images`; `->limit($roomsLimit)` trong closure rooms
  - Khi `with_rooms=0`: không eager rooms
  - Bỏ subquery `cover_image_url` khỏi SELECT list (trừ khi `include=cover`)
  - Validate `rooms_limit` (1–20), `with_rooms` enum
- **Acceptance:**
  - [ ] `with_rooms=0` → response không có key `rooms`
  - [ ] `with_rooms=preview&rooms_limit=6` → max 6 phòng/property, không có `utilityFees`/`images`
  - [ ] `with_rooms=1` vẫn hoạt động như cũ (backward compat)
  - [ ] Partner A không thấy property Partner B
- **Dependencies:** Không
- **Blocks:** P1-F1, P2-B1

#### Task P1-B3. Aggregate reviews bằng JOIN (property level)

- **File:** `app/Repositories/PropertyRepository/PropertyRepository.php`
- **Việc cần làm:**
  - Thay 2 correlated subquery property reviews bằng `leftJoinSub` aggregate:
    ```sql
    SELECT rooms.property_id,
           COUNT(reviews.id) AS reviews_count,
           ROUND(AVG(reviews.rating), 1) AS reviews_avg_rating
    FROM reviews
    INNER JOIN rooms ON reviews.room_id = rooms.id
    GROUP BY rooms.property_id
    ```
  - Room-level reviews: dùng `withCount` + `withAvg('reviews', 'rating')` thay subquery raw (trong eager rooms)
- **Acceptance:**
  - [ ] Kết quả `reviews_count`/`reviews_avg_rating` khớp dữ liệu cũ (sample 5 property)
  - [ ] Query count giảm so với baseline (log Telescope)
  - [ ] Không duplicate row khi paginate (groupBy/join đúng)
- **Dependencies:** P1-B1 (khuyến nghị)
- **Blocks:** P1-B4 (benchmark)

#### Task P1-B4. Benchmark & log baseline

- **File mới:** `tests/Feature/Partner/PartnerPropertiesListPerformanceTest.php` (hoặc Feature functional + note benchmark manual)
- **Việc cần làm:**
  - Seed partner có 5 property × 20 phòng
  - Assert response 200, structure, ownership
  - Ghi baseline query count vào comment test / docs
- **Acceptance:**
  - [ ] Test pass CI
  - [ ] Có số baseline trước/sau trong PR description
- **Dependencies:** P1-B2, P1-B3
- **Blocks:** Phase 2

#### Task P1-F1. FE — list không kèm phòng + lazy expand

- **File:**
  - `bks-system-fe/src/pages/Partner/Properties.tsx`
  - `bks-system-fe/src/services/partnerService.ts` (param types)
- **Việc cần làm:**
  - First load: `with_rooms: 0`
  - Dùng `property.rooms_count` cho badge "X đơn vị" (bỏ đếm từ rooms[])
  - State `roomsByPropertyId: Map<string, Room[]>`
  - State `loadingRoomsFor: Set<string>`
  - Khi expand property lần đầu → gọi API:
    `getProperties({ id: propertyId, with_rooms: 'preview', rooms_limit: 6 })`
  - Mặc định **collapse all** (bỏ auto-expand-all `useEffect`)
  - Debounce expand: không fetch lại nếu đã có cache trong Map
- **Acceptance:**
  - [ ] First paint: 1 request properties (không rooms) + types
  - [ ] Expand property → thêm 1 request preview
  - [ ] Collapse/expand lại không refetch (cache local)
  - [ ] Pagination/filter vẫn đúng; abort signal vẫn hoạt động
- **Dependencies:** P1-B2
- **Blocks:** P1-F2

#### Task P1-F2. FE — dọn normalization + profile

- **File:** `bks-system-fe/src/pages/Partner/Properties.tsx`
- **Việc cần làm:**
  - `normalizeProperties`: lấy `totalRooms` từ `rooms_count`
  - Bỏ `useGetUserProfileQuery` — lấy `user_id` từ `useUserStore` hoặc context layout
  - Giữ `normalizeRooms` cho preview response
- **Acceptance:**
  - [ ] Tạo property mới vẫn gửi đúng `user_id`
  - [ ] Không duplicate call profile trên page (layout vẫn giữ)
- **Dependencies:** P1-F1
- **Blocks:** Phase 2

---

### Phase 2 — API contract & DX

**Mục tiêu:** Endpoint rõ ràng, payload ổn định, FE cache chuẩn.

#### Task P2-B1. `PartnerPropertyListResource`

- **File mới:** `app/Http/Resources/Partner/PartnerPropertyListResource.php`
- **File sửa:** `app/Services/PropertiesService.php`, `PartnerPropertyController.php`
- **Việc cần làm:**
  - Map field gọn; `rooms_preview` chỉ khi có eager preview
  - `status` room trả int (FE đã có `normalizeRoomStatus`)
- **Acceptance:**
  - [ ] Response không leak field thừa (`utilityFees`, raw joins)
  - [ ] Pagination meta giữ nguyên format hiện tại
- **Dependencies:** P1-B2
- **Blocks:** P2-B2

#### Task P2-B2. Endpoint `GET /partner/properties/{id}/rooms/preview`

- **File mới:**
  - `app/Http/Controllers/Partner/PartnerPropertyRoomPreviewController.php` (hoặc method trong `PartnerPropertyController`)
  - `app/Services/PartnerPropertyRoomPreviewService.php`
  - `app/Http/Resources/Partner/PartnerRoomPreviewResource.php`
- **File sửa:** `routes/api.php`
- **Việc cần làm:**
  - Route: `GET properties/{id}/rooms/preview` (đặt **trước** route `{id}` nếu cần, hoặc dùng prefix `rooms/preview` nested)
  - Query: ownership `property.user_id = auth id`
  - Limit default 6, max 20
  - Eager: amenities/services/prices only; reviews via withCount/withAvg
- **Acceptance:**
  - [ ] 404 khi property không thuộc partner
  - [ ] `limit=6` trả đúng số lượng; `total_rooms` = count thật
  - [ ] OpenAPI/api-doc cập nhật nếu project có convention
- **Dependencies:** P2-B1
- **Blocks:** P2-F1

#### Task P2-F1. Hook React Query

- **File mới:**
  - `bks-system-fe/src/hooks/Partner/usePartnerPropertiesQuery.ts`
  - `bks-system-fe/src/hooks/Partner/usePartnerPropertyRoomPreviewQuery.ts`
- **File sửa:** `Properties.tsx`, `partnerService.ts`
- **Việc cần làm:**
  ```typescript
  // List
  queryKey: ['partner', 'properties', { page, perPage, name, propertyTypeId }]
  staleTime: 30_000
  placeholderData: keepPreviousData

  // Preview
  queryKey: ['partner', 'properties', propertyId, 'rooms-preview']
  staleTime: 60_000
  enabled: expanded && !cached
  ```
  - Thêm `getPropertyRoomPreview(propertyId, limit)` vào `partnerService`
- **Acceptance:**
  - [ ] Filter/pagination không flash empty state
  - [ ] Invalidate list sau create/update/delete property
  - [ ] Invalidate preview sau create/update/delete room
- **Dependencies:** P2-B2, P1-F1
- **Blocks:** P2-F2

#### Task P2-F2. Migrate `PropertySelector`, `Calendar`, `Services` (optional slice)

- **File:** các component gọi `getProperties(undefined)` không cần rooms
- **Việc cần làm:**
  - Dùng `GET /partner/properties/all` (names only) nếu chỉ cần dropdown
  - Hoặc `with_rooms=0` + `per_page` hợp lý
- **Acceptance:**
  - [ ] Dropdown property không kéo full rooms
  - [ ] Không regression màn Calendar/Services
- **Dependencies:** P2-F1
- **Blocks:** Phase 3

---

### Phase 3 — Cache & invalidation

**Mục tiêu:** F5 lặp lại < 100ms khi cache hit (pattern `DEC-260510-PP360-006`).

#### Task P3-B1. Cache service list property

- **File mới:** `app/Services/PartnerPropertyListCacheService.php`
- **File sửa:** `PropertiesService.php`, `config/cache.php` (TTL constant nếu cần)
- **Key pattern:**
  ```
  partner:{partnerId}:properties:list:{md5(filters)}:page:{n}
  TTL: 60 seconds
  ```
- **Acceptance:**
  - [ ] Cache hit giảm query DB
  - [ ] Response có header hoặc field `cached_at` (optional, giống calendar)
  - [ ] Tắt cache khi `APP_DEBUG=true` hoặc env `PARTNER_PROPERTY_LIST_CACHE=false`
- **Dependencies:** Phase 2
- **Blocks:** P3-B2

#### Task P3-B2. Invalidation listener

- **File mới:** `app/Listeners/InvalidatePartnerPropertyListCache.php`
- **Events:** Property/Room created/updated/deleted; Review created (rating)
- **Acceptance:**
  - [ ] Sau create room → list + preview cache cleared cho partner đó
  - [ ] Unit test listener với fake cache
- **Dependencies:** P3-B1
- **Blocks:** Phase 4

#### Task P3-B3. Cache property types (optional, low effort)

- **File sửa:** `PropertiesService::handleGetAllPropertyTypes` hoặc controller partner types
- **Key:** `partner:property-types`, TTL 3600s
- **Acceptance:**
  - [ ] Types API < 20ms cache hit
- **Dependencies:** Không
- **Blocks:** Không

---

### Phase 4 — QA, benchmark, rollout

#### Task P4-Q1. Test cases functional

| ID | Scenario | Expected |
|----|----------|----------|
| TC-PROP-01 | Load trang lần đầu | List hiển thị, không có rooms trong response |
| TC-PROP-02 | Expand property | Preview ≤6 phòng, đúng amenities/prices |
| TC-PROP-03 | Filter tên + loại hình | Pagination reset page 1, kết quả đúng |
| TC-PROP-04 | Partner B gọi property Partner A | 403/404, không leak data |
| TC-PROP-05 | `with_rooms=1` (legacy) | Vẫn trả full rooms (compat) |
| TC-PROP-06 | Create/update/delete property | List refresh đúng |
| TC-PROP-07 | Bulk delete | List refresh; không N+1 DELETE (future) |

#### Task P4-Q2. Performance regression gate

- **Acceptance:**
  - [ ] p95 `searchAll` (no rooms) < 300ms với seed 5×20 phòng
  - [ ] p95 preview < 200ms
  - [ ] Payload list < 50 KB
  - [ ] Query count list ≤ 8

#### Task P4-Q3. Deprecation notice

- **Việc cần làm:**
  - Ghi chú trong api-doc: `with_rooms=1` deprecated → dùng preview endpoint
  - Sau 1 sprint: FE không gọi `with_rooms=1` trên list page
- **Acceptance:**
  - [ ] Không còn call `with_rooms=1` từ `Properties.tsx`

---

## 5. Thứ tự triển khai & dependency graph

```text
P1-B1 (index)
  └─> P1-B3 (aggregate reviews)
        └─> P1-B4 (benchmark)

P1-B2 (preview mode) ──> P1-F1 (FE lazy) ──> P1-F2 (FE cleanup)
        └─> P2-B1 (Resource) ──> P2-B2 (preview endpoint)
                                      └─> P2-F1 (React Query) ──> P2-F2 (other pages)

P2-F1 ──> P3-B1 (cache) ──> P3-B2 (invalidate) ──> P4-Q1/Q2/Q3
```

**Thứ tự PR đề xuất:**

| PR | Nội dung | Review focus |
|----|----------|--------------|
| PR1 | P1-B1 + P1-B2 + P1-B3 | SQL, index, backward compat |
| PR2 | P1-F1 + P1-F2 + P1-B4 | UX expand, network waterfall |
| PR3 | P2-B1 + P2-B2 + P2-F1 | API contract, React Query |
| PR4 | P3-B1 + P3-B2 + P4 | Cache correctness, QA |

---

## 6. Phân tích xung đột & rủi ro

| ID | Loại | Mô tả | Giải pháp |
|----|------|-------|-----------|
| C-PPROP-01 | UX | User quen expand-all, lazy load thấy "trống" lúc đầu | Skeleton trong vùng expand; optional "expand first property" |
| C-PPROP-02 | Contract | `PropertyRooms.tsx` gọi `getProperties({ id })` — cần vẫn hoạt động | Giữ filter `id` trên searchAll; không breaking |
| C-PPROP-03 | SQL | JOIN aggregate + paginate có thể duplicate nếu sai GROUP BY | Test paginate với property nhiều reviews |
| C-PPROP-04 | Cache | Stale list sau mutation nếu quên invalidate | Listener tập trung; test integration |
| C-PPROP-05 | Route | `properties/{id}` vs `properties/{id}/rooms/preview` order | Khai báo route preview trước `{id}` hoặc dùng tên tĩnh |

---

## 7. Out of scope (ghi nhận, không làm trong plan này)

- Bulk delete property: `POST /partner/properties/bulk-delete` (hiện N request DELETE)
- Denormalize `reviews_count` lên bảng `properties` (chỉ cần nếu >10k reviews/partner)
- Ảnh cover trên list card (UI chưa có)
- Full-text search Elasticsearch cho filter `name`

---

## 8. Ước lượng effort

| Phase | BE | FE | QA | Tổng |
|-------|----|----|-----|------|
| Phase 1 | 1.5d | 1d | 0.5d | **3d** |
| Phase 2 | 1.5d | 1.5d | 0.5d | **3.5d** |
| Phase 3 | 1d | 0.25d | 0.5d | **1.75d** |
| Phase 4 | 0.25d | 0.25d | 1d | **1.5d** |
| **Tổng** | **4.25d** | **3d** | **2.5d** | **~9.75 dev-days** |

> Có thể ship **Phase 1 only** (~3 ngày) để đạt 60–70% cải thiện trước khi làm Phase 2–3.

---

## 9. Acceptance checklist cuối plan

- [ ] First load không gọi `with_rooms=1`
- [ ] Expand property fetch preview ≤6 phòng
- [ ] `rooms_count` hiển thị đúng không cần load rooms
- [ ] p95 API list < 300ms; preview < 200ms
- [ ] Payload list < 50 KB
- [ ] Index `properties.user_id` deployed
- [ ] Backward compat `with_rooms=1` verified
- [ ] Cache invalidate sau CRUD property/room
- [ ] PHPUnit Feature tests pass
- [ ] FE build pass; không regression PropertyRooms/Calendar

---

## 10. Handoff downstream

### stack-task
- Thực thi PR1 → PR2 → PR3 → PR4 theo thứ tự trên
- Persona: `.cursor/skills/stack-personas/senior-engineer.md`
- Sau mỗi PR: chạy benchmark và ghi số vào PR description

### stack-testcase
- Sinh test case từ bảng TC-PROP-01..07
- Thêm performance check TC-PROP-PERF (p95, payload size)

### stack-review-branch
- Review SQL aggregate + pagination duplicate
- Review cache invalidation coverage
- Review FE waterfall (Network 3 requests first paint)

### report-writer / memory
- Cập nhật `docs/memory/knowledge_base.md`: API contract `with_rooms` modes, preview endpoint
- Nếu adopt cache: thêm decision vào `docs/memory/decisions.md` (TTL 60s list property)

---

## 11. Quick start (dev có thể làm ngay)

**~2–3 giờ, không cần endpoint mới:**

1. BE: P1-B2 — bỏ `utilityFees`/`images`; default `with_rooms=0`
2. BE: P1-B1 — migration index `user_id`
3. FE: P1-F1 — bỏ `with_rooms:1`, lazy fetch preview khi expand

→ Ước tính giảm **40–50%** response time ngay, chưa cần Phase 2–3.
