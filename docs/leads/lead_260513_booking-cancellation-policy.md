# Lead: Cơ chế yêu cầu hủy phòng (theo thời gian & trạng thái) — My Bookings & Stay Portal

## Document Information

- **Lead ID:** L260513-booking-cancellation-policy
- **Created:** 2026-05-13
- **Updated:** 2026-05-13 (Round 3 — T6/T7/T8, B7, bảng phí & benchmark)
- **Status:** Clarified — sẵn sàng `stack-analyze` (chỉ còn **số cụ thể**: chu kỳ T7 phút/giờ, ngưỡng đêm dài/ngắn hạn, % phí sau khi research)
- **Next Step:** `stack-analyze` / design: API sync merge, enum status bậc 1 vs bậc cao, bảng metric + DDL gợi ý, bảng phí (sau benchmark OTA).

## Original Input

Thiết kế cơ chế yêu cầu hủy phòng theo **thời gian** và **trạng thái đặt phòng**, áp dụng cho:

- Trang công khai **My Bookings** (`http://localhost:5173/my-bookings`)
- **Stay portal** (BKS Stay — ví dụ `/bks-stay/bookings` và luồng liên quan)

---

## Bối cảnh đã biết (từ codebase / phiên làm việc trước)

| Kênh | Nguồn sự thật đơn | Hủy hiện tại (mô tả ngắn) |
|------|-------------------|---------------------------|
| **My Bookings** | `localStorage` (`publicMyBookings`) — đơn từ đặt phòng công khai | UI: dialog xác nhận, cảnh báo “chỉ trên thiết bị”; rule demo thời gian; **chưa có API hủy** |
| **Stay portal** | API `/api/v1/stay/bookings` (authenticated) | Cần API **cancel-request**, `pending_cancellation`, audit, rule theo loại đơn dài/ngắn hạn |

---

## Clarified Requirements (đã cập nhật sau Round 2)

| Aspect | Clarified Requirement |
|--------|-------------------------|
| **Problem Statement** | Chuẩn hóa yêu cầu hủy: **Partner duyệt** sau khi khách gửi; có trạng thái **pending_cancellation**; **không hủy** khi đã ở; đơn chờ thanh toán / chờ xác nhận do **Partner xem xét**; **bắt buộc lý do hủy**; phí/hoàn tiền **tạm thời không phân biệt theo partner/loại phòng**; rule thời gian theo **đơn dài hạn vs ngắn hạn** (không global đơn thuần theo property). |
| **Target Users** | Khách (Stay + public); **Partner** là bên quyết định hủy sau yêu cầu. |
| **Business Value** | Rõ trách nhiệm duyệt; audit; giảm hiểu nhầm với đơn chưa xác nhận (cho phép hủy khi partner chưa xác nhận — xem B6). |
| **Success Metrics** | **B7:** (1) Thời gian Partner xử lý yêu cầu hủy (SLA — đo từ DB). (2) % yêu cầu **không bị treo** (không kẹt `pending_cancellation` quá ngưỡng). (3) Xu hướng **giảm cuộc gọi hotline** liên quan hủy — benchmark **tham khảo ông lớn** (OTA) + dữ liệu nội bộ. |
| **Technical Context** | **Nguồn chân lý:** ưu tiên **tích hợp ngoài**; DB + audit + metric. **T6:** [ĐÃ BÃI BỎ] Cơ chế đồng bộ local → server. **T7:** Rate limit dạng **chu kỳ** (cooldown) giữa các lần gửi lại — **tham số phút/giờ cấu hình được** (default do design đề xuất). **T8:** **Hủy trực tiếp** (`cancel`) ở **trạng thái bậc thấp (đầu)**; **`cancel-request`** ở **trạng thái cao hơn** (đã xác nhận partner trở đi). Idempotency: khuyến nghị giữ. |
| **Key Features** | 1) Hai lộ API/UI: **cancel** vs **cancel-request** theo bậc trạng thái. 2) `pending_cancellation` + Partner duyệt. 3) [ĐÃ BÃI BỎ] Đồng bộ **publicMyBookings** sau login. 4) Cooldown gửi lại. 5) Metric B7 + bảng phí **theo thời gian** (ngắn/dài) sau **benchmark OTA**. |
| **Out of Scope (giữ)** | Tự động hoàn tiền cổng thanh toán cho đến khi có nguồn tiền & hợp đồng đối soát rõ. |
| **Assumptions** | “Trạng thái thứ nhất” = tập status chưa cần Partner xác nhận (định nghĩa chính xác trong analyze); “cao hơn” = đã confirm + các bậc sau (map enum). |
| **Open Questions** | Chỉ còn **số học:** (1) Chu kỳ T7 cụ thể (vd. 30p / 60p / 24h). (2) Ngưỡng **đêm** phân loại ngắn vs dài hạn. (3) Bảng % phí sau khi research OTA + pháp lý VN. |

