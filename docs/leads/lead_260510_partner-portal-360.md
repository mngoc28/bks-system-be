# Lead: Partner Portal 360° Review & Cải tiến Dashboard / Bookings / Calendar

## Document Information
- **Lead ID:** L260510-partner-portal-360
- **Created:** 2026-05-10
- **Status:** Clarified (đủ độ rõ để chuyển sang `stack-analyze`)
- **Next Step:** Chạy `stack-analyze` để soạn SRS/PRD cho 3 module Dashboard + Bookings + Calendar (segment Serviced Apartment + Homestay), sau đó `stack-design` cho phần realtime WebSocket
- **Owner brainstorm:** (đặt tên người yêu cầu nếu có)
- **Skill applied:** `.cursor/skills/stack-brainstorm/SKILL.md`

## Original Input
> "Áp dụng skill stack-brainstorm cho dự án bks-system: chỉ discovery, không sửa code. Bắt đầu brainstorm cho role partner của tôi"

User định hướng kèm sau 2 vòng câu hỏi: brainstorm 360° toàn Partner Portal, áp dụng cho cả 4 segment, ở giai đoạn **shaping** (đã có ý tưởng mơ hồ, cần làm rõ thành requirements để chuyển PRD), có tham chiếu đối thủ.

---

## Clarified Requirements

### Problem Statement
Partner Portal hiện tại đã có nhiều module (Dashboard, Properties, Calendar, Bookings, Stay Services, Chat, Contracts, Services, Amenities, Finance, Reports, Maintenances, Profile…) nhưng **chất lượng không đồng đều**. Một số module quan trọng đối với vận hành thực tế của Partner đang thiếu trải nghiệm tối ưu hoặc còn dùng mock data:

- **Bookings**: thiếu noti realtime khi có đơn mới → Partner xác nhận chậm, làm tăng `time-to-confirm` và rủi ro mất khách.
- **Calendar**: khó nhìn tổng quan đa-tòa/đa-phòng cùng lúc, chưa có cảnh báo overbooking; Partner sở hữu nhiều property phải chuyển cảnh nhiều lần.
- **Dashboard**: chưa thể hiện rõ KPI mà Partner thực sự cần để vận hành (Net Revenue sau commission, occupancy, time-to-confirm, ADR theo segment).
- **Hợp đồng dài hạn** cho căn hộ dịch vụ (Serviced Apartment) chưa flow chốt, gia hạn, thanh lý, tính utility fee gọn gàng — đây là segment ưu tiên trong lead này.

### Target Users
- **Primary:** Partner SME của BKS, sở hữu **1–nhiều property** (apartment property hoặc cụm homestay), tự vận hành (không có nhân viên hoặc rất ít), tiếp cận portal qua **trình duyệt máy tính + mobile web**.
- **Secondary:**
  - End User (khách thuê) — gián tiếp hưởng lợi từ tốc độ confirm và độ chính xác của lịch.
  - Admin BKS — gián tiếp hưởng lợi vì giảm support ticket.

### Business Context
- **Business Value:**
  - Tăng **GMV** tổng booking từ Partner.
  - Tăng **Occupancy %** thực tế (do Partner xác nhận nhanh + lịch đúng).
  - Giảm **time-to-confirm** booking → tăng tỉ lệ chốt đơn so với đối thủ.
  - Tăng **Partner retention** (giảm churn, tăng NPS Partner).
- **Success Metrics (đề xuất gắn KPI cụ thể trong PRD):**
  - Time-to-confirm trung bình giảm từ X phút → ≤ 5 phút (cần đo baseline hiện tại).
  - Tỉ lệ booking xác nhận trong 15 phút đầu ≥ 80%.
  - Số overbooking / month ≈ 0.
  - Occupancy % theo segment Apartment + Homestay tăng ≥ 10% sau 1 quý áp dụng.
  - Partner active sau 30 ngày (D30 retention) ≥ 70%.
