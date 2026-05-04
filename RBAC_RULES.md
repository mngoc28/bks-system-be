# 🛡️ Hệ Thống Phân Quyền Backend (RBAC Rules) — BKS System

Tài liệu này chi tiết hóa toàn bộ cơ chế Phân quyền dựa trên vai trò (Role-Based Access Control - RBAC) đang được áp dụng trong dự án `bks-system-be`.

---

## 1. TỔNG QUAN KIẾN TRÚC
Hệ thống sử dụng mô hình bảo mật 3 lớp (Layered Security):
1.  **Lớp Middleware (Route Level)**: Chặn truy cập dựa trên tiền tố URL và vai trò cơ bản.
2.  **Lớp Authorization Logic (Middleware Level)**: Xử lý logic kiểm tra vai trò từ Payload JWT.
3.  **Lớp Policy (Data Level)**: Kiểm tra quyền sở hữu dữ liệu (Owner-based) trước khi thực hiện CRUD.

---

## 2. DANH SÁCH VAI TRÒ (ROLES)
Được định nghĩa trong `app/Enums/UserType.php`:

| Role | Định danh | Phạm vi truy cập |
| :--- | :--- | :--- |
| **Admin** | `admin` | Toàn quyền hệ thống (System-wide). |
| **Partner** | `partner` | Quản lý Tòa nhà & Phòng thuộc sở hữu của mình. |
| **User** | `user` | Người dùng đã thuê phòng (Stay Portal). |
| **Public** | *Anonymous* | Tìm kiếm phòng, xem tin tức, đăng ký/đăng nhập. |

> [!NOTE]
> Hệ thống có hỗ trợ mở rộng vai trò `staff` (nhân viên) trong tương lai thông qua logic Policy.

---

## 3. CƠ CHẾ XÁC THỰC (AUTHENTICATION)
- **Công nghệ**: JSON Web Token (JWT) thông qua thư viện `tymon/jwt-auth`.
- **Guards**: `api` (cho user/partner) và `admin` (cho quản trị viên).
- **Flow**: User Login → Nhận Token → Đính kèm Token vào Header `Authorization: Bearer <token>` cho mọi request sau đó.

---

## 4. CHI TIẾT CÁC LỚP CHẶN TRUY CẬP

### 4.1. Chặn tại Route Middleware
Toàn bộ routes trong `routes/api.php` được nhóm theo vai trò để dễ quản lý:

```php
// Routes cho Admin
Route::middleware(['jwt.auth', 'role:admin'])->prefix('admin')->group(function () {
    // API Quản lý User, System Statistics, Global Config...
});

// Routes cho Partner
Route::middleware(['jwt.auth', 'role:partner'])->prefix('partner')->group(function () {
    // API Quản lý Building, Room của đối tác...
});

// Routes cho Người thuê (Stay)
Route::middleware(['jwt.auth'])->prefix('stay')->group(function () {
    // API Xem hợp đồng, Order dịch vụ, Thông báo cá nhân...
});
```

### 4.2. Logic RoleMiddleware
File: `app/Http/Middleware/RoleMiddleware.php`
```php
public function handle(Request $request, Closure $next, ...$roles) {
    $user = Auth::guard('api')->user();
    // Chuyển danh sách roles truyền từ route thành mảng để check
    if (! in_array($user->role, $allowedRoles)) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }
    return $next($request);
}
```

### 4.3. Chặn tại Controller (Data-level Security)
Sử dụng **Laravel Policies** để đảm bảo người dùng không can thiệp vào dữ liệu của người khác.

- **File**: `app/Policies/` (ví dụ `BuildingPolicy`, `UserPolicy`)
- **Cách gọi**: `$this->authorize('action', $model)` trong Controller.

**Ví dụ thực tế:**
- Một `Partner` có thể truy cập route `/partner/buildings/{id}` (đã qua middleware).
- Tuy nhiên, trong `BuildingController@update`, Policy sẽ kiểm tra nếu `building->user_id == auth()->id()`. Nếu không, sẽ trả về `403 Forbidden`.

---

## 5. MA TRẬN PHÂN QUYỀN CHI TIẾT (PERMISSION MATRIX)

| Module | Hành động | Public | User | Partner | Admin |
| :--- | :--- | :---: | :---: | :---: | :---: |
| **Auth** | Login / Register / Reset Pass | ✅ | ✅ | ✅ | ✅ |
| **User** | Xem/Sửa Profile cá nhân | ❌ | ✅ | ✅ | ✅ |
| | Quản lý toàn bộ người dùng | ❌ | ❌ | ❌ | ✅ |
| **Building** | Tìm kiếm & Xem công khai | ✅ | ✅ | ✅ | ✅ |
| | Tạo mới / Sửa / Xóa | ❌ | ❌ | ✅ (Của mình) | ✅ (Tất cả) |
| **Room** | Xem chi tiết phòng | ✅ | ✅ | ✅ | ✅ |
| | Quản lý phòng (CRUD) | ❌ | ❌ | ✅ (Của mình) | ✅ (Tất cả) |
| **Booking** | Đặt phòng | ✅ | ✅ | ❌ | ✅ |
| | Xác nhận / Hủy Booking | ❌ | ❌ | ✅ (Của mình) | ✅ (Tất cả) |
| **Service** | Xem danh sách dịch vụ | ✅ | ✅ | ✅ | ✅ |
| | Order dịch vụ (Stay portal) | ❌ | ✅ | ❌ | ❌ |
| | Quản lý danh mục dịch vụ | ❌ | ❌ | ❌ | ✅ |
| **Report** | Xem Dashboard / Thống kê | ❌ | ❌ | ✅ (Của mình) | ✅ (Toàn cục) |

---

## 6. XỬ LÝ LỖI (EXCEPTION HANDLING)
Hệ thống trả về các mã lỗi chuẩn RESTful:
- `401 Unauthorized`: Token không hợp lệ hoặc hết hạn.
- `403 Forbidden`: Vai trò không đủ quyền hoặc không phải chủ sở hữu dữ liệu.
- `404 Not Found`: API hoặc tài nguyên không tồn tại.

---

## 7. QUY TRÌNH THÊM QUYỀN MỚI
1.  **Bước 1**: Khai báo route trong `routes/api.php` và gán Middleware tương ứng.
2.  **Bước 2**: Tạo/Cập nhật Policy trong `app/Policies` để định nghĩa logic sở hữu dữ liệu.
3.  **Bước 3**: Gọi `$this->authorize()` ở đầu method trong Controller.
4.  **Bước 4**: Cập nhật Localization message trong `lang/vi/auth.php`.
