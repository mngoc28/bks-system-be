# Test Case Specification: Landing Page Prominence — Homepage nổi bật / city highlights / grouped suggestions

## Document Information

| Trường | Giá trị |
|---|---|
| **Testcase ID** | TC003 |
| **Related SRS** | [docs/SRC/srs_landing_page_prominence.md](../SRC/srs_landing_page_prominence.md) |
| **Related Design** | [docs/designs/design_003.md](../designs/design_003.md) |
| **Related Plan** | [docs/plans/plan_003.md](../plans/plan_003.md) |
| **Canonical DB** | N/A (scope FE landing page, chỉ đọc dữ liệu public hiện có) |
| **Status** | Draft |
| **Ngôn ngữ thực thi QC** | Tiếng Việt |

## Scope

### In-scope (theo SRS + plan P003 đã triển khai)

- Hero search vẫn là hành động chính ở phần đầu trang.
- Khối thành phố / điểm đến được ưu tiên theo 3 thành phố lớn, trong đó Đà Nẵng đứng đầu nhóm gợi ý.
- Khối phòng nổi bật vẫn xuất hiện và không bị loại bỏ.
- Khối gợi ý theo điểm đến sử dụng grouped rooms by province; nếu dữ liệu grouped rỗng thì FE fallback từ latest rooms để tránh section trống.
- CTA ở khối featured rõ hơn, dẫn tới route hợp lệ có sẵn.
- Trang chủ vẫn giữ thứ tự section chính: hero search, city highlights, featured rooms, partner grid, contact card, news grid.

### Out-of-scope

- Không kiểm thử logic booking, payment, partner management hay realtime WebSocket.
- Không kiểm DB migration/schema vì scope này không tạo bảng/cột mới.
- Không kiểm backend business rule ngoài contract public homepage đã có sẵn.

## Preconditions

| ID | Điều kiện |
|---|---|
| P-01 | FE chạy được trên môi trường staging/local với endpoint public homepage và API data hiện có. |
| P-02 | Có dữ liệu provinces, latest rooms, partners và news trong môi trường test. |
| P-03 | Endpoint grouped rooms by province trả dữ liệu hợp lệ cho ít nhất 1 trong 3 city ưu tiên; nếu không có room cho city nào đó, FE vẫn phải fallback từ latest rooms. |
| P-04 | Browser test có thể truy cập landing page public `/`. |
| P-05 | Đã có route hợp lệ cho CTA trên block featured (`SEARCH_ROOMS` và `SEARCH_ROOMS_BY_PROVINCE`). |

## Test Data (gợi ý)

| Mã | Mô tả |
|---|---|
| TD-01 | Danh sách provinces có các city ưu tiên: Hà Nội, Đà Nẵng, Khánh Hòa / Quảng Ninh. |
| TD-02 | latest rooms có ít nhất 1 room thuộc Đà Nẵng, 1 room thuộc city ưu tiên khác, và 1 room không thuộc city ưu tiên để kiểm tra fallback/sort. |
| TD-03 | grouped suggestions trả group có rooms cho Đà Nẵng và group trống cho một city ưu tiên khác. |
| TD-04 | CTA click vào route `/search/rooms` và `/search/rooms/province/:provinceId`. |

## Test Cases

