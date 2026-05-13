# Database Documentation Hub

Thư mục này là nơi lưu tài liệu cấu trúc và liên kết database của repository.

## Quy tắc bắt buộc (canonical)

- **Một file schema chính:** Toàn bộ định nghĩa bảng, cột, quan hệ và cập nhật theo thời gian phải ghi trong **`docs/databases_docs/db_overview_etc_core_schema.md`**.
- **Không** tạo file `db_mapping_<chức_năng>.md` mới cho mỗi lần phân tích — dễ rối, khó biết bảng đã tồn tại hay cần thêm cột. Thay vào đó: **mở overview → tìm bảng** → nếu có thì **bổ sung cột/quan hệ**; nếu chưa có thì **thêm mục bảng** trong cùng file.
- **Metadata:** Trong `db_overview_etc_core_schema.md` phải có **Người tạo**, **Ngày tạo** (baseline), và mỗi lần sửa phải ghi **Nhật ký thay đổi** (**ngày**, **người cập nhật**, **nội dung**).
- Các skill có xử lý DB phải **đọc `db_overview_etc_core_schema.md` trước**, sau đó mới đọc tài liệu khác trong thư mục (nếu cần).
- Không để đặc tả DB chỉ nằm trong SRS/plan mà không đồng bộ vào overview.

## File khác trong thư mục

- **`db_mapping_*.md` (legacy):** Chỉ giữ làm tham chiếu lịch sử nếu đã tồn tại. **Mọi nội dung mới** merge vào `db_overview_etc_core_schema.md`.
- **`db_migration_*.md`, `db_constraints.md`:** Có thể dùng khi cần tài liệu chuyên đề (migration runbook, ràng buộc tổng hợp), nhưng **bảng/cột nguồn sự thật** vẫn là overview.