- **Mô hình thương mại đã chốt:** **Commission-only** theo từng booking → Dashboard phải hiển thị Net Revenue (Gross − Commission − Refund).
- **Constraints:**
  - Web-only (không build mobile native trong phase này).
  - Scale nhỏ: < 100 Partner, < 1k room, < 10k booking/tháng.
  - Không brainstorm Channel Manager, Mobile native, AI, Finance/Payment, Chat trong lần này.
  - Phải tuân theo `karpathy-behavioral-guidelines.mdc` và `laravel-implementation-standards.mdc` (PSR-12, Service/Repository, migration kèm theo schema change).

### Technical Context
- **Stack hiện tại:** Laravel 10 (BE) + React/Vite (FE).
- **Module Partner hiện có:** Dashboard, Notifications, News, Properties, Calendar, Bookings, StayServices, Chat, Contracts, Services, Amenities, Finance (mock data), Reports, Maintenances, Profile, PriceRules, RoomDetail.
- **Realtime:** chốt **WebSocket** (Laravel Reverb hoặc Pusher) — phục vụ noti booking + sync calendar.
- **Multi-user trong Partner:** **Single owner**, không cần RBAC nội bộ trong lần này.
- **Tích hợp ngoài:** không có (Channel Manager, payment gateway đều out-of-scope).
- **Benchmark UX:** Booking.com Extranet và **MRB (mrb.co.jp)** — đặc biệt cho phần inbox booking + lịch khả dụng + báo cáo.

### Key Features (đề xuất gom theo module, MoSCoW sơ bộ)

#### A. Dashboard (segment Apartment + Homestay là chuẩn baseline)
1. **(Must)** KPI cards: Today's bookings, Time-to-confirm avg, Occupancy hôm nay, GMV tháng, **Net Revenue** sau commission.
2. **(Must)** Bảng "Booking chờ duyệt" có CTA xác nhận trực tiếp (không phải vào Bookings page).
3. **(Should)** Biểu đồ Occupancy + GMV 30 ngày + so sánh tháng trước.
4. **(Should)** Cảnh báo: overbooking, room hết tồn, contract sắp hết hạn (apartment).
5. **(Could)** Mini-leaderboard giữa các property (so sánh chéo trong 1 Partner đa-tòa).

#### B. Bookings
1. **(Must)** Noti realtime (toast + badge + sound) khi có booking mới — qua WebSocket (xem mục "Realtime").
2. **(Must)** "Quick confirm" 1 click với time-target SLA (đếm ngược).
3. **(Must)** Bộ lọc nâng cao: theo property, theo segment, theo status, theo nguồn booking, theo khoảng ngày.
4. **(Must)** Action mở rộng: hủy với lý do, đánh dấu no-show, gán phòng (assign room), refund (chỉ cờ trạng thái — payment thật out-of-scope).
5. **(Should)** Booking timeline (audit log) cho mỗi đơn.
6. **(Should)** Bulk action: confirm/cancel nhiều đơn cùng lúc.
7. **(Could)** Template tin nhắn xác nhận tự động cho khách qua email.

#### C. Calendar
1. **(Must)** **Multi-property switcher** + view "All my properties" gộp lịch (giải pain "Multi-property").
2. **(Must)** Drag-and-drop để gia hạn/đổi phòng booking, có check conflict realtime.
3. **(Must)** Cảnh báo overbooking trực tiếp trên ô lịch.
4. **(Must)** **Block lịch** (maintenance, owner-use, off-market) không cần tạo booking giả.
5. **(Should)** View "Tháng" + "Tuần" + "Timeline (Gantt theo phòng)"; mặc định Timeline cho Apartment dài hạn.
6. **(Should)** Hiển thị **contract span** cho Apartment (ô booking dài, có badge "Contract").
7. **(Could)** Kéo-thả từ Booking pending vào Calendar để gán phòng nhanh.

#### D. Contract dài hạn (cross-cut, vì là pain đã chọn cho Apartment)
1. **(Must)** Lifecycle hợp đồng: draft → ký điện tử → đang hiệu lực → gia hạn → thanh lý.
2. **(Must)** Đính utility fee định kỳ (điện, nước, phí quản lý) ↔ liên kết với `UtilityFee` model đã thêm trong git diff.
3. **(Should)** Reminder gia hạn tự động ≥ 30 ngày trước hạn.
4. **(Could)** Sinh hóa đơn tháng tự động cho hợp đồng đang hiệu lực.

