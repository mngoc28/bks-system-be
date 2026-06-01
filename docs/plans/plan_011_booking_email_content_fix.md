# Plan 011 — Cải Thiện Nội Dung Email Xác Nhận Đặt Phòng

**Ngày thực hiện:** 2026-06-01  
**Trạng thái:** ✅ Hoàn thành

---

## Bối cảnh

Email xác nhận đặt phòng (`room-booking.blade.php`) được viết khi hệ thống còn ở giai
đoạn đầu, tham khảo từ mô hình "báo giá" (quote email) của website lưu trú Nhật Bản.
Sau khi hệ thống trưởng thành hơn (conflict check tự động, grace period, cancellation
policy từ DB), nội dung email không còn phản ánh đúng thực tế nghiệp vụ.

---

## Vấn đề được giải quyết

| # | Vấn đề | File |
|---|---|---|
| 1 | `<title>` và header dùng ngôn ngữ "báo giá" | `room-booking.blade.php` |
| 2 | Lời chào nói "phòng sẵn sàng và có thể đặt" — sai, phòng đã được đặt rồi | `room-booking.blade.php` |
| 3 | Lưu ý "có thể đã kín chỗ" — sai nghiệp vụ | `room-booking.blade.php` |
| 4 | Thiếu **deadline nộp cọc** cụ thể | `BookingService.php` + template |
| 5 | `estimate_deadline` hardcode +7 ngày — sai hoàn toàn so với grace period thực | `BookingService.php` |
| 6 | Thiếu **chính sách hủy phòng** trong email | `BookingService.php` + template |
| 7 | Countdown trên `BookingSuccess` page cứng 2h, không khớp với logic 2h/12h | `BookingSuccess/index.tsx` |

---

## Thay đổi chi tiết

### `BookingService.php`

**Field `estimate_deadline` → `deposit_deadline`:**
```
// Cũ (sai):
'estimate_deadline' => Carbon::now()->addDays(7)->format('d/m/Y'),

// Mới (đúng):
'deposit_deadline'  => $this->computeDepositDeadline($startDate, Carbon::now()),
```

**Logic tính grace period** (đồng bộ với FE `BookingDetail.tsx` line 216):
- Nếu `startDate - createdAt <= 48h` → grace **2 giờ**
- Còn lại → grace **12 giờ**

**Field mới `cancellation_policy`:**  
Gọi `formatCancellationPolicyForEmail()` — query `CancellationPolicyTier` theo
`stay_kind` (short/long) từ version config `bcp.baseline_policy_version`,
format thành bảng HTML inline.

### `room-booking.blade.php`

| Phần | Trước | Sau |
|---|---|---|
| `<title>` | "Báo giá và thủ tục đăng ký" | "Xác nhận đặt phòng - BKS Stay" |
| Header sub | "Báo giá và thủ tục đăng ký đặt phòng" | "Xác nhận đặt phòng tại hệ thống BKS Stay" |
| Lời chào | "phòng sẵn sàng và có thể đặt" | "đặt phòng đã được ghi nhận thành công" |
| Block mới | — | ⏰ Hạn nộp cọc (hiển thị `deposit_deadline`) |
| Block mới | — | Chính sách hủy phòng (hiển thị `cancellation_policy`) |
| Lưu ý cũ | "có thể đã kín chỗ, số tiền có thể thay đổi" | "Phòng đã được giữ cho bạn" |

### `BookingSuccess/index.tsx`

- Thêm `createdAt?: string` vào `BookingSuccessState` type
- Thay `useState(2 * 60 * 60)` cứng bằng `computeInitialGraceSeconds()` tính
  động theo `startDate` và `createdAt` từ navigation state

---

## Lưu ý triển khai

- Nơi navigate đến `BookingSuccess` (trong booking flow) cần truyền thêm
  `createdAt: booking.created_at` vào `location.state` để countdown hoạt động
  chính xác ngay từ đầu.
- Nếu `createdAt` không có, countdown fallback về 2h (safe default).
- `cancellation_policy` được tạo từ DB tại thời điểm gửi email, nếu chưa có
  tier nào trong DB thì hiển thị text dự phòng "Liên hệ hỗ trợ".
