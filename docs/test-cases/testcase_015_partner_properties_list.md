# Test Cases — PP-015 Partner Properties List

**Nguồn:** `docs/SRC/prd_partner_properties_list_gaps.md`, `docs/plans/plan_015_partner_properties_list_gaps.md`  
**Phạm vi:** Partner portal `/partner/properties` — Must / Should / Could (Phase 1–4)

---

## Must-have

| TC ID | Mô tả | Bước thực hiện | Kết quả mong đợi |
|-------|--------|----------------|------------------|
| PP-015-01 | Lọc hình thức cho thuê | Chọn loại hình thuê trong filter bar | Danh sách chỉ hiển thị property khớp `rent_category` |
| PP-015-02 | Keyword tìm địa chỉ | Nhập từ khóa trùng `address_detail` | Property xuất hiện trong kết quả |
| PP-015-03 | Thêm phòng từ overview | Click **Thêm phòng** trên card property | Modal tạo phòng mở; sau lưu, preview expand hiển thị phòng mới |
| PP-015-04 | Xóa đơn — hủy | Click xóa property → **Hủy** | Property không bị xóa |
| PP-015-05 | Xóa đơn — xác nhận | Click xóa → **Xác nhận xóa** | Property bị xóa; toast thành công |

## Should-have

| TC ID | Mô tả | Bước thực hiện | Kết quả mong đợi |
|-------|--------|----------------|------------------|
| PP-015-06 | Lọc tỉnh/phường | Mở bộ lọc nâng cao → chọn Tỉnh + Phường | API gọi `province_name`, `ward_name`; kết quả khớp |
| PP-015-07 | Sort rating | Chọn **Đánh giá cao nhất** | Property có rating cao lên trước; chưa review ở cuối |
| PP-015-08 | Cover image | Load danh sách với `include=cover` | Thumbnail 64×64 hiển thị khi có ảnh |

## Could-have (Phase 4)

| TC ID | Mô tả | Bước thực hiện | Kết quả mong đợi |
|-------|--------|----------------|------------------|
| PP-015-09 | URL persist reload | Áp filter → copy URL → reload trang | Filter được giữ nguyên sau reload |
| PP-015-10 | Occupancy filter | Chọn **Có phòng trống** / **đang thuê** | Chỉ property có ≥1 phòng khớp trạng thái |
| PP-015-11 | Rating filter | Chọn **Từ 4 sao** hoặc **Chưa có đánh giá** | Kết quả khớp `min_rating` API |
| PP-015-12 | Has rooms filter | Chọn **Chưa có phòng** | Chỉ property `rooms_count = 0` |
| PP-015-13 | Preview room filter | Expand property → tìm tên + lọc trạng thái | Lọc client-side trên preview ≤6 phòng; banner "xem toàn bộ" vẫn hiện khi `rooms_count > 6` |

---

## Regression

- `with_rooms=0` không trả nested `rooms` trên list API
- Partner không thấy property của partner khác (keyword / id filter)
- Bulk xóa vẫn yêu cầu gõ `XÁC NHẬN XÓA`

---

## Automated coverage (BE)

`tests/Feature/Partner/PartnerPropertiesListTest.php` — keyword, rent, cover, sort, occupancy, has_rooms, min_rating, ownership isolation.
