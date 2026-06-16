# EU Feedback Session: Public Booking Flow

- **test_method**: browser_automation (Chrome DevTools MCP — Playwright MCP không khả dụng trong session)
- **ui_observed**: yes — http://127.0.0.1:5173, viewport iPhone 390×844, Fast 4G
- **persona_id**: EU-GUEST
- **scene**: Tối cuối tuần, iPhone một tay, gia đình 4 người (2 NL + 2 trẻ 4 & 7 tuổi), Đà Nẵng 2 đêm
- **tasks_run**: GST-01, GST-02, GST-03 (dừng trước VietQR), GST-07

## Summary (first person — Chị Mai)

Tôi vào BKS tìm phòng Đà Nẵng cho cả nhà — chọn được 2 người lớn 2 trẻ em là ổn, nhưng lên danh sách phòng toàn giá **/tháng** và chữ **Thuê dài hạn**, trong khi tôi bấm **Đặt phòng theo ngày**. Chọn phòng 740k/đêm nghe hợp lý, vào chi tiết thì mô tả phòng **40m² jacuzzi** mà diện tích ghi **11m²** — tôi không dám đặt cho con. Chatbot AI chiếm gần hết màn hình điện thoại ngay từ trang chủ. Đến bước thanh toán: đặt 21–23/6 mà báo **3 ngày**, cọc 50% vì lý do **Thuê dài hạn**, cuối cùng còn bảo phải ký **Hợp đồng thuê nhà điện tử** — tôi chỉ đi chơi 2 đêm thôi! Agoda không làm kiểu này. Tôi **chưa bấm xác nhận cuối** vì sợ ký nhầm hợp đồng thuê.

## Raw Feedback Items

### EU-RAW-001: Kết quả tìm kiếm toàn phòng thuê tháng dù chọn đặt theo ngày
- **persona_id**: EU-GUEST
- **task_id**: GST-01
- **buoc_hanh_trinh**: tim_phong
- **thiet_bi_mang**: iPhone 4G, mot_tay
- **ky_vong_doi_thu**: Traveloka/Agoda — giá /đêm, lọc ngắn hạn rõ
- **thuc_te_gap_phai**: 83 kết quả Đà Nẵng; sort giá thấp → đầu danh sách toàn **10–26 triệu/tháng**, badge **Thuê dài hạn**. Phòng /đêm nằm giữa list, khó thấy.
- **muc_buc**: 4
- **bo_cuoc**: khong — xong nhung khong quay lai (near-miss: vẫn lọc tiếp)
- **review_1_cau**: "Tìm phòng cuối tuần mà ra toàn thuê tháng — không biết bấm đâu"
- **thoi_gian_mat**: ~2 phút cuộn + đọc nhầm giá
- **handoff_to**: uat-tester
- **handoff_reason**: UX/filter không khớp intent "Đặt phòng theo ngày"

### EU-RAW-002: Chatbot AI mở sẵn che màn hình mobile
- **persona_id**: EU-GUEST
- **task_id**: GST-07
- **buoc_hanh_trinh**: tim_phong
- **thiet_bi_mang**: iPhone 4G, mot_tay
- **ky_vong_doi_thu**: App OTA — chat ẩn, không chặn CTA
- **thuc_te_gap_phai**: Trang chủ + search + chi tiết + booking đều có **BKS AI ASSISTANT** mở panel lớn, nút gợi ý + ô chat. Một tay khó bấm "Tìm phòng ngay".
- **muc_buc**: 3
- **bo_cuoc**: khong
- **review_1_cau**: "Vào web chưa tìm phòng đã bị chatbot hỏi — phiền"
- **thoi_gian_mat**: ~30 giây đóng chat mỗi trang
- **handoff_to**: uat-tester
- **handoff_reason**: Mobile UX — widget che nội dung chính

### EU-RAW-003: Mô tả phòng 40m² nhưng diện tích hiển thị 11,38 m²
- **persona_id**: EU-GUEST
- **task_id**: GST-02
- **buoc_hanh_trinh**: xem_chi_tiet
- **thiet_bi_mang**: iPhone 4G
- **ky_vong_doi_thu**: Agoda — ảnh + m² khớp mô tả
- **thuc_te_gap_phai**: Phòng Sang Trọng 827: badge **11.38 m²**, mô tả viết **"phòng 40m²"**, King, jacuzzi, ban công. Gia đình 4 người — không tin được.
- **muc_buc**: 5
- **bo_cuoc**: co — sang doi thu (nếu không có phòng khác tin cậy)
- **review_1_cau**: "Ghi 11m² mà mô tả 40m² — lừa đảo hay gì?"
- **thoi_gian_mat**: 1 phút đọc + hoang mang
- **handoff_to**: business-analyst
- **handoff_reason**: Dữ liệu listing không nhất quán — niềm tin sản phẩm

