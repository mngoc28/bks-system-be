# Domain Review: Public Booking — UAT-ISSUE-004 & UAT-ISSUE-005

**Nguồn UAT:** [`uat_triage_public-booking_eu-raw_2026-06-11.md`](../uat/uat_triage_public-booking_eu-raw_2026-06-11.md)  
**EU trace:** EU-RAW-004, EU-RAW-005  
**Case thực tế:** Phòng `1247` (Nhà nghỉ Đà Nẵng), check-in **21/06/2026**, check-out **23/06/2026**, 2 NL + 2 trẻ  
**Ngày review:** 2026-06-11

---

## Executive Summary

- **Domain Recommendation:** **REJECTED (High Operational Risk)** — luồng đặt phòng ngắn hạn đang vi phạm chuẩn OTA và SRS nội bộ; **không được phát hành** cho khách du lịch cho đến khi sửa.
- **Tóm tắt:** Hai issue UAT-004/005 **cùng một lỗi phân loại nghiệp vụ** trên FE, cộng thêm **sai chuẩn đếm đêm** so với ngành khách sạn. Khách đặt **2 đêm nhà nghỉ** bị hiển thị **3 ngày × giá**, lý do cọc **"Thuê dài hạn"**, và copy **ký Hợp đồng thuê nhà** — hoàn toàn trái kỳ vọng Agoda/Traveloka và trái `REQ-DOC-001` / `REQ-SDEP-001` trong SRS tài chính.

---

## Phán quyết nghiệp vụ (Authoritative)

| ID | Phán quyết | Mức độ |
|----|------------|--------|
| **DOM-PB-001** | Đặt phòng ngắn hạn (Khách sạn, Nhà nghỉ, Homestay &lt; 30 đêm) **không bao giờ** sinh `LEASE_AGREEMENT` hay copy "Hợp đồng thuê nhà" trên Public Booking | **Bắt buộc** |
| **DOM-PB-002** | Giá ngắn hạn tính theo **số đêm lưu trú** (nights), không nhân theo **số ngày lịch inclusive** | **Bắt buộc** |
| **DOM-PB-003** | Nhãn UI ngắn hạn: **"X đêm"**; chỉ dùng **"ngày"** khi gói prorate theo tháng (căn hộ &lt; 30 ngày) | **Bắt buộc** |
| **DOM-PB-004** | Lý do cọc ngắn hạn: cuối tuần / mùa cao / last-minute — **không** gắn nhãn "Thuê dài hạn" | **Bắt buộc** |
| **DOM-PB-005** | `Căn hộ dịch vụ`: lease + cọc escrow chỉ khi **≥ 30 đêm** hoặc khách chọn gói **tháng** | **Bắt buộc** |
| **DOM-PB-006** | `Homestay`: &lt; 30 đêm → voucher ngắn hạn; ≥ 30 đêm → lease (theo `PRICING_RESTRUCTURE_PLAN`) | **Bắt buộc** |

---

## Hospitality Business Rules & Standards

### 1. Đếm đêm vs đếm ngày (UAT-ISSUE-004 — phần giá)

**Chuẩn ngành (Hotel / Guesthouse / OTA):**

- Check-in **21/06**, check-out **23/06** = **2 đêm** (đêm 21→22, 22→23).
- Ngày trả phòng **không** tính là một đêm bán thêm (checkout exclusive), khớp conflict semantics `[start, end)` trong SRS.

**Quy tắc BKS cho nhóm ngắn hạn:**

$$\text{Số đêm} = \max(1,\ \text{diffCalendarDays}(\text{check-in},\ \text{check-out}))$$

Với 21→23: diff = 2 → **2 đêm**, tổng phòng = **2 × đơn giá/đêm**.

**Hiện trạng hệ thống (sai):**

- FE/BE dùng `countBookingDaysInclusive` = `diffInDays + 1` → **3 ngày**.
- Gói `unit = day` nhân **3 × 740.856** → khách trả thừa ~1 đêm so với OTA.
- Nhãn **"Tổng số ngày đặt: 3 ngày"** gây hiểu nhầm (EU: *"21 đến 23 là 2 đêm hay 3 ngày?"*).

**Khớp SRS:** `REQ-FIN-001` quy định *"Đơn giá phòng theo ngày × **số đêm**"* — implementation đang lệch domain.

> **Ngoại lệ duy nhất:** Căn hộ dịch vụ **chỉ có gói tháng**, đặt &lt; 30 ngày: prorate theo ngày lịch (30 ngày = 1 tháng) — nhãn phải ghi rõ *"X ngày · gói tháng"*, không gọi là "đêm".

### 2. Phân loại ngắn hạn / dài hạn (UAT-ISSUE-004 cọc + UAT-ISSUE-005 hợp đồng)

