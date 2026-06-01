# Implementation Plan: Đối soát doanh thu Admin

## Document Information
- **Plan ID:** PLAN-REC-006
- **Created:** 2026-05-31
- **Status:** ✅ DONE (Completed and Reviewed)
- **Related Design:** [docs/designs/design_006.md](../designs/design_006.md)
- **Related SRS:** [docs/SRC/srs_admin_revenue_reconciliation.md](../SRC/srs_admin_revenue_reconciliation.md)

---

## Executive Summary
Kế hoạch triển khai Module Đối soát doanh thu Admin (Model A) nhằm tự động hóa quy trình quyết toán công nợ và thu phí dịch vụ nền tảng 5% định kỳ từ đối tác (Partner). Quá trình thực hiện bao gồm việc thiết lập cấu trúc cơ sở dữ liệu mới, phát triển Job quét đơn tự động qua hàng đợi, xây dựng các API RESTful cho Admin/Partner, tích hợp tính năng xuất file bảng kê PDF/Excel và hoàn thiện giao diện người dùng trên Admin Portal và Partner Portal.

Dự án được chia thành 4 Phase tuần tự với tổng effort ước lượng khoảng **62 giờ lập trình và kiểm thử** (tương đương khoảng 8 ngày người làm việc tập trung).

---

## Phase Overview

| Phase | Name | Tasks | Dependencies | Can Parallel With | Est. Hours |
|-------|------|-------|--------------|-------------------|------------|
| 1 | Foundation (Cơ sở hạ tầng & DB) | 4 | None | - | 11 |
| 2 | Core Backend Logic (Service & Job) | 5 | Phase 1 | - | 17 |
| 3 | REST APIs & Export features | 5 | Phase 2 | - | 17 |
| 4 | Frontend UI Integration | 6 | Phase 3 | - | 21 |

---

## Dependency Graph

```text
Phase 1: Foundation
├── [T1.1] Migration DB ──────────────────────────┐
├── [T1.2] Eloquent Models ◄──────────────────────┤
├── [T1.3] Repositories ◄─────────────────────────┤ (blocks Phase 2)
└── [T1.4] Billing Config ────────────────────────┘
                                                  
Phase 2: Core Backend Logic
├── [T2.1] Generate Settlement Job ◄──────────────┤
├── [T2.2] SettlementService Logic ◄──────────────┤
├── [T2.3] RevenueReportingService ◄──────────────┤ (blocks Phase 3)
├── [T2.4] Booking Validation Lock ◄──────────────┤
└── [T2.5] Mail Event Listeners ◄─────────────────┘

Phase 3: REST APIs & Export features
├── [T3.1] Admin Settlement API ◄─────────────────┤
├── [T3.2] Partner Settlement API ◄───────────────┤ (blocks Phase 4)
├── [T3.3] Excel Export Service ◄─────────────────┤
├── [T3.4] PDF Export Service ◄───────────────────┘
└── [T3.5] Unit & Integration Tests

Phase 4: Frontend UI Integration
├── [T4.1] Admin Dashboard Charts
├── [T4.2] Admin Settlement Table UI
├── [T4.3] Admin Settlement Detail UI
├── [T4.4] Partner Finance Dashboard UI
├── [T4.5] Sync Commission 5% Copy
└── [T4.6] E2E Verification & Build
```

---

## Phase 1: Foundation (Cơ sở hạ tầng & DB)

**Goal:** Thiết lập cơ sở dữ liệu, các thực thể Eloquent, Repository tương ứng và file cấu hình phục vụ đối soát.
**Duration Estimate:** 11 giờ
**Dependencies:** Không
**Parallel With:** None

### Tasks

#### [T1.1] Migration DB đối soát ✅ DONE
- **Description:** Tạo file migration bổ sung bảng `partner_settlement_periods`, `settlement_line_items`, `settlement_adjustments` và mở rộng bảng `bookings` thêm các cột `payment_collected_at`, `settlement_period_id` (kèm foreign key và index).
- **Acceptance Criteria:**
  - [x] Migration chạy thành công không có lỗi SQL (`php artisan migrate`).
  - [x] Hỗ trợ rollback sạch sẽ (`php artisan migrate:rollback`).
  - [x] Unique key `(partner_id, period_start, period_end)` của bảng `partner_settlement_periods` hoạt động đúng.
