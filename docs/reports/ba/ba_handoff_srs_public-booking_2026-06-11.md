# BA Handoff: Cập nhật SRS Public Booking (UAT-004/005)

**Vai:** Senior Business Analyst  
**Nguồn:** [`domain_review_public-booking_uat-004-005_2026-06-11.md`](../domain/domain_review_public-booking_uat-004-005_2026-06-11.md)  
**SRS cập nhật:** [`srs_booking_deposit_and_financial_rules.md`](../../SRC/srs_booking_deposit_and_financial_rules.md) **v2.1**  
**Ngày:** 2026-06-11

---

## Executive Summary

Đã chuyển phán quyết Hospitality Expert (DOM-PB-001 → 006) thành **yêu cầu có thể test** trong SRS v2.1. Phạm vi: **đếm đêm**, **phân loại ngắn/dài hạn**, **nhãn cọc**, **copy pháp lý Public Booking**, và **7 acceptance criteria** cho UAT retest.

**Trạng thái SRS:** Chờ Technical Lead duyệt triển khai; Hospitality đã sign-off domain.

---

## Thay đổi SRS (v2.0 → v2.1)

| Hạng mục | Nội dung |
|----------|----------|
| **REQ-FIN-001** | Làm rõ công thức theo `unit=day` (đêm) vs prorate tháng |
| **REQ-DOC-003** | Copy pháp lý bước xác nhận Public Booking |
| **§3.7 mới** | REQ-STAY-001 → 005 + Glossary + AC-PB-01 → 07 |
| **§4.2** | Liên kết `[start, end)` với đếm đêm |
| **§4.3 mới** | Bảng `property_types` id ↔ slug |
| **Q5–Q7** | FAQ từ câu hỏi EU |

---

## Yêu cầu mới (tóm tắt cho Dev/QA)

| ID | Must? | Mô tả ngắn |
|----|-------|------------|
| REQ-STAY-001 | Must | `resolveStayClassification` — ma trận theo slug + đêm + unit |
| REQ-STAY-002 | Must | Đếm **đêm**; UI "X đêm"; giá × đêm |
| REQ-STAY-003 | Must | Prorate tháng khi chỉ có gói month |
| REQ-STAY-004 | Must | Lý do cọc — cấm "Thuê dài hạn" khi short_term |
| REQ-STAY-005 | Must | Preview = BE = email |
| REQ-DOC-003 | Must | Copy voucher vs lease trên bước xác nhận |

---

## Acceptance Criteria (handoff UAT)

| ID | Kịch bản retest |
|----|-----------------|
| AC-PB-01 | Nhà nghỉ 2 đêm → không lease copy |
| AC-PB-02 | 21→23/06 → 2 đêm, 2 × giá |
| AC-PB-03 | Cọc cuối tuần — lý do đúng |
| AC-PB-04 | Căn hộ DV 2 đêm gói day → voucher |
| AC-PB-05 | Căn hộ DV ≥30 đêm / month → lease |
| AC-PB-06 | Homestay ngưỡng 29 vs 30 đêm |
| AC-PB-07 | Đồng bộ tiền preview / BE / email |

Map 1:1 với **UAT-PB-RET-01 → 05** trong domain review + AC-PB-06/07 bổ sung.

---

## Out of Scope (v2.1)

| Item | Lý do |
|------|-------|
| UAT-ISSUE-003 (m² vs mô tả) | Data quality listing — SRS riêng / ticket data |
| EU-RAW-001, 002, 006, 007 | UX/search/chatbot — không thuộc SRS tài chính v2.1 |
| Implement code | Handoff Engineering — xem mapping file domain review |

---

## Handoff tiếp theo

| Đối tượng | Hành động |
|-----------|-----------|
| **Technical Lead** | Duyệt §3.7; estimate P0 (REQ-STAY-001, 002, 004, DOC-003) |
| **Engineering** | Implement + shared classification FE/BE |
| **UAT** | Retest AC-PB-01 → 07 sau deploy |
| **BA (follow-up)** | Ticket riêng EU-RAW-007 nếu PO ưu tiên |

---

## Traceability chain

```
EU-RAW-004/005
  → UAT-ISSUE-004/005
    → DOM-PB-001..006
      → REQ-STAY-001..005, REQ-DOC-003, AC-PB-01..07
        → (pending) Implementation + UAT retest
```

---

**Sign-off:** Senior Business Analyst  
**Date:** 2026-06-11
