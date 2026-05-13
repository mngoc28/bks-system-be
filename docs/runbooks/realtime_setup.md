# Runbook — Realtime Setup (Pusher protocol)

Tài liệu này hướng dẫn cấu hình WebSocket cho Partner Portal 360 (Phase 2).
Hệ thống dùng giao thức Pusher; có thể chạy với **Soketi** (self-host) ở
local/staging hoặc **Pusher Cloud** ở production.

---

## 1. Local/Staging với Soketi

### 1.1 Khởi động container

```bash
cd /path/to/bks-system-be
docker compose -f docker-compose.soketi.yml up -d
```

Soketi lắng nghe `ws://localhost:6001` (WebSocket) và HTTP API `:9601`.

### 1.2 Cấu hình `.env` của backend

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=local-app
PUSHER_APP_KEY=local-key
PUSHER_APP_SECRET=local-secret
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
PUSHER_APP_CLUSTER=mt1

# FE đọc qua Vite ENV (xem README frontend)
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

> Sau khi đổi `.env`, chạy `php artisan config:clear`.

### 1.3 Smoke test

```bash
# Cần cài wscat: npm i -g wscat
npx wscat -c "ws://127.0.0.1:6001/app/local-key?protocol=7&client=js&version=8.5.0"
```

Trả về JSON `{"event":"pusher:connection_established", ...}` là OK.

Test broadcast từ Tinker:

```bash
php artisan tinker
>>> $b = App\Models\Booking::first();
>>> broadcast(new App\Events\BookingConfirmed($b, $b->room->building->user_id, $b->room->building_id, 1));
```

---

## 2. Production với Pusher Cloud

### 2.1 Tạo app trên Pusher dashboard

1. Truy cập https://dashboard.pusher.com → **Channels** → **Create app**.
2. Chọn cluster gần region production (ví dụ `ap1` cho Singapore, `mt1` cho US East).
3. Copy `app_id`, `key`, `secret`, `cluster`.

### 2.2 Cấu hình `.env`

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=<app_id từ dashboard>
PUSHER_APP_KEY=<key>
PUSHER_APP_SECRET=<secret>
PUSHER_HOST=             # để trống → tự fallback api-{cluster}.pusher.com
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=ap1   # hoặc cluster bạn chọn
```

### 2.3 Rate limit Pusher Free plan

Theo Pusher Cloud Free (sandbox tier, 2026):

| Hạn mức              | Giá trị                    |
| -------------------- | -------------------------- |
| Concurrent conns     | 100                        |
| Daily messages       | 200,000                    |
| Max message size     | 10 KB                      |
| SSL endpoints        | Có                         |
| Webhooks             | Có (HTTPS only)            |

> Khi vượt hạn mức, message rớt im lặng. Hệ thống đã có **polling fallback
> 30s** ở FE (`useBookingsRealtime`) nên trải nghiệm vẫn không bị block,
> chỉ trễ tối đa 30s.

### 2.4 Khi cần upgrade?

- > 100 partners online cùng lúc → upgrade plan **Startup**.
- > 200K messages/ngày → cân nhắc switch sang Soketi self-host (chạy
  trên Kubernetes hoặc VPS riêng), reuse cùng Pusher protocol nên FE
  KHÔNG cần thay đổi code.

---

## 3. Channel namespace

| Channel                         | Auth                              | Mục đích             |
| ------------------------------- | --------------------------------- | -------------------- |
| `private-partner.{partnerId}`   | `Auth::id() === $partnerId`       | Booking events scope partner |
| `private-property.{propertyId}` | Owner of `Building::$propertyId`  | Calendar events theo property (Phase 3+) |

Endpoint auth: **`POST /api/v1/broadcasting/auth`** (middleware `jwt.auth`).

---

## 4. Troubleshooting

| Triệu chứng                                         | Nguyên nhân & xử lý                                                                                  |
| --------------------------------------------------- | ---------------------------------------------------------------------------------------------------- |
| FE không nhận event sau confirm                     | Check queue worker (`php artisan queue:work redis`). Pusher Cloud cần `BROADCAST_CONNECTION=redis`.  |
| `/broadcasting/auth` trả 401                        | Token JWT expired → FE phải refresh trước khi subscribe lại.                                         |
| `/broadcasting/auth` trả 403                        | Channel callback trả false → user không sở hữu partner/property tương ứng. Đúng theo SRS AC #10.     |
| Soketi container restart loop                       | Port 6001 đang bị chiếm. Đổi port mapping hoặc kill process khác.                                    |
| Message size > 10KB                                 | Slim payload theo `broadcastWith()` — không gửi PII, không gửi nested relations.                     |
