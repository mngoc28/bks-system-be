# 🤖 AGENT BLUEPRINT — Laravel Layered Architecture Template
> **Mục đích**: Tài liệu này được viết để AI Agent đọc và tự động sinh code cho dự án Laravel mới theo đúng kiến trúc và phong cách của dự án `bks-system-be`.  
> **Không được thay đổi phong cách code, cấu trúc thư mục, hay naming convention** được mô tả trong tài liệu này.

---

## PHẦN 0 — TỔNG QUAN KIẾN TRÚC

Dự án sử dụng **Layered Architecture** (Controller → Service → Repository → Model):

```
HTTP Request
    │
    ▼
[Controller]       → Nhận Request, validate, gọi Service, trả về Response
    │
    ▼
[Service]          → Xử lý Business Logic, gọi Repository, bắt Exception
    │
    ▼
[Repository]       → Tầng duy nhất tương tác với Eloquent Model / DB
    │
    ▼
[Model]            → Eloquent ORM, khai báo $fillable, relationships, casts
```

**Stack bắt buộc:**
- PHP 8.1+, Laravel 10+
- JWT Auth (`tymon/jwt-auth`) — KHÔNG dùng Sanctum
- `declare(strict_types=1)` trong mọi Repository và Interface
- PSR-12 code style
- Localization: tất cả message dùng `__('domain.key')`

---

## PHẦN 1 — CẤU TRÚC THƯ MỤC BẮT BUỘC

```
app/
├── Enums/
│   ├── HttpStatus.php                  ← PHẢI có, dùng cho mọi response
│   └── [DomainEnum].php               ← Enum cho từng domain
├── Exceptions/
│   ├── BusinessException.php           ← PHẢI có
│   └── Handler.php                     ← Global exception handler
├── Helpers/                            ← Hàm helper dùng chung
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php              ← Base Controller (use ApiResponser)
│   │   ├── [Role]/                     ← Phân theo vai trò: Admin/, Partner/, EU/
│   │   │   └── [Name]Controller.php
│   │   └── [Name]Controller.php        ← Controller không phân role
│   ├── Middleware/
│   │   ├── JwtAuthMiddleware.php
│   │   └── RoleMiddleware.php
│   ├── Resources/
│   │   └── [Name]Resource.php
│   └── Validations/
│       └── [Name]Validation.php        ← KHÔNG validate trong Controller
├── Models/
│   └── [Name].php
├── Providers/
│   └── RepositoryServiceProvider.php   ← Đăng ký Interface → Implementation
├── Repositories/
│   ├── RepositoryInterface.php         ← Base interface (PHẢI có)
│   ├── BaseRepository.php              ← Base class (PHẢI có)
│   └── [Name]Repository/
│       ├── [Name]Repository.php
│       └── [Name]RepositoryInterface.php
├── Services/
│   └── [Name]Service.php
└── Traits/
    └── ApiResponser.php                ← PHẢI có, dùng cho Controller
routes/
└── api.php                             ← Toàn bộ routes, nhóm theo role
lang/
├── en/
│   └── [domain].php
└── vi/
    └── [domain].php
config/
└── const.php                           ← Hằng số toàn hệ thống
```

---

## PHẦN 2 — CODE TEMPLATE (COPY Y CHANG)

### 2.1 — Enum: HttpStatus
**File:** `app/Enums/HttpStatus.php`
```php
<?php

namespace App\Enums;

enum HttpStatus: int
{
    case OK = 200;
    case CREATED = 201;
    case NO_CONTENT = 204;
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
    case VALIDATION_ERROR = 422;
    case INTERNAL_SERVER_ERROR = 500;
}
```

---

### 2.2 — Trait: ApiResponser
**File:** `app/Traits/ApiResponser.php`
```php
<?php

namespace App\Traits;

use App\Enums\HttpStatus;

trait ApiResponser
{
    protected function successResponse($data, $message = null, $code = HttpStatus::OK)
    {
        return response()->json([
            "status"  => "success",
            "message" => $message,
            "data"    => $data,
        ], $code->value);
    }

    protected function createdResponse($data, $message = null, $code = HttpStatus::CREATED)
    {
        return response()->json([
            "status"  => "success",
            "message" => $message,
            "data"    => $data,
        ], $code->value);
    }

    protected function errorResponse($message = null, $err_code = null, $code = HttpStatus::BAD_REQUEST, $data = null)
    {
        return response()->json([
            "status"  => "error",
            "message" => $message,
            "code"    => $err_code,
            "data"    => $data,
        ], $code->value);
    }

    protected function validateError($message = null, $err_code = null, HttpStatus $code = HttpStatus::VALIDATION_ERROR, $data = null)
    {
        return response()->json([
            "status" => "error",
            "errors" => $message,
            "code"   => $err_code,
        ], $code->value);
    }

    protected function forbiddenResponse($message = null, $err_code = null, HttpStatus $code = HttpStatus::FORBIDDEN, $data = null)
    {
        return response()->json([
            "status"  => "error",
            "message" => $message,
            "code"    => $err_code,
            "data"    => $data,
        ], $code->value);
    }
}
```

---

### 2.3 — Base Controller
**File:** `app/Http/Controllers/Controller.php`
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Traits\ApiResponser;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;
    use ApiResponser;
}
```

---

### 2.4 — BusinessException
**File:** `app/Exceptions/BusinessException.php`
```php
<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
use Exception;
use Illuminate\Support\Facades\Lang;

class BusinessException extends Exception
{
    use ApiResponser;

    public function render($request)
    {
        $errorCode = $this->getMessage();
        $message = Lang::get("messages." . $errorCode);
        return $this->errorResponse($message, $errorCode);
    }
}
```

---

### 2.5 — Exception Handler
**File:** `app/Exceptions/Handler.php`
```php
<?php

namespace App\Exceptions;

