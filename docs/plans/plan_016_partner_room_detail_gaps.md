# Implementation Plan: Partner Room Detail — Domain Gap Remediation

## Document Information

| Mục | Nội dung |
|-----|----------|
| **Plan ID** | PLAN-RD-016 |
| **Created** | 2026-06-21 |
| **Status** | Complete — Phase 1–4 ✅ (2026-06-21) |
| **Related Domain Review** | `docs/reports/domain/partner_room_detail_review.md` |
| **Related SRS** | `docs/SRC/srs_partner_portal_360.md` (YC-R01 — quản lý phòng vật lý) |
| **Phụ thuộc nền** | PLAN-MNT-014 (maintenance lifecycle), migration `housekeeping_status` trên `rooms` |
| **Màn hình** | `http://localhost:5173/partner/rooms/:roomId` |
| **API chính** | `GET/PATCH /api/v1/partner/rooms/{id}`, `PATCH .../housekeeping`, `GET/POST/DELETE .../room-images`, `GET .../bookings` |
| **Risk level** | Low–Medium — chủ yếu FE; Gap 7 (apply-to-all amenities) chạm BE sync nhiều phòng |

### Executive Summary

Triển khai 7 gap domain đã chốt cho màn **Partner Room Detail**: housekeeping trên header, chỉnh sửa phòng tại chỗ, liên hệ động, fix tiêu đề, hành động trên lịch sử booking, quản lý ảnh, và giảm nhập liệu lặp amenities.

**Khác biệt so với As-Is:** FE chỉ xem; housekeeping API có sẵn nhưng chưa gọi; contact hardcode; Gallery read-only; TenantsTab không có CTA; tiêu đề có thể duplicate prefix.

**Ước lượng:** 5.5–8 dev-days (0.5 BE + 1 FE chính), 4 release slice.

### Timeline đề xuất

| Phase | Thời gian | Deliverable | Release slice |
|-------|-----------|-------------|---------------|
| **Phase 1** — Quick wins | 0.5 ngày | Fix tiêu đề + liên hệ động | Slice A |
| **Phase 2** — Housekeeping | 1–1.5 ngày | Dropdown header + auto-dirty checkout | Slice B |
| **Phase 3** — Edit & Gallery | 1.5–2 ngày | PartnerRoomFormSheet + PartnerImageManager | Slice C |
| **Phase 4** — Tenants CTA + Amenities bulk | 2–3 ngày | Booking drawer/contract link + apply-to-all amenities | Slice D |

---

## Quyết định planning (chốt tạm để unblock dev)

| ID | Quyết định | Ghi chú |
|----|------------|---------|
| PD-RD-016-001 | Housekeeping UI dùng `PATCH /partner/rooms/{id}/housekeeping` — **không** tạo API mới | API + test đã có (`PartnerRoomsListTest::test_partner_can_update_housekeeping_status`) |
| PD-RD-016-002 | Hiển thị occupancy badge (`Trống`/`Đang thuê`/`Đang bảo trì`) **tách biệt** badge Housekeeping (`Sạch`/`Cần dọn`/`Đang kiểm tra`) | Tránh nhầm lẫn `rooms.status` (PUBLIC/PRIVATE) vs occupancy computed |
| PD-RD-016-003 | Checkout booking partner → tự set `housekeeping_status = dirty` (BE hook) | Đáp ứng business rule domain; không bắt partner thao tác thủ công sau mỗi checkout |
| PD-RD-016-004 | Liên hệ Overview: ưu tiên `users.phone` + `users.email` của partner owner property; fallback `config('app.support_phone')` / `config('app.support_email')` | Không hardcode FE; thêm 2 key config nếu chưa có |
| PD-RD-016-005 | TenantsTab: click row → **reuse** dialog chi tiết booking từ `Bookings.tsx` (extract component), nút phụ "Xem hợp đồng" nếu có `contract_id` | Tránh duplicate UI; BE bổ sung `contract_id` (contract đầu tiên của booking) |
| PD-RD-016-006 | Gallery: reuse `PartnerImageManager` (sheet) thay viết upload mới | Pattern đã có ở `PropertyRooms.tsx`, `Units.tsx` |
| PD-RD-016-007 | Gap 7 **không** introduce bảng `room_categories` mới trong plan này — chỉ `apply_to_all_rooms` khi update amenities/services | Phù hợp kiến trúc hiện tại (amenities gắn `rooms` qua pivot) |