---

## Chính sách đã chốt (tóm tắt điều hướng implement)

1. **Duyệt hủy:** **Partner** (sau khi khách gửi yêu cầu hủy).
2. **Phí & hoàn tiền:** Tạm thời **không** tách rule theo partner hay loại phòng; bảng % theo mốc thời gian (ngắn/dài) — **benchmark OTA** + pháp lý VN ở bước analyze.
3. **Đã nhận phòng / đang ở:** **Không** được hủy (theo nghĩa “hủy đặt” — case trả phòng sớm/no-show xử lý riêng nếu sau này có).
4. **Chờ thanh toán / chờ xác nhận:** **Partner xem xét** (không tự động duyệt chỉ bằng rule thời gian nếu chưa có quy tắc khác).
5. **Lý do hủy:** **Bắt buộc** (danh sách + ghi chú — chi tiết UI ở analyze).
6. **Partner chưa xác nhận:** **Có thể hủy** (nghiệp vụ: trước khi partner confirm, khách được phép hủy — áp dụng cho cả kịch bản đơn đã lên server; My Bookings local vẫn cần copy rõ phạm vi cho đến khi sync).
7. **API:** **`cancel-request`**; trạng thái trung gian **`pending_cancellation`**; khi partner đã xác nhận **trở đi** áp dụng luồng yêu cầu hủy + duyệt partner (cần map chính xác status code trong DB).
8. **Audit:** **Có** — ghi actor, timestamp, (khuyến nghị) version policy / loại đơn dài-ngắn hạn áp dụng.
9. **Rule thời gian:** Theo **đơn dài hạn vs ngắn hạn** (hai tập rule), không chỉ một global đơn giản theo property.
10. **Tích hợp ngoài:** **Đề xuất** làm nguồn chân lý / đồng bộ nếu có thể (calendar/OTA/etc.) — fallback DB nội bộ.
11. [ĐÃ BÃI BỎ] **T6 — Đồng bộ local:** Cơ chế đồng bộ đơn local lên server sau login đã bị xóa bỏ hoàn toàn.
12. **T7 — Chu kỳ:** Giới hạn **gửi lại** cancel-request theo **chu kỳ làm mát** (cooldown) trên cùng booking — thời lượng **cấu hình** (số cụ thể chốt ở design).
13. **T8 — Hai kiểu thao tác:** Trạng thái **bậc đầu / thấp** → **hủy trực tiếp** (`cancel`); trạng thái **cao hơn** (đã xác nhận…) → **`cancel-request`** + `pending_cancellation`.
14. **B7 — Metric:** SLA Partner; % không treo; hotline — đo **DB** + benchmark **ông lớn** (OTA).
15. **Phí–hoàn tiền:** Bảng theo **mốc thời gian** tách **ngắn hạn / dài hạn**; nội dung % **research tham khảo OTA** + điều chỉnh pháp lý VN (không ghi cứng % trong lead).

---

## Clarification Q&A

### Business Questions

