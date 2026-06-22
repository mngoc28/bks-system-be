# Partner Properties List — Product Requirement Document (PRD)

## Document Information

| Mục | Nội dung |
|-----|----------|
| **Version** | 1.0 |
| **Status** | Draft — chờ review Product/TL |
| **Author** | Business Analyst |
| **Date** | 2026-06-19 |
| **Màn hình** | `http://localhost:5173/partner/properties` |
| **Liên quan** | [`plan_015_partner_properties_list_gaps.md`](../plans/plan_015_partner_properties_list_gaps.md), [`plan_009_partner_properties_api_optimization.md`](../plans/plan_009_partner_properties_api_optimization.md), [`srs_partner_portal_360.md`](srs_partner_portal_360.md), [`partner-portal.md`](../../user-manual/partner-portal.md) |
| **API chính** | `GET /api/v1/partner/properties/searchAll` |

---

## Executive Summary

Trang **Quản lý Dữ liệu Tài sản** đã đủ chức năng CRUD cốt lõi nhưng còn gap về **bộ lọc**, **tìm kiếm**, **entry point thêm phòng** và **an toàn thao tác xóa**. Backend đã hỗ trợ nhiều tham số lọc/sort mà FE chưa khai thác; Admin Property Manager đã có pattern bộ lọc nâng cao có thể tái sử dụng.

PRD này đặc tả các cải tiến theo **MoSCoW** (Must / Should / Could) để Partner vận hành danh mục nhiều cơ sở hiệu quả hơn, không thay đổi luồng nghiệp vụ booking/calendar.

---

## Problem Statement

### Current State

- FE chỉ lọc theo **tên** và **loại hình**; phân trang 5/10/20/50; expand preview tối đa 6 phòng/property.
- Placeholder ô tìm kiếm gợi ý "tên bất động sản, khách sạn…" nhưng API chỉ match `properties.name`.
- `RoomModal` hỗ trợ tạo phòng nhưng **không có nút "Thêm phòng"** trên trang overview.
- Xóa 1 property không có confirm; xóa hàng loạt có dialog bắt gõ `XÁC NHẬN XÓA`.
- Card property không hiển thị ảnh cover dù API hỗ trợ `include=cover`.

### Pain Points

| Đối tượng | Vấn đề |
|-----------|--------|
| Partner nhiều cơ sở | Khó lọc theo khu vực hoặc hình thức cho thuê (ngắn/dài hạn) |
| Partner mới onboarding | Phải vào "Quản lý phòng" mới thêm phòng — thêm bước không cần thiết |
| Partner vận hành hàng ngày | Không sort theo rating/số phòng; khó quét nhanh danh sách |
| Mọi Partner | Rủi ro xóa nhầm 1 property vì thiếu confirm |

### Opportunity

Tận dụng contract API hiện có + pattern UI Admin để nâng UX với effort FE chủ yếu; một số hạng mục Could cần mở rộng BE nhẹ.

---

## Goals & Success Metrics

### Primary Goal

Giảm thời gian Partner **tìm đúng cơ sở/phòng** và **thao tác an toàn** trên trang danh sách tài sản.

### Success Metrics

| KPI | Baseline (ước lượng) | Target sau rollout |
|-----|----------------------|-------------------|
| Số click trung bình để thêm phòng mới từ overview | ≥ 3 (overview → quản lý phòng → thêm) | ≤ 2 (thêm trực tiếp từ overview) |
| Tỷ lệ xóa property không chủ ý (support ticket) | Chưa đo | 0 case/tháng |
| Thời gian tìm property khi có ≥ 10 cơ sở | Chưa đo | Giảm ≥ 30% (survey nội bộ/UAT) |
| API list p95 | < 300ms (sau plan 009) | Giữ nguyên khi thêm filter (không tăng > 20%) |

### Non-Goals

- Thay đổi luồng booking, calendar, finance, maintenance.
- RBAC nhân viên nội bộ Partner.
- Export Excel/PDF (để phase sau — xem Could).
- Redesign toàn bộ layout trang (chỉ bổ sung control/filter/CTA).

