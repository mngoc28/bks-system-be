# Lead: Room-to-Tourist-Spot Mapping

## Document Information
- **Lead ID:** L260521-room-tourist-mapping
- **Created:** 2026-05-21
- **Status:** Clarified
- **Next Step:** Ready for `stack-analyze` / SRS.

---

## Original Input
> `http://localhost:5173/`
>
> mục tiêu: map các phòng nổi bật với các khu du lịch (ví dụ: phòng ... cách bà nà hill 2km,...)

---

## Clarified Requirements

### Problem Statement
Người dùng cần nhìn thấy phòng nổi bật gắn với điểm du lịch cụ thể để hiểu nhanh phòng đó phù hợp với chuyến đi nào. Mục tiêu là hiển thị tên điểm đến và thời gian di chuyển ước tính ngay trên các khối phòng nổi bật, thay vì chỉ hiển thị thông tin phòng đơn lẻ.

### Target Users
- **Primary:** Khách đặt phòng trên trang public.
- **Secondary:** Người dùng đang so sánh phòng theo điểm đến du lịch, team nội dung / marketing.

### Business Context
- **Business Value:** Tăng khả năng chọn phòng phù hợp với lịch trình du lịch và tăng click-through từ landing page sang chi tiết phòng / tìm kiếm.
- **Success Metric:** End user biết được phòng của họ mất bao nhiêu thời gian để đến được địa điểm du lịch.
- **Confirmed Preference:** Chỉ thêm table hoặc field, không bớt dữ liệu hiện có.

### Technical Context
- **Screens Involved:** Trang chủ và trang kết quả tìm kiếm phòng.
- **Data Source Preference:** Dựa trên dữ liệu phòng / khu vực hiện có, không bắt buộc tích hợp live routing trong scope này.
- **Display Style:** Hiển thị cả tên điểm đến và thời gian di chuyển.
- **Current UI Baseline:** Trang chủ đã có hero search, block điểm đến nổi bật, và block phòng nổi bật; feature mới cần lồng vào luồng hiện có.

### Key Features
1. Gắn phòng nổi bật với một hoặc nhiều điểm du lịch nổi tiếng.
2. Hiển thị tên địa điểm + thời gian di chuyển trên card / block phòng.
3. Áp dụng trên cả trang chủ và trang kết quả tìm kiếm.
4. Giữ logic ưu tiên theo dữ liệu hiện có, không thay đổi routing.

### Out of Scope
- Không redesign toàn bộ homepage.
- Không đổi luồng đặt phòng hiện tại.
- Không xóa field / bảng cũ trong giai đoạn discovery này.
- Không làm bản đồ GIS / route engine phức tạp nếu chưa được xác nhận.

---

## Clarification Q&A

### Business Questions
| Question | Answer |
|----------|--------|
| Bạn muốn hiển thị mapping phòng ↔ địa điểm du lịch ở đâu? | Cả trang chủ và trang kết quả tìm kiếm |
| Phạm vi MVP của mapping là gì? | Tất cả phòng có đủ dữ liệu địa lý |
| Tiêu chí thành công của tính năng này là gì? | End user biết được phòng của họ mất bao nhiêu thời gian để đến được địa điểm du lịch |

### Technical Questions
| Question | Answer |
|----------|--------|
| Nguồn dữ liệu địa điểm du lịch sẽ lấy từ đâu? | Từ dữ liệu phòng / khu vực hiện có |
| Khoảng cách nên được tính và hiển thị thế nào? | Thời gian di chuyển |
| Quy tắc ưu tiên khi một phòng gần nhiều điểm du lịch là gì? | Ưu tiên điểm nổi tiếng nhất |
| Bạn muốn thông điệp hiển thị theo kiểu nào trên card? | Cả tên điểm đến và khoảng cách |
| Có ràng buộc kỹ thuật hoặc dữ liệu nào cần giữ nguyên không? | Chỉ thêm table hoặc field, không bớt |

---

## Assumptions
- Có thể suy ra / duy trì mapping từ dữ liệu khu vực hiện có và danh mục điểm du lịch.
- Travel time là thời gian ước tính hoặc giá trị được quản trị, không phải live routing theo thời gian thực.
- Nếu phòng không có đủ dữ liệu, hệ thống sẽ fallback sang nhãn tỉnh / thành hoặc ẩn label du lịch.

## Open Questions
- [ ] Danh mục điểm du lịch nổi tiếng sẽ do hệ thống tự suy ra hay cần admin cấu hình?
- [ ] Một phòng có thể map với bao nhiêu điểm du lịch trên một card / một màn hình?
- [ ] Khi phòng không có dữ liệu địa lý đủ tốt, hệ thống sẽ ẩn block hay fallback sang nhãn tỉnh/thành?

## Risks Identified
| Risk | Impact | Mitigation |
|------|--------|------------|
| Dữ liệu khu vực hiện có không đủ chi tiết để suy ra thời gian di chuyển chính xác | H | Cần chốt rule ước tính và nguồn dữ liệu trong SRS / design |
| Hiển thị quá nhiều điểm du lịch cho một phòng có thể làm card rối | M | Giới hạn số điểm ưu tiên và chỉ show điểm nổi bật nhất |
| Thông tin ước tính không nhất quán giữa các phòng | M | Chốt chuẩn dữ liệu / seed và rule ưu tiên rõ ràng |

---

## Next Steps
- [ ] Nếu scope đã đủ rõ, chuyển sang `stack-analyze` để viết SRS chi tiết cho mapping phòng với điểm du lịch.
- [ ] Nếu cần chốt thêm rule tính thời gian di chuyển hoặc dữ liệu địa lý, tiếp tục discovery với một vòng hỏi ngắn nữa.

## Appendix

### Discovery Session Log
- **Round 1:** Xác định scope hiển thị trên trang chủ và trang kết quả, nguồn dữ liệu từ phòng / khu vực hiện có, ưu tiên điểm nổi tiếng nhất, hiển thị tên địa điểm + thời gian di chuyển, success metric là người dùng biết phòng mất bao lâu để tới điểm du lịch.