#### E. Realtime layer (foundation cho A + B + C)
1. **(Must)** Channel partner-`{partner_id}` cho noti booking mới + booking status change.
2. **(Must)** Channel calendar-`{property_id}` cho cập nhật ô lịch (khi guest đặt từ phía End User).
3. **(Must)** Fallback polling 30s nếu WebSocket disconnect.

### Out of Scope (đã chốt)
- **Channel Manager** đẩy lịch/giá lên Booking.com / Agoda / Traveloka (phase sau).
- **Mobile native app** cho Partner (giữ web responsive).
- **Tích hợp cổng thanh toán** thật (VNPay / MoMo / ZaloPay / payout ngân hàng) — Finance vẫn ở mock cho phase này.
- **AI** (dynamic pricing, chatbot, OCR, AI listing).
- **Chat module** — không cải tiến trong lead này.
- **Multi-user RBAC nội bộ Partner** (mỗi Partner = 1 owner duy nhất).
- **Onboarding flow** mới cho Partner (giữ flow hiện tại).
- **News, Marketing, Maintenance, StayServices, Amenities/Services** không nằm trong scope cải tiến lần này (chỉ giữ nguyên).

---

## Clarification Q&A

### Business Questions
| # | Câu hỏi | Trả lời |
|---|---------|---------|
| B1 | Pain point lớn nhất Partner đang gặp? | Booking ops (xác nhận chậm, không noti realtime); Calendar khó nhìn tổng quan / không sync OTA; Hợp đồng dài hạn rườm rà; Multi-property khó chuyển cảnh |
| B2 | KPI chính cần cải thiện? | Occupancy %, GMV, Time-to-confirm, Partner retention |
| B3 | Đối thủ tham chiếu cụ thể? | Booking.com Extranet + **MRB (mrb.co.jp)** |
| B4 | Segment ưu tiên cho 3 module Dashboard/Bookings/Calendar? | **Serviced Apartment + Homestay** |
| B5 | Module Partner Portal nào cần cải tiến sớm nhất? | Dashboard + Bookings + Calendar |
| B6 | Assumption nào đang bị "đoán"? | (Open) — đề xuất xác minh sau với Partner thật: ngưỡng SLA confirm, hành vi xem Calendar (theo phòng vs theo property), nhu cầu báo cáo trên Dashboard |
| B7 | Mô hình thương mại với Partner? | **Commission-only** → Dashboard cần Net Revenue |
| B8 | Out-of-scope cho lần này? | Channel Manager, Mobile native, Finance/Payment, AI, **Chat** (theo lựa chọn vòng 2) |

### Technical Questions
| # | Câu hỏi | Trả lời |
|---|---------|---------|
| T1 | Channel Manager? | Phase sau, **không** brainstorm lần này |
| T2 | Mobile native? | Web-only |
| T3 | Quy mô 6-12 tháng? | Nhỏ (< 100 Partner, < 1k room, < 10k booking/tháng) |
| T4 | Finance đang mock — xử lý? | Để **phase sau**, không brainstorm thanh toán lần này |
| T5 | Realtime cho noti & calendar? | **WebSocket / Laravel Reverb hoặc Pusher** |
| T6 | Constraint pháp lý hợp đồng số? | (Open) — chưa được trả lời trong phiên này, cần xác nhận trong stack-analyze |
| T7 | AI integration? | Không brainstorm lần này |
| T8 | Multi-user trong Partner? | **Single owner**, không cần RBAC nội bộ |

---