**Ma trận loại hình** (theo `PropertyTypesSeeder` + `PRICING_RESTRUCTURE_PLAN`):

| `property_type_id` | Slug | Loại hình | Đặt 2 đêm (case EU) |
|--------------------|------|-----------|---------------------|
| 1 | `khach-san-hotel` | Khách sạn | **Ngắn hạn** — voucher, cọc động (cuối tuần/last-minute) |
| 2 | `nha-nghi-guesthouse` | Nhà nghỉ | **Ngắn hạn** — voucher, **không** lease |
| 3 | `can-ho-dich-vu-theo-phong` | Căn hộ DV | **Ngắn hạn** nếu &lt; 30 đêm + gói `day`; **Dài hạn** nếu ≥ 30 đêm hoặc gói `month` |
| 4 | `homestay-co-chia-phong` | Homestay | **Ngắn hạn** nếu &lt; 30 đêm; **Dài hạn** nếu ≥ 30 đêm |

**Root cause đã xác minh trong code:**

`BookingPage.tsx` đang dùng **ID sai**:

```typescript
// SAI: id 2 = Nhà nghỉ, id 3 = Căn hộ DV — không phải "Căn hộ + Văn phòng"
room.property_type_id === 2 || room.property_type_id === 3
```

→ Phòng **Nhà nghỉ (id=2)** — đúng case EU phòng 1247 — bị **luôn** coi là dài hạn → lý do cọc *"Thuê dài hạn"* + copy *"ký Hợp đồng thuê nhà"*.

**Logic đúng (domain function — BA ghi vào PRD):**

```
isLongTermLeaseBooking =
  NOT isHotelOrGuesthouse(propertyType)
  AND (
    stayNights >= 30
    OR (isServicedApartment(propertyType) AND selectedPriceUnit === 'month' AND stayNights < 30 AND noDayRateAvailable)
  )
```

- **Khách sạn / Nhà nghỉ:** `isLongTermLeaseBooking` = **false** mọi độ dài lưu trú.
- **Căn hộ DV, 2 đêm, gói day:** ngắn hạn, cọc theo `REQ-SDEP-001` (cuối tuần 21–22/06 → cọc 50% **hợp lệ**, nhưng lý do phải là *"Cuối tuần"*, không phải *"Thuê dài hạn"*).

### 3. Tài liệu pháp lý (UAT-ISSUE-005)

| Phân khúc | Sau Partner confirm | Copy Public Booking bước xác nhận |
|-----------|---------------------|-----------------------------------|
| Ngắn hạn | `TERMS_AND_CONDITIONS` — auto-signed voucher | *"Đồng ý Điều khoản lưu trú ngắn hạn"* |
| Dài hạn (≥ 30 đêm / lease) | `LEASE_AGREEMENT` — chờ ký Stay Portal | *"Sau xác nhận, ký Hợp đồng thuê điện tử tại Hồ sơ lưu trú"* |

**BE `BookingService` (confirm)** đã branch đúng theo `property slug` `can-ho` / `apartment` — **FE preview bước xác nhận** và **deposit reason** đang **lệch** so với BE → khách abandon trước khi tạo đơn.

**Rủi ro vận hành:** Lễ tân / Partner nhận đơn nhà nghỉ 2 đêm nhưng khách tưởng phải ký lease → ticket CS, chargeback, mất conversion.

---

## Gap Analysis

### GAP-PB-01: Đếm inclusive ngày trên booking ngắn hạn

- **Business Risk:** Thu **thừa 1 đêm** (case 21–23); dispute tại quầy; vi phạm minh bạch giá (ADR sai, GMV sai).
- **Domain Recommendation:** Đổi công thức ngắn hạn sang **nights**; đồng bộ FE `bookingAmount.ts`, BE `BookingStayAmountCalculator` (unit `day`), email, Stay Portal.
- **UAT retest:** 21→23 = **2 đêm**, 740.856 × 2 = 1.481.712đ (không phải × 3).

### GAP-PB-02: Hard-code `property_type_id` 2/3 = dài hạn

- **Business Risk:** **100% nhà nghỉ** public bị messaging lease; blocker conversion (EU abandon).
- **Domain Recommendation:** Dùng `property_types.slug` hoặc helper `supportsElectronicContractByPropertyType` + ngưỡng **30 đêm**; xóa magic number `2`, `3`.
- **UAT retest:** Phòng 1247 (Nhà nghỉ), 2 đêm → **không** hiện "Hợp đồng thuê nhà"; lý do cọc **"Cuối tuần"** (21–22 là T7, CN).

### GAP-PB-03: Nhãn "Tổng số ngày đặt" trên mọi phân khúc

