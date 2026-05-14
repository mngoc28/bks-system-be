# API Coding Rules & Guidelines - Standard Backend Blueprint

Tài liệu này tổng hợp các quy tắc và tiêu chuẩn lập trình API chuẩn hóa, được thiết kế để áp dụng nhất quán cho các dự án Backend (Laravel). Mục tiêu là cung cấp một bản hướng dẫn chung để các Agent AI và lập trình viên có thể phối hợp hiệu quả.

---

## 1. Cấu trúc Thư mục & Phân lớp (Layered Architecture)

Dự án tuân thủ mô hình layered architecture để tách biệt logic. Cấu trúc thư mục được phân chia theo vai trò người dùng (Role-based) để đảm bảo tính mở rộng:

```text
├── app/
│   ├── Enums/            # Định nghĩa các hằng số, trạng thái (HttpStatus, UserType...)
│   ├── Exceptions/       # Xử lý ngoại lệ (BusinessException, Custom Handlers)
│   ├── Helpers/          # Các hàm phụ trợ dùng chung (Global functions)
│   ├── Http/
│   │   ├── Controllers/  # Tiếp nhận Request, điều phối Logic
│   │   │   ├── [Role_1]/ # Ví dụ: Admin/
│   │   │   ├── [Role_2]/ # Ví dụ: Partner/
│   │   │   └── [Role_n]/ # Ví dụ: Customer/
│   │   ├── Middleware/   # Các bộ lọc Request (JWT, CheckRole, Logging...)
│   │   ├── Resources/    # Transform Model sang JSON (API Resources)
│   │   └── Validations/  # Chứa các class Validate dữ liệu (custom validation)
│   ├── Models/           # Eloquent Models & Relationships
│   ├── Providers/        # Đăng ký Service, Repository Binding
│   ├── Repositories/     # Tầng truy vấn dữ liệu (DB Logic)
│   │   ├── [Role_1]/     # Repository logic dành riêng cho Role 1
│   │   ├── [Role_2]/     # Repository logic dành riêng cho Role 2
│   │   ├── BaseRepositoryInterface.php
│   │   └── BaseRepository.php
│   ├── Services/         # Tầng chứa Logic nghiệp vụ (Business Logic)
│   │   ├── [Role_1]/     # Xử lý nghiệp vụ Role 1
│   │   ├── [Role_2]/     # Xử lý nghiệp vụ Role 2
│   │   └── BaseService.php
│   ├── Traits/           # Các đặc tính tái sử dụng (ApiResponser...)
├── config/               # Cấu hình hệ thống (auth, database, services...)
├── database/
│   ├── migrations/       # Cấu trúc bảng DB
│   ├── seeders/          # Dữ liệu mẫu (Master data)
│   └── factories/        # Tạo dữ liệu giả lập cho testing
├── lang/                 # Đa ngôn ngữ (vi, en) - Chứa các file translation
├── routes/
│   └── api.php           # Khai báo tất cả các Route API
└── tests/                # Unit & Feature Tests
```

- **Controllers**: Chỉ tiếp nhận Request, gọi Service và trả về Response. Phân chia theo role người dùng để tránh phình to code.
- **Services**: Nơi xử lý logic nghiệp vụ chính. Việc phân chia theo role giúp quản lý các quy tắc nghiệp vụ đặc thù của từng đối tượng người dùng.
- **Repositories**: Tầng duy nhất tương tác với Model/DB. Phân chia theo role khi logic truy vấn dữ liệu của các role khác nhau đáng kể (ví dụ: Quyền xem dữ liệu scoped theo chủ sở hữu).
- **Validations**: Tách biệt logic kiểm tra dữ liệu đầu vào (Rules & Messages) khỏi Controller.

---

## 2. Versioning

- Tất cả API phải đặt trong prefix version: `/api/v1/...`
- Khi có breaking changes, tăng version: `/api/v2/...`
- Các version cũ cần được duy trì trong thời gian chuyển tiếp.
- Khai báo trong `routes/api.php`:

```php
Route::group(['prefix' => 'v1'], function () {
    // Tất cả routes của phiên bản v1
});
```

---

## 3. Naming Convention

### URL
- Sử dụng **kebab-case** cho path segments: `/user-profiles`, `/order-history`, `/property-images`.
- Sử dụng **danh từ số nhiều** cho resource: `/users`, `/properties`, `/bookings`.
- Nhóm theo vai trò (Role-based prefix):
    - `/api/v1/admin/...`: Quản trị viên (admin).
    - `/api/v1/partner/...`: Đối tác (partner).
    - `/api/v1/[role]/...`: Mở rộng theo role của dự án.
    - `/api/v1/common/...`: API dùng chung.
    - `/api/v1/auth/...`: Login, Register, Reset Password.

### Controller Methods (Resourceful)
| Action        | HTTP Method | URL                         |
|---------------|-------------|-----------------------------|
| index         | GET         | `/resources`                |
| show          | GET         | `/resources/{id}`           |
| store         | POST        | `/resources`                |
| update        | PUT/PATCH   | `/resources/{id}`           |
| destroy       | DELETE      | `/resources/{id}`           |