| # | Question | Answer |
|---|----------|--------|
| B1 | Ai là người **phê duyệt** hủy sau khi khách gửi yêu cầu? | **Partner** |
| B2 | Chính sách **hoàn tiền / phí hủy** theo mốc thời gian? Có khác theo partner/loại phòng? | **Tạm thời không khác** theo partner/loại phòng; **bảng % theo mốc thời gian** (ngắn/dài) xây sau **benchmark OTA** + pháp lý VN — số cụ thể ở `stack-analyze`. |
| B3 | Sau khi đã **ở** / trong kỳ lưu trú? | **Không được hủy** (hủy đặt). |
| B4 | Đơn **chờ thanh toán / chờ xác nhận**? | **Partner xem xét**. |
| B5 | **Lý do hủy** bắt buộc? | **Có** |
| B6 | My Bookings / không tài khoản & roadmap? | **Nếu partner chưa xác nhận thì có thể hủy** (điều kiện nghiệp vụ); vẫn cần phân tách **local-only** vs đơn server khi implement. |
| B7 | Metric thành công? | **(1)** SLA Partner xử lý yêu cầu (**bao lâu** — đo từ DB). **(2)** **%** yêu cầu **không bị treo** (không kẹt `pending_c` quá ngưỡng). **(3)** **Giảm hotline** liên quan hủy — đối chiếu **DB** + **tham khảo ông lớn** (OTA / best practice). |

### Technical Questions

| # | Question | Answer |
|---|----------|--------|
| T1 | **Nguồn chân lý** booking Stay? | **Đề xuất tích hợp ngoài nếu có thể** |
| T2 | API **cancel-request** vs PATCH status? Idempotency? | **`cancel-request`**; áp dụng với luồng trạng thái **khi partner đã xác nhận trở lên** (cần chi tiết hóa trong thiết kế). **Idempotency:** khuyến nghị có (key / request_id) — *chưa xác nhận trong câu trả lời*. |
| T3 | Trạng thái trung gian? | **`pending_cancellation`** (và phân nhánh sau khi partner xử lý — tên status cuối TBD). |
| T4 | **Audit log**? | **Có** |
| T5 | Rule **global** vs **property**? | Theo **đơn dài hạn và ngắn hạn** (hai khối rule thời gian), không mô tả “theo từng property” trong câu trả lời. |
| T6 | [ĐÃ BÃI BỎ] Merge **My Bookings → server** khi login Stay? | Đã bãi bỏ toàn bộ cơ chế đồng bộ đơn local lên server. |
| T7 | Rate limit / chống spam hủy? | **Chu kỳ làm mát** (cooldown): chỉ được **gửi lại** sau một **khoảng thời gian** cấu hình (vd. N phút / N giờ trên cùng booking) — **số N chốt trong design** (có thể `.env`). |
| T8 | Tách quyền **read** vs **cancel** theo role? | Không tách theo “role” mà theo **bậc trạng thái đơn**: **trạng thái thứ nhất / thấp** → thao tác **`cancel`** (hủy trực tiếp trong phạm vi cho phép); **trạng thái cao hơn** → **`cancel-request`** (+ lý do, `pending_cancellation`). **Read:** chủ đơn / Partner (scope property) / Admin — chi tiết policy ở analyze. |

---

## Ma trận trạng thái (bản làm việc — align DB khi implement)

| Tình huống | API / thao tác khách (T8) | Partner |
|------------|---------------------------|--------|
| Trạng thái **bậc thấp** (chưa xác nhận / “thứ nhất”) | **`cancel`** — hủy trực tiếp (theo B6) | Theo rule có thể đóng ngay / không cần `pending_c` |
| Partner **đã** xác nhận trở đi | **`cancel-request`** → `pending_cancellation` | **Duyệt** |
| Chờ thanh toán / chờ xác nhận | Theo enum: có thể `cancel` hoặc request — **Partner xem xét** (B4) | **Xem xét** |
| Đang ở / đã check-in | **Không** | — |
| Đã `pending_cancellation` | Không gửi trùng (idempotency + **T7 cooldown**) | Một phiên xử lý |

---

## Ma trận thời gian (bản làm việc)

- **Hai tập rule độc lập:** **Ngắn hạn** vs **dài hạn** (ngưỡng số đêm — **Số học còn chốt**).
- **Nội dung %:** không ghi trong lead; lấy từ **benchmark OTA** + điều chỉnh VN (mục **Bảng phí–hoàn tiền**).

---

## Hai kênh FE (định hướng)