### EU-RAW-004: Đặt 21–23/6 hiển thị "3 ngày" và cọc kiểu thuê dài hạn
- **persona_id**: EU-GUEST
- **task_id**: GST-03
- **buoc_hanh_trinh**: thanh_toan
- **thiet_bi_mang**: iPhone 4G
- **ky_vong_doi_thu**: Giá = số đêm × đơn giá; cọc ngắn hạn đơn giản
- **thuc_te_gap_phai**: Check-in 21/06, check-out 23/06 → **"Tổng số ngày đặt: 3 ngày"**, phí **3 ngày × 740.856**. Lý do cọc: **"Thuê dài hạn (Căn hộ / Dịch vụ dài hạn)"**. Cọc 50% = 1.111.284đ.
- **muc_buc**: 5
- **bo_cuoc**: khong — dừng ở bước xác nhận
- **review_1_cau**: "2 đêm mà tính 3 ngày, cọc dài hạn — không hiểu"
- **thoi_gian_mat**: 3 phút đọc chính sách dài
- **handoff_to**: hospitality-expert
- **handoff_reason**: Công thức đêm/ngày và policy cọc có thể sai nghiệp vụ

### EU-RAW-005: Bước xác nhận bắt ký hợp đồng thuê nhà cho đặt 2 đêm
- **persona_id**: EU-GUEST
- **task_id**: GST-03
- **buoc_hanh_trinh**: thanh_toan
- **thiet_bi_mang**: iPhone 4G
- **ky_vong_doi_thu**: Agoda — xác nhận đặt phòng, không hợp đồng thuê
- **thuc_te_gap_phai**: Màn "Quy trình pháp lý": **"loại hình lưu trú dài hạn"**, phải ký **Hợp đồng thuê nhà điện tử** tại Hồ sơ lưu trú — trong khi đặt nhà nghỉ 2 đêm.
- **muc_buc**: 5
- **bo_cuoc**: co — khong bam Xac Nhan
- **review_1_cau**: "Đi du lịch 2 đêm mà bắt ký hợp đồng thuê nhà — không booking được"
- **thoi_gian_mat**: Dừng hẳn flow
- **handoff_to**: hospitality-expert
- **handoff_reason**: Copy/policy sai loại hình cho booking ngắn hạn

### EU-RAW-006: Chính sách hủy quá dài — khó hiểu trước khi trả tiền (near-miss)
- **persona_id**: EU-GUEST
- **task_id**: GST-05
- **buoc_hanh_trinh**: thanh_toan
- **thiet_bi_mang**: iPhone 4G
- **ky_vong_doi_thu**: Agoda — 1 dòng "Hủy miễn phí đến ..."
- **thuc_te_gap_phai**: 4 đoạn + lưu ý pháp lý dài; mốc 00:00 ngày 14/06, 19/06... Phải đọc kỹ mới hiểu hoàn bao nhiêu % cọc.
- **muc_buc**: 3
- **bo_cuoc**: khong
- **review_1_cau**: null
- **thoi_gian_mat**: ~2 phút
- **handoff_to**: uat-tester
- **handoff_reason**: UX copy — cần tóm tắt trước khi thanh toán

### EU-RAW-007: Tìm kiếm hiện "4 Khách" không tách trẻ em trên kết quả (near-miss)
- **persona_id**: EU-GUEST
- **task_id**: GST-01
- **buoc_hanh_trinh**: tim_phong
- **thiet_bi_mang**: iPhone 4G
- **ky_vong_doi_thu**: Hiển thị "2 NL, 2 trẻ" sau khi chọn ở popup
- **thuc_te_gap_phai**: Popup chọn đúng **2 người lớn, 2 trẻ em**; sau tìm kiếm filter chỉ ghi **"4 Khách"**.
- **muc_buc**: 2
- **bo_cuoc**: khong
- **review_1_cau**: null
- **thoi_gian_mat**: 10 giây thắc mắc
- **handoff_to**: business-analyst
- **handoff_reason**: Có cần hiển thị breakdown trẻ em trên search không

## Questions only I would ask

1. "21 đến 23 là 2 đêm hay 3 ngày? Sao nhân 3 lần giá?"
2. "Nhà nghỉ cuối tuần sao lại 'thuê dài hạn' và ký hợp đồng thuê nhà?"
3. "11 mét vuông có đủ 4 người ngủ không — mô tả viết 40m² là sao?"
4. "Cọc 1,1 triệu — chuyển xong biết đã nhận chưa? VietQR ở đâu?" (chưa tới bước QR)
5. "Con 4 tuổi có tính thêm phí không — sao không hỏi tuổi lúc đặt?"

## Handoff

| ID | Route to | Lý do |
|---|---|---|
| EU-RAW-001 | uat-tester | Filter/giá tháng lẫn ngắn hạn |
| EU-RAW-002 | uat-tester | Chatbot che mobile |
| EU-RAW-003 | business-analyst | M² vs mô tả sai |
| EU-RAW-004 | hospitality-expert | Đếm ngày/đêm + cọc dài hạn |
| EU-RAW-005 | hospitality-expert | Hợp đồng thuê cho booking ngắn |
| EU-RAW-006 | uat-tester | Chính sách hủy khó đọc |
| EU-RAW-007 | business-analyst | Hiển thị số khách/trẻ em |

---
**Simulated by**: End User Persona Agent (Chị Mai)