---

## Phase Overview

| Phase | Tên | Tasks | Dependencies | Song song |
|-------|-----|-------|--------------|-----------|
| 1 | Quick wins | 5 | None | T1.1 ∥ T1.3 |
| 2 | Housekeeping | 6 | None | T2.4 sau T2.3 |
| 3 | Edit & Gallery | 5 | Phase 1 (optional) | T3.1 ∥ T3.4 |
| 4 | Tenants + Amenities bulk | 7 | Phase 3 (T4.3 reuse form) | T4.1 ∥ T4.4 |

---

## Dependency Graph

```text
Phase 1: Quick wins
[T1.1] formatRoomDisplayTitle helper
[T1.2] RoomDetail header apply helper
[T1.3] BE config support_phone/email + enrich room detail contact
[T1.4] OverviewTab dynamic contact props
[T1.5] Smoke manual Slice A
         │
         ▼
Phase 2: Housekeeping
[T2.1] partnerService.updateHousekeepingStatus
[T2.2] useRoomDetailQuery map housekeeping_status
[T2.3] HousekeepingStatusControl component (header)
[T2.4] BE checkout → set dirty
[T2.5] i18n labels + toast messages
[T2.6] Feature test checkout sets dirty
         │
         ├────────────────────┐
         ▼                    ▼
Phase 3: Edit & Gallery   (prep Phase 4)
[T3.1] Wire PartnerRoomFormSheet RoomDetail
[T3.2] Header CTA "Chỉnh sửa phòng"
[T3.3] Invalidate queries onSaved
[T3.4] GalleryTab CTA → PartnerImageManager
[T3.5] FE build + smoke Slice C
         │
         ▼
Phase 4: Tenants + Amenities bulk
[T4.1] BE booking list enrich contract_id
[T4.2] Extract PartnerBookingDetailDialog
[T4.3] TenantsTab row click + contract link
[T4.4] BE apply_to_all_rooms on room update (amenities/services)
[T4.5] FE checkbox PartnerRoomFormSheet
[T4.6] AmenitiesTab hint "Đồng bộ N phòng cùng tòa" (optional)
[T4.7] Feature tests + E2E delta
```

---

## Hiện trạng kỹ thuật (As-Is)

### Backend

| Thành phần | File | Ghi chú |
|------------|------|---------|
| Room detail | `PartnerRoomController::show` → `RoomsService::handleGetRoomDetailForPartner` | Đã select `housekeeping_status`; chưa trả contact partner |
| Housekeeping | `PATCH /partner/rooms/{id}/housekeeping` | `RoomsService::updateHousekeepingStatus` — ownership check OK |
| Room images | `PartnerRoomController::getImages/storeImages/deleteImage` | FE `partnerService.addRoomImage/deleteRoomImage` sẵn |
| Bookings by room | `BookingRepository::getBookingsForPartner` | Filter `room_id` OK; **không** trả `contract_id` |
| Checkout | `BookingService` check-out handler | **Không** set `housekeeping_status` |
| Tourist spot pattern | `RoomTouristSpotMapService` | `apply_to_all_rooms` — tham chiếu cho Gap 7 |

### Frontend

| Thành phần | File | Gap |
|------------|------|-----|
| Page | `RoomDetail.tsx` | Read-only; hardcode contact qua OverviewTab; title `Phòng {name}` |
| Queries | `useRoomDetailQueries.ts` | Không map `housekeeping_status` |
| Edit form | `PartnerRoomFormSheet.tsx` | Chỉ mount ở `PropertyRooms`, `Units` |
| Images | `PartnerImageManager.tsx` | Chưa mount ở RoomDetail |
| Booking detail UI | `Bookings.tsx` L1383+ | Dialog inline — chưa extract/reuse |
| API client | `partnerService.ts` | Thiếu `updateHousekeepingStatus` |