---

## User Stories

### Must-have

**US-M01 — Lọc theo hình thức cho thuê**

> Là Partner, tôi muốn lọc danh sách cơ sở theo hình thức cho thuê (ngắn hạn / dài hạn / hỗn hợp), để nhanh chóng tìm đúng nhóm tài sản mình đang vận hành.

**Acceptance Criteria**

- [ ] Given Partner ở `/partner/properties`, when chọn `rent_category` (1/2/3), then danh sách chỉ hiển thị property khớp giá trị.
- [ ] Given đã chọn filter, when nhấn "Xóa lọc", then `rent_category` reset về "Tất cả".
- [ ] Given thay đổi filter, when API trả về, then trang reset về page 1.

---

**US-M02 — Tìm kiếm theo tên hoặc địa chỉ**

> Là Partner, tôi muốn tìm cơ sở bằng tên hoặc địa chỉ, để không bị nhầm khi nhiều property tên tương tự.

**Acceptance Criteria**

- [ ] Given nhập từ khóa, when debounce 500ms, then API tìm trong `properties.name` **và** `properties.address_detail` (LIKE, không phân biệt hoa thường).
- [ ] Given placeholder ô tìm kiếm, then hiển thị: "Tìm theo tên hoặc địa chỉ…" (không gây hiểu nhầm).
- [ ] Given không có kết quả, then empty state gợi ý đổi từ khóa hoặc xóa lọc.

**Ghi chú kỹ thuật:** Cần mở rộng BE `PropertyRepository::getPropertiesForPartner` — tham số mới `keyword` (hoặc tái dùng `name` mở rộng scope). Ưu tiên `keyword` để backward-compat.

---

**US-M03 — Thêm phòng từ trang overview**

> Là Partner, tôi muốn thêm phòng trực tiếp từ card property, để rút ngắn onboarding sau khi tạo cơ sở mới.

**Acceptance Criteria**

- [ ] Given property đang hiển thị, when nhấn "Thêm phòng" trên header card, then mở `RoomModal` ở chế độ tạo mới với `propertyId` đúng.
- [ ] Given property chưa có phòng (empty state expand), when nhấn "Thêm phòng ngay", then mở cùng modal.
- [ ] Given lưu thành công, when đóng modal, then invalidate preview property + refresh list; phòng mới xuất hiện khi expand.
- [ ] Given hủy modal, then không thay đổi dữ liệu.

---

**US-M04 — Xác nhận xóa property đơn lẻ**

> Là Partner, tôi muốn hệ thống xác nhận trước khi xóa 1 property, để tránh thao tác nhầm.

**Acceptance Criteria**

- [ ] Given nhấn icon xóa 1 property, when chưa confirm, then hiện dialog cảnh báo (nội dung tương tự bulk delete, rút gọn).
- [ ] Given confirm, when API thành công, then toast success + refresh list.
- [ ] Given API lỗi (property có booking active), then hiển thị message từ BE, property không bị xóa.
- [ ] Dialog đơn lẻ **không** bắt gõ `XÁC NHẬN XÓA` (chỉ bulk delete giữ yêu cầu này).

---

### Should-have

**US-S01 — Lọc theo Tỉnh/Thành và Phường/Xã**

> Là Partner, tôi muốn lọc cơ sở theo địa bàn hành chính, để quản lý nhiều tòa ở các khu vực khác nhau.

**Acceptance Criteria**

- [ ] Dropdown Tỉnh/Thành (searchable) → load Phường/Xã phụ thuộc.
- [ ] API gửi `province_name`, `ward_name` (string match như Admin).
- [ ] Đổi tỉnh → reset phường/xã.
- [ ] Tái sử dụng endpoint partner: `GET /partner/provinces/all`, `GET /partner/wards/{provinceId}`.

---

**US-S02 — Sắp xếp danh sách**

