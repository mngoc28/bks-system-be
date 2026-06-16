# UAT Triage Report: Public Booking Flow (từ EU-RAW)

**Nguồn:** [`eu-session_public-booking_2026-06-11.md`](../eu-feedback/eu-session_public-booking_2026-06-11.md)  
**Persona nguồn:** EU-GUEST (Chị Mai)  
**Môi trường:** `http://127.0.0.1:5173` — mobile 390×844, BE `127.0.0.1:8000`  
**Ngày triage:** 2026-06-11

---

## Executive Summary

- **UAT Recommendation:** **NO-GO (Needs Revision)** — luồng đặt phòng công khai **không thể hoàn tất** với khách du lịch ngắn hạn ở trạng thái hiện tại.
- **Tóm tắt:** EU session ghi nhận 7 EU-RAW. Sau triage: **3 Blocker**, **2 Major**, **2 Minor/Suggestion**. Ba blocker (UAT-ISSUE-004, 005, 003) liên quan **niềm tin sản phẩm + sai loại hình thuê + chặn conversion**. Các issue UX (search filter, chatbot, chính sách hủy) cần sửa trước hoặc song song nhưng không phải nguyên nhân duy nhất của abandon.

## Metrics & Status

| Chỉ số | Giá trị |
|--------|---------|
| EU-RAW nhận | 7 |
| UAT-ISSUE tạo | 7 |
| Blocker | 3 |
| Major | 2 |
| Minor / Suggestion | 2 |
| Kịch bản EU hoàn thành | 0/1 (dừng trước VietQR) |

---

## Bảng triage nhanh

| EU-RAW | UAT-ISSUE | Type | Severity | Owner fix | Sprint |
|--------|-----------|------|----------|-----------|--------|
| 001 | UAT-ISSUE-001 | Usability | Major | FE + Product | S1 |
| 002 | UAT-ISSUE-002 | Usability | Major | FE | S1 |
| 003 | UAT-ISSUE-003 | Functional Bug / Data | **Blocker** | BE/Data + BA | S0 |
| 004 | UAT-ISSUE-004 | Functional Bug | **Blocker** | BE + Domain | S0 |
| 005 | UAT-ISSUE-005 | Functional Bug | **Blocker** | BE + FE + Domain | S0 |
| 006 | UAT-ISSUE-006 | Usability | Minor | FE + BA copy | S2 |
| 007 | UAT-ISSUE-007 | Enhancement | Suggestion | BA + FE | S3 |

**Ghi chú owner:** Domain = phối hợp `hospitality-expert` + BA; UAT giữ verify sau fix.

---

## Blocking Issues

### UAT-ISSUE-003: Diện tích phòng và mô tả mâu thuẫn (từ EU-RAW-003)

- **Type:** Functional Bug / Data Integrity
- **Severity:** Blocker
- **Target Persona:** EU-GUEST
- **EU source:** EU-RAW-003
- **Steps to Reproduce:**
  1. Mở `http://127.0.0.1:5173/rooms/1247?guests=4`
  2. Đợi load chi tiết phòng **Phòng Sang Trọng 827**
  3. So sánh mục **DIỆN TÍCH** (11.38 m²) với **Mô tả phòng** (ghi "40m²", King, jacuzzi)
- **Expected:** Diện tích, mô tả, tiện nghi nhất quán; khách tin được listing.
- **Actual:** 11.38 m² vs mô tả 40m² — EU báo abandon / nghi lừa đảo.
- **User Pain Point:** Mất niềm tin; rủi ro review 1 sao, khiếu nại; gia đình 4 người không dám đặt.
- **Proposed Solution:** Rà soát seed/data phòng 1247; validate mô tả vs `area` khi Partner publish; hiển thị cảnh báo admin nếu lệch ngưỡng.
- **Cross-team:** `business-analyst.md` — rule dữ liệu listing; QA regression data.
- **Status:** Open

### UAT-ISSUE-004: Đếm "3 ngày" và cọc "Thuê dài hạn" cho booking 21–23/06 (từ EU-RAW-004)

- **Type:** Functional Bug
- **Severity:** Blocker
- **Target Persona:** EU-GUEST
- **EU source:** EU-RAW-004
- **Steps to Reproduce:**
  1. Chi tiết phòng 1247 → chọn nhận **21/06/2026**, trả **23/06/2026**
  2. Vào `booking/1247?guests=4&startDate=2026-06-21&endDate=2026-06-23`
  3. Xem **Tổng số ngày đặt** và **Tiền cọc** / lý do cọc
