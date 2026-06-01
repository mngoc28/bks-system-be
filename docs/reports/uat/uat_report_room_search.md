# UAT Report: Màn hình Tìm kiếm Phòng (Room Search) - Final Sign-off

## Executive Summary
- **UAT Recommendation**: **GO (Approved) - Hoàn tất nghiệm thu và Thông qua**
- **Summary of Findings**: 
  Màn hình Tìm kiếm Phòng lưu trú (`/search/rooms`) đã được triển khai các Yêu cầu thay đổi (CR-01, CR-02) và khắc phục toàn bộ các lỗi trải nghiệm người dùng (UX) ghi nhận trong báo cáo trước. Giao diện thanh công cụ tìm kiếm ngang đồng bộ tốt với URL, mã giảm giá động được kết nối API backend, chuẩn hóa icon diện tích, xử lý triệt để lặp địa chỉ và vi phạm lồng thẻ HTML5.
  Hệ thống đã trải qua kiểm thử tự động (E2E Playwright) và thủ công đạt trạng thái hoạt động tốt, ổn định, giao diện mượt mà và trực quan. Do đó, kiểm thử viên UAT chính thức khuyến nghị **GO** để phát hành tính năng lên môi trường Production.

---

## Metrics & Status
- **Kịch bản kiểm thử đã thực hiện (Scenarios Executed)**: 7/7 (Đạt 100%)
- **Lỗi nghiêm trọng/Chặn (Critical/Blocking Issues)**: 0
- **Lỗi Trải nghiệm & Giao diện (Usability/UX Findings)**: 0 (Đã khắc phục 3/3)
- **Khoảng trống Nghiệp vụ (Requirement Gaps)**: 0 (Đã khắc phục 4/4)

---

## Kết quả nghiệm thu kịch bản kiểm thử (UAT Scenarios)

### UAT-01: Bộ lọc loại hình lưu trú (Property Type Filter)
- **Kết quả**: **PASS**. Lọc chính xác danh sách phòng theo phân khúc (Khách sạn, Căn hộ, Homestay), URL đồng bộ tham số `propertyTypeId`.

### UAT-02: Tìm kiếm theo từ khóa (Keyword Search)
- **Kết quả**: **PASS**. Tìm kiếm tự động thông qua `useDeferredValue` của React giúp màn hình mượt mà, không giật lag.

### UAT-03: Đăng ký nhận Coupon ưu đãi (Discount Banner) - CR-02
- **Kết quả**: **PASS**. 
  - Đăng ký qua email đã được kết nối với API `/home/coupons/register`.
  - Khi có coupon hoạt động trong DB (ví dụ: `WELCOME100K`), hệ thống trả về chính xác và hiển thị động trên UI. Nếu không có coupon nào khả dụng, trả về mã fallback `BKSSUMMER10`.

### UAT-04: Sắp xếp kết quả (Sorting)
- **Kết quả**: **PASS**. Sắp xếp theo giá tăng/giảm và sức chứa hoạt động tốt, đồng bộ tham số URL.

### UAT-05: Phân trang (Pagination)
- **Kết quả**: **PASS**. Phân trang nhảy trang đúng. Thay đổi số lượng bản ghi hiển thị hoạt động tốt.

### UAT-06: Tích hợp thanh tìm kiếm ngang & Đồng bộ URL - CR-01
- **Kết quả**: **PASS**. 
  - Tích hợp thành công thanh tìm kiếm ngang 7 cột: `[Chọn Tỉnh/Thành] [Chọn Phường/Xã] [Nhận phòng] [Trả phòng] [Số khách] [Từ khóa] [Tìm kiếm]`.
  - Các input hoạt động ở local state, khi bấm "Tìm kiếm" sẽ cập nhật toàn bộ tham số lên URL (`provinceId`, `wardId`, `startDate`, `endDate`, `guests`, `keyword`), kích hoạt bộ lọc phòng.

### UAT-07: Cô lập sự kiện click thẻ phòng (HTML5 Nesting Fix)
- **Kết quả**: **PASS**. Thay đổi thẻ bọc ngoài từ `<Link>` sang `<div>` có click handler cô lập. Nhấp vào các phần tử nội bộ (nút wishlist, share, link đối tác) không kích hoạt điều hướng thẻ phòng chính, hoạt động chuẩn xác theo HTML5.

---

## Chi tiết lỗi giao diện & Nghiệp vụ đã khắc phục 🟢

### UAT-ISSUE-01: Sai Icon đại diện cho Diện tích phòng
- **Trạng thái**: **RESOLVED**. Thay thế thành công biểu tượng `<Filter>` bằng `<Square>` tượng trưng cho diện tích hình học ($m^2$).

### UAT-ISSUE-02: Lặp từ thông tin Địa chỉ trên Card phòng
- **Trạng thái**: **RESOLVED**. Thêm helper `formatRoomAddress` ở client-side để lọc trùng tỉnh thành (Ví dụ: chuỗi lặp `"Hà Nội - ..., Quận Cầu Giấy, Hà Nội"` nay chỉ hiển thị duy nhất `"..., Quận Cầu Giấy, Hà Nội"`).

### UAT-ISSUE-03: Vi phạm cú pháp HTML5 (Nested Interactive Elements)
- **Trạng thái**: **RESOLVED**. Khắc phục hoàn toàn bằng cách thay đổi cơ chế điều hướng sang click handler cô lập các phần tử con qua class `.interactive-click`.

### GAP-01 -> GAP-03: Thiếu bộ chọn địa điểm, ngày, số khách trên trang kết quả
- **Trạng thái**: **RESOLVED**. Đã tích hợp đầy đủ các input chọn Tỉnh/Thành/Phường/Xã, ngày Check-in/Check-out, số khách thông qua Popover Counter trực quan và chuẩn hóa UI.

---
**Sign-off Signature:** Senior UAT Tester / Specialist  
**Date:** 2026-05-27