> Là Partner, tôi muốn sắp xếp danh sách property, để ưu tiên xem cơ sở mới nhất, nhiều phòng, hoặc rating cao.

**Acceptance Criteria**

- [ ] Control "Sắp xếp" với tối thiểu 4 option:

| Label UI | Field | Order |
|----------|-------|-------|
| Mới nhất | `id` | `desc` (default) |
| Tên A → Z | `name` | `asc` |
| Nhiều phòng nhất | `rooms_count` | `desc` |
| Đánh giá cao nhất | `reviews_avg_rating` | `desc` |

- [ ] Gửi `sort[0][field]` + `sort[0][order]` theo convention BE hiện có.
- [ ] Đổi sort → reset page 1.

**Ghi chú:** `rooms_count` đã có qua `withCount('rooms')`. Sort `reviews_avg_rating` cần verify BE hỗ trợ `orderBy` trên cột aggregate — nếu chưa, bổ sung trong repository (effort nhỏ).

---

**US-S03 — Ảnh đại diện trên card property**

> Là Partner, tôi muốn thấy ảnh đại diện trên mỗi card, để nhận diện cơ sở nhanh hơn.

**Acceptance Criteria**

- [ ] Request list kèm `include=cover` (hoặc field `cover_image_url` trong response hiện tại).
- [ ] Card hiển thị thumbnail 64×64 (hoặc tương đương), fallback icon `Building2` khi không có ảnh.
- [ ] Không tăng payload list > 50 KB p95 (theo plan 009) — chỉ 1 URL ảnh/property.

---

### Could-have

**US-C01 — Lọc theo trạng thái vận hành phòng (occupancy)**

> Là Partner, tôi muốn lọc property có ít nhất 1 phòng trống / đang thuê / bảo trì, để ưu tiên xử lý vận hành.

**Acceptance Criteria**

- [ ] Filter "Trạng thái phòng": Tất cả | Có phòng trống | Có phòng đang thuê | Có phòng bảo trì.
- [ ] BE filter property `WHERE EXISTS (room matching occupancy_status)` — logic đồng bộ `buildOccupancyStatusSelect` trong repository.
- [ ] Hiển thị badge tóm tắt trên card: "3 trống · 1 thuê · 0 bảo trì" (optional, nếu có data).

**Phụ thuộc:** Mở rộng API mới `occupancy_filter` — chưa có trong contract hiện tại.

---

**US-C02 — Lọc theo rating và tình trạng có phòng**

> Là Partner, tôi muốn lọc property theo rating tối thiểu hoặc property chưa có phòng, để ưu tiên cải thiện chất lượng / hoàn thiện listing.

**Acceptance Criteria**

- [ ] Filter rating: ≥ 4 sao | 3–4 sao | Chưa có đánh giá.
- [ ] Filter "Số phòng": Có phòng | Chưa có phòng (`rooms_count = 0`).
- [ ] Kết hợp được với filter Must/Should.

---

**US-C03 — Tìm/lọc trong vùng preview phòng**

> Là Partner, khi expand property, tôi muốn tìm nhanh phòng theo tên hoặc trạng thái trong preview, thay vì phải sang trang quản lý phòng.

**Acceptance Criteria**

- [ ] Khi expand: ô tìm + dropdown trạng thái (client-side trên preview đã load, hoặc gọi `rooms/preview` với param nếu BE hỗ trợ).
- [ ] Không load full rooms list — giới hạn preview API `limit` ≤ 20.

---

**US-C04 — Persist bộ lọc vào URL**

> Là Partner, tôi muốn reload hoặc chia sẻ link vẫn giữ bộ lọc, để không mất ngữ cảnh làm việc.

**Acceptance Criteria**

- [ ] Query string: `?name=&type=&rent=&province=&sort=` (chỉ non-empty params).
- [ ] Mount trang → hydrate state từ URL.
- [ ] Thay filter → `replaceState` URL (không spam history).

**Tham chiếu:** Pattern đã có ở `PropertyRooms.tsx` (`useSearchParams`).

