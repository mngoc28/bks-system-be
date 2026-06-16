# UAT Retest Handoff: AC-PB-01 → AC-PB-07

- **Nguồn yêu cầu:** `docs/SRC/srs_booking_deposit_and_financial_rules.md` (v2.1, mục `3.7.3`)
- **Nguồn triển khai:** `docs/reports/implementation/impl_public-booking_stay-classification_2026-06-11.md`
- **Mục tiêu:** Xác nhận fix P0 cho UAT-ISSUE-004/005 đã đạt theo AC-PB-01 → AC-PB-07
- **Ngày handoff:** 2026-06-11
- **Người nhận:** UAT Tester

---

## 1) Scope Retest

Trong đợt retest này chỉ xác nhận các tiêu chí:
- `REQ-STAY-001` đến `REQ-STAY-005`
- `REQ-DOC-003`
- AC-PB-01 → AC-PB-07

Ngoài scope:
- UAT-ISSUE-001, 002, 003, 006, 007

---

## 2) Test Data Bắt Buộc

- **Case A (Nhà nghỉ):** phòng thuộc `nha-nghi-guesthouse` (ưu tiên lại phòng `1247` nếu còn dữ liệu tương đương)
  - Check-in: `2026-06-21`
  - Check-out: `2026-06-23`
  - Giá kỳ vọng mẫu: `740,856` / đêm (nếu dữ liệu seed vẫn giữ)
- **Case B (Căn hộ DV ngắn hạn):** phòng `can-ho-dich-vu-theo-phong`, lưu trú `2 đêm`, chọn gói `day`
- **Case C (Căn hộ DV dài hạn):** phòng `can-ho-dich-vu-theo-phong`, lưu trú `>=30 đêm` hoặc gói `month`
- **Case D (Homestay ngưỡng):**
  - Booking 1: `29 đêm`
  - Booking 2: `30 đêm`

---

## 3) Kịch bản chạy AC

### AC-PB-01
- **Given:** Nhà nghỉ, 2 đêm
- **When:** Đi tới bước xác nhận Public Booking
- **Then:**
  - Copy pháp lý chỉ là điều khoản lưu trú ngắn hạn
  - Không xuất hiện chữ "Hợp đồng thuê nhà" / "lưu trú dài hạn"

### AC-PB-02
- **Given:** Check-in 21/06, check-out 23/06, gói `day`, giá 740,856/đêm
- **When:** Xem tạm tính
- **Then:**
  - Hiển thị `2 đêm`
  - Tổng phòng `1,481,712`

### AC-PB-03
- **Given:** Cùng dữ liệu AC-PB-02 (có T7/CN trong kỳ lưu trú)
- **When:** Xem lý do cọc
- **Then:**
  - Có lý do "Cuối tuần" (hoặc wording tương đương)
  - Không có lý do "Thuê dài hạn"

### AC-PB-04
- **Given:** Căn hộ DV, 2 đêm, gói `day`
- **When:** Tới bước xác nhận
- **Then:** Luồng ngắn hạn (voucher), không lease copy

### AC-PB-05
- **Given:** Căn hộ DV, `>=30 đêm` hoặc gói `month`
- **When:** Tới bước xác nhận
- **Then:** Luồng lease + cọc dài hạn

### AC-PB-06
- **Given:** Homestay chạy 2 booking (29 đêm và 30 đêm)
- **When:** So sánh bước xác nhận
- **Then:**
  - 29 đêm => `short_term`
  - 30 đêm => `long_term_lease`

### AC-PB-07
- **Given:** Bất kỳ case AC-PB-01..06
- **When:** Hoàn tất tạo booking + nhận email xác nhận
- **Then:** Phân loại lưu trú và số tiền phải khớp giữa:
  - preview trên UI
  - kết quả booking đã tạo
  - email xác nhận

---

## 4) Bằng chứng cần nộp

Cho mỗi AC:
- 1 screenshot màn hình chính (preview/xác nhận)
- 1 ảnh/chuỗi chứng minh số tiền
- 1 ảnh/chuỗi chứng minh copy pháp lý hoặc lý do cọc
- (AC-PB-07) ảnh email xác nhận tương ứng booking

Đặt tên file:
- `ac-pb-0X_<result>_<timestamp>.png`
- Ví dụ: `ac-pb-02_pass_20260611-2015.png`

---

## 5) Template kết quả UAT

- `AC-PB-01`: PASS/FAIL — note:
- `AC-PB-02`: PASS/FAIL — note:
- `AC-PB-03`: PASS/FAIL — note:
- `AC-PB-04`: PASS/FAIL — note:
- `AC-PB-05`: PASS/FAIL — note:
- `AC-PB-06`: PASS/FAIL — note:
- `AC-PB-07`: PASS/FAIL — note:

**Gate đề xuất:**
- Nếu có bất kỳ FAIL ở AC-PB-01..07 => giữ `NO-GO`
- Chỉ chuyển `CONDITIONAL GO` khi 7/7 PASS

**Kết quả retest (2026-06-11):** 7/7 PASS — chi tiết tại `uat_retest_result_ac-pb-01-07_2026-06-11.md`. AC-PB-07: email xác nhận do user verify ngoài phiên (bỏ qua inbox retest).

---

## 6) Traceability

- EU-RAW-004/005
  -> UAT-ISSUE-004/005
  -> Domain review: `docs/reports/domain/domain_review_public-booking_uat-004-005_2026-06-11.md`
  -> SRS v2.1 AC-PB-01..07
  -> Implementation P0
  -> UAT Retest (tài liệu này)