---

## Phase 1: Quick wins — Tiêu đề & Liên hệ

**Goal:** Sửa lỗi hiển thị tiêu đề và thay contact hardcode.  
**Duration:** 0.5 ngày  
**Dependencies:** None

### Tasks

#### [T1.1] Helper — formatRoomDisplayTitle
- **Description:** Tạo util `formatRoomDisplayTitle(name: string): string` — nếu `name` đã match `/^phòng\s/i` thì return `name`, else return `` `Phòng ${name}` ``.
- **Acceptance Criteria:**
  - [ ] `"Đơn A"` → `"Phòng Đơn A"`
  - [ ] `"Phòng Đơn Tiêu Chuẩn"` → giữ nguyên, không duplicate
- **Files:** `bks-system-fe/src/utils/partnerRoomDisplay.ts` (new) hoặc mở rộng `partnerPropertyData.ts`
- **Dependencies:** None
- **Blocks:** T1.2
- **Est.:** 30m

#### [T1.2] RoomDetail — apply display title
- **Description:** Thay `Phòng {room.name}` bằng `formatRoomDisplayTitle(room.name)`.
- **Acceptance Criteria:**
  - [ ] Header không còn "Phòng Phòng ..."
- **Files:** `bks-system-fe/src/pages/Partner/RoomDetail.tsx`
- **Dependencies:** T1.1
- **Est.:** 15m

#### [T1.3] BE — contact fields on room detail
- **Description:**
  1. Thêm `config/app.php` keys: `support_phone`, `support_email` (env `APP_SUPPORT_PHONE`, `APP_SUPPORT_EMAIL`; default rỗng).
  2. Trong `getRoomDetailForPartnerSelectColumns` hoặc Resource wrapper: join `users` (property owner) → trả `partner_phone`, `partner_email`.
  3. Response thêm `support_phone`, `support_email` từ config (fallback cuối).
- **Acceptance Criteria:**
  - [ ] `GET /partner/rooms/{id}` có `partner_phone`, `partner_email`
  - [ ] Partner scope không đổi
- **Files:** `RoomsRepository.php`, `config/app.php`, `.env.example`
- **Dependencies:** None
- **Blocks:** T1.4
- **Est.:** 2h

#### [T1.4] FE — OverviewTab dynamic contact
- **Description:** Truyền `contactPhone`, `contactEmail` từ room detail query; hiển thị theo thứ tự ưu tiên: partner → support config → placeholder "Chưa cấu hình".
- **Acceptance Criteria:**
  - [ ] Không còn hardcode `0333494850` / `admin@gmail.com`
  - [ ] Empty state có copy hướng dẫn cập nhật profile
- **Files:** `OverviewTab.tsx`, `useRoomDetailQueries.ts`, `types.ts`
- **Dependencies:** T1.3
- **Est.:** 1h

#### [T1.5] Smoke — Slice A
- **Description:** Manual: mở room có tên "Phòng X" và "X" — verify title + contact.
- **Acceptance Criteria:**
  - [ ] Pass 2 case trên
- **Est.:** 30m

---

## Phase 2: Housekeeping — Điều khiển buồng phòng

**Goal:** Partner đổi trạng thái dọn phòng từ header; checkout tự đánh dirty.  
**Duration:** 1–1.5 ngày  
**Dependencies:** None (song song Phase 1 OK)

### Tasks

#### [T2.1] FE API — updateHousekeepingStatus
- **Description:** Thêm vào `partnerService.ts`:
  ```ts
  updateHousekeepingStatus: (id, status: 'clean' | 'dirty' | 'inspecting') =>
    apiService.patch(`${BASE_URL}/rooms/${id}/housekeeping`, { housekeeping_status: status })
  ```
