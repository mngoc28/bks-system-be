# UAT Retest Result Sheet: AC-PB-01 → AC-PB-07

- **Feature:** Public Booking Stay Classification (P0)
- **Reference Handoff:** `docs/reports/uat/uat_retest_ac-pb-01-07_2026-06-11.md`
- **Reference SRS:** `docs/SRC/srs_booking_deposit_and_financial_rules.md` (v2.1, mục 3.7.3)
- **Tester:** AI Agent (EU-GUEST mode)
- **Environment:** `http://localhost:5173`, Chrome DevTools MCP, viewport `390x844` (mobile)
- **Build/Commit:** Workspace local state @ 2026-06-11 19:54
- **Date:** 2026-06-11

---

## 1) Test Execution Summary

| Metric | Value |
|---|---|
| Total AC | 7 |
| Pass | 7 |
| Fail | 0 |
| Blocker | 0 |
| Recommendation | CONDITIONAL GO |

---

## 2) Result Matrix (Điền trực tiếp)

| AC ID | Result (PASS/FAIL) | Actual Result | Evidence Path | Defect ID (nếu FAIL) | Note |
|---|---|---|---|---|---|
| AC-PB-01 | PASS | Nhà nghỉ 2 đêm hiển thị copy ngắn hạn, không xuất hiện lease copy ở bước xác nhận | `docs/reports/uat/evidence/ac-pb-retest-2026-06-11/ac-pb-01_pass_20260611-1951.png` |  | Room `1247` |
| AC-PB-02 | PASS | Check-in 21/06, check-out 23/06 hiển thị `2 đêm`, tổng phòng `3.200.000` = `1.600.000 x 2` | `docs/reports/uat/evidence/ac-pb-retest-2026-06-11/ac-pb-02_pass_20260611-1951.png` |  | Room `1247` |
| AC-PB-03 | PASS | Lý do cọc hiển thị `Thời gian lưu trú có ngày Cuối tuần`, không có `Thuê dài hạn` | `docs/reports/uat/evidence/ac-pb-retest-2026-06-11/ac-pb-03_pass_20260611-1951.png` |  | Room `1247` |
| AC-PB-04 | PASS | Căn hộ DV 2 đêm gói day vẫn đi luồng ngắn hạn, copy pháp lý không lease | `docs/reports/uat/evidence/ac-pb-retest-2026-06-11/ac-pb-04_pass_20260611-1952.png` |  | Room `36` |
| AC-PB-05 | PASS | Căn hộ DV 31 ngày (gói tháng) hiển thị cọc dài hạn + copy yêu cầu ký hợp đồng điện tử | `docs/reports/uat/evidence/ac-pb-retest-2026-06-11/ac-pb-05_pass_20260611-1952.png` |  | Room `36` |
| AC-PB-06 | PASS | Homestay 29 đêm = short-term; 30/31 ngày = long-term_lease (copy & cọc dài hạn) | `docs/reports/uat/evidence/ac-pb-retest-2026-06-11/ac-pb-06_pass_29nights_confirm_20260611-1954.png`; `docs/reports/uat/evidence/ac-pb-retest-2026-06-11/ac-pb-06_pass_30nights_confirm_20260611-1955.png` |  | Room `443` |
| AC-PB-07 | PASS | Booking `RM-2026-000617` tạo thành công; preview/UI khớp; email xác nhận do user tự verify (bỏ qua inbox trong retest) | `docs/reports/uat/evidence/ac-pb-retest-2026-06-11/ac-pb-07_fail_email-not-verified_20260611-1954.png` | — | User-confirmed; không retest inbox |

---

## 3) Checklist Theo AC (copy/paste evidence nhanh)

### AC-PB-01
- Result: PASS
- Actual: Ở bước xác nhận, phần "Quy trình pháp lý" ghi điều khoản lưu trú ngắn hạn, không có cụm "Hợp đồng thuê nhà".
- Evidence: `docs/reports/uat/evidence/ac-pb-retest-2026-06-11/ac-pb-01_pass_20260611-1951.png`
- Defect ID: N/A

### AC-PB-02
- Result: PASS
- Actual: Room `1247` hiển thị `2 đêm`, phí thuê phòng `3.200.000` (`1.600.000/đêm x 2`).
- Evidence: `docs/reports/uat/evidence/ac-pb-retest-2026-06-11/ac-pb-02_pass_20260611-1951.png`
- Defect ID: N/A

### AC-PB-03
- Result: PASS
- Actual: Lý do cọc thể hiện cuối tuần, không còn lý do "Thuê dài hạn" cho case nhà nghỉ 2 đêm.
- Evidence: `docs/reports/uat/evidence/ac-pb-retest-2026-06-11/ac-pb-03_pass_20260611-1951.png`
- Defect ID: N/A

### AC-PB-04
- Result: PASS
- Actual: Căn hộ DV room `36`, 2 đêm, gói day -> bước xác nhận vẫn là điều khoản ngắn hạn (voucher flow).
- Evidence: `docs/reports/uat/evidence/ac-pb-retest-2026-06-11/ac-pb-04_pass_20260611-1952.png`
- Defect ID: N/A

### AC-PB-05
- Result: PASS
- Actual: Căn hộ DV room `36`, 31 ngày/gói tháng -> bước xác nhận hiển thị luồng dài hạn và yêu cầu ký hợp đồng điện tử.
- Evidence: `docs/reports/uat/evidence/ac-pb-retest-2026-06-11/ac-pb-05_pass_20260611-1952.png`
- Defect ID: N/A

### AC-PB-06
- Result: PASS
- Actual: Homestay room `443`: 29 đêm cho luồng short-term; 30/31 ngày cho luồng long-term_lease.
- Evidence: `docs/reports/uat/evidence/ac-pb-retest-2026-06-11/ac-pb-06_pass_29nights_confirm_20260611-1954.png`; `docs/reports/uat/evidence/ac-pb-retest-2026-06-11/ac-pb-06_pass_30nights_confirm_20260611-1955.png`
- Defect ID: N/A

### AC-PB-07
- Result: PASS (user-confirmed)
- Actual: Booking `RM-2026-000617` tạo thành công trên UI; mã booking + trạng thái email khớp preview. Phần nhận email thực tế do user đã xác nhận ngoài phiên — bỏ qua retest inbox.
- Evidence: `docs/reports/uat/evidence/ac-pb-retest-2026-06-11/ac-pb-07_fail_email-not-verified_20260611-1954.png` (UI booking-success)
- Defect ID: N/A

---

## 4) Defect Log (nếu có)

| Defect ID | Severity | Summary | AC Link | Status |
|---|---|---|---|---|
| — | — | Không có defect mở | — | — |

---

## 5) Sign-off

- **Gate Rule:** 7/7 PASS (AC-PB-07: user xác nhận email ngoài phiên retest).
- **Final Recommendation:** **CONDITIONAL GO** — P0 stay-classification đạt; release slice Public Booking có thể tiếp tục với ghi chú AC-PB-07 verified by user.
- **Tester Signature:** AI Agent (EU-GUEST mode)
- **Reviewed By:** User (AC-PB-07 confirmed)

