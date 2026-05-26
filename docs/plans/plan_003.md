# Plan: Tối ưu hiển thị thông tin nổi bật trên landing page

## 1. Thông tin kế hoạch
- **Mã plan:** PLAN-LP-003
- **SRS đầu vào:** [docs/SRC/srs_landing_page_prominence.md](../SRC/srs_landing_page_prominence.md)
- **Design đầu vào:** [docs/designs/design_003.md](../designs/design_003.md)
- **Scope:** FE landing page public `/`
- **Mục tiêu:** Tăng độ nổi bật cho block mix city highlights + featured rooms, giữ search-first, không đổi route hay backend contract.

## 1.1. Timeline triển khai
1. Giai đoạn đầu: chỉ chỉnh prominence cho homepage bằng dữ liệu sẵn có.
2. Giai đoạn giữa: thêm block gợi ý theo điểm đến, kéo theo contract grouped-by-province từ backend.
3. Giai đoạn sau: chốt 3 thành phố lớn cho khối gợi ý, đặt Đà Nẵng lên đầu, và thêm fallback từ dữ liệu home để tránh section rỗng.

---

## 2. Nguyên tắc triển khai
- Chỉ sửa các file thuộc landing page public và component carousel liên quan.
- Không thêm request API mới trừ khi cần để render block gợi ý theo điểm đến ổn định hơn.
- Không tạo route mới.
- Không đổi luồng search / booking.
- Ưu tiên tái sử dụng component hiện có, chỉ chỉnh data shaping, copy, và CTA.

---

## 3. Phạm vi công việc

### Phase A. Data shaping và content priority
Mục tiêu: chuẩn bị dữ liệu hiển thị đúng với chiến lược mix featured.

Task A1. Tách city highlights và featured rooms trong landing page state
- File chính: [src/pages/Admin/Home/index.tsx](../../src/pages/Admin/Home/index.tsx)
- Việc cần làm:
  - Giữ `featuredProvinces` theo API hiện có.
  - Xác định 3 thành phố lớn đầu tiên theo thứ tự ưu tiên đã chốt cho block gợi ý.
  - Giữ `featuredRooms` từ latest rooms API, không thêm nguồn mới.
- Dependency: không có.

Task A2. Chuẩn hóa copy heading/description cho 2 block đầu sau hero
- File chính: [src/pages/Admin/Home/index.tsx](../../src/pages/Admin/Home/index.tsx)
- Việc cần làm:
  - Viết heading ngắn, mạnh hơn cho city highlights.
  - Viết heading/description cho featured rooms theo hướng chuyển đổi.
  - Giữ tiếng Việt và tone public site.
- Dependency: A1.

### Phase B. UI emphasis và CTA
Mục tiêu: làm block nổi bật rõ hơn mà không phá layout.

Task B1. Tăng trọng số hiển thị city highlights
- File chính: [src/pages/Admin/Home/components/ProvinceCarousel.tsx](../../src/pages/Admin/Home/components/ProvinceCarousel.tsx)
- Việc cần làm:
  - Giữ carousel hiện có.
  - Tăng độ rõ của heading / description nếu cần.
  - Nếu bổ sung CTA, đặt ở wrapper section thay vì thêm vào từng card.
  - Với block gợi ý theo điểm đến, ưu tiên render đủ Đà Nẵng trước rồi mới tới các city còn lại theo timeline đã chốt.
- Dependency: A2.

Task B2. Tăng trọng số hiển thị featured rooms
- File chính: [src/components/rooms/RoomCarouselContainer.tsx](../../src/components/rooms/RoomCarouselContainer.tsx)
- File phụ: [src/components/rooms/RoomCarouselItem.tsx](../../src/components/rooms/RoomCarouselItem.tsx)
- Việc cần làm:
  - Dùng heading/description mới để định vị block này là block chủ đạo.
  - Thêm CTA phụ dẫn tới trang khám phá phòng nếu phù hợp.
  - Không sửa logic render card hiện có ngoài phạm vi giao diện.
- Dependency: A2.

Task B3. Thêm CTA mạnh hơn cho khối featured
- File chính: [src/pages/Admin/Home/index.tsx](../../src/pages/Admin/Home/index.tsx)
- Việc cần làm:
  - Chọn vị trí CTA ở phần heading wrapper hoặc footer của block.
  - Đảm bảo route đích đã có sẵn.
  - CTA phải dễ nhận biết trên mobile.
- Dependency: B1, B2.

### Phase C. Responsive, accessibility, và polish
Mục tiêu: giữ mobile-first và không làm hỏng trải nghiệm hiện tại.

Task C1. Soát responsive trên breakpoint nhỏ
- File chính: [src/pages/Admin/Home/index.tsx](../../src/pages/Admin/Home/index.tsx)
- File phụ: [src/pages/Admin/Home/components/ProvinceCarousel.tsx](../../src/pages/Admin/Home/components/ProvinceCarousel.tsx)
- File phụ: [src/components/rooms/RoomCarouselContainer.tsx](../../src/components/rooms/RoomCarouselContainer.tsx)
- Việc cần làm:
  - Kiểm tra spacing, title wrap, CTA wrap.
  - Đảm bảo carousel vẫn đọc được ở 375px.
- Dependency: B1, B2, B3.

Task C2. Soát accessibility cơ bản
- File chính: các file UI ở trên.
- Việc cần làm:
  - Giữ `aria-label` hiện có.
  - Đảm bảo CTA mới là link/button thật.
  - Giữ tương phản chữ/nền đủ rõ.
- Dependency: B1, B2, B3.

### Phase D. Verification
Mục tiêu: xác nhận không vỡ build và đúng scope.

Task D1. Chạy kiểm tra lỗi / type liên quan file đã sửa
- Scope: các file landing page public đã chạm.
- Việc cần làm:
  - Sửa lỗi cú pháp/type nếu có.
  - Không mở rộng sang file ngoài scope.
- Dependency: hoàn thành A/B/C.

Task D2. So khớp với SRS / design / lead
- Việc cần làm:
  - Verify search-first vẫn còn.
  - Verify featured strategy là mix rooms + cities, nhưng block gợi ý theo điểm đến có timeline ưu tiên riêng.
  - Verify 3 thành phố lớn được ưu tiên trước và Đà Nẵng đứng đầu khối gợi ý.
  - Verify CTA mạnh hơn nhưng vẫn tiếng Việt.
- Dependency: D1.

---

## 4. Thứ tự thực thi khuyến nghị
1. A1
2. A2
3. B1
4. B2
5. B3
6. C1
7. C2
8. D1
9. D2

---

## 5. Handoff cho bước sau

### Handoff cho `stack-task`
- Bắt đầu từ phase A, sửa đúng các file đã chỉ định.
- Không refactor ngoài scope plan.
- Sau mỗi cụm thay đổi, chạy kiểm tra lỗi liên quan file đã sửa.

### Handoff cho `stack-testcase`
- Viết testcase requirement-centric cho:
  - search-first landing page.
  - city highlights ưu tiên.
  - featured rooms mix.
  - CTA mạnh hơn.
  - responsive / mobile.

### Handoff cho `stack-review-branch`
- Review các rủi ro:
  - phá search-first.
  - lệch ranking API.
  - CTA quá mạnh hoặc sai route.
  - vỡ responsive mobile.

---

## 6. Tiêu chí hoàn thành plan
- Có task nhỏ, rõ dependency.
- Có đường vào rõ cho task/testcase/review.
- Không chạm backend, DB, hay route mới.
- Plan bám sát SRS và design đã chốt.