- **Acceptance Criteria:**
  - [ ] Gọi đúng endpoint, body đúng contract BE
- **Files:** `partnerService.ts`
- **Dependencies:** None
- **Blocks:** T2.3
- **Est.:** 30m

#### [T2.2] Query — map housekeeping_status
- **Description:** `useRoomDetailQuery` map `housekeeping_status` (default `'clean'`); export type trên `Room`.
- **Acceptance Criteria:**
  - [ ] Badge/control nhận đúng giá trị từ API
- **Files:** `useRoomDetailQueries.ts`, `types.ts`
- **Dependencies:** None
- **Blocks:** T2.3
- **Est.:** 30m

#### [T2.3] UI — HousekeepingStatusControl
- **Description:** Component dropdown/badge cạnh occupancy badge trên header `RoomDetail.tsx`:
  - Labels: `clean` → "Sạch sẽ", `dirty` → "Cần dọn", `inspecting` → "Đang kiểm tra"
  - Optimistic update + rollback on error
  - Tooltip: "Cập nhật trạng thái buồng phòng cho lễ tân/nhân viên dọn"
  - **Không** thay đổi occupancy badge; nếu `dirty` + occupancy `Trống` → hiển thị cảnh báo nhỏ "Phòng trống nhưng chưa dọn"
- **Acceptance Criteria:**
  - [ ] Đổi status → PATCH 200 → UI cập nhật
  - [ ] Lỗi API → toast + revert
  - [ ] Mobile: dropdown vẫn usable
- **Files:** `RoomDetail.tsx`, `components/HousekeepingStatusControl.tsx` (new)
- **Dependencies:** T2.1, T2.2
- **Est.:** 3h

#### [T2.4] BE — checkout auto dirty
- **Description:** Trong `BookingService` handler check-out partner (sau khi booking chuyển completed): `Room::where('id', $booking->room_id)->update(['housekeeping_status' => 'dirty'])`.
- **Acceptance Criteria:**
  - [ ] `PUT /partner/bookings/{id}/check-out` → room `housekeeping_status = dirty`
  - [ ] Idempotent nếu checkout 2 lần (reject lần 2 — behavior hiện tại)
- **Files:** `BookingService.php`
- **Dependencies:** None
- **Est.:** 2h

#### [T2.5] i18n / copy
- **Description:** Thêm chuỗi VI cho housekeeping vào `resources/lang/vi/property.php` hoặc FE constants (theo pattern hiện tại partner maintenance).
- **Est.:** 30m

#### [T2.6] Tests — housekeeping lifecycle
- **Description:** Feature test: checkout → assert room dirty. Reuse/extend `PartnerRoomsListTest`.
- **Acceptance Criteria:**
  - [ ] Test pass trong CI
- **Files:** `tests/Feature/Partner/PartnerBookingCheckoutTest.php` (new hoặc extend)
- **Dependencies:** T2.4
- **Est.:** 2h

---

## Phase 3: Edit tại chỗ & Gallery

**Goal:** Partner chỉnh sửa phòng và ảnh không cần quay lại list.  
**Duration:** 1.5–2 ngày  
**Dependencies:** None (khuyến nghị sau Phase 2)

### Tasks

#### [T3.1] Wire PartnerRoomFormSheet
- **Description:** Mount `PartnerRoomFormSheet` trong `RoomDetail.tsx`:
  - State `isEditOpen`, `editingRoom` = room hiện tại (map từ query data sang shape `Room`)
  - `propertyId={room.propertyId}`, `room={room}`, `onSaved` invalidate `partnerRoomDetail`, `partnerRoomImages`
- **Acceptance Criteria:**
  - [ ] Mở sheet → form prefill đúng amenities/services/prices
  - [ ] Save thành công → header + tabs refresh
- **Files:** `RoomDetail.tsx`
- **Dependencies:** None
- **Est.:** 2h

#### [T3.2] Header CTA — "Chỉnh sửa phòng"
- **Description:** Nút outline cạnh "Hợp đồng" / "Bảo trì" → `setIsEditOpen(true)`.
- **Acceptance Criteria:**
  - [ ] Visible desktop + mobile
  - [ ] aria-label accessible
