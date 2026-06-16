# Test Case Specification: Chat Realtime Partner-EndUser

## Document Information
- **Testcase ID:** TC-CHAT-RT
- **Related Plan:** global-chat-partner-enduser
- **Status:** Ready for QC

## Scope
- In-scope: API chat Partner/Stay, authz conversation, bootstrap conversation khi tạo booking, realtime qua private channel `conversation.{id}`.
- Out-of-scope: gửi file/ảnh, gọi video, chatbot AI.

## Preconditions
- Môi trường bật Laravel API + Queue + Soketi.
- Account test:
  - Partner: `partner@gmail.com` / `123456a!`
  - EndUser: `user@gmail.com` / `123456a!`
- Đã có ít nhất 1 booking giữa guest và partner.

## Test Cases
| TC ID | Requirement Ref | Screen/Module | Scenario | Steps | Test Data | Expected Result | Priority |
|------|------------------|---------------|----------|-------|-----------|-----------------|----------|
| TC-CHAT-001 | FR-CHAT-01 | Partner Chat | Partner gửi/nhận tin | Đăng nhập partner → `/partner/chat` → chọn hội thoại → gửi tin | partner token | Tin hiển thị ngay, lưu DB | High |
| TC-CHAT-002 | FR-CHAT-01 | Stay Chat | EndUser gửi/nhận tin | Đăng nhập stay → `/bks-stay/chat` → gửi tin | user token | Tin hiển thị ngay, lưu DB | High |
| TC-CHAT-003 | FR-CHAT-02 | Partner + Stay | Hai chiều realtime | Mở 2 trình duyệt partner/stay, gửi qua lại 5 tin | 2 account | Cả 2 phía nhận realtime không refresh | High |
| TC-CHAT-004 | FR-CHAT-03 | API Stay | Chặn IDOR đọc tin | User A gọi `GET /api/v1/stay/chat/{id}` của user B | token user A | HTTP 403 | High |
| TC-CHAT-005 | FR-CHAT-03 | API Partner | Chặn IDOR gửi tin | Partner A gọi `POST /api/v1/partner/chat` với conversation không thuộc mình | token partner A | HTTP 403, không lưu DB | High |
| TC-CHAT-006 | FR-CHAT-04 | Booking flow | Bootstrap conversation | Tạo booking mới guest-partner | booking mới | Conversation global được tạo/reuse đúng cặp | High |
| TC-CHAT-007 | FR-CHAT-05 | Stay Support | Điều hướng chat | Vào `/bks-stay/support` → bấm `Nhắn chủ nhà` | - | Mở `/bks-stay/chat` | Medium |
| TC-CHAT-008 | FR-CHAT-05 | Stay Booking Detail | Điều hướng chat khẩn cấp | Vào booking detail → bấm `Nhắn chủ nhà` | - | Mở `/bks-stay/chat` | Medium |
| TC-CHAT-009 | FR-CHAT-06 | API | Validation rỗng | POST chat với `content` rỗng/khoảng trắng | - | HTTP 422 | Medium |
| TC-CHAT-010 | FR-CHAT-07 | Read status | Đánh dấu đã đọc | User mở conversation có tin chưa đọc | unread messages | `unread_count` giảm, `is_read=true` sau khi mở | Medium |

## Traceability Matrix
| Requirement | Covered By | Status |
|------------|------------|--------|
| FR-CHAT-01 Partner/Stay chat 2 chiều | TC-CHAT-001,002,003 | Covered |
| FR-CHAT-02 Realtime <500ms (manual) | TC-CHAT-003 | Covered |
| FR-CHAT-03 Authz conversation | TC-CHAT-004,005 | Covered |
| FR-CHAT-04 Bootstrap on booking | TC-CHAT-006 | Covered |
| FR-CHAT-05 UI entry points | TC-CHAT-007,008 | Covered |
| FR-CHAT-06 Validation | TC-CHAT-009 | Covered |
| FR-CHAT-07 Read/unread sync | TC-CHAT-010 | Covered |

## Execution Notes for QC
- Chạy smoke trước: `TC-CHAT-001,002,004,005,006`.
- Automated backend: `php artisan test --filter=ChatApiTest`.
- Nếu realtime fail: kiểm tra `BROADCAST_DRIVER`, Soketi, token JWT tại `/api/v1/broadcasting/auth`.