### File & Class Naming
- **Controllers**: `[Name]Controller.php` — `UserController.php`
- **Services**: `[Name]Service.php` — `UserService.php`
- **Repositories**: `[Name]Repository.php` — `UserRepository.php`
- **Interfaces**: `[Name]RepositoryInterface.php` — `UserRepositoryInterface.php`
- **Models**: `[Name].php` (PascalCase, số ít) — `User.php`, `PropertyImage.php`
- **Validation Classes**: `[Name]Validation.php` — `UserValidation.php`

### Code Convention
- **JSON Keys / DB Columns**: `snake_case` — `first_name`, `created_at`.
- **PHP Variables**: `camelCase` — `$userId`, `$propertyData`.
- **Constants (Enum/Config)**: `UPPER_SNAKE_CASE` — `DEFAULT_PER_PAGE`.

---

## 4. Auth Guard & Middleware

### Cơ chế xác thực
- Sử dụng **JWT** (JSON Web Token) hoặc **Sanctum** tùy dự án.
- Token phải được gửi qua header: `Authorization: Bearer <token>`.
- Access Token có thời hạn ngắn, sử dụng Refresh Token để gia hạn.

### Middleware
- `jwt.auth` (hoặc `auth:api`): Yêu cầu xác thực token.
- `role:[role_name]`: Kiểm tra vai trò người dùng, đọc từ field `role` trong model User.

```php
// Route công khai - không cần auth
Route::post('auth/login', [AuthController::class, 'login']);

// Route yêu cầu xác thực
Route::middleware('jwt.auth')->group(function () {
    Route::get('profile', [UserController::class, 'show']);
});

// Route yêu cầu xác thực + kiểm tra role
Route::middleware(['jwt.auth', 'role:admin'])->group(function () {
    Route::apiResource('users', UserController::class);
});
```

### Auth Guard Flow
```
Request -> jwt.auth Middleware:
   ├── Token hợp lệ  → Tiếp tục → role Middleware:
   │                               ├── Role khớp → Request được xử lý ✓
   │                               └── Role không khớp → 401 Unauthorized ✗
   ├── Token hết hạn → 401 Token expired ✗
   └── Không có Token → 401 Token required ✗
```

### RoleMiddleware Implementation
Middleware `role` hỗ trợ nhiều role cùng lúc bằng dấu phân cách `,`:
```php
Route::middleware(['jwt.auth', 'role:admin,partner'])->group(...);
```

---

## 5. Pagination

### Query Parameters chuẩn
Tất cả các API trả về danh sách phải hỗ trợ các query params sau:

| Param           | Kiểu    | Mặc định | Mô tả                                             |
|-----------------|---------|----------|---------------------------------------------------|
| `page`          | int     | `1`      | Trang hiện tại                                    |
| `per_page`      | int     | `10`     | Số bản ghi mỗi trang                             |
| `q`             | string  | `null`   | Từ khoá tìm kiếm (tìm trên nhiều trường)         |
| `sort_field`    | string  | `id`     | Trường để sắp xếp                                |
| `sort_direction`| string  | `desc`   | Chiều sắp xếp: `asc` hoặc `desc`                 |

### Giá trị mặc định
Khai báo trong `config/const.php`:
```php
'DEFAULT_PAGE'     => 1,
'DEFAULT_PER_PAGE' => 10,
```

### Sử dụng trong Repository
```php
$page    = (int) ($request->input('page', config('const.DEFAULT_PAGE', 1)));
$perPage = (int) ($request->input('per_page', config('const.DEFAULT_PER_PAGE', 10)));

return $query->paginate($perPage, ['*'], 'page', $page);
```

### Response Format cho danh sách có phân trang
```json
{
    "status": "success",
    "message": "Lấy danh sách thành công",
    "data": {
        "current_page": 1,
        "data": [ ... ],
        "per_page": 10,
        "total": 100,
        "last_page": 10,
        "from": 1,
        "to": 10
    }
}
```

### Lưu ý
- **Whitelist** các trường được phép sắp xếp (`sort_field`) trong Repository để tránh SQL Injection.
- Append query params vào pagination links: `->appends($request->query())`.
- Không dùng `simplePaginate` cho các API có yêu cầu hiển thị tổng số trang/bản ghi.

---

## 3. Quy tắc Phản hồi (Response Format)

Tất cả các Controller phải sử dụng trait chuẩn (ví dụ: `ApiResponser`) để đảm bảo format đồng nhất.

### Thành công (Success)
```json
{
    "status": "success",
    "message": "Thông báo thành công",
    "data": { ... }
}
```
- Sử dụng `successResponse($data, $message, $code)` (Default Code: 200).
- Sử dụng `createdResponse($data, $message)` (Default Code: 201).

### Lỗi (Error)
```json
{
    "status": "error",
    "message": "Thông báo lỗi chi tiết",
    "code": "ERROR_INTERNAL_CODE",
    "data": null
}
```
- Sử dụng `errorResponse($message, $err_code, $status_code)`.