- **Files:** `RoomDetail.tsx`
- **Dependencies:** T3.1
- **Est.:** 30m

#### [T3.3] Query invalidation contract
- **Description:** Document + implement invalidate keys: `partnerRoomDetail`, `partnerRoomImages`, `partnerRoomBookings` (nếu đổi ảnh/giá ảnh hưởng listing).
- **Est.:** 30m

#### [T3.4] Gallery — PartnerImageManager
- **Description:** Trong `GalleryTab` hoặc `RoomDetail`:
  - Nút "Quản lý ảnh" → mở `PartnerImageManager` (`type="room"`, `targetId=roomId`)
  - Sau close → refetch `useRoomImagesQuery`
  - Pattern copy từ `PropertyRooms.tsx` L803–812
- **Acceptance Criteria:**
  - [ ] Upload/delete/reorder ảnh hoạt động
  - [ ] Gallery grid refresh sau khi đóng manager
- **Files:** `GalleryTab.tsx` hoặc `RoomDetail.tsx`
- **Dependencies:** None
- **Est.:** 2h

#### [T3.5] Smoke — Slice C
- **Description:** Edit giá + upload 1 ảnh từ Room Detail → verify list property/occupancy không bắt buộc reload full page.
- **Est.:** 1h

---

## Phase 4: Tenants CTA & Amenities apply-to-all ✅

**Goal:** Hành động tiếp theo từ lịch sử thuê; giảm nhập liệu amenities lặp.  
**Duration:** 2–3 ngày  
**Dependencies:** Phase 3 (form sheet pattern)  
**Status:** Complete (2026-06-21) — T4.6 deferred; E2E delta optional

### Tasks

#### [T4.1] BE — contract_id on partner booking list
- **Description:** Trong `getBookingsForPartner`, thêm subselect:
  ```sql
  (SELECT id FROM contracts WHERE contracts.booking_id = bookings.id ORDER BY id ASC LIMIT 1) as contract_id
  ```
  Hoặc `with(['contracts:id,booking_id'])` + map first.
- **Acceptance Criteria:**
  - [x] Booking có hợp đồng → `contract_id` populated
  - [x] Không có → `null`
  - [x] Không N+1 query
- **Files:** `BookingRepository.php`
- **Dependencies:** None
- **Blocks:** T4.3
- **Est.:** 2h

#### [T4.2] Extract PartnerBookingDetailDialog
- **Description:** Tách dialog L1383+ từ `Bookings.tsx` → `components/PartnerBookingDetailDialog.tsx` với props:
  - `booking`, `open`, `onOpenChange`
  - Optional actions: approve/reject/check-in (pass handlers hoặc `mode="readonly"` cho Room Detail)
- **Acceptance Criteria:**
  - [x] `Bookings.tsx` behavior không regression
  - [x] Room Detail dùng `mode="readonly"` + link hợp đồng
- **Files:** `PartnerBookingDetailDialog.tsx`, `Bookings.tsx`, `TenantsTab.tsx`
- **Dependencies:** None
- **Est.:** 4h

#### [T4.3] TenantsTab — row actions
- **Description:**
  - Click row/card → mở `PartnerBookingDetailDialog`
  - Nút "Xem hợp đồng" → `navigate(/partner/contracts/${contract_id})` khi có `contract_id`; else disabled + tooltip
  - Map `contract_id` trong `useRoomBookingsQuery`
- **Acceptance Criteria:**
  - [x] Click tenant → thấy chi tiết booking
  - [x] Link hợp đồng hoạt động khi có data
  - [x] Mobile card có `role="button"` + keyboard
- **Files:** `TenantsTab.tsx`, `useRoomDetailQueries.ts`
- **Dependencies:** T4.1, T4.2
- **Est.:** 3h

