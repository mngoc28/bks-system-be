# BKS System
## 📋 Yêu cầu hệ thống

- PHP >= 8.0.2
- Composer
- Node.js & NPM
- MySQL/MariaDB
- Nginx (tùy chọn)

## 🚀 Hướng dẫn cài đặt

### 1. Clone dự án
```bash
git clone <repository-url>
cd BKS_SYSTEM
```

### 2. Cài đặt dependencies PHP
```bash
composer install --ignore-platform-reqs
composer update
```

### 3. Cài đặt dependencies Node.js
```bash
npm install
```

### 4. Cấu hình môi trường
Tạo file `.env` từ file `.env.example` (nếu có) hoặc tạo mới:
```bash
cp .env.example .env
```

Hoặc tạo file `.env` với nội dung cơ bản:
```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=go-edu_app
DB_USERNAME=go-edu-user
DB_PASSWORD=secret

# Các cấu hình khác...
```

### 5. Tạo application key
```bash
php artisan key:generate
```

### 6. Chạy database migrations
```bash
php artisan migrate
```

### 7. Tối ưu hóa Laravel
```bash
php artisan optimize
```

### 8. Build frontend assets
```bash
npm run build
```

### 9. Build API documentation
```bash
cd api-doc
npm install apidoc -g
apidoc -i . -o ../public/apidoc
# (tuỳ chọn) bản copy trong api-doc/public để mở file HTML tương đối khi dev:
apidoc -i . -o ./public/apidoc
cd ..
```

Nguồn mô tả API nằm trong `api-doc/*.js` (ví dụ `properties.js`, `property-image.js`, `dashboard.js`). Sau khi sửa nguồn, chạy lại lệnh trên để cập nhật `main.bundle.js`.

## 🐳 Chạy với Docker

### Sử dụng Docker Compose
```bash
docker-compose up -d
```

Dự án sẽ chạy trên:
- **Web**: http://localhost:8081
- **Database**: localhost:3306

### Các service trong Docker:
- **app**: PHP-FPM container
- **nginx**: Web server
- **db**: MariaDB database

## 📁 Cấu trúc dự án

```
BKS_SYSTEM/
├── app/
│   ├── Console/          # Artisan commands
│   ├── Enums/           # Enums định nghĩa
│   ├── Exceptions/      # Custom exceptions
│   ├── Helpers/         # Helper functions
│   ├── Http/
│   │   ├── Controllers/ # API Controllers
│   │   ├── Middleware/  # Middleware
│   │   ├── Resources/   # API Resources
│   │   └── Validations/ # Validation rules
│   ├── Jobs/           # Queue jobs
│   ├── Mail/           # Mail classes
│   ├── Models/         # Eloquent models
│   ├── Policies/       # Authorization policies
│   ├── Providers/      # Service providers
│   ├── Repositories/   # Repository pattern
│   ├── Rules/          # Custom validation rules
│   ├── Services/       # Business logic services
│   └── Traits/         # Reusable traits
├── config/             # Configuration files
├── database/
│   ├── migrations/     # Database migrations
│   └── seeders/        # Database seeders
├── public/             # Public assets
├── resources/
│   ├── css/           # CSS files
│   ├── js/            # JavaScript files
│   ├── lang/          # Language files
│   └── views/         # Blade templates
├── routes/            # Route definitions
└── tests/             # Test files
```

## 🔧 Các lệnh hữu ích

### Laravel Artisan Commands
```bash
# Chạy migrations
php artisan migrate

# Chạy seeders
php artisan db:seed

# Tạo model mới
php artisan make:model ModelName

# Tạo controller mới
php artisan make:controller ControllerName

# Tạo migration mới
php artisan make:migration create_table_name

# Xóa cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Tối ưu hóa
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### NPM Scripts
```bash
# Development
npm run dev

# Build production
npm run build

# Code formatting
npm run prettier

# Khởi chạy tunnel webhook qua localtunnel (Lưu ý: SePay có thể gặp lỗi 511 do trang cảnh báo của localtunnel)
npm run tunnel

# Khởi chạy tunnel webhook qua localhost.run SSH (URL thay đổi ngẫu nhiên mỗi lần restart)
npm run tunnel:ssh

# Khởi chạy tunnel webhook qua ngrok với Static Domain cố định (Khuyên dùng)
npm run tunnel:ngrok

# Tự động khởi động Soketi Docker, Laravel Server (php artisan serve) và ngrok tunnel cùng lúc (Khuyên dùng)
npm run dev:tunnel

# Tự động khởi động Soketi Docker, Laravel Server (php artisan serve) và localhost.run SSH tunnel cùng lúc
npm run dev:ssh
```

### Composer Scripts
```bash
# Code style check
composer phpcs

# Code style fix
composer phpcbf

# Static analysis
composer phpstan