| Kênh | Định hướng |
|------|------------|
| **My Bookings** | Hoạt động cục bộ độc lập trên trình duyệt của thiết bị (không đồng bộ lên server); trạng thái thấp → `cancel` cục bộ |
| **Stay portal** | Trạng thái thấp → **`cancel`**; trạng thái cao → **`cancel-request`** + lý do + `pending_c` + **cooldown T7** |

---

## Luồng tổng quát (ASCII)

```
[Khách] → đang ở? → từ chối
        → trạng thái bậc thấp? → cancel (instant / theo rule)
        → trạng thái cao? → cancel-request + lý do → pending_c (kiểm tra T7 cooldown)
[Đăng nhập Stay] → (Không đồng bộ local, hoạt động riêng biệt)
[Partner] → duyệt (bậc cao) → cancelled | từ chối yêu cầu
[Audit] + [Metric B7]
[External] → nếu có: đồng bộ sau khi nội bộ chốt
```

---

## Risks Identified

| Risk | Impact | Mitigation |
|------|--------|------------|
| Khách tưởng hủy web = chốt với KS | Cao | Email/push + trạng thái rõ “chờ Partner” |
| pending_c không được xử lý | Trung bình | SLA + nhắc Partner + escalation BKS (optional) |
| Tích hợp ngoài lệch trạng thái | Cao | Transactional outbox / retry; soát audit |

---

## Số học còn chốt (giao design / `.env`)

| Hạng mục | Ghi chú |
|----------|--------|
| **T7 — N** | Chu kỳ cooldown giữa hai lần **gửi lại** `cancel-request` trên **cùng booking** (vd. 30 / 60 phút hoặc 24h). |
| **Ngưỡng đêm** | Phân loại đơn **ngắn hạn** vs **dài hạn** (vd. ≤ 30 đêm vs > 30 — số đêm do nghiệp vụ chốt). |
| **% phí / hoàn** | Điền sau **benchmark OTA** + pháp lý VN (lead không ghi cứng %). |

---

## Cơ chế đồng bộ T6 (local → server sau login Stay) — [ĐÃ BÃI BỎ]

> [!IMPORTANT]
> Cơ chế này đã được bãi bỏ toàn bộ khỏi dự án. Luồng đặt phòng của khách luôn được lưu trực tiếp trên server từ đầu, không cần đồng bộ offline-to-online.

---

## T7 — Rate limit (chu kỳ gửi lại)

- Áp dụng chủ yếu cho **`cancel-request`** (trạng thái cao).
- **Mô hình:** *cooldown* — sau mỗi lần gửi thành công (hoặc cả khi 4xx tùy policy), khóa gửi tiếp trên cùng `booking_id` trong **N** phút/giờ.
- **Cấu hình:** `CANCEL_REQUEST_COOLDOWN_SECONDS` (hoặc tương đương) — **N** chốt ở design; có thể khác nhau giữa môi trường dev/prod.

---

## T8 — `cancel` vs `cancel-request` (theo bậc trạng thái)

| Nhóm trạng thái (tên logic) | Thao tác khách | Ghi chú |
|-----------------------------|----------------|--------|
| **Bậc thấp / “thứ nhất”** (vd. chưa xác nhận partner, hoặc draft theo enum DB) | **`cancel`** — hủy trực tiếp trong phạm vi đã chốt (B6) | Có thể `DELETE`/`POST …/cancel` idempotent; không cần `pending_c` nếu policy cho phép kết thúc ngay |
| **Bậc cao hơn** (đã xác nhận partner trở đi) | **`cancel-request`** + lý do bắt buộc → `pending_cancellation` | Partner duyệt; T7 áp dụng |
| **Read** | Chủ đơn / Partner (property) / Admin | Quyền xem chi tiết tách khỏi quyền gọi `cancel` / `cancel-request` — chi tiết RBAC ở analyze |

---

## B7 — Metric thành công (DB + tham khảo ông lớn)

| Metric | Cách đo (gợi ý) | Benchmark |
|--------|-----------------|------------|
| SLA Partner | `resolved_at - requested_at` trên bảng **cancellation_requests** (hoặc audit) — p50/p90 | So sánh SLA công bố của OTA / industry report |
| % không treo | % yêu cầu có `pending_c` < **T_max** (vd. 24h/48h) hoặc đã terminal | Dashboard nội bộ |
| Hotline | Đếm ticket/call có tag “hủy” — trước/sau go-live | Mục tiêu giảm % theo quý |