- **Business Risk:** Cognitive load; khách OTA quen "2 nights" không hiểu "3 ngày".
- **Domain Recommendation:**
  - Ngắn hạn: **"Tổng số đêm: X đêm"**
  - Prorate tháng: giữ **"X ngày (tính theo gói tháng)"**

### GAP-PB-04: BE/FE không đồng nhất tiêu chí long-term

- **Business Risk:** Khách thấy lease ở preview nhưng BE sinh voucher (hoặc ngược lại) sau confirm.
- **Domain Recommendation:** Một **StayClassificationService** (hoặc shared constant) dùng chung Public Booking, Deposit, Contract, Stay Portal — single source of truth.

---

## Mapping UAT → Domain → Hành động

| UAT-ISSUE | Domain rule | Fix owner | Ưu tiên |
|-----------|-------------|-----------|---------|
| **004** (3 ngày × giá) | DOM-PB-002, DOM-PB-003, GAP-PB-01 | BE + FE | **P0** |
| **004** (cọc "Thuê dài hạn") | DOM-PB-004, GAP-PB-02 | FE | **P0** |
| **005** (hợp đồng thuê 2 đêm) | DOM-PB-001, DOM-PB-005, GAP-PB-02 | FE (+ align BE) | **P0** |

**Lưu ý case EU:** Cọc 50% ~1,1 triệu **có thể đúng** sau khi sửa đếm đêm (2 × 740k = ~1,48M → 50% ≈ 740k), và vì **có cuối tuần** — không phải vì dài hạn. Retest phải tách **số tiền** vs **lý do hiển thị**.

---

## Collaboration Action Items

### Cho Business Analyst (BA)

1. Cập nhật PRD / SRS (`srs_booking_deposit_and_financial_rules.md` §3.1):
   - Định nghĩa rõ **night** vs **calendar day**; conflict `[start, end)` checkout exclusive.
   - Bổ sung **REQ-STAY-001**: `resolveStayClassification(propertyType, priceUnit, stayNights)` — bảng ma trận như trên.
2. Sửa acceptance criteria Public Booking:
   - AC-PB-01: Nhà nghỉ 2 đêm → voucher, không lease copy.
   - AC-PB-02: 21→23 → label "2 đêm", total = 2 × nightly rate.
3. Ghi nhận **data issue** phòng 1247 (UAT-003) tách biệt — không liên quan DOM-PB-002.

### Cho UAT Tester

1. **UAT-PB-RET-01** — Nhà nghỉ, 21–23/06: 2 đêm, không lease, cọc lý do cuối tuần (nếu applicable).
2. **UAT-PB-RET-02** — Căn hộ DV, 2 đêm, gói day: voucher, không lease.
3. **UAT-PB-RET-03** — Căn hộ DV, ≥ 30 đêm: lease copy + cọc dài hạn.
4. **UAT-PB-RET-04** — Homestay, 29 vs 30 đêm: ngưỡng chuyển lease.
5. **UAT-PB-RET-05** — Đối chiếu số tiền FE preview = BE tạo booking = email xác nhận.

### Cho Engineering (tham chiếu — không triển khai trong handoff này)

| File | Vấn đề |
|------|--------|
| `bks-system-fe/.../BookingPage.tsx` | `property_type_id === 2 \|\| 3`, `depositRequirements`, copy "Quy trình pháp lý" |
| `bks-system-fe/src/utils/dateUtils.ts` | `countBookingDaysInclusive` + label "Tổng số ngày đặt" |
| `bks-system-fe/src/utils/bookingAmount.ts` | `unit=day` × inclusive days |
| `bks-system-be/.../BookingStayAmountCalculator.php` | `countStayDays` + `default => unitPrice * days` |
| `bks-system-fe/src/utils/stayPropertyType.ts` | Đã có helper đúng — **nên reuse** thay magic ID |

---

## Domain Recommendation cho Release

| Tiêu chí | Trước fix | Sau fix (kỳ vọng) |
|----------|-----------|-------------------|
| Đặt nhà nghỉ 2 đêm — hoàn tất booking | FAIL | PASS |
| Minh bạch giá vs OTA | FAIL | PASS |
| Lease chỉ khi đủ điều kiện dài hạn | FAIL | PASS |

**Kết luận domain:** Giữ **NO-GO** UAT cho đến khi **GAP-PB-01** và **GAP-PB-02** closed. Sau fix, hospitality sign-off **CONDITIONALLY APPROVED** pending UAT retest scenarios UAT-PB-RET-01 → 05.

---

**Sign-off:** Senior Hospitality & Accommodation Domain Expert  
**Date:** 2026-06-11  
**Traceability:** EU-RAW-004/005 → UAT-ISSUE-004/005 → `domain_review_public-booking_uat-004-005_2026-06-11.md`