- **Expected:** 2 đêm (hoặc label rõ "2 đêm / 3 calendar days" nếu cố ý); cọc theo policy **ngắn hạn** nhà nghỉ; tổng = 2 × đơn giá (nếu tính theo đêm).
- **Actual:** "3 ngày", phí × 3; lý do cọc **"Thuê dài hạn (Căn hộ / Dịch vụ dài hạn)"**, cọc 50%.
- **User Pain Point:** Khách không hiểu phải trả bao nhiêu; nghi hệ thống tính sai tiền.
- **Proposed Solution:** Xác nhận công thức nights vs days với domain; map đúng `rental_type` cho Guesthouse ngắn hạn; hiển thị breakdown "X đêm × giá".
- **Cross-team:** `hospitality-expert.md` + `business-analyst.md`
- **Status:** Open

### UAT-ISSUE-005: Màn xác nhận yêu cầu ký Hợp đồng thuê nhà cho đặt 2 đêm (từ EU-RAW-005)

- **Type:** Functional Bug
- **Severity:** Blocker
- **Target Persona:** EU-GUEST
- **EU source:** EU-RAW-005
- **Steps to Reproduce:**
  1. Tiếp tục từ UAT-ISSUE-004 — điền form (Họ tên, Email, SĐT) → **Tiếp tục xác nhận**
  2. Đọc mục **Quy trình pháp lý**
- **Expected:** Booking ngắn hạn → phiếu xác nhận / điều khoản lưu trú; **không** hợp đồng thuê dài hạn.
- **Actual:** Copy: *"loại hình lưu trú dài hạn"*, yêu cầu ký **Hợp đồng thuê nhà điện tử** tại Hồ sơ lưu trú.
- **User Pain Point:** EU **dừng hẳn**, không bấm "Xác Nhận Đặt Phòng" — conversion = 0.
- **Proposed Solution:** Branch UI/copy theo `booking_type` / độ dài lưu trú; LEASE chỉ khi ≥30 đêm (theo SRS partner); TERMS cho ngắn hạn.
- **Cross-team:** `hospitality-expert.md` — liên kết UAT-ISSUE-004 (cùng root: classify sai long-term).
- **Status:** Open

---

## Usability & UX Findings

### UAT-ISSUE-001: Kết quả tìm kiếm lẫn thuê tháng khi intent "Đặt phòng theo ngày" (từ EU-RAW-001)

- **Type:** Usability Issue
- **Severity:** Major
- **Target Persona:** EU-GUEST
- **EU source:** EU-RAW-001
- **Steps to Reproduce:**
  1. Trang chủ → **Tìm phòng ngay** → tab **Đặt phòng theo ngày**
  2. Chọn Đà Nẵng, 2 NL + 2 trẻ em → **TÌM KIẾM**
  3. Mở `/search/rooms?provinceId=21&guests=4` — sort **Giá thấp đến cao**
- **Expected:** Ưu tiên phòng **/đêm**, badge ngắn hạn; hoặc filter mặc định ẩn thuê dài hạn.
- **Actual:** Đầu list toàn **/tháng**, badge **Thuê dài hạn**; phòng /đêm khó tìm.
- **User Pain Point:** ~2 phút cuộn; dễ bỏ sang OTA; review: *"ra toàn thuê tháng"*.
- **Proposed Solution:** Default filter theo tab search; tab ngày → ẩn/giảm weight monthly; label giá rõ `/đêm` vs `/tháng`.
- **Status:** Open

### UAT-ISSUE-002: Chatbot AI mở sẵn che UI mobile (từ EU-RAW-002)

- **Type:** Usability Issue
- **Severity:** Major
- **Target Persona:** EU-GUEST
- **EU source:** EU-RAW-002
- **Steps to Reproduce:**
  1. Viewport mobile ~390px
  2. Truy cập `/`, `/search/rooms`, `/rooms/1247`, `/booking/1247`
- **Expected:** Chat collapsed mặc định; không che CTA chính.
- **Actual:** Panel **BKS AI ASSISTANT** mở, gợi ý + ô chat chiếm phần lớn màn hình.
- **User Pain Point:** ~30s/trang đóng chat; khó thao tác một tay.
- **Proposed Solution:** Default `collapsed` trên mobile; nhớ preference; không auto-open lần đầu.
- **Status:** Open