**DDL gợi ý (concept):** bảng `booking_cancellation_requests` (`id`, `booking_id`, `user_id`, `reason_code`, `reason_text`, `status`, `requested_at`, `resolved_at`, `resolved_by_partner_user_id`, …) + bảng/append-only **audit**.

---

## Bảng phí–hoàn tiền (ngắn hạn / dài hạn — benchmark OTA)

- **Nguồn research:** Chính sách hủy công khai của các nền tảng OTA lớn (ví dụ Booking.com, Agoda, Expedia) — **chỉ tham khảo cấu trúc mốc thời gian**, không copy nguyên văn nếu chưa có bản quyền / phù hợp pháp lý VN.
- **Hai hàng bảng:** Cùng mốc **T-7d, T-3d, T-24h…** nhưng **% hoàn / phí** khác nhau giữa **ngắn hạn** và **dài hạn** (đã chốt hướng Round 2–3).
- **Deliverable analyze:** Một ma trận số + mapping sang `policy_version` lưu trong audit khi áp dụng.

---

## Giải thích (glossary — Round 1–2; đáp án chính thức xem bảng Q&A Round 3)

Các mục T6/T7/T8/B7/bảng phí đã được **chốt hướng** ở Round 3; phần mô tả dài dưới đây có thể dùng khi onboarding stakeholder mới.

### T6 — Merge đơn local khi login Stay / trùng ID

**Vấn đề:** Trang **My Bookings** lưu đơn trên **máy khách** (trình duyệt). Cổng **BKS Stay** lưu đơn trên **server**. Cùng một người: trước đặt phòng **chưa đăng nhập**, sau đó **đăng nhập** Stay.

**Round 3:** Cần **cơ chế đồng bộ** — xem mục **Cơ chế đồng bộ T6** phía trên.

---

### T7 — Rate limit (giới hạn tần suất)

**Round 3:** Dùng **chu kỳ làm mát** (cooldown) giữa các lần **gửi lại** — số **N** cấu hình được.

---

### T8 — Phân quyền read vs cancel

**Round 3:** Phân biệt thao tác **`cancel`** (trạng thái thấp) vs **`cancel-request`** (trạng thái cao); **read** theo chủ đơn / Partner / Admin — xem mục **T8** phía trên.

---

### B7 — Metric thành công

**Round 3:** SLA Partner; % không treo; hotline — DB + benchmark OTA — xem mục **B7** phía trên.

---

### Bảng phí–hoàn tiền theo thời gian (ngắn hạn / dài hạn)

**Round 3:** Theo mốc thời gian, **research OTA** + pháp lý VN — xem mục **Bảng phí–hoàn tiền** phía trên.

---

## Next Steps

- [ ] Chạy **stack-analyze** / design: enum **bậc thấp vs cao**, API **`cancel`** vs **`cancel-request`**, schema **cancellation_requests** + audit, **T7** default N, **ngưỡng đêm** ngắn/dài. (Đã hoàn thành và bãi bỏ sync-local).
- [ ] Research OTA → điền **bảng %** + `policy_version`.
- [ ] FE: Stay History + My Bookings (sync UI + CTA theo trạng thái + cooldown client-side mirror).

---

## Appendix — Discovery Session Log

- **Round 1:** Khởi tạo lead + bảng câu hỏi.
- **Round 2:** Stakeholder trả lời B1–B5, B6 (một phần), T1–T5, T3–T4; bổ sung chính sách “đã ở không hủy”, “partner xem xét chờ TT/xác nhận”, “dài hạn/ngắn hạn”, `pending_cancellation`, `audit`, `cancel-request`.
- **Round 3:** T6 đã bãi bỏ (cơ chế đồng bộ bị hủy); T7 chu kỳ cooldown (N cấu hình); T8 `cancel` vs `cancel-request` theo bậc trạng thái; B7 3 metric + DB + benchmark OTA; bảng phí theo thời gian + tham khảo OTA.