use App\Enums\HttpStatus;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    protected $dontFlash = ['current_password', 'password', 'password_confirmation'];

    public function register()
    {
        $this->reportable(function (Throwable $e) {});
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            if ($exception instanceof TokenInvalidException) {
                return $this->jsonError(__('auth.token_invalid'), HttpStatus::UNAUTHORIZED);
            }
            if ($exception instanceof TokenExpiredException) {
                return $this->jsonError(__('auth.token_expired'), HttpStatus::UNAUTHORIZED);
            }
            if ($exception instanceof JWTException) {
                return $this->jsonError(__('auth.token_required'), HttpStatus::UNAUTHORIZED);
            }
            if ($exception instanceof AuthenticationException) {
                return $this->jsonError(__('auth.unauthenticated'), HttpStatus::UNAUTHORIZED);
            }
            if ($exception instanceof RouteNotFoundException || $exception instanceof NotFoundHttpException) {
                return $this->jsonError($exception->getMessage() ?: __('auth.route_not_found'), HttpStatus::NOT_FOUND);
            }
            if ($exception instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => __('auth.invalid_data'),
                    'errors'  => $exception->errors(),
                ], HttpStatus::VALIDATION_ERROR->value);
            }
            return $this->jsonError(
                $exception->getMessage() ?: __('auth.general_error'),
                HttpStatus::INTERNAL_SERVER_ERROR
            );
        }
        return parent::render($request, $exception);
    }

    private function jsonError(string $message, HttpStatus $statusCode)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
        ], $statusCode->value);
    }
}
```

---

### 2.6 — RepositoryInterface (Base)
**File:** `app/Repositories/RepositoryInterface.php`
```php
<?php

declare(strict_types=1);

namespace App\Repositories;

interface RepositoryInterface
{
    public function all();
    public function find($id);
    public function findOnlyColumn($id, $columns = ['*']);
    public function first();
    public function create($attributes = []);
    public function insert(array $attributes);
    public function update($id, $attributes = []);
    public function delete($id);
    public function show($id);
    public function getQuery();
    public function clearQuery();
    public function findBy(array $filter, bool $toArray = true);
    public function findOneBy(array $filter, bool $toArray = true);
    public function paginate($page);
    public function updateWhere(array $attributes = [], array $params = []): void;
    public function updateOrCreate(array $attributes = [], array $params = []): void;
    public function deleteBy(array $filter): void;
    public function findWhereIn(array $filter, bool $toArray = true);
    public function deleteWhereIn(array $filter): void;
    public function countRecord(array $filter = []): int;
    public function findByIds(array $ids, array $filter = [], bool $returnOnlyIds = false): array;
    public function updateWhereIn(string $column, array $values, array $attributes, array $whereConditions = []): void;
    public function updateWhereNotIn(string $column, array $values, array $attributes, array $whereConditions = []): void;
    public function deleteNotInIds(string $columnName, int $value, array $ids, string $primaryKey = 'id'): void;
}
```

---

### 2.7 — BaseRepository
**File:** `app/Repositories/BaseRepository.php`

> Giữ nguyên toàn bộ nội dung file `BaseRepository.php` từ dự án gốc. File này KHÔNG được sửa đổi. Copy y chang.

Các method có sẵn trong BaseRepository (Agent cần biết để không cần khai báo lại trong sub-Repository):
- `all()`, `find($id)`, `findOnlyColumn($id, $columns)`, `first()`
- `create($attributes)`, `insert(array $attributes)`, `update($id, $attributes)`, `delete($id)`, `show($id)`
- `with($relations)`, `getQuery()`, `clearQuery()`
- `findBy(array $filter, bool $toArray)`, `findOneBy(array $filter, bool $toArray)`
- `paginate($page)`, `updateWhere($attributes, $params)`, `deleteBy(array $filter)`
- `findWhereIn(array $filter, bool $toArray)`, `deleteWhereIn(array $filter)`
- `updateOrCreate($attributes, $params)`, `countRecord(array $filter)`
- `findByIds(array $ids, array $filter, bool $returnOnlyIds)`
- `updateWhereIn($column, $values, $attributes, $whereConditions)`
- `updateWhereNotIn($column, $values, $attributes, $whereConditions)`
- `deleteNotInIds($columnName, $value, array $ids, $primaryKey)`

---

### 2.8 — Domain Repository Interface (Template)
**File:** `app/Repositories/[Name]Repository/[Name]RepositoryInterface.php`
```php
<?php

declare(strict_types=1);

namespace App\Repositories\[Name]Repository;

use App\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

/**
 * Interface [Name]RepositoryInterface
 *
 * @package App\Repositories\[Name]Repository
 */
