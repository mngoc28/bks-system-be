# Design: Tối ưu hiển thị thông tin nổi bật trên landing page

## Document Information
- **Design ID:** D003
- **Created:** 2026-05-21
- **Status:** Draft
- **Related SRS:** [docs/SRC/srs_landing_page_prominence.md](../SRC/srs_landing_page_prominence.md)
- **Related Lead:** [docs/leads/lead_260521_landing-page-prominence.md](../leads/lead_260521_landing-page-prominence.md)
- **Canonical schema:** [docs/databases_docs/db_overview_etc_core_schema.md](../databases_docs/db_overview_etc_core_schema.md)
- **Persona áp dụng:** `.cursor/skills/stack-personas/technical-lead-architect.md`
- **Áp dụng rule:** `.cursor/rules/php-laravel-rule.mdc`, `.cursor/rules/laravel-implementation-standards.mdc`, `.cursor/rules/karpathy-behavioral-guidelines.mdc`

## 1. Mục tiêu thiết kế
Thiết kế này chuyển SRS landing page prominence thành hướng triển khai cụ thể cho FE hiện tại, với nguyên tắc:
- Không thay đổi route hoặc flow tìm kiếm.
- Ban đầu ưu tiên chỉ tái cấu trúc dữ liệu đang có; về sau bổ sung thêm contract grouped-by-province để render block gợi ý theo điểm đến ổn định hơn.
- Chỉ tái cấu trúc cách lấy, sắp xếp và nhấn mạnh dữ liệu đang có, rồi thêm fallback khi cần.
- Giữ UI tiếng Việt, mobile-first, API-driven ordering.

### 1.1. Timeline triển khai
1. Đầu tiên chỉ nâng prominence cho homepage bằng cách chỉnh heading/CTA và thứ tự ưu tiên.
2. Sau đó thêm block gợi ý theo điểm đến với contract grouped rooms by province.
3. Cuối cùng, FE thêm fallback để các city ưu tiên vẫn hiện đủ nội dung ngay cả khi response grouped chưa hoàn toàn đầy đủ.

---

## 2. Kiến trúc thành phần

### 2.1. Component hiện tại sẽ dùng lại
- [src/pages/Admin/Home/index.tsx](../../src/pages/Admin/Home/index.tsx)
- [src/pages/Admin/Home/components/ProvinceCarousel.tsx](../../src/pages/Admin/Home/components/ProvinceCarousel.tsx)
- [src/components/rooms/RoomCarouselContainer.tsx](../../src/components/rooms/RoomCarouselContainer.tsx)
- [src/components/rooms/RoomCarouselItem.tsx](../../src/components/rooms/RoomCarouselItem.tsx)
- [src/components/common/ContactCard.tsx](../../src/components/common/ContactCard.tsx)
- [src/pages/Admin/Home/components/PartnerGrid.tsx](../../src/pages/Admin/Home/components/PartnerGrid.tsx)
- [src/pages/Admin/Home/components/NewsGrid.tsx](../../src/pages/Admin/Home/components/NewsGrid.tsx)

### 2.2. Điểm thay đổi chính
1. Tầng dữ liệu ở [src/pages/Admin/Home/index.tsx](../../src/pages/Admin/Home/index.tsx)
   - Tạo 2 danh sách hiển thị riêng:
     - city highlights ưu tiên 3 thành phố lớn đầu tiên theo thứ tự API.
     - featured rooms mix từ latest rooms API.
   - Không thay đổi request API.

2. Tầng hiển thị của province carousel
   - Giữ component hiện có.
   - Nâng trọng số hiển thị của phần heading, mô tả và card đầu danh sách.
   - Nếu cần CTA mạnh hơn, thêm CTA ở phần heading wrapper của section thay vì nhét vào từng card.

3. Tầng hiển thị của featured rooms carousel
   - Giữ `RoomCarouselContainer` và `RoomCarouselItem`.
   - Dùng heading/description mới để định vị section như một khối chủ đạo.
   - Có thể thêm CTA phụ ngay trên carousel để đẩy người dùng sang trang khám phá phòng.

4. Tầng content priority
   - Top 3 thành phố lớn được hiển thị trước trong block city highlights.
   - Các thành phố du lịch cấp dưới tiếp theo vẫn giữ nguyên thứ tự API.
   - Không tự curation manual ở FE nếu điều đó làm lệch ranking API.

---

## 3. API / Integration

### 3.1. API hiện có
- Provinces API từ `useGetAllProvincesTypes()`.
- Latest rooms API từ `useLatestRoomsQuery()`.
- Partners API từ `useRandomPartnersQuery()`.
- News API từ `useLatestNewsQuery(6)`.

### 3.2. Không thay đổi contract
- Không tạo endpoint mới nếu chỉ làm prominence cho city highlights và featured rooms.
- Với block gợi ý theo điểm đến, FE hiện đã dùng endpoint grouped rooms by province để tránh tự curation phức tạp.
- FE vẫn map dữ liệu sang card model và sắp xếp theo rule hiển thị.