### UAT-ISSUE-006: Chính sách hủy quá dài trước thanh toán (từ EU-RAW-006)

- **Type:** Usability Issue
- **Severity:** Minor
- **Target Persona:** EU-GUEST
- **EU source:** EU-RAW-006
- **Steps to Reproduce:**
  1. Vào trang booking bước 1 — cuộn **Chính Sách Đặt Phòng**
- **Expected:** Tóm tắt 1–2 dòng + "Xem chi tiết"; mốc hủy miễn phí nổi bật.
- **Actual:** 4+ đoạn pháp lý, mốc 00:00 nhiều ngày; ~2 phút đọc.
- **User Pain Point:** Cognitive load trước khi trả tiền; so sánh xấu với Agoda.
- **Proposed Solution:** Summary box: *"Hủy miễn phí đến [date] — hoàn 100% cọc"*; chi tiết trong accordion.
- **Cross-team:** BA xác nhận nội dung tóm tắt đủ pháp lý.
- **Status:** Open

---

## Recommended Enhancements / Future Scope

### UAT-ISSUE-007: Search hiển thị "4 Khách" thay vì breakdown NL/trẻ (từ EU-RAW-007)

- **Type:** Enhancement
- **Severity:** Suggestion
- **Target Persona:** EU-GUEST
- **EU source:** EU-RAW-007
- **Steps to Reproduce:**
  1. Popup tìm kiếm: 2 NL + 2 trẻ em → TÌM KIẾM
  2. Xem chip/filter trên `/search/rooms`
- **Expected:** **"2 người lớn, 2 trẻ em"** (như popup).
- **Actual:** Chỉ **"4 Khách"**.
- **Proposed Solution:** Persist `adults`/`children` query params; hiển thị breakdown trên search bar.
- **Cross-team:** `business-analyst.md` — AC hiển thị.
- **Status:** Open

---

## Câu hỏi EU → Chuyển BA / Domain (chưa có UAT-ISSUE riêng)

| # | Câu hỏi EU | Phân loại UAT | Hành động |
|---|------------|---------------|-----------|
| Q1 | 21–23 là 2 đêm hay 3 ngày? | Requirement clarification | BA + Domain — gắn UAT-ISSUE-004 |
| Q2 | Sao nhà nghỉ lại thuê dài hạn / hợp đồng? | Requirement bug | Domain — gắn UAT-ISSUE-004, 005 |
| Q3 | 11m² đủ 4 người? | Data trust | BA — gắn UAT-ISSUE-003 |
| Q4 | VietQR — biết đã nhận tiền chưa? | Chưa test (dừng trước QR) | UAT scenario tiếp sau fix blocker |
| Q5 | Trẻ em có tính phí / tuổi? | Requirement gap | BA — có thể link UAT-ISSUE-007 |

---

## Đề xuất thứ tự xử lý (UAT)

**S0 — Trước khi retest EU:**
1. UAT-ISSUE-004 + 005 (cùng root classification)
2. UAT-ISSUE-003 (data phòng 1247 + rule validate)

**S1 — Retest EU-GST-01 → GST-03:**
3. UAT-ISSUE-001, 002

**S2+:**
4. UAT-ISSUE-006, 007

**Retest criteria (EU-GUEST):**
- [ ] Đặt 2 đêm: label đêm/giá khớp; không copy "thuê dài hạn" / hợp đồng thuê
- [ ] Search tab ngày: top results có /đêm
- [ ] Mobile: chatbot đóng mặc định
- [ ] Phòng 1247: m² khớp mô tả
- [ ] Hoàn thành tới VietQR (scenario mới)

---

## UAT Recommendation chi tiết

| Tiêu chí | Đánh giá |
|----------|----------|
| Job hoàn thành (đặt phòng + thanh toán) | **FAIL** — abandon tại xác nhận |
| Niềm tin (giá, m², policy) | **FAIL** |
| Mobile UX | **FAIL** — chatbot |
| Tìm phòng ngắn hạn | **PARTIAL** — có kết quả nhưng UX kém |

**Kết luận:** **NO-GO** cho release luồng Public Booking cho khách du lịch ngắn hạn. **CONDITIONAL GO** chỉ sau khi 3 blocker Closed và EU retest PASS.

---

**Sign-off:** Senior UAT Tester (triage từ EU-RAW)  
**Date:** 2026-06-11  
**Traceability:** `eu-session_public-booking_2026-06-11.md` → `uat_triage_public-booking_eu-raw_2026-06-11.md`