### Lỗi Validate (Validation Error)
```json
{
    "status": "error",
    "errors": {
        "field_name": ["Thông báo lỗi 1", "Thông báo lỗi 2"]
    },
    "code": "VALIDATION_FAILED"
}
```
- Trả về mã lỗi **422 Unprocessable Entity**.

---

## 4. Authentication & Phân quyền

- **Authentication**: Sử dụng **JWT (JSON Web Token)** hoặc **Sanctum** tùy dự án.
- **Middleware**: Luôn bọc các API bảo mật trong middleware xác thực (ví dụ: `auth:api`, `jwt.auth`).
- **Phân quyền (RBAC)**: Sử dụng middleware kiểm tra role (ví dụ: `role:admin`).
- **Xác thực tự động**: Ưu tiên nhóm các route có cùng cơ chế bảo mật vào trong Route Group.

---

## 5. Quy tắc Validation

- Tuyệt đối không validate trực tiếp trong Controller.
- Khuyến nghị sử dụng **Form Request** hoặc **Validation Classes** riêng biệt.
- Luôn cung cấp custom message thông qua hệ thống Localization (`lang/`) để hỗ trợ đa ngôn ngữ.

---

## 6. Xử lý Lỗi (Exception Handling)

- **BusinessException**: Tạo custom exception để ném ra các lỗi nghiệp vụ dự kiến.
- **Global Handler**: Quản lý tập trung tại `app/Exceptions/Handler.php` để đảm bảo kết quả trả về luôn là JSON cho các API request.
- **Logging**: Log lỗi kèm theo context (user_id, request_data, trace) để phục vụ giám sát và debug.

---

## 7. Quy tắc Database & Model

- **Soft Deletes**: Áp dụng cho dữ liệu nghiệp vụ quan trọng để tránh xóa vĩnh viễn dữ liệu người dùng.
- **Mass Assignment**: Luôn khai báo `$fillable` hoặc `$guarded`.
- **Security**: Ẩn các trường nhạy cảm trong `$hidden` (password, token...).
- **Casts**: Sử dụng Eloquent Attribute Casts để định dạng dữ liệu tự động (datetime, boolean, json).
- **Naming**: Bảng đặt tên số nhiều (users), Khóa ngoại sử dụng `singular_model_name_id`.

---

## 8. Localization (Đa ngôn ngữ)

- Mọi chuỗi văn bản (message) trả về cho client phải được quản lý trong thư mục `lang/`.
- Sử dụng helper `__()` hoặc `trans()` để lấy nội dung.

---

## 9. Tiêu chuẩn Code & Chất lượng

- **Code Style**: Tuân thủ PSR-12.
- **Strict Typing**: Khuyến khích sử dụng `declare(strict_types=1);` và type hinting cho tham số/giá trị trả về.
- **Documentation**: Comment đầy đủ DocBlock cho các hàm phức tạp.
- **Performance**: 
    - Tránh N+1 query bằng cách sử dụng `with()`.
    - Sử dụng `Eloquent Resources` để lọc dữ liệu trả về, tránh Over-fetching.
- **Security**: 
    - Luôn lọc và xác thực dữ liệu đầu vào.
    - Không bao giờ lưu mật khẩu dưới dạng plain text (Sử dụng `Hash::make`).

---

## 10. Quy tắc Chú thích Code (Commenting Rules)

Việc chú thích code rõ ràng giúp các Agent AI và lập trình viên hiểu nhanh nội dung mà không cần phân tích sâu từng dòng lệnh.

### DocBlocks (PHPDoc)
Bắt buộc sử dụng cho mọi **Class, Method, và Property**:
- **Mô tả**: Tóm tắt ngắn gọn chức năng của đối tượng.
- **@param**: Xác định rõ kiểu dữ liệu và ý nghĩa của các tham số.
- **@return**: Xác định kiểu dữ liệu trả về. Đối với mảng (array), nên mô tả cấu trúc mảng nếu có thể (ví dụ: `array{success: bool, data: mixed}`).
- **@throws**: Liệt kê các Exception có thể ném ra.

Ví dụ:
```php
/**
 * Lấy thông tin chi tiết người dùng
 *
 * @param int $id
 * @return array{status: string, data: User}
 * @throws ModelNotFoundException
 */
public function getUserDetail(int $id): array { ... }
```

### Chú thích trong Code (Inline Comments)
- **Hạn chế**: Không chú thích những điều hiển nhiên.
- **Giải thích Logic**: Giải thích "Tại sao" thay vì "Làm gì" cho các đoạn xử lý phức tạp.
- **Ngôn ngữ**: Khuyến nghị sử dụng **Tiếng Anh** để Agent AI xử lý tốt nhất, hoặc tuân thủ ngôn ngữ chung của dự án.

### Phân cách khối code
Sử dụng các thanh ngăn cách để chia nhóm chức năng trong các file dài:
```php
// =========================================================================
// AUTHENTICATION METHODS
// =========================================================================
```