## Assumptions (cần được validate ở `stack-analyze`)
- A1. Partner trung bình sở hữu 1–10 property; "multi-property" được hiểu là **nhiều property/tòa của cùng 1 owner**, không phải nhiều người dùng cùng 1 tài khoản.
- A2. Segment Apartment ≈ Homestay-trên-30-ngày về mặt nghiệp vụ pricing & contract (đã có nền tảng từ `PRICING_RESTRUCTURE_PLAN.md`).
- A3. Partner sẵn sàng dùng web mở liên tục để nhận noti realtime; không yêu cầu push noti khi đóng tab (web push chưa bắt buộc trong scope này).
- A4. Time-to-confirm baseline hiện tại chưa được đo — cần thêm metric tracking trong sprint đầu.
- A5. Net Revenue Dashboard chỉ tính trên **booking** đã hoàn tất check-out; trạng thái pending/cancel KHÔNG cộng vào Net Revenue.
- A6. WebSocket có thể chạy trên Laravel Reverb (self-host) — quyết định Reverb vs Pusher để stack-design chốt theo chi phí và độ phức tạp ops.
- A7. "Cảnh báo overbooking" giả định BE đang có khóa đặt vé (database-level lock) trong `BookingService` — cần verify ở stack-analyze.
- A8. Bộ lọc booking "theo nguồn" chấp nhận giá trị hiện tại (web/portal); khi Channel Manager bật ở phase sau sẽ mở rộng enum.

## Open Questions (cần làm rõ ở `stack-analyze` hoặc làm việc với Partner thật)
- [ ] **OQ1.** Baseline đo time-to-confirm hiện nay là bao nhiêu? Có log để tính lùi không?
- [ ] **OQ2.** Khi Partner sở hữu nhiều property, Dashboard mặc định hiện toàn bộ hay chọn 1 property mặc định (sticky)?
- [ ] **OQ3.** Cảnh báo overbooking nên là **block** (không cho confirm) hay **warn** (vẫn confirm được nhưng yêu cầu xác nhận thêm)?
- [ ] **OQ4.** Block lịch (maintenance/owner-use) có cần lý do bắt buộc và lưu lịch sử để Admin BKS audit không?
- [ ] **OQ5.** Hợp đồng dài hạn cần loại chữ ký số nào (CA, OTP-based, signature image)? Có vướng pháp lý lưu trữ ≥10 năm không? (T6 chưa được trả lời)
- [ ] **OQ6.** Drag-and-drop calendar có cần thông báo cho khách thuê khi đổi phòng không? Mức gợi ý: cần email confirm.
- [ ] **OQ7.** Net Revenue trên Dashboard có cần chia tách theo **segment** (Apartment vs Homestay) hay gộp tổng?
- [ ] **OQ8.** SLA mục tiêu cho time-to-confirm: ≤5 phút (như assumption ở Success Metrics) — có chấp nhận không?
- [ ] **OQ9.** Reverb vs Pusher: scale nhỏ liệu có đáng tự host Reverb không? Cần TLA so sánh ops cost.
- [ ] **OQ10.** Bulk confirm/cancel có giới hạn bao nhiêu booking/lần để tránh khóa DB (lock contention)?

## Risks Identified
| Risk | Tác động | Khả năng | Mitigation |
|------|----------|----------|-----------|
| R1. WebSocket overkill cho scale nhỏ → ops phức tạp | M | M | Đánh giá Reverb (rẻ, self-host) trước; có fallback polling sẵn |
| R2. "Multi-property view" có thể chậm khi gộp lịch nhiều property | M | M | Lazy load theo viewport; cap số property mặc định, có pagination/virtualization |
| R3. Bộ KPI Dashboard mới có thể "chưa đo lường được" do thiếu log lịch sử | H | H | Sprint 0: bổ sung event log + metric tracking trước khi build Dashboard |
| R4. Drag-and-drop dễ tạo bug logic ngày (off-by-one timezone) | H | M | Dùng date-fns + UTC normalize, viết test ngày biên (DST, đầu/cuối tháng) |
| R5. Quick confirm 1-click có thể gây xác nhận nhầm | H | M | Có Undo trong 30s; log audit; quyền confirm gắn theo property |
| R6. Hợp đồng dài hạn vượt scope → phình | M | H | Chỉ làm subset Apartment trong lead này; tách sub-lead riêng nếu phình to |
| R7. Net Revenue tính sai do commission rule chưa thống nhất | H | M | Lock công thức commission ở stack-analyze; đưa unit test |
| R8. Single-owner assumption (A1) sai với một số Partner thật | M | L | Thiết kế DB sẵn sàng cho user table riêng (đa user trong Partner) trong tương lai |