| TC ID | Requirement Ref | Screen/Module | Scenario | Steps | Test Data | Expected Result | Priority |
|------|-----------------|---------------|----------|-------|-----------|-----------------|----------|
| TC003-001 | FR-01 | Public Home | Hero search vẫn là hành động chính | 1) Mở `/`.<br>2) Quan sát phần đầu trang. | P-04 | Hero search xuất hiện ở đầu trang, không bị thay thế bởi block featured. | High |
| TC003-002 | FR-02, FR-04 | Public Home | Khối nổi bật hiển thị đầy đủ city + room | 1) Mở `/`.<br>2) Scroll tới các section đầu sau hero. | TD-01, TD-02 | City highlights và featured rooms đều xuất hiện; featured rooms không bị mất khỏi homepage. | High |
| TC003-003 | FR-03 | Public Home | Ưu tiên 3 thành phố lớn trong khối gợi ý | 1) Mở `/`.<br>2) Quan sát thứ tự block gợi ý theo điểm đến. | TD-01 | Nhóm city ưu tiên hiển thị trước; Đà Nẵng đứng đầu nhóm gợi ý. | High |
| TC003-004 | FR-03, FR-07 | Public Home | Dữ liệu grouped theo province được dùng thay vì tự curation | 1) Mở `/`.<br>2) So khớp room trong block gợi ý với dữ liệu API grouped. | TD-03 | Các room hiển thị trong block gợi ý trùng với dữ liệu trả về từ grouped suggestions hoặc fallback được thiết kế, không sinh data giả. | High |
| TC003-005 | FR-05 | Public Home | CTA khối featured rõ và dẫn đúng route | 1) Mở `/`.<br>2) Click CTA ở block featured / gợi ý theo điểm đến. | TD-04 | Điều hướng tới route hợp lệ; không xảy ra dead-end hoặc route lỗi. | High |
| TC003-006 | FR-06 | Public Home | Giữ nguyên thứ tự section chính | 1) Mở `/`.<br>2) Kiểm tra thứ tự section từ trên xuống. | P-04 | Thứ tự chính vẫn là: hero search → city highlights → featured rooms → partner grid → contact card → news grid. | High |
| TC003-007 | FR-08 | Public Home | Responsive mobile 375px | 1) Mở `/` với viewport mobile.<br>2) Kiểm tra wrap title, CTA, carousel. | Mobile viewport | Không vỡ layout; CTA không tràn; carousel vẫn đọc được. | High |
| TC003-008 | FR-09 | Public Home | UI tiếng Việt | 1) Mở `/`.<br>2) Kiểm tra heading/CTA mới. | P-04 | Label, heading, CTA hiển thị tiếng Việt nhất quán; không còn copy tiếng Anh không cần thiết. | Medium |
| TC003-009 | Validation/negative | Public Home | Grouped suggestions rỗng vẫn có nội dung fallback | 1) Cho response grouped rỗng cho một city.<br>2) Reload `/`. | TD-03 | FE vẫn render section cho city đó bằng fallback từ latest rooms hoặc hiển thị state an toàn, không mất hẳn section. | High |
| TC003-010 | Validation/negative | Public Home | Thiếu dữ liệu cities ưu tiên | 1) Giả lập provinces thiếu 1 city ưu tiên.<br>2) Reload `/`. | TD-01 bị thiếu 1 city | Trang không crash; city còn lại vẫn hiển thị; không có lỗi runtime. | Medium |
| TC003-011 | Data integrity | Public Home | Không sinh room giả / không lệch địa điểm | 1) Mở `/` với data thật.<br>2) So khớp label province và card room. | TD-02, TD-03 | Card room thuộc đúng city/group; không có room bị gán sang city sai. | High |
| TC003-012 | Permission / public access | Public Home | Landing page public không yêu cầu đăng nhập | 1) Mở `/` ở tab ẩn danh.<br>2) Quan sát trang. | P-04 | Trang vẫn truy cập được; không redirect login; không xuất hiện lỗi auth. | High |
| TC003-013 | Cross-screen dependency | Home → Search Rooms | CTA điều hướng đúng sang trang tìm kiếm | 1) Từ `/`, click CTA “Xem tất cả điểm đến” hoặc CTA khối gợi ý.<br>2) Quan sát màn đích. | TD-04 | Điều hướng đúng sang trang search rooms hoặc search rooms by province; filter route được giữ đúng. | Medium |
| TC003-014 | Cross-screen dependency | Home → Province Search | Province card / CTA city dẫn đúng province search | 1) Từ `/`, click một card thành phố ưu tiên.<br>2) Quan sát URL đích. | TD-04 | Điều hướng tới `/search/rooms/province/:provinceId` đúng province đang chọn. | High |
| TC003-015 | Regression | Public Home | Partners/contact/news vẫn còn sau thay đổi prominence | 1) Scroll gần cuối trang home.<br>2) Kiểm tra 3 section cuối. | P-04 | Partner grid, contact card, news grid vẫn render; không bị thay thế bởi block mới. | Medium |