---

**US-C05 — Export danh sách property/phòng**

> Là Partner, tôi muốn xuất Excel danh sách cơ sở và phòng, để đối soát nội bộ.

**Acceptance Criteria**

- [ ] Nút "Xuất Excel" áp dụng filter hiện tại.
- [ ] File gồm: tên property, địa chỉ, loại hình, rent_category, số phòng, rating, ngày tạo.
- [ ] Giới hạn tối đa 500 dòng/export — tránh timeout.

**Phụ thuộc:** Endpoint export mới — out of scope Must/Should.

---

## Functional Requirements Summary

| ID | Requirement | Priority | Phụ thuộc BE |
|----|-------------|----------|--------------|
| FR-PP-001 | Filter `rent_category` trên FE + gọi API | Must | Không (đã có) |
| FR-PP-002 | Search `keyword` trên name + address_detail | Must | **Có** — mở rộng repository |
| FR-PP-003 | CTA "Thêm phòng" + empty state trên overview | Must | Không |
| FR-PP-004 | Confirm dialog xóa property đơn lẻ | Must | Không |
| FR-PP-005 | Filter Tỉnh/Phường | Should | Không (đã có) |
| FR-PP-006 | Sort 4 option | Should | Có thể — verify sort rating |
| FR-PP-007 | Hiển thị cover image | Should | Không (`include=cover`) |
| FR-PP-008 | Filter occupancy | Could | **Có** — param mới |
| FR-PP-009 | Filter rating / rooms_count | Could | **Có** — param mới |
| FR-PP-010 | Search/filter preview phòng | Could | Tùy chọn |
| FR-PP-011 | URL query persistence | Could | Không |
| FR-PP-012 | Export Excel | Could | **Có** — endpoint mới |

---

## Non-Functional Requirements

| Hạng mục | Yêu cầu |
|----------|---------|
| **Performance** | Thêm filter/sort không làm p95 API list > 360ms (local). Debounce search 500ms. |
| **Security** | Chỉ property thuộc `Auth::id()` partner — giữ nguyên scope repository. |
| **Accessibility** | Filter/sort có `Label` + `id`; dialog xóa trap focus, `aria-labelledby`. |
| **i18n** | Label tiếng Việt; `rent_category` dùng key `RENT_CATEGORY.*` hiện có. |
| **Responsive** | Filter bar stack vertical mobile; không vỡ layout card expand. |

---

## Technical Considerations

### API contract đề xuất (delta)

```http
GET /api/v1/partner/properties/searchAll
  ?page=1
  &per_page=5
  &with_rooms=0
  &include=cover                    # Should — FR-PP-007
  &keyword=linh                       # Must — FR-PP-002 (thay/mở rộng name)
  &property_type_id=2
  &rent_category=1                    # Must — FR-PP-001
  &province_name=Đà Nẵng              # Should — FR-PP-005
  &ward_name=Hải Châu
  &sort[0][field]=reviews_avg_rating  # Should — FR-PP-006
  &sort[0][order]=desc
  &occupancy_filter=vacant            # Could — FR-PP-008
  &min_rating=4                       # Could — FR-PP-009
  &has_rooms=1                        # Could — FR-PP-009
```

### FE files dự kiến

| File | Thay đổi |
|------|----------|
| `src/pages/Partner/Properties.tsx` | Filter bar, sort, CTA, confirm delete, cover |
| `src/hooks/Partner/usePartnerPropertiesQuery.ts` | Mở rộng `PartnerPropertiesFilters` |
| `src/utils/partnerPropertyData.ts` | Parse `cover_image_url` nếu cần |
| `app/Repositories/PropertyRepository/PropertyRepository.php` | `keyword`, sort rating, Could filters |
| `app/Http/Validations/PropertiesValidation.php` | Rule param mới |

### Rủi ro