# Run tests
composer phpunit
```

## 🗄️ Database

### Kết nối database
- **Host**: localhost (hoặc db container trong Docker)
- **Port**: 3306
- **Database**: go-edu_app
- **Username**: go-edu-user
- **Password**: secret

### Chạy migrations
```bash
php artisan migrate
```

### Chạy seeders
```bash
php artisan db:seed
```

## ☁️ Cấu hình Webhook Tunnel (SePay)

Để có thể nhận callback Webhook từ cổng thanh toán SePay về môi trường phát triển cục bộ (local/127.0.0.1:8000), bạn cần mở một đường truyền tunnel bảo mật ra internet.

> [!TIP]
> **Khởi động nhanh trọn bộ môi trường phát triển cục bộ (Backend, Soketi & Tunnel):**
> Thay vì khởi động riêng lẻ Soketi Docker, server Backend và đường truyền Tunnel ở nhiều terminal khác nhau, bạn có thể dùng một lệnh duy nhất ở thư mục Backend để khởi động tất cả (lưu ý cần bật ứng dụng Docker Desktop trước):
> - Sử dụng ngrok tĩnh cố định: `npm run dev:tunnel` (Khuyên dùng)
> - Sử dụng SSH localhost.run động: `npm run dev:ssh`
> 
> *Lưu ý về Docker:* Script `scripts/check-docker.js` sẽ tự động kiểm tra Docker Daemon:
> - Nếu Docker chưa khởi động, script sẽ in ra dòng cảnh báo màu vàng bằng tiếng Anh (`WARNING: Docker daemon is not running!...`) tại terminal để nhắc nhở và tự động bỏ qua Soketi WebSocket, nhưng **vẫn tiếp tục** khởi động Laravel server và Webhook Tunnel.

### Phương án 1: Sử dụng ngrok với Static Domain (Khuyên dùng - Đang dùng - Cố định URL)
*Nguyên nhân khuyên dùng:* ngrok hỗ trợ cấu hình 1 Static Domain miễn phí, giúp URL webhook của bạn không bị thay đổi mỗi khi khởi động lại máy/restart tunnel, đồng thời không bị lỗi `511 Network Authentication Required` như `localtunnel`.

1. **Đăng ký tài khoản ngrok miễn phí** tại [ngrok.com](https://ngrok.com).
2. **Đăng ký Static Domain miễn phí:** Vào tab **Domains** trên trang quản lý ngrok, đăng ký nhận 1 Free Static Domain (ví dụ: `foam-proximity-unraveled.ngrok-free.dev`).
3. **Cấu hình Authtoken trên máy cá nhân:**
   ```bash
   npx ngrok config add-authtoken <TOKEN_CỦA_BẠN>
   ```
4. **Cập nhật Domain vào `package.json`** (nếu dùng domain khác với domain mặc định có sẵn trong code):
   Mở file `package.json` sửa script `tunnel:ngrok` trỏ tới domain của bạn:
   ```json
   "tunnel:ngrok": "npx ngrok http 8000 --domain=your-domain.ngrok-free.dev"
   ```
5. **Khởi chạy Tunnel:**
   ```bash
   npm run tunnel:ngrok
   ```
6. **Cấu hình trên SePay:** Copy URL webhook của bạn và lưu trên dashboard SePay:
   `https://your-domain.ngrok-free.dev/api/v1/payments/sepay-webhook`

---

### Phương án 2: Sử dụng localhost.run SSH Tunnel (Dự phòng nhanh)
*Ưu điểm:* Không cần cài đặt, không cần đăng ký tài khoản ngrok.
*Nhược điểm:* Mỗi lần khởi chạy lại, URL sẽ bị thay đổi ngẫu nhiên, bạn phải vào SePay Dashboard cập nhật lại URL Webhook mới.

1. **Khởi chạy Tunnel:**
   ```bash
   npm run tunnel:ssh
   ```
2. **Cấu hình trên SePay:** Copy URL public (ví dụ: `https://xxx.lhr.life`) hiển thị trên terminal và lưu trên dashboard SePay:
   `https://xxx.lhr.life/api/v1/payments/sepay-webhook`

---

## 🔐 Authentication

Dự án sử dụng:
- Laravel Sanctum cho API authentication
- AWS Cognito integration
- JWT tokens

## 📚 API Documentation

API documentation được tạo tự động và có thể truy cập tại:
```
http://localhost:8081/apidoc
```

## 🧪 Testing

### Chạy tests
```bash
# Chạy tất cả tests
php artisan test

# Chạy specific test
php artisan test --filter TestName

# Chạy với coverage
php artisan test --coverage
```

## 🚀 Deployment

### Production build
```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Build frontend
npm run build

# Optimize Laravel
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Environment variables cần thiết cho production
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password

# AWS Configuration
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=your-region
AWS_BUCKET=your-bucket

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

## 🐛 Troubleshooting

### Lỗi thường gặp

1. **Composer memory limit**
   ```bash
   php -d memory_limit=-1 /usr/local/bin/composer install
   ```

2. **Permission issues**
   ```bash
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

3. **Database connection issues**
   - Kiểm tra cấu hình database trong `.env`
   - Đảm bảo database server đang chạy
   - Kiểm tra firewall và port

4. **NPM install issues**
   ```bash
   npm cache clean --force
   rm -rf node_modules package-lock.json
   npm install
   ```