interface [Name]RepositoryInterface extends RepositoryInterface
{
    /**
     * Get all [name]s or search by criteria with pagination
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllOrSearch(Request $request): LengthAwarePaginator;
}
```

---

### 2.9 — Domain Repository Implementation (Template)
**File:** `app/Repositories/[Name]Repository/[Name]Repository.php`
```php
<?php

declare(strict_types=1);

namespace App\Repositories\[Name]Repository;

use App\Models\[Name];
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

/**
 * Class [Name]Repository
 *
 * @package App\Repositories\[Name]Repository
 */
final class [Name]Repository extends BaseRepository implements [Name]RepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return [Name]::class;
    }

    /**
     * Get all [name]s or search by criteria with pagination
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllOrSearch(Request $request): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // ── Filtering ────────────────────────────────────────────────────────
        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        // ── Sorting ──────────────────────────────────────────────────────────
        $allowedSortFields = ['id', 'name', 'created_at', 'updated_at'];
        $sortField     = $request->input('sort_field', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');

        if (in_array($sortField, $allowedSortFields) && in_array($sortDirection, ['asc', 'desc'])) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }

        // ── Pagination ───────────────────────────────────────────────────────
        $perPage = (int) $request->input('per_page', config('const.DEFAULT_PER_PAGE', 10));
        $page    = (int) $request->input('page', config('const.DEFAULT_PAGE', 1));

        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}
```

---

### 2.10 — Service (Template)
**File:** `app/Services/[Name]Service.php`
```php
<?php

namespace App\Services;

use App\Repositories\[Name]Repository\[Name]RepositoryInterface;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Http\Request;

class [Name]Service
{
    protected $[name]Repository;

    /**
     * @param [Name]RepositoryInterface $[name]Repository
     */
    public function __construct([Name]RepositoryInterface $[name]Repository)
    {
        $this->[name]Repository = $[name]Repository;
    }

    // =========================================================================
    // READ METHODS
    // =========================================================================

    /**
     * Get paginated list of [name]s
     *
     * @param Request $request
     * @return array{success: bool, data: mixed, message: string}
     */
    public function getAll(Request $request): array
    {
        try {
            $data = $this->[name]Repository->getAllOrSearch($request);
            return [
                'success' => true,
                'data'    => $data,
                'message' => __('[name].messages.fetch_success'),
            ];
        } catch (Exception $e) {
            Log::error('[Name]Service::getAll — ' . $e->getMessage());
            return [
                'success' => false,
                'data'    => null,
                'message' => __('[name].messages.fetch_error'),
            ];
        }
    }

    /**
     * Get [name] by ID
     *
     * @param int $id
     * @return object|null
     */
    public function getById(int $id): object|null
    {
        try {
            return $this->[name]Repository->find($id);
        } catch (Exception $e) {
            Log::error('[Name]Service::getById — ' . $e->getMessage());
            return null;
        }
    }

    // =========================================================================
    // WRITE METHODS
    // =========================================================================

    /**
     * Create a new [name]
     *
     * @param Request $request
     * @return object|null
     */
    public function create(Request $request): object|null
    {
        try {
            $data = $request->only([/* allowed fields */]);
            return $this->[name]Repository->create($data);
        } catch (Exception $e) {
            Log::error('[Name]Service::create — ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update an existing [name]
     *
     * @param int $id
     * @param Request $request
     * @return object|null
     */
    public function update(int $id, Request $request): object|null
    {
        try {
            $data = $request->only([/* allowed fields */]);
            $this->[name]Repository->update($id, $data);
            return $this->[name]Repository->find($id);
        } catch (Exception $e) {
            Log::error('[Name]Service::update — ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete [name] by ID
     *
     * @param int $id
     * @return bool|null
     */
    public function delete(int $id): bool|null
    {
        try {
            return $this->[name]Repository->delete($id);
        } catch (Exception $e) {
            Log::error('[Name]Service::delete — ' . $e->getMessage());
            return null;
        }
    }
}
```

---

### 2.11 — Controller (Template)
**File:** `app/Http/Controllers/[Name]Controller.php`
```php
<?php

namespace App\Http\Controllers;

use App\Services\[Name]Service;
use App\Enums\HttpStatus;
use Illuminate\Http\JsonResponse;
use App\Http\Validations\[Name]Validation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class [Name]Controller extends Controller
{
    protected [Name]Service $[name]Service;
    protected [Name]Validation $[name]Validation;

    public function __construct([Name]Service $[name]Service, [Name]Validation $[name]Validation)
    {
        $this->[name]Service     = $[name]Service;
        $this->[name]Validation  = $[name]Validation;
    }

    // =========================================================================
    // INDEX — GET /[name]s
    // =========================================================================

    /**
     * Get paginated list
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = $this->[name]Validation->indexValidation($request);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->[name]Service->getAll($request);
        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    // =========================================================================
    // SHOW — GET /[name]s/{id}
    // =========================================================================

    /**
     * Get detail by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $validator = $this->[name]Validation->getByIdValidation($id);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->[name]Service->getById($id);
        if (!$result) {
            return $this->errorResponse(__('[name].messages.not_found'), null, HttpStatus::NOT_FOUND);
        }

        return $this->successResponse($result, __('[name].messages.fetch_success'));
    }

    // =========================================================================
    // STORE — POST /[name]s
    // =========================================================================

    /**
     * Create a new record
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->merge(['created_by' => Auth::user()->id]);

        $validator = $this->[name]Validation->createValidation($request);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->[name]Service->create($request);
        if (!$result) {
            return $this->errorResponse(__('[name].messages.create_error'), null, HttpStatus::BAD_REQUEST);
        }

        return $this->createdResponse($result, __('[name].messages.create_success'));
    }

    // =========================================================================
    // UPDATE — PUT /[name]s/{id}
    // =========================================================================

    /**
     * Update existing record
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update($id, Request $request): JsonResponse
    {
        $request->merge(['updated_by' => Auth::user()->id]);

        $validator = $this->[name]Validation->updateValidation($id, $request);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->[name]Service->update($id, $request);
        if (!$result) {
            return $this->errorResponse(__('[name].messages.update_error'), null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result, __('[name].messages.update_success'));
    }

    // =========================================================================
    // DESTROY — DELETE /[name]s/{id}
    // =========================================================================

    /**
     * Delete a record
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $validator = $this->[name]Validation->getByIdValidation($id);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->[name]Service->delete($id);
        if (!$result) {
            return $this->errorResponse(__('[name].messages.delete_error'), null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse(null, __('[name].messages.delete_success'));
    }
}
```

---

### 2.12 — Validation Class (Template)
**File:** `app/Http/Validations/[Name]Validation.php`
```php
<?php

namespace App\Http\Validations;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

final class [Name]Validation
{
    /**
     * Validate index (list) request
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function indexValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'q'              => 'nullable|string|max:100',
            'sort_field'     => 'nullable|in:id,name,created_at,updated_at',
            'sort_direction' => 'nullable|in:asc,desc',
            'per_page'       => 'nullable|integer|min:1|max:100',
            'page'           => 'nullable|integer|min:1',
        ], [
            'sort_field.in'      => __('[name].sort_field_invalid'),
            'sort_direction.in'  => __('[name].sort_direction_invalid'),
            'per_page.integer'   => __('[name].per_page_integer'),
            'per_page.min'       => __('[name].per_page_min'),
            'per_page.max'       => __('[name].per_page_max'),
            'page.integer'       => __('[name].page_integer'),
            'page.min'           => __('[name].page_min'),
        ]);
    }

    /**
     * Validate get-by-id request
     *
     * @param mixed $id
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function getByIdValidation($id)
    {
        return Validator::make(['id' => $id], [
            'id' => 'required|integer|exists:[table_name],id',
        ], [
            'id.required' => __('[name].id_required'),
            'id.integer'  => __('[name].id_integer'),
            'id.exists'   => __('[name].id_exists'),
        ]);
    }

    /**
     * Validate create request
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function createValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:[table_name],name',
            // Add more fields
        ], [
            'name.required' => __('[name].name_required'),
            'name.string'   => __('[name].name_string'),
            'name.max'      => __('[name].name_max'),
            'name.unique'   => __('[name].name_unique'),
        ]);
    }

    /**
     * Validate update request
     *
     * @param mixed $id
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function updateValidation($id, Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:[table_name],name,' . $id,
            // Add more fields
        ], [
            'name.required' => __('[name].name_required'),
            'name.string'   => __('[name].name_string'),
            'name.max'      => __('[name].name_max'),
            'name.unique'   => __('[name].name_unique'),
        ]);
    }
}
```

---

### 2.13 — RepositoryServiceProvider (Template)
**File:** `app/Providers/RepositoryServiceProvider.php`
```php
<?php

namespace App\Providers;

use App\Repositories\[Name]Repository\[Name]Repository;
use App\Repositories\[Name]Repository\[Name]RepositoryInterface;
use App\Repositories\BaseRepository;
use App\Repositories\RepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register bindings: Interface → Concrete Implementation (singleton)
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(RepositoryInterface::class, BaseRepository::class);

        // ── Add new bindings below for each domain ────────────────────────────
        $this->app->singleton([Name]RepositoryInterface::class, [Name]Repository::class);
        // $this->app->singleton([Name2]RepositoryInterface::class, [Name2]Repository::class);
    }

    public function boot()
    {
        //
    }
}
```

> ⚠️ **QUAN TRỌNG**: Mỗi khi tạo Repository mới, BẮT BUỘC phải đăng ký binding tại đây, nếu không Laravel sẽ không biết inject implementation nào cho Interface.

---

### 2.14 — Routes (Template)
**File:** `routes/api.php`
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\[Name]Controller;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Structure:
| /api/v1/admin/     - Admin API
| /api/v1/[role]/    - Role-specific API
| /api/v1/common/    - Public/shared API
*/

Route::group(['prefix' => 'v1'], function () {

    // =========================================================================
    // AUTH — Public (no token required)
    // =========================================================================
    Route::prefix('[role]/auth')->group(function () {
        Route::post('login',    [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
    });

    // =========================================================================
    // [ROLE] — Protected routes
    // Base URL: /api/v1/[role]/
    // =========================================================================
    Route::middleware(['jwt.auth', 'role:[role]'])->prefix('[role]')->group(function () {

        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
        });

        // [Name] CRUD
        Route::prefix('[name]s')->group(function () {
            Route::get('/',            [[Name]Controller::class, 'index']);
            Route::get('{id}',         [[Name]Controller::class, 'show'])->whereNumber('id');
            Route::post('/',           [[Name]Controller::class, 'store']);
            Route::put('{id}',         [[Name]Controller::class, 'update'])->whereNumber('id');
            Route::delete('{id}',      [[Name]Controller::class, 'destroy'])->whereNumber('id');
        });
    });

    // =========================================================================
    // COMMON — Public API
    // Base URL: /api/v1/common/
    // =========================================================================
    Route::prefix('common')->group(function () {
        // Add public routes here
    });
});
```

---

## PHẦN 3 — QUY TẮC RESPONSE FORMAT

Mọi response phải theo format sau (thông qua `ApiResponser` trait):

### Success (200/201)
```json
{
    "status": "success",
    "message": "Thông báo thành công",
    "data": { ... }
}
```

### Error (400/404/500)
```json
{
    "status": "error",
    "message": "Mô tả lỗi",
    "code": "ERROR_CODE_STRING",
    "data": null
}
```

### Validation Error (422)
```json
{
    "status": "error",
    "errors": {
        "field_name": ["Message lỗi 1", "Message lỗi 2"]
    },
    "code": "VALIDATION_FAILED"
}
```

### Paginated List (200)
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

---

## PHẦN 4 — QUY TẮC NAMING CONVENTION

| Loại               | Convention       | Ví dụ                             |
|--------------------|------------------|-----------------------------------|
| URL path           | kebab-case       | `/user-profiles`, `/room-images`  |
| URL resource       | số nhiều         | `/users`, `/buildings`            |
| PHP class          | PascalCase       | `BuildingImage`, `UserService`    |
| PHP variable       | camelCase        | `$userId`, `$buildingData`        |
| DB column / JSON   | snake_case       | `first_name`, `created_at`        |
| Constant / Enum    | UPPER_SNAKE_CASE | `DEFAULT_PER_PAGE`, `BAD_REQUEST` |
| Controller file    | `[Name]Controller.php`       |                   |
| Service file       | `[Name]Service.php`          |                   |
| Repository file    | `[Name]Repository.php`       |                   |
| Interface file     | `[Name]RepositoryInterface.php` |                |
| Validation file    | `[Name]Validation.php`       |                   |
| Model file         | PascalCase, số ít — `BuildingImage.php` |          |

---

## PHẦN 5 — CHECKLIST TRIỂN KHAI MODULE MỚI

Agent phải thực hiện **đúng thứ tự** sau khi nhận yêu cầu tạo module `[Name]`:

```
[ ] 1. Tạo Migration: database/migrations/[timestamp]_create_[name]s_table.php
[ ] 2. Tạo Model: app/Models/[Name].php
       - $fillable, $hidden (nếu có), $casts, relationships
[ ] 3. Tạo RepositoryInterface: app/Repositories/[Name]Repository/[Name]RepositoryInterface.php
       - extends RepositoryInterface
       - Khai báo các method đặc thù của domain
[ ] 4. Tạo Repository: app/Repositories/[Name]Repository/[Name]Repository.php
       - extends BaseRepository, implements [Name]RepositoryInterface
       - getModel() trả về [Name]::class
       - Implement getAllOrSearch() với filtering, sorting, pagination
[ ] 5. Đăng ký binding trong: app/Providers/RepositoryServiceProvider.php
       - $this->app->singleton([Name]RepositoryInterface::class, [Name]Repository::class);
[ ] 6. Tạo Service: app/Services/[Name]Service.php
       - Inject [Name]RepositoryInterface qua constructor
       - Mỗi method bọc trong try/catch, log error, trả về array{success, data, message}
[ ] 7. Tạo Validation: app/Http/Validations/[Name]Validation.php
       - indexValidation(), getByIdValidation(), createValidation(), updateValidation()
[ ] 8. Tạo Controller: app/Http/Controllers/[Name]Controller.php
       - extends Controller (đã có ApiResponser)
       - Inject Service và Validation qua constructor
       - index(), show(), store(), update(), destroy()
[ ] 9. Thêm Routes vào: routes/api.php
       - Nhóm theo role và middleware
[ ] 10. Tạo lang file: lang/vi/[name].php và lang/en/[name].php
        - Định nghĩa các message key dùng trong Validation và Controller
```

---

## PHẦN 6 — QUY TẮC CODE STYLE BẮT BUỘC

1. **`declare(strict_types=1)`** — Bắt buộc trong mọi file Repository và Interface
2. **DocBlocks** — Bắt buộc cho mọi class, method, property:
   ```php
   /**
    * Mô tả ngắn gọn
    *
    * @param int $id
    * @return array{success: bool, data: mixed, message: string}
    * @throws ModelNotFoundException
    */
   ```
3. **Service method** — Luôn bọc trong `try/catch`, luôn `Log::error()`, luôn trả về `array{success, data, message}`
4. **Controller method** — Không chứa business logic, chỉ: validate → gọi service → trả response
5. **Repository** — Không chứa business logic, chỉ query/persist data
6. **Validation** — Không để trong Controller, luôn dùng class riêng với `Validator::make()`
7. **`Auth::user()->id`** — Luôn merge `created_by` và `updated_by` tại Controller trước khi gọi service
8. **Messages** — Không hardcode string, luôn dùng `__('domain.key')`
9. **Soft Delete** — Áp dụng `SoftDeletes` cho dữ liệu nghiệp vụ quan trọng
10. **Sorting whitelist** — Luôn whitelist `sort_field` trong Repository để tránh SQL injection

---

## PHẦN 7 — CẤU HÌNH HỆ THỐNG

### config/const.php (cần có)
```php
<?php
return [
    'DEFAULT_PAGE'     => 1,
    'DEFAULT_PER_PAGE' => 10,
    'CHUNK_SIZE'       => 500,
];
```

### Middleware cần đăng ký trong Kernel.php
```php
protected $routeMiddleware = [
    'jwt.auth' => \App\Http\Middleware\JwtAuthMiddleware::class,
    'role'     => \App\Http\Middleware\RoleMiddleware::class,
];
```

### Provider cần đăng ký trong config/app.php
```php
'providers' => [
    // ...
    App\Providers\RepositoryServiceProvider::class,
],
```

---

## PHẦN 8 — HƯỚNG DẪN SỬ DỤNG TÀI LIỆU NÀY

### Dành cho AI Agent
1. Đọc **PHẦN 0** để hiểu kiến trúc tổng thể.
2. Đọc **PHẦN 1** để biết cần tạo những thư mục nào.
3. Dùng **PHẦN 2** như một template library — thay `[Name]` / `[name]` bằng tên domain thực tế.
4. Tuân thủ **PHẦN 3** khi viết response.
5. Tuân thủ **PHẦN 4** về naming.
6. Thực hiện đúng thứ tự **PHẦN 5** khi tạo module mới.
7. Tuân thủ tuyệt đối **PHẦN 6** về code style.

### Cách thay thế placeholder
| Placeholder | Ý nghĩa | Ví dụ |
|---|---|---|
| `[Name]` | Tên domain, PascalCase | `Product`, `Category`, `Order` |
| `[name]` | Tên domain, camelCase | `product`, `category`, `order` |
| `[role]` | Tên role, lowercase | `admin`, `staff`, `customer` |
| `[table_name]` | Tên bảng DB, snake_case số nhiều | `products`, `order_items` |

---

---

## PHẦN 9 — CODE TEMPLATE (TIẾP THEO)

### 9.1 — Model (Template đơn giản, không SoftDelete)
**File:** `app/Models/[Name].php`
```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class [Name] extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '[table_name]';

    /**
     * Allow mass assignment for all fields.
     * Using $guarded = [] is the convention in this project.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created this record.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
```

---

### 9.2 — Model (Template có SoftDelete và timezone accessor)
**File:** `app/Models/[Name].php` — Dùng khi cần SoftDelete hoặc format datetime
```php
<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class [Name] extends Model
{
    use HasFactory;
    use SoftDeletes; // Bỏ nếu không cần soft delete

    protected $table = '[table_name]';

    protected $guarded = [];

    protected $hidden = ['pivot']; // Ẩn pivot trong many-to-many nếu cần

    protected $casts = [
        'status'     => 'integer',
        'is_active'  => 'boolean',
        'start_date' => 'date',
        'end_date'   => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // =========================================================================
    // ACCESSORS — Format datetime về timezone Asia/Ho_Chi_Minh
    // =========================================================================

    /**
     * @param mixed $value
     * @return string
     */
    public function getCreatedAtAttribute($value): string
    {
        return Carbon::parse($value)->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s');
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getUpdatedAtAttribute($value): string
    {
        return Carbon::parse($value)->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s');
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
```

---

### 9.3 — User Model (JWT — bắt buộc implement JWTSubject)
**File:** `app/Models/User.php`
```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

final class User extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = 'users';

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
        'verification_token',
    ];

    protected $casts = [
        'status'            => 'integer',
        'is_email_verified' => 'boolean',
        'email_verified_at' => 'datetime',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    /**
     * Check if user has specific role
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles
     *
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    // ── JWT Required ──────────────────────────────────────────────────────────

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
```

---

### 9.4 — Migration (Template chuẩn)
**File:** `database/migrations/[timestamp]_create_[table_name]_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('[table_name]', function (Blueprint $table) {
            $table->id();

            // ── Domain fields ─────────────────────────────────────────────────
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->tinyInteger('status')->unsigned()->default(1)
                ->comment('0: inactive, 1: active');

            // ── Foreign keys ──────────────────────────────────────────────────
            // $table->unsignedBigInteger('parent_id')->nullable();
            // $table->foreign('parent_id')->references('id')->on('other_table');

            // ── Audit fields (bắt buộc) ───────────────────────────────────────
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // ── Soft delete (nếu cần) ─────────────────────────────────────────
            // $table->softDeletes();

            // ── Indexes ───────────────────────────────────────────────────────
            $table->index('status');
            // $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('[table_name]');
    }
};
```

**Quy tắc migration:**
- Luôn dùng `useCurrent()` và `useCurrentOnUpdate()` cho timestamps (KHÔNG dùng `$table->timestamps()` mặc định)
- Luôn có `created_by` và `updated_by` dạng `unsignedBigInteger()->nullable()`
- Đặt `->comment()` cho các trường có giá trị enum/status
- Đặt index cho các cột thường xuyên filter/sort

---

### 9.5 — Migration (Template cho Users table — foundation)
Đây là migration `users` bắt buộc tạo trước:
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name', 100);
    $table->string('email', 150)->unique();
    $table->boolean('is_email_verified')->default(false);
    $table->timestamp('email_verified_at')->nullable();
    $table->string('verification_token', 255)->nullable();
    $table->timestamp('token_expires_at')->nullable();
    $table->string('password', 255);
    $table->enum('role', ['admin', '[role1]', '[role2]'])->default('[role2]');
    $table->string('phone', 20)->nullable();
    $table->string('avatar', 255)->nullable();
    $table->string('id_avatar', 255)->nullable();
    $table->tinyInteger('status')->unsigned()->default(0)
        ->comment('0: pending, 1: active, 2: blocked');
    $table->unsignedBigInteger('created_by')->nullable();
    $table->unsignedBigInteger('updated_by')->nullable();
    $table->timestamp('created_at')->useCurrent();
    $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

    $table->index('role');
    $table->index('status');
});
```

---

### 9.6 — RoleMiddleware
**File:** `app/Http/Middleware/RoleMiddleware.php`
```php
<?php

namespace App\Http\Middleware;

use App\Enums\HttpStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Supports multiple roles separated by comma: role:admin,partner
     *
     * @param Request $request
     * @param Closure $next
     * @param string ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::guard('api')->user();

        // Flatten comma-separated roles into a single array
        $allowed = [];
        foreach ($roles as $r) {
            $allowed = array_merge($allowed, explode(',', $r));
        }

        if (!in_array($user->role, $allowed)) {
            return response()->json([
                'status'  => 'error',
                'message' => __('auth.unauthorized'),
            ], HttpStatus::UNAUTHORIZED->value);
        }

        return $next($request);
    }
}
```

---

### 9.7 — LocaleMiddleware (Đa ngôn ngữ qua Header)
**File:** `app/Http/Middleware/LocaleMiddleware.php`
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LocaleMiddleware
{
    /**
     * Set locale from Accept-Language header.
     * Client sends: Accept-Language: vi  OR  Accept-Language: en
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('Accept-Language', 'vi');
        if (in_array($locale, ['en', 'vi'])) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
```

---

### 9.8 — Kernel.php (Đăng ký Middleware)
**File:** `app/Http/Kernel.php` — Phần `$routeMiddleware` cần có:
```php
protected $routeMiddleware = [
    'auth'      => \App\Http\Middleware\Authenticate::class,
    'role'      => \App\Http\Middleware\RoleMiddleware::class,
    'jwt.auth'  => \Tymon\JWTAuth\Http\Middleware\Authenticate::class,
    // Laravel defaults
    'auth.basic'       => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
    'cache.headers'    => \Illuminate\Http\Middleware\SetCacheHeaders::class,
    'can'              => \Illuminate\Auth\Middleware\Authorize::class,
    'guest'            => \App\Http\Middleware\RedirectIfAuthenticated::class,
    'throttle'         => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    'verified'         => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    'bindings'         => \Illuminate\Routing\Middleware\SubstituteBindings::class,
];

// Phần $middleware global (chạy mọi request):
protected $middleware = [
    \App\Http\Middleware\TrustProxies::class,
    \Illuminate\Http\Middleware\HandleCors::class,
    \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
    \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
    \App\Http\Middleware\TrimStrings::class,
    \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    \App\Http\Middleware\LocaleMiddleware::class,  // ← Đa ngôn ngữ
];
```

---

### 9.9 — Lang File (Template)
**File:** `lang/vi/[name].php`
```php
<?php

return [
    // ── Validation messages ────────────────────────────────────────────────
    'id_required'         => 'ID là bắt buộc.',
    'id_integer'          => 'ID phải là số nguyên.',
    'id_exists'           => '[Name] không tồn tại.',
    'name_required'       => 'Tên là bắt buộc.',
    'name_string'         => 'Tên phải là chuỗi ký tự.',
    'name_max'            => 'Tên không được vượt quá :max ký tự.',
    'name_unique'         => 'Tên đã tồn tại trong hệ thống.',
    'sort_field_invalid'  => 'Trường sắp xếp không hợp lệ.',
    'sort_direction_invalid' => 'Chiều sắp xếp không hợp lệ. Chỉ chấp nhận asc hoặc desc.',
    'per_page_integer'    => 'Số bản ghi mỗi trang phải là số nguyên.',
    'per_page_min'        => 'Số bản ghi mỗi trang phải ít nhất là 1.',
    'per_page_max'        => 'Số bản ghi mỗi trang không được vượt quá 100.',
    'page_integer'        => 'Số trang phải là số nguyên.',
    'page_min'            => 'Số trang phải ít nhất là 1.',

    // ── API response messages ──────────────────────────────────────────────
    'messages' => [
        'fetch_success'  => 'Lấy dữ liệu thành công.',
        'fetch_error'    => 'Lấy dữ liệu thất bại.',
        'not_found'      => '[Name] không tồn tại.',
        'create_success' => 'Tạo [name] thành công.',
        'create_error'   => 'Tạo [name] thất bại.',
        'update_success' => 'Cập nhật [name] thành công.',
        'update_error'   => 'Cập nhật [name] thất bại.',
        'delete_success' => 'Xóa [name] thành công.',
        'delete_error'   => 'Xóa [name] thất bại.',
    ],
];
```

**File:** `lang/en/[name].php`
```php
<?php

return [
    'id_required'         => 'The ID field is required.',
    'id_integer'          => 'The ID must be an integer.',
    'id_exists'           => '[Name] not found.',
    'name_required'       => 'The name field is required.',
    'name_string'         => 'The name must be a string.',
    'name_max'            => 'The name may not be greater than :max characters.',
    'name_unique'         => 'The name has already been taken.',
    'sort_field_invalid'  => 'The sort field is invalid.',
    'sort_direction_invalid' => 'The sort direction is invalid. Only asc or desc are accepted.',
    'per_page_integer'    => 'The per page must be an integer.',
    'per_page_min'        => 'The per page must be at least 1.',
    'per_page_max'        => 'The per page may not be greater than 100.',
    'page_integer'        => 'The page must be an integer.',
    'page_min'            => 'The page must be at least 1.',

    'messages' => [
        'fetch_success'  => 'Data retrieved successfully.',
        'fetch_error'    => 'Failed to retrieve data.',
        'not_found'      => '[Name] not found.',
        'create_success' => '[Name] created successfully.',
        'create_error'   => 'Failed to create [name].',
        'update_success' => '[Name] updated successfully.',
        'update_error'   => 'Failed to update [name].',
        'delete_success' => '[Name] deleted successfully.',
        'delete_error'   => 'Failed to delete [name].',
    ],
];
```

**File:** `lang/vi/auth.php` — (Phần auth messages dùng trong Handler.php)
```php
<?php

return [
    'token_invalid'    => 'Token không hợp lệ.',
    'token_expired'    => 'Token đã hết hạn.',
    'token_required'   => 'Token là bắt buộc.',
    'unauthenticated'  => 'Chưa xác thực.',
    'unauthorized'     => 'Không có quyền truy cập.',
    'route_not_found'  => 'Đường dẫn không tồn tại.',
    'general_error'    => 'Đã có lỗi xảy ra. Vui lòng thử lại.',
    'invalid_data'     => 'Dữ liệu không hợp lệ.',
];
```

---

## PHẦN 10 — SETUP DỰ ÁN MỚI (STEP BY STEP)

### Bước 1: Cài Laravel và packages cốt lõi

```bash
# Tạo project Laravel mới
composer create-project laravel/laravel ten-du-an-moi

# Vào thư mục
cd ten-du-an-moi

# Cài JWT Auth (BẮT BUỘC)
composer require tymon/jwt-auth

# Publish JWT config
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"

# Generate JWT secret key
php artisan jwt:secret
```

### Bước 2: Cấu hình `.env`

```env
APP_NAME="Ten Du An"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ten_du_an_db
DB_USERNAME=root
DB_PASSWORD=

JWT_SECRET=  # Tự động điền sau khi chạy php artisan jwt:secret
JWT_ALGO=HS256
JWT_TTL=60          # Token hết hạn sau 60 phút
JWT_REFRESH_TTL=20160  # Refresh token hết hạn sau 2 tuần

# Locale mặc định
APP_LOCALE=vi
APP_FALLBACK_LOCALE=en
```

### Bước 3: Cấu hình `config/auth.php`

```php
'defaults' => [
    'guard'     => 'api',
    'passwords' => 'users',
],

'guards' => [
    'api' => [
        'driver'   => 'jwt',
        'provider' => 'users',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model'  => App\Models\User::class,
    ],
],
```

### Bước 4: Tạo file cốt lõi theo thứ tự

```
1. app/Enums/HttpStatus.php              ← Template 2.1
2. app/Traits/ApiResponser.php           ← Template 2.2
3. app/Http/Controllers/Controller.php   ← Template 2.3
4. app/Exceptions/BusinessException.php  ← Template 2.4
5. app/Exceptions/Handler.php            ← Template 2.5
6. app/Repositories/RepositoryInterface.php ← Template 2.6
7. app/Repositories/BaseRepository.php   ← Copy từ dự án gốc
8. app/Providers/RepositoryServiceProvider.php ← Template 2.13
9. app/Http/Middleware/RoleMiddleware.php ← Template 9.6
10. app/Http/Middleware/LocaleMiddleware.php ← Template 9.7
11. app/Http/Kernel.php                  ← Cập nhật theo Template 9.8
12. config/const.php                     ← Tạo mới
13. lang/vi/auth.php + lang/en/auth.php  ← Template 9.9
```

### Bước 5: Đăng ký Provider

Trong `config/app.php`, thêm vào mảng `providers`:
```php
App\Providers\RepositoryServiceProvider::class,
```

### Bước 6: Tạo Migration & Domain Modules

Với mỗi domain `[Name]`, thực hiện theo PHẦN 5 — CHECKLIST TRIỂN KHAI MODULE MỚI.

---

## PHẦN 11 — DEPENDENCIES CHUẨN

### Packages bắt buộc (`require`)
```json
{
    "php": "^8.1",
    "laravel/framework": "^10.0",
    "tymon/jwt-auth": "^2.1"
}
```

### Packages tùy chọn (thêm nếu dự án cần)
```json
{
    "guzzlehttp/guzzle": "^7.2",
    "cloudinary-labs/cloudinary-laravel": "^2.1",
    "intervention/image": "^2.7",
    "maatwebsite/excel": "^3.1",
    "mpdf/mpdf": "^8.2"
}
```

### Dev dependencies (bắt buộc cho code quality)
```json
{
    "fakerphp/faker": "^1.9.1",
    "nunomaduro/larastan": "^2.0",
    "phpunit/phpunit": "^10.0",
    "squizlabs/php_codesniffer": "^3.7"
}
```

### Scripts trong `composer.json`
```json
{
    "scripts": {
        "phpcs":    ["./vendor/bin/phpcs --standard=phpcs.xml ./"],
        "phpcbf":   ["./vendor/bin/phpcbf --standard=phpcs.xml ./"],
        "phpstan":  ["./vendor/bin/phpstan analyse"]
    }
}
```

### Autoload helper files (nếu có)
```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        },
        "files": [
            "app/Helpers/index.php"
        ]
    }
}
```

---

## PHẦN 12 — LỖI THƯỜNG GẶP & CÁCH XỬ LÝ

| Lỗi | Nguyên nhân | Cách sửa |
|-----|-------------|----------|
| `Target [RepositoryInterface] is not instantiable` | Chưa đăng ký binding trong RepositoryServiceProvider | Thêm `$this->app->singleton(Interface::class, Implementation::class)` |
| `Class App\Repositories\... not found` | Sai namespace hoặc chưa chạy `composer dump-autoload` | Kiểm tra namespace khớp đường dẫn, chạy `composer dump-autoload` |
| `jwt.auth middleware not found` | Chưa đăng ký middleware trong Kernel.php | Thêm `'jwt.auth' => \Tymon\JWTAuth\Http\Middleware\Authenticate::class` |
| `Token not provided` | Route chưa bọc middleware `jwt.auth` | Bọc route trong `->middleware('jwt.auth')` |
| `Call to undefined method __()` | File lang chưa tồn tại | Tạo file `lang/vi/[domain].php` với key tương ứng |
| `SQLSTATE: Column not found` | Migration chưa chạy hoặc tên column sai | Chạy `php artisan migrate`, kiểm tra `$fillable` và tên column |
| `Validation always passes` | Gọi `$validator->fails()` nhưng không return | Đảm bảo có `return` sau `$validator->fails()` |
| `405 Method Not Allowed` | Route method không khớp (GET vs POST) | Kiểm tra HTTP method trong `routes/api.php` |

---

## PHẦN 13 — PATTERN NÂNG CAO

### 13.1 — DB Transaction trong Service (Dùng khi write nhiều bảng)
```php
use Illuminate\Support\Facades\DB;

public function createWithRelation(Request $request): array
{
    DB::beginTransaction();
    try {
        $parent = $this->parentRepository->create($request->only(['name', 'created_by']));

        foreach ($request->items as $item) {
            $this->childRepository->create([
                'parent_id'  => $parent->id,
                'name'       => $item['name'],
                'created_by' => $request->created_by,
            ]);
        }

        DB::commit();
        return ['success' => true, 'data' => $parent, 'message' => __('[name].messages.create_success')];
    } catch (Exception $e) {
        DB::rollBack();
        Log::error('[Name]Service::createWithRelation — ' . $e->getMessage());
        return ['success' => false, 'data' => null, 'message' => __('[name].messages.create_error')];
    }
}
```

### 13.2 — Eager Loading trong Repository (Tránh N+1)
```php
public function getAllOrSearch(Request $request): LengthAwarePaginator
{
    $query = $this->model->newQuery()
        ->with(['creator:id,name', 'category:id,name']); // Chỉ lấy cột cần thiết

    // ... filtering, sorting, pagination
}
```

### 13.3 — Role-scoped Repository (Query theo owner)
Khi Partner chỉ được xem data của mình:
```php
public function getByOwner(int $userId, Request $request): LengthAwarePaginator
{
    $query = $this->model->newQuery()
        ->where('owner_id', $userId); // Scope theo user

    // ... filtering, sorting, pagination
}
```

### 13.4 — Truyền User ID xuống Service (Pattern chuẩn)
```php
// Controller — KHÔNG truyền Auth::user() xuống Service
public function index(Request $request): JsonResponse
{
    $request->merge(['auth_user_id' => Auth::user()->id]); // Merge vào request
    $result = $this->service->getAll($request);
    // ...
}

// Service — Lấy từ request
public function getAll(Request $request): array
{
    $userId = $request->input('auth_user_id');
    $data = $this->repository->getByOwner($userId, $request);
    // ...
}
```

### 13.5 — Custom Enum (Template)
```php
<?php

namespace App\Enums;

enum UserStatus: int
{
    case PENDING = 0;
    case ACTIVE  = 1;
    case BLOCKED = 2;

    /**
     * Get label for display
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Chờ duyệt',
            self::ACTIVE  => 'Hoạt động',
            self::BLOCKED => 'Bị khóa',
        };
    }

    /**
     * Get all values as array for validation
     *
     * @return array<int>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
```

Sử dụng trong validation:
```php
'status' => 'required|integer|in:' . implode(',', \App\Enums\UserStatus::values()),
```

---

*Tài liệu này được tổng hợp từ dự án `bks-system-be`. Phiên bản: 2.0.0 — 2026-04-14*
*Để sử dụng: Cung cấp file này cho AI Agent kèm theo yêu cầu tạo module mới, Agent sẽ tự sinh code theo đúng chuẩn này.*