| Rủi ro | Mitigation |
|--------|------------|
| Sort `reviews_avg_rating` không có index | Sort in-memory sau aggregate hoặc subquery — giới hạn per_page |
| `keyword` search chậm khi data lớn | Index `properties.name`, `address_detail`; fulltext phase sau |
| UI filter bar quá dài mobile | Collapse "Bộ lọc nâng cao" (pattern `AdvancedFilterPanel` Admin) |

### Giả định

- Partner single-owner, không RBAC.
- `rent_category` enum 1/2/3 đã ổn định (`RENT_CATEGORY` constant FE).
- Plan 009 Phase 2 (lazy preview) đã deploy — không revert `with_rooms=0`.

---

## Out of Scope

- Chỉnh sửa `RoomModal` form đầy đủ như `PropertyRooms` (bulk entry, utility fees, occupancy view).
- Thay đổi logic xóa property (cascade rooms/bookings) — giữ rule hiện tại.
- Admin Property Manager.
- Mobile native app.

---

## Timeline & Milestones (đề xuất)

| Phase | Deliverable | Effort ước lượng |
|-------|-------------|------------------|
| **P1 — Must** | FR-PP-001 → 004 + BE keyword | 2–3 dev days |
| **P2 — Should** | FR-PP-005 → 007 | 1.5–2 dev days |
| **P3 — Could** | FR-PP-008 → 011 (trừ export) | 2–3 dev days |
| **P4 — Could export** | FR-PP-012 | 1–2 dev days |

**Tổng:** 6.5–10 dev days (1 FE + 0.5 BE cho P1/P2).

---

## Open Questions

| # | Câu hỏi | Owner đề xuất | Ảnh hưởng |
|---|---------|---------------|-----------|
| Q1 | Search dùng param `keyword` mới hay mở rộng `name`? | TL | Backward-compat mobile/API khác |
| Q2 | Sort rating khi property chưa có review — đẩy xuống cuối hay coi là 0? | BA + Product | UX sort |
| Q3 | Filter occupancy ở overview hay chỉ ở `PropertyRooms`? | Product | Scope Could |
| Q4 | Export Excel có cần trong Q3/2026 không? | Product | Scope P4 |
| Q5 | Dialog xóa đơn có hiển thị số phòng sẽ bị xóa kèm theo? | BA | Copy dialog |

---

## Appendix

### A. Ma trận hiện trạng Filter (as-is vs to-be)

| Tham số API | As-is FE | To-be |
|-------------|----------|-------|
| `name` | ✅ | → `keyword` (Must) |
| `property_type_id` | ✅ | ✅ |
| `rent_category` | ❌ | ✅ Must |
| `province_name` | ❌ | ✅ Should |
| `ward_name` | ❌ | ✅ Should |
| `sort` | ❌ | ✅ Should |
| `include=cover` | ❌ | ✅ Should |
| `occupancy_filter` | ❌ | Could |
| `min_rating` / `has_rooms` | ❌ | Could |

### B. User journey — Thêm phòng (to-be Must)

```text
/partner/properties
  → Expand property (optional)
  → Click "Thêm phòng"
  → RoomModal (create)
  → Save → invalidate preview → card cập nhật
```

### C. Tham chiếu code

- FE list: `bks-system-fe/src/pages/Partner/Properties.tsx`
- FE hook: `bks-system-fe/src/hooks/Partner/usePartnerPropertiesQuery.ts`
- BE repository: `bks-system-be/app/Repositories/PropertyRepository/PropertyRepository.php` (L147–262)
- Admin filter pattern: `bks-system-fe/src/pages/Admin/PropertyManager/components/PropertySearchSection.tsx`

---

## Quality Checklist (BA sign-off)

- [x] Requirements có acceptance criteria testable
- [x] Phân tách Must / Should / Could rõ ràng
- [x] Edge cases: empty state, API lỗi xóa, filter reset page
- [x] Dependencies BE/FE được ghi nhận
- [ ] Stakeholder review (Product, TL, QA)
- [ ] Open questions Q1–Q5 có owner trả lời