---

## Next Steps
- [ ] **NS1.** Chạy `stack-analyze` để tạo SRS dạng `srs_partner-portal-360.md` cho 3 module (Dashboard + Bookings + Calendar) cộng layer Realtime + lifecycle Contract dài hạn cho Apartment.
- [ ] **NS2.** Trước hoặc song song NS1: phỏng vấn 3–5 Partner thật để validate Open Questions OQ1, OQ2, OQ3, OQ8, OQ10.
- [ ] **NS3.** Sau SRS, chạy `stack-design` để chốt kiến trúc realtime (Reverb vs Pusher), schema event log, schema utility fee mở rộng.
- [ ] **NS4.** Lên `stack-plan` chia sprint: Sprint 0 (metric tracking + event log), Sprint 1 (Bookings noti realtime + Quick confirm), Sprint 2 (Calendar multi-property + drag-drop), Sprint 3 (Dashboard KPI + Net Revenue), Sprint 4 (Contract dài hạn cho Apartment).
- [ ] **NS5.** Trao đổi T6 (pháp lý hợp đồng số) với stakeholder (Legal/BA) trước Sprint 4.

## Appendix

### Discovery Session Log
- **Round 1 (Khoanh vùng scope):** Xác định hướng = brainstorm 360°, segment = cả 4, stage = shaping, input = có tham chiếu đối thủ.
- **Round 2 (BA + TLA cốt lõi):** Khóa Top pain (booking ops + calendar sync + contract dài hạn + multi-property), KPI (occupancy + GMV + time-to-confirm + retention), benchmark (Booking.com Extranet + MRB.co.jp), module ưu tiên (Dashboard + Bookings + Calendar), tech (web-only, scale nhỏ, không Channel Manager / Mobile native / Finance / AI).
- **Round 3 (Cuối):** Khóa segment ưu tiên (Apartment + Homestay), realtime = WebSocket, multi-user = single owner, commercial = commission-only, out-of-scope thêm Chat.

### Quan sát code hiện tại (đã đọc, không sửa)
- BE: `app/Http/Controllers/Partner/PartnerBookingController.php` — đã có index/checkIn/checkOut → cần thêm SSE/WebSocket event publishing và quick-confirm endpoint chuyên biệt.
- BE: `app/Services/BookingService.php` đang được sửa song song trong working tree → cần đồng bộ thiết kế.
- FE: `src/pages/Partner/Bookings.tsx` đã có pagination + status filter + dialog detail → mở rộng được noti realtime + quick confirm.
- FE: `src/pages/Partner/Calendar.tsx` dùng FullCalendar (dayGrid + timeGrid + interaction) → đã có hạ tầng drag-drop, cần mở multi-property + overbooking warning.
- FE: `src/pages/Partner/Finance.tsx` dùng `mockData` (xác nhận lại đây là tech-debt được chuyển phase sau theo lựa chọn của user).
- FE: `src/pages/Partner/components/Sidebar.tsx` — sidebar đã có đủ menu, không cần thêm route trong lead này.
- Đã có migration `2026_05_05_141930_create_utility_fees_table.php` + model `UtilityFee` → khớp với feature D2 utility fee định kỳ cho Apartment.

### Tham chiếu liên quan
- `business-script/bks_srs_overview.md` (vai trò Partner trong hệ thống tổng quan).
- `business-script/PRICING_RESTRUCTURE_PLAN.md` (quy tắc giá ngắn / dài hạn / linh hoạt).
- `business-script/E2E_BOOKING_PARTNER_USER_SCRIPT.md` (luồng booking E2E chuẩn).
- `.cursor/skills/stack-personas/business-analyst.md`, `.cursor/skills/stack-personas/technical-lead-architect.md`.
- `.cursor/rules/karpathy-behavioral-guidelines.mdc`, `.cursor/rules/laravel-implementation-standards.mdc`, `.cursor/rules/php-laravel-rule.mdc`.