### 3.3. Data shaping rule
- Provinces response được map sang `ProvinceCard` như hiện tại.
- Các item đầu tiên trong response được xem là nhóm ưu tiên cao nhất.
- Nếu dữ liệu API đã được backend sắp xếp, FE chỉ render đúng thứ tự trả về.
- Nếu cần nhấn mạnh 3 thành phố lớn, chỉ thực hiện bằng visual emphasis, không đổi logic ranking trong FE.

---

## 4. Thiết kế UX / UI

### 4.1. Nguyên tắc hiển thị
- Hero search vẫn là anchor chính.
- Khối nổi bật phải tạo cảm giác “curated” hơn, nhưng không lấn át search.
- CTA mới phải rõ hơn hiện tại, nhưng không dùng copy quá dài.
- Tất cả text mới dùng tiếng Việt.

### 4.2. Chiến lược bố cục
- Giữ thứ tự section hiện tại.
- Tăng độ nổi bật bằng cách:
  - Đặt heading mạnh hơn cho 2 block đầu sau hero.
  - Thêm CTA ngay cạnh heading của city highlights hoặc featured rooms.
  - Dùng nhãn phụ ngắn gọn kiểu “Ưu tiên hôm nay”, “Xem ngay phòng nổi bật” nếu cần.

### 4.3. Chiến lược hiển thị city highlights
- Top 3 thành phố lớn xuất hiện đầu carousel.
- Các thành phố du lịch cấp dưới hiển thị sau.
- Giữ card đơn giản, dễ scan trên mobile.
- Không đổi route hiện có của province card.

### 4.4. Chiến lược hiển thị featured rooms
- Giữ carousel phòng nổi bật, nhưng đặt nó như một block chuyển đổi chính.
- Mỗi card vẫn hiển thị ảnh, tên, địa chỉ, giá, diện tích và số giường.
- CTA phụ có thể dẫn tới trang search rooms hoặc room detail tùy ngữ cảnh.

---

## 5. Responsive / Accessibility

### 5.1. Responsive
- Mobile first: mỗi block phải đọc được ngay ở màn 375px.
- Carousel cần giữ perPage thấp ở breakpoint nhỏ.
- CTA phải không bị wrap vỡ layout.

### 5.2. Accessibility
- Giữ `aria-label` hiện có cho carousel và card link.
- CTA mới phải là button/link thật, không chỉ là text nhấn mạnh.
- Đảm bảo tương phản chữ và nền đủ rõ trên mobile.

---

## 6. Security / Performance

### 6.1. Security
- Không thêm dữ liệu nhạy cảm.
- Không thay đổi auth hoặc access control.
- Chỉ đọc dữ liệu public hiện có.

### 6.2. Performance
- Tránh render thêm request mới.
- Không nạp thêm bộ ảnh mới ngoài image source hiện hữu nếu không cần thiết.
- Hạn chế tạo logic sort phức tạp ở render; ưu tiên `useMemo` như hiện tại.
- Giữ carousel count hợp lý để không tăng cost paint trên mobile.

---

## 7. Data model / Mapping

### 7.1. Province highlights
Input:
- `ProvinceTypes[]` từ API.

Output:
- `ProvinceCard[]` cho `ProvinceCarousel`.

Rule:
- Render theo thứ tự API.
- Visual emphasis có thể tăng ở heading/CTA của section, không cần đổi schema.

### 7.2. Featured rooms
Input:
- `latestRoomsData` từ API.

Output:
- `RoomCard[]` cho `FeaturedRoomCarousel`.

Rule:
- Dùng 6 phòng đầu tiên theo dữ liệu API hoặc số lượng hiện có nếu ít hơn.
- Giữ giá hiển thị theo logic hiện tại.

### 7.3. CTA routing
Đích CTA phải map tới route có sẵn:
- Search rooms.
- Room detail.
- Province rooms.

Không thêm route mới trong scope này.

---

## 8. Kế hoạch triển khai kỹ thuật
1. Chỉnh `src/pages/Admin/Home/index.tsx` để tách rõ 2 khối ưu tiên:
   - city highlights.
   - featured rooms.
2. Bổ sung CTA mạnh hơn tại phần heading hoặc footer của block.
3. Nếu bài toán chuyển sang gợi ý theo điểm đến, bổ sung contract grouped-by-province và giữ thứ tự ưu tiên 3 city lớn.
4. Nếu response grouped chưa đầy đủ, fallback từ dữ liệu home để tránh section rỗng.
5. Giữ nguyên component carousel hiện tại, chỉ truyền data / copy / CTA phù hợp.

---

## 9. Rủi ro thiết kế
- Nếu CTA quá nặng, homepage sẽ mất cân bằng search-first.
- Nếu city highlights được nhấn mạnh quá mức, phòng nổi bật có thể bị mờ đi.
- Nếu API order không đúng business priority, FE không nên tự curation vượt quá scope.

---

## 10. Tiêu chí design review
- Có thể trace từ SRS sang component cụ thể.
- Không tạo endpoint mới.
- Không phá thứ tự section hiện tại.
- Featured block rõ ràng hơn về mặt thị giác và CTA.
- Giữ mobile-first và tiếng Việt.