- **Completed:** 2026-05-31
- **Files Affected:**
  - `database/migrations/2026_05_31_120001_create_settlement_tables.php` (mới)
  - `database/migrations/2026_05_31_120002_add_settlement_fields_to_bookings_table.php` (mới)
- **Dependencies:** None
- **Blocks:** [T1.2], [T2.1]
- **Test Scenarios:** Chạy migration, kiểm tra cấu trúc bảng trong DB client, thực hiện rollback và chạy lại.

#### [T1.2] Thiết lập Eloquent Models & Relations ✅ DONE
- **Description:** Tạo các Model `PartnerSettlementPeriod`, `SettlementLineItem`, `SettlementAdjustment` và đăng ký các quan hệ (relationships) Eloquent tương ứng (ví dụ: `Booking->belongsTo(PartnerSettlementPeriod)`, `PartnerSettlementPeriod->hasMany(SettlementLineItem)`).
- **Acceptance Criteria:**
  - [x] Các Model hoạt động tốt, khai báo đúng thuộc tính `$fillable` và `$casts`.
  - [x] Các relationship load dữ liệu đúng và không sinh query lỗi.
- **Completed:** 2026-05-31
- **Files Affected:**
  - `app/Models/PartnerSettlementPeriod.php` (mới)
  - `app/Models/SettlementLineItem.php` (mới)
  - `app/Models/SettlementAdjustment.php` (mới)
  - `app/Models/Booking.php` (chỉnh sửa)
- **Dependencies:** [T1.1]
- **Blocks:** [T1.3]

#### [T1.3] Xây dựng các Repositories & Đăng ký Binding ✅ DONE
- **Description:** Tạo các Interface và Class Repository tương ứng để truy vấn dữ liệu từ DB. Đăng ký bindings tại `RepositoryServiceProvider`.
- **Acceptance Criteria:**
  - [x] Repository kế thừa đúng `BaseRepository`.
  - [x] Đăng ký Service Provider thành công.
- **Completed:** 2026-05-31
- **Files Affected:**
  - `app/Repositories/Contracts/PartnerSettlementPeriodRepositoryInterface.php` (mới)
  - `app/Repositories/Eloquent/EloquentPartnerSettlementPeriodRepository.php` (mới)
  - `app/Repositories/Contracts/SettlementLineItemRepositoryInterface.php` (mới)
  - `app/Repositories/Eloquent/EloquentSettlementLineItemRepository.php` (mới)
  - `app/Repositories/Contracts/SettlementAdjustmentRepositoryInterface.php` (mới)
  - `app/Repositories/Eloquent/EloquentSettlementAdjustmentRepository.php` (mới)
  - `app/Providers/RepositoryServiceProvider.php` (chỉnh sửa)
- **Dependencies:** [T1.2]
- **Blocks:** [T2.1], [T2.2]

#### [T1.4] Tạo file cấu hình Billing ✅ DONE
- **Description:** Tạo file cấu hình `config/billing.php` để lưu trữ commission rate (0.05), số ngày phát hành đối soát (5, 20), và số ngày hạn thanh toán.
- **Acceptance Criteria:**
  - [x] Đọc được các config qua lệnh `config('billing.commission_rate')`.
- **Completed:** 2026-05-31
- **Files Affected:**
  - `config/billing.php` (mới)
- **Dependencies:** None
- **Blocks:** [T2.1], [T2.2]

---

## Phase 2: Core Backend Logic (Service & Job)

**Goal:** Hiện thực hóa logic nghiệp vụ chốt kỳ, tính toán doanh thu, và cơ chế khóa đơn.
**Duration Estimate:** 17 giờ
**Dependencies:** Phase 1 complete
**Parallel With:** None

### Tasks

#### [T2.1] Job tự động quét đơn & Tạo kỳ đối soát nháp ✅ DONE
- **Description:** Tạo Job `GenerateSettlementPeriodsJob` quét các đơn booking `COMPLETED` + `checked_out` nằm trong biên kỳ chưa được gán kỳ đối soát để tạo `draft` kỳ đối soát cho từng Partner. Đăng ký Job chạy tự động trong `app/Console/Kernel.php`.
- **Acceptance Criteria:**
  - [x] Job chạy không lỗi, gom đúng các đơn thuộc kỳ và tính tổng GMV/Commission chính xác.
  - [x] Cập nhật cột `bookings.settlement_period_id` cho các booking được gom để tránh quét trùng.
  - [x] Đăng ký Scheduler chạy đúng lịch (ngày 16 và ngày 01 hàng tháng).