#### [T4.4] BE — apply_to_all_rooms on room update
- **Description:** Mở rộng `PartnerRoomController::update`:
  - Nhận `apply_to_all_rooms: boolean` (optional)
  - Khi true + có `amenities` hoặc `services`: sync pivot cho **tất cả rooms cùng `property_id`** (transaction)
  - Tham chiếu logic `RoomTouristSpotMapService` (property scope, skip room hiện tại nếu đã sync)
- **Acceptance Criteria:**
  - [x] Update room A amenities + apply_all → room B,C cùng property có cùng amenities
  - [x] Ownership check partner
  - [x] Rollback on failure
- **Files:** `PartnerRoomController.php`, `RoomsService.php`, `RoomsValidation.php`
- **Dependencies:** None
- **Est.:** 4h

#### [T4.5] FE — checkbox apply to all (form sheet)
- **Description:** Thêm checkbox "Áp dụng tiện ích/dịch vụ cho tất cả phòng cùng tòa nhà" trong `PartnerRoomFormSheet` (chỉ hiện khi edit, không bulk create).
- **Acceptance Criteria:**
  - [x] Payload gửi `apply_to_all_rooms: true`
  - [x] Confirm dialog nếu property có >5 phòng
- **Files:** `PartnerRoomFormSheet.tsx`, `partnerRoomForm.ts`
- **Dependencies:** T4.4
- **Est.:** 2h

#### [T4.6] FE — AmenitiesTab inherit hint (optional) — **SKIPPED**
- **Description:** Badge nhỏ "Đồng bộ với N phòng khác" nếu amenities trùng 100% với sibling rooms — **defer** nếu API chưa có endpoint compare; có thể skip Slice D.
- **Est.:** 2h (optional)

#### [T4.7] Tests + E2E delta
- **Description:**
  - Feature: apply_to_all amenities sync
  - Feature: booking list returns contract_id
  - Cập nhật `business-script/E2E_PARTNER_MAINTENANCE.md` hoặc tạo `E2E_PARTNER_ROOM_DETAIL.md` với case housekeeping + edit + tenant click
- **Est.:** 3h

---

## QA Handoff Checklist

| # | Scenario | Expected |
|---|----------|----------|
| 1 | Room tên "Phòng 101" | Header không duplicate prefix |
| 2 | Overview contact | Hiện phone/email partner hoặc support config |
| 3 | Đổi housekeeping → Sạch | PATCH 200, badge cập nhật |
| 4 | Checkout booking | Room chuyển "Cần dọn" |
| 5 | Chỉnh sửa phòng từ detail | Sheet save OK, tabs refresh |
| 6 | Quản lý ảnh từ Gallery | Upload/delete OK |
| 7 | Click tenant row | Dialog booking + link hợp đồng |
| 8 | Edit amenities + apply all | Sibling rooms sync |

---

## Out of Scope (plan riêng)

- Bảng `room_categories` / inherit pricing từ category
- Inline edit amenities trực tiếp trên tab (không qua sheet) — có thể phase 2 của PLAN-RD-016 nếu cần
- Auto-set occupancy "Trống" khi housekeeping → clean (cần thống nhất occupancy engine — hiện computed từ booking/maintenance)

---

## File Impact Summary

| Layer | Files (chính) |
|-------|----------------|
| BE | `RoomsRepository.php`, `BookingRepository.php`, `BookingService.php`, `PartnerRoomController.php`, `RoomsService.php`, `config/app.php` |
| FE | `RoomDetail.tsx`, `OverviewTab.tsx`, `GalleryTab.tsx`, `TenantsTab.tsx`, `useRoomDetailQueries.ts`, `partnerService.ts`, `PartnerRoomFormSheet.tsx`, `HousekeepingStatusControl.tsx`, `PartnerBookingDetailDialog.tsx` |
| Tests | `PartnerRoomsListTest.php`, `PartnerBookingCheckoutTest.php`, new apply-to-all test |
| Docs | `docs/reports/domain/partner_room_detail_review.md` (link plan), `.env.example` |

---

**Author:** Technical Lead / Architect  
**Date:** 2026-06-21