## Validation Matrix

| Field / UI | Rule | Valid | Invalid | Ghi chú |
|---|---|---|---|---|
| Hero search | Vẫn là anchor chính | Hiển thị đầu trang | Bị đẩy xuống dưới block featured | FR-01 |
| City highlights | 3 city lớn ưu tiên trước | Đà Nẵng đứng đầu nhóm gợi ý | Đà Nẵng không hiện hoặc đứng sai thứ tự | FR-03 |
| Featured rooms | Không bị loại bỏ | Có carousel phòng nổi bật | Không có block phòng | FR-04 |
| CTA | Route hợp lệ | Dẫn đến search rooms / province search | Dead-end / route lỗi | FR-05 |
| Section order | Giữ nguyên luồng | Hero → city → rooms → partner → contact → news | Đổi thứ tự section chính | FR-06 |
| Fallback | Dữ liệu grouped rỗng | Section vẫn có nội dung an toàn | Mất hẳn section / crash | FR-07 |
| Responsive | Mobile first | Không vỡ layout 375px | CTA wrap vỡ / carousel tràn | FR-08 |
| i18n | Tiếng Việt | Copy tiếng Việt nhất quán | Lẫn copy tiếng Anh không cần thiết | FR-09 |

## Traceability Matrix

| Requirement / Function | Covered By | Ghi chú |
|------------------------|------------|---------|
| FR-01 | TC003-001 | Hero search anchor |
| FR-02 | TC003-002 | Khối nổi bật đầy đủ |
| FR-03 | TC003-003, TC003-004, TC003-010 | Ưu tiên city / Đà Nẵng-first |
| FR-04 | TC003-002, TC003-015 | Featured rooms còn giữ |
| FR-05 | TC003-005, TC003-013, TC003-014 | CTA & route |
| FR-06 | TC003-006, TC003-015 | Thứ tự section |
| FR-07 | TC003-004, TC003-009, TC003-011 | Data integrity / fallback |
| FR-08 | TC003-007 | Responsive mobile |
| FR-09 | TC003-008 | Vietnamese UI |

## Execution Notes for QC

1. **Thứ tự gợi ý:** TC003-001 → TC003-003 → TC003-005 → TC003-006 → TC003-007 → TC003-009.
2. **Dữ liệu:** cần ít nhất 1 bộ dữ liệu thật cho Đà Nẵng và 1 city ưu tiên khác; nếu nhóm gợi ý trả rỗng, phải verify fallback.
3. **Cross-screen:** kiểm tra kỹ điều hướng từ card city và CTA khối featured sang search rooms / province search.
4. **Không cần DB:** scope này chỉ đọc dữ liệu public hiện có, không kiểm migration/schema.
5. **Browser check:** nên test cả desktop và mobile viewport.

## Smoke Regression (sau mỗi release landing page prominence)

| # | Mô tả ngắn | Kỳ vọng |
|---|------------|---------|
| S-01 | Mở `/` | Hero search vẫn là anchor chính |
| S-02 | City gợi ý | Đà Nẵng hiển thị đầu nhóm gợi ý |
| S-03 | CTA featured | Điều hướng đúng sang search rooms / province search |
| S-04 | Grouped suggestions rỗng | Section vẫn có nội dung fallback |
| S-05 | Mobile 375px | Layout không vỡ |

*Tài liệu sinh theo skill `stack-testcase`, đồng bộ plan P003 và SRS landing page prominence.*