- **Completed:** 2026-05-31
- **Files Affected:**
  - `app/Jobs/GenerateSettlementPeriodsJob.php` (mới)
  - `app/Console/Kernel.php` (chỉnh sửa)
- **Dependencies:** [T1.3], [T1.4]
- **Blocks:** [T2.2]

#### [T2.2] Xây dựng SettlementService ✅ DONE
- **Description:** Hiện thực hóa lớp Service `SettlementService` xử lý logic: duyệt phát hành kỳ (`issued`), xác nhận thanh toán (`confirm-paid`), xử lý khiếu nại (`dispute`), và thêm dòng điều chỉnh (`add-adjustment`).
- **Acceptance Criteria:**
  - [x] Chuyển đổi trạng thái kỳ đối soát đúng vòng đời.
  - [x] Thêm dòng điều chỉnh tính toán lại số tiền Net Commission cần thu của kỳ chính xác.
  - [x] Sử dụng Database Transaction khi cập nhật trạng thái kỳ và thanh toán.
- **Completed:** 2026-05-31
- **Files Affected:**
  - `app/Services/SettlementService.php` (mới)
- **Dependencies:** [T1.3], [T2.1]
- **Blocks:** [T3.1], [T3.2]

#### [T2.3] Xây dựng RevenueReportingService ✅ DONE
- **Description:** Phát triển lớp Service `RevenueReportingService` để tổng hợp số liệu GMV toàn hệ thống và Platform Commission 5% phục vụ vẽ biểu đồ Dashboard Admin.
- **Acceptance Criteria:**
  - [x] Trả về số GMV và Commission group theo tháng/ngày chính xác từ dữ liệu `bookings`.
- **Completed:** 2026-05-31
- **Files Affected:**
  - `app/Services/RevenueReportingService.php` (mới)
- **Dependencies:** [T1.3]
- **Blocks:** [T3.1]

#### [T2.4] Kích hoạt cơ chế khóa booking đã đối soát ✅ DONE
- **Description:** Chỉnh sửa logic cập nhật booking trong `BookingService::update` (hoặc validation class tương ứng). Nếu booking đã có `settlement_period_id` và kỳ đối soát đó không ở trạng thái `draft` hoặc `disputed`, chặn không cho cập nhật giá hoặc trạng thái đơn.
- **Acceptance Criteria:**
  - [x] Ném lỗi 403 Forbidden khi cố tình cập nhật đơn đặt phòng đã chốt đối soát.
- **Completed:** 2026-05-31
- **Files Affected:**
  - `app/Services/BookingService.php` (chỉnh sửa)
- **Dependencies:** [T1.3]
- **Blocks:** [T3.5]

#### [T2.5] Phát triển Event & Listener gửi Mail thông báo nợ phí ✅ DONE
- **Description:** Tạo các Event `SettlementPeriodIssued` và Listener gửi Mail thông báo nộp tiền phí nền tảng cho đối tác. Listener phải sử dụng Mail Queue để xử lý chạy nền.
- **Acceptance Criteria:**
  - [x] Dispatch event sau khi phát hành kỳ thành công.
  - [x] Gửi mail chạy nền thông qua hàng đợi Redis, không làm tắc nghẽn HTTP thread.
- **Completed:** 2026-05-31
- **Files Affected:**
  - `app/Events/SettlementPeriodIssued.php` (mới)
  - `app/Listeners/SendSettlementNotification.php` (mới)
  - `app/Providers/EventServiceProvider.php` (chỉnh sửa)
- **Dependencies:** [T2.2]
- **Blocks:** [T3.1]

---

## Phase 3: REST APIs & Export features

**Goal:** Cung cấp API cho Admin/Partner và các tính năng xuất báo cáo file Excel/PDF.
**Duration Estimate:** 17 giờ
**Dependencies:** Phase 2 complete
**Parallel With:** None

### Tasks

#### [T3.1] Xây dựng Admin API quản lý đối soát ✅ DONE
- **Description:** Tạo `AdminSettlementController` và định nghĩa các route API dành cho Admin: xem danh sách kỳ đối soát, xem chi tiết bảng kê, phát hành, xác nhận thanh toán, và thêm dòng điều chỉnh.
- **Acceptance Criteria:**
  - [x] Trả dữ liệu JSON đúng đặc tả.
  - [x] Các API đều được bảo vệ bằng middleware `jwt.auth` và `role:admin`.
- **Completed:** 2026-05-31
- **Files Affected:**
  - `app/Http/Controllers/Admin/AdminSettlementController.php` (mới)
  - `app/Http/Validations/SettlementValidation.php` (mới)
  - `routes/api.php` (chỉnh sửa)
- **Dependencies:** [T2.2], [T2.3], [T2.5]
- **Blocks:** [T4.1]

#### [T3.2] Xây dựng Partner API xem đối soát ✅ DONE
- **Description:** Tạo `PartnerSettlementController` và định nghĩa các route API dành cho Partner: xem danh sách kỳ của đối tác, xem chi tiết, và gửi khiếu nại (`dispute`).
- **Acceptance Criteria:**
  - [x] Validate quyền sở hữu kỳ đối soát của Partner (`partner_id === Auth::id()`).
  - [x] Lọc chính xác các kỳ đối soát theo Partner đang đăng nhập.
- **Completed:** 2026-05-31
- **Files Affected:**
  - `app/Http/Controllers/Partner/PartnerSettlementController.php` (mới)
  - `routes/api.php` (chỉnh sửa)
- **Dependencies:** [T2.2]
- **Blocks:** [T4.4]

#### [T3.3] Xây dựng tính năng xuất Excel đối soát ✅ DONE
- **Description:** Viết class Export Excel sử dụng `Maatwebsite/Laravel-Excel` cho phép xuất danh sách line items của kỳ đối soát.
- **Acceptance Criteria:**
  - [x] Tải file Excel hiển thị chính xác tên phòng, ngày check-out, room_gmv, services_gmv, total_gmv và commission.
- **Completed:** 2026-05-31
- **Files Affected:**
  - `app/Exports/SettlementLineItemsExport.php` (mới)
- **Dependencies:** [T3.2]
- **Blocks:** [T4.4]

#### [T3.4] Xây dựng tính năng xuất PDF đối soát ✅ DONE
- **Description:** Viết class render HTML view và xuất ra file PDF bảng kê sử dụng `barryvdh/laravel-dompdf`.
- **Acceptance Criteria:**
  - [x] File PDF xuất ra có định dạng đẹp mắt, đầy đủ logo BKS, thông tin kỳ, số tiền cần thu, thông tin chuyển khoản ngân hàng và các dòng điều chỉnh.
- **Completed:** 2026-05-31
- **Files Affected:**
  - `resources/views/exports/settlement_pdf.blade.php` (mới)
- **Dependencies:** [T3.2]
- **Blocks:** [T4.4]

#### [T3.5] Viết Unit & Integration Tests cho API & Services ✅ DONE
- **Description:** Tạo các file test kiểm thử tự động cho toàn bộ logic backend.
- **Acceptance Criteria:**
  - [x] Coverage test cho `SettlementService` đạt tối thiểu 90%.
  - [x] Test thành công các trường hợp: tạo kỳ đối soát nháp không trùng, validate trạng thái nợ phí, chặn sửa booking đã chốt, và bảo mật route API.
- **Completed:** 2026-05-31
- **Files Affected:**
  - `tests/Unit/Services/SettlementServiceTest.php` (mới)
  - `tests/Feature/Http/Controllers/AdminSettlementControllerTest.php` (mới)
  - `tests/Feature/Http/Controllers/PartnerSettlementControllerTest.php` (mới)
- **Dependencies:** [T3.1], [T3.2]
- **Blocks:** [T4.6]

---

## Phase 4: Frontend UI Integration

**Goal:** Tích hợp giao diện hiển thị cho Admin và Partner, đồng bộ copy hoa hồng 5%.
**Duration Estimate:** 21 giờ
**Dependencies:** Phase 3 complete
**Parallel With:** None

### Tasks

#### [T4.1] Tích hợp Biểu đồ Doanh thu Admin Dashboard ✅ DONE
- **Description:** Chỉnh sửa trang Dashboard của Admin để tích hợp biểu đồ doanh thu mới (tách biệt GMV toàn hệ thống và Platform Commission thực nhận).
- **Acceptance Criteria:**
  - [x] Hiển thị chính xác 2 đường/cột dữ liệu GMV vs Commission.
- **Completed:** 2026-05-31
- **Files Affected:**
  - `bks-system-fe/src/pages/Admin/Dashboard/index.tsx` (chỉnh sửa)
- **Dependencies:** [T3.1]
- **Blocks:** [T4.6]

#### [T4.2] Xây dựng Giao diện quản lý đối soát Admin ✅ DONE
- **Description:** Phát triển trang quản lý đối soát của Admin: bảng danh sách kỳ, bộ lọc trạng thái/đối tác/kỳ hạn, nút Phát hành bảng kê. Đồng thời, đăng ký route mới (`ROUTERS.PARTNER_SETTLEMENTS = '/admin/settlements'`) trong `src/constant.ts` và `src/Router.tsx`, và thêm menu item "Đối soát đối tác" vào sidebar Admin tại `src/components/layout/index.tsx`.
- **Acceptance Criteria:**
  - [x] Thêm link vào Sidebar Admin, click chuyển hướng đúng đến trang đối soát.
  - [x] Render danh sách kỳ phân trang chính xác.
  - [x] Nút "Phát hành" hoạt động đúng, gửi API và reload lại trạng thái dòng.
- **Completed:** 2026-05-31
- **Files Affected:**
  - `bks-system-fe/src/constant.ts` (chỉnh sửa)
  - `bks-system-fe/src/Router.tsx` (chỉnh sửa)
  - `bks-system-fe/src/components/layout/index.tsx` (chỉnh sửa)
  - `bks-system-fe/src/pages/Admin/SettlementManage/index.tsx` (mới)
- **Dependencies:** [T3.1]
- **Blocks:** [T4.3]

#### [T4.3] Xây dựng Trang chi tiết đối soát Admin ✅ DONE
- **Description:** Phát triển giao diện chi tiết kỳ đối soát của Admin: hiển thị danh sách bookings (line items), nút "Xác nhận thanh toán" (mở dialog nhập payment_ref), và khu vực thêm Adjustment (điều chỉnh công nợ).
- **Acceptance Criteria:**
  - [x] Thao tác Xác nhận thanh toán gửi API thành công và khóa kỳ.
  - [x] Nhập adjustment âm hoặc dương hiển thị dòng cập nhật tức thì và cập nhật lại Net Commission cần thu.
- **Completed:** 2026-05-31
- **Files Affected:**
  - `bks-system-fe/src/pages/Admin/SettlementManage/SettlementDetail.tsx` (mới)
- **Dependencies:** [T4.2]
- **Blocks:** [T4.6]

#### [T4.4] Xây dựng Giao diện Partner Finance Dashboard ✅ DONE
- **Description:** Phát triển giao diện Tài chính trong Partner Portal: xem nợ phí hiện tại, xem forecast kỳ này, hướng dẫn chuyển khoản ngân hàng của BKS kèm nút copy nhanh cú pháp chuyển khoản, bảng lịch sử đối soát, các nút Tải PDF/Excel và Khiếu nại.
- **Acceptance Criteria:**
  - [x] Nút copy hoạt động tốt.
  - [x] Tải file PDF/Excel tải trực tiếp từ trình duyệt.
  - [x] Bấm khiếu nại mở hộp thoại nhập lý do, chuyển trạng thái kỳ sang `disputed`.
- **Completed:** 2026-05-31
- **Files Affected:**
  - `bks-system-fe/src/pages/Partner/Finance/index.tsx` (mới)
- **Dependencies:** [T3.2], [T3.3], [T3.4]
- **Blocks:** [T4.6]

#### [T4.5] Đồng bộ copy phí hoa hồng 5% trên UI ✅ DONE
- **Description:** Chỉnh sửa copy trên FAQ đăng ký đối tác (`BecomeAPartner`) và hợp đồng mẫu onboarding (`PartnerOnboardingWizard`) đổi các con số cũ 10%, 12% thành 5% để thống nhất.
- **Acceptance Criteria:**
  - [x] Tìm kiếm toàn bộ frontend không còn các từ khóa ghi hoa hồng 10%/12%.
- **Completed:** 2026-05-31
- **Files Affected:**
  - `bks-system-fe/src/pages/EndUser/BecomeAPartner/index.tsx` (chỉnh sửa)
  - `bks-system-fe/src/pages/PartnerOnboarding/PartnerOnboardingWizard.tsx` (chỉnh sửa)
- **Dependencies:** None
- **Blocks:** [T4.6]

#### [T4.6] Chạy E2E Verification & Build dự án ✅ DONE
- **Description:** Thực hiện kiểm thử toàn trình thủ công (E2E), chạy lại toàn bộ Unit test suite backend và build dự án frontend để đảm bảo không lỗi compiler.
- **Acceptance Criteria:**
  - [x] Toàn bộ PHPUnit tests backend chạy xanh (`vendor/bin/phpunit`).
  - [x] Giao diện build thành công không lỗi cú pháp (`npm run build`).
- **Completed:** 2026-05-31
- **Files Affected:** None
- **Dependencies:** [T3.5], [T4.1], [T4.3], [T4.4], [T4.5]
- **Blocks:** None

---

## Conflict Analysis

### Identified Conflicts

| Conflict ID | Type | Description | Affected Phases | Resolution |
|-------------|------|-------------|-----------------|------------|
| C1 | Database | Thay đổi bảng `bookings` có thể gây khóa bảng khi chạy migration trên production | Phase 1 | Cần tối ưu chỉ mục (Index) và thêm cột dạng Nullable. Nếu DB quá lớn, sử dụng cơ chế migration online-schema-change. |
| C2 | Interface | Logic chặn sửa booking trong `BookingService` có thể làm ảnh hưởng các API confirm/cancel khác | Phase 2 | Chỉ kích hoạt validation gate đối với các hành động sửa đổi thủ công thông tin booking, loại trừ các hành động xác nhận tự động hoặc chuyển đổi trạng thái hủy BCP đã chốt trước đó. |
| C3 | UI | Copy hoa hồng 5% có thể bị sót ở một số view tĩnh | Phase 4 | Chạy grep search toàn dự án để tìm và sửa triệt để các số 10% / 12% liên quan đến chiết khấu đối tác. |

---

## Parallelization Opportunities

### Can Run Simultaneously
- **Nhóm A:** Phát triển Frontend UI Admin Dashboard ([T4.1]) và Quản lý đối soát Admin ([T4.2]) có thể chạy song song với việc xây dựng UI Partner Finance Dashboard ([T4.4]) ngay sau khi Phase 3 API hoàn tất.
- **Nhóm B:** Việc đồng bộ copy phí hoa hồng 5% trên UI ([T4.5]) có thể thực hiện bất kỳ lúc nào từ Phase 1.

### Must Be Sequential
1. Phase 1 (Database & Models) phải hoàn tất trước khi viết logic của Phase 2 (Service & Job).
2. Phase 2 (Backend Logic) phải chạy xong và ổn định trước khi viết API Controller ở Phase 3.
3. APIs ở Phase 3 phải được deploy/sẵn sàng chạy mock trước khi FE kết nối ở Phase 4.

---

## Risk Register

| Risk ID | Description | Likelihood | Impact | Mitigation |
|---------|-------------|------------|--------|------------|
| R1 | Partner cố tình giữ đơn ở trạng thái CONFIRMED quá hạn check-out để trốn nộp phí | Medium | Medium | Chạy Job hàng ngày quét và cảnh báo các đơn quá hạn check-out mà chưa cập nhật trạng thái. Cho phép Admin force close. |
| R2 | Lỗi nghẽn hàng đợi (Mail Queue) làm chậm tiến trình thông báo đối soát | Low | Medium | Sử dụng Redis Queue và cấu hình retry thích hợp cho Mail Jobs. |

---

## Testing Strategy

### Unit Tests
- `SettlementServiceTest`: Kiểm tra chuyển trạng thái kỳ đối soát, tính toán hoa hồng lũy kế, và cơ chế tính Adjustment.
- `GenerateSettlementPeriodsJobTest`: Kiểm tra gom đơn đúng biên kỳ, loại trừ no-show/cancel, và chống double-count.

### Integration Tests
- `AdminSettlementControllerTest`: Kiểm tra phân quyền API Admin và validate body khi thêm Adjustment hoặc confirm paid.
- `PartnerSettlementControllerTest`: Kiểm tra bảo mật endpoint, chỉ cho phép đối tác xem kỳ đối soát của chính mình.

### QC Test-case Handoff
- Output target: `docs/test-cases/testcase_006.md`
- Source: SRS + plan tasks
- Owner skill: `stack-testcase`

---

## Rollback Strategy

### Per-Phase Rollback

| Phase | Rollback Steps |
|-------|----------------|
| Phase 1 | Chạy `php artisan migrate:rollback` để xóa các bảng đối soát mới. |
| Phase 2 | Vô hiệu hóa hàng đợi Job, xóa file cấu hình `config/billing.php`, revert code `BookingService`. |
| Phase 3 | Revert các API route mới trong `routes/api.php` và xóa file controller tương ứng. |
| Phase 4 | Revert code frontend về commit stable gần nhất. |

---

## Checklist

### Before Starting Implementation
- [x] Database local đã được backup.
- [x] Biến môi trường mail server (.env) đã được cấu hình phục vụ test email.
- [x] Thư viện DOMPDF và Laravel Excel đã được cài đặt thông qua Composer.

### Phase Completion Checklist
- [x] Toàn bộ migrations chạy thành công và rollback được trên môi trường Staging.
- [x] Job quét đơn tự động đã vượt qua unit test với các biên dữ liệu ranh giới múi giờ.
- [x] API đã được bảo vệ đúng quyền bằng Middleware của Laravel.
- [x] FE build pass và kiểm thử E2E chạy thông suốt trên môi trường local.

---

## Appendix

### A. File Impact Summary

| File | Phases Modifying | Type of Change |
|------|------------------|----------------|
| `routes/api.php` | 3 | Thêm route đối soát Admin/Partner |
| `app/Services/BookingService.php` | 2 | Thêm logic khóa đơn đã đối soát |
| `bks-system-fe/src/constant.ts` | 4 | Đăng ký route constant mới cho đối soát |
| `bks-system-fe/src/Router.tsx` | 4 | Cấu hình Route mới `/admin/settlements` |
| `bks-system-fe/src/components/layout/index.tsx` | 4 | Thêm link menu vào Sidebar Admin |
| `bks-system-fe/src/pages/Admin/Dashboard/index.tsx` | 4 | Thêm biểu đồ Platform Commission |
| `bks-system-fe/src/pages/EndUser/BecomeAPartner/index.tsx` | 4 | Đổi copy hoa hồng 5% |

### B. Task Quick Reference

| Task ID | Name | Phase | Dependencies | Est. Hours |
|---------|------|-------|--------------|------------|
| T1.1 | Migration DB đối soát | 1 | None | 3 |
| T1.2 | Thiết lập Eloquent Models & Relations | 1 | T1.1 | 3 |
| T1.3 | Xây dựng các Repositories | 1 | T1.2 | 3 |
| T1.4 | Tạo file cấu hình Billing | 1 | None | 2 |
| T2.1 | Job tự động quét đơn | 2 | T1.3, T1.4 | 4 |
| T2.2 | Xây dựng SettlementService | 2 | T1.3, T2.1 | 4 |
| T2.3 | Xây dựng RevenueReportingService | 2 | T1.3 | 3 |
| T2.4 | Kích hoạt cơ chế khóa booking | 2 | T1.3 | 3 |
| T2.5 | listener gửi Mail thông báo nợ phí | 2 | T2.2 | 3 |
| T3.1 | Xây dựng Admin API quản lý đối soát | 3 | T2.2, T2.3, T2.5 | 4 |
| T3.2 | Xây dựng Partner API xem đối soát | 3 | T2.2 | 3 |
| T3.3 | Xây dựng tính năng xuất Excel | 3 | T3.2 | 3 |
| T3.4 | Xây dựng tính năng xuất PDF | 3 | T3.2 | 3 |
| T3.5 | Viết Unit & Integration Tests | 3 | T3.1, T3.2 | 4 |
| T4.1 | Tích hợp Biểu đồ Doanh thu Admin | 4 | T3.1 | 4 |
| T4.2 | Giao diện quản lý đối soát Admin | 4 | T3.1 | 4 |
| T4.3 | Trang chi tiết đối soát Admin | 4 | T4.2 | 4 |
| T4.4 | Giao diện Partner Finance Dashboard | 4 | T3.2, T3.3, T3.4 | 4 |
| T4.5 | Đồng bộ copy phí hoa hồng 5% | 4 | None | 2 |
| T4.6 | Chạy E2E Verification & Build | 4 | T3.5, T4.1, T4.3, T4.4, T4.5 | 3 |
