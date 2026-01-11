# Khodat - Nền tảng Ký gửi Bất động sản

Dự án sử dụng **Laravel** (Backend API), **Next.js** (Frontend) và **OpenCart** (Admin quản lý) với Docker.

## 🚀 Khởi chạy nhanh với Docker

### Yêu cầu
- Docker Desktop
- Docker Compose

### Cài đặt và chạy

```bash
# Clone project
cd khodat

# Khởi động tất cả services
docker-compose up -d --build

# Xem logs
docker-compose logs -f

# Chạy migrations và setup Laravel
docker-compose exec backend composer install
docker-compose exec backend cp .env.example .env
docker-compose exec backend php artisan key:generate
docker-compose exec backend php artisan migrate

# Setup OpenCart webhook tables
docker exec -i khodat-mysql mysql -u root -proot khodat_db < admin/docker/webhook_tables.sql
```

### Truy cập

| Service | URL | Mô tả |
|---------|-----|-------|
| **OpenCart Frontend** | http://localhost:8088 | Trang chủ bất động sản |
| **OpenCart Admin** | http://localhost:8088/quantri | Quản trị OpenCart |
| **Next.js Frontend** | http://localhost:3015 | Dashboard người dùng |
| **Backend API** | http://localhost:8015/api | Laravel REST API |
| **phpMyAdmin** | http://localhost:8095 | Quản lý database |

## 📁 Cấu trúc dự án

```
khodat/
├── docker-compose.yml          # Docker configuration
├── admin/                      # OpenCart Admin (PHP 8)
│   ├── docker/
│   │   ├── Dockerfile          # PHP 8.2-FPM
│   │   ├── nginx.conf          # Nginx config
│   │   ├── php.ini             # PHP config
│   │   └── webhook_tables.sql  # Webhook database tables
│   ├── catalog/                # OpenCart frontend
│   │   └── controller/api/     # Webhook receiver
│   └── quantri/                # OpenCart admin panel
│       └── controller/extension/webhook.php
│
├── backend/                    # Laravel API
│   ├── Dockerfile
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   └── ConsignmentWebhookController.php
│   │   ├── Models/
│   │   └── Services/
│   │       └── ConsignmentWebhookService.php
│   ├── config/
│   ├── database/migrations/
│   └── routes/api.php
│
└── frontend/                   # Next.js App
    ├── Dockerfile
    └── src/app/
```

## ✨ Tính năng

### Authentication
- ✅ Đăng nhập/Đăng ký thường
- ✅ Đăng nhập Google OAuth
- ✅ Đăng nhập Facebook OAuth  
- ✅ Đăng nhập Zalo OAuth

### Dashboard
- ✅ Tổng quan tài khoản
- ✅ Thống kê giao dịch
- ✅ Hoạt động gần đây

### Ký gửi
- ✅ Tạo yêu cầu ký gửi mới
- ✅ Danh sách ký gửi
- ✅ Chi tiết & lịch sử ký gửi
- ✅ Quản lý trạng thái

### Nạp tiền
- ✅ VNPay (ATM/Internet Banking)
- ✅ Momo
- ✅ Chuyển khoản ngân hàng
- ✅ Lịch sử giao dịch

### Hỗ trợ
- ✅ Liên hệ Admin
- ✅ Tạo yêu cầu hỗ trợ
- ✅ Chat với hỗ trợ viên

### Gói đăng bài
- ✅ Gói 1 tháng (99.000đ - 10 bài đăng)
- ✅ Gói 2 tháng (179.000đ - 25 bài đăng, tiết kiệm 10%)
- ✅ Gói 3 tháng (249.000đ - 50 bài đăng, tiết kiệm 16%, phổ biến nhất)
- ✅ Gói 6 tháng (449.000đ - Không giới hạn, tiết kiệm 24%)
- ✅ Thanh toán bằng ví
- ✅ Gia hạn gói tự động
- ✅ Xem lịch sử mua gói

### 🔗 Webhook Integration (Mới)
- ✅ Tích hợp OpenCart với Backend Laravel qua Webhooks
- ✅ Đồng bộ ký gửi tự động từ Backend → OpenCart
- ✅ Nhận thông báo real-time khi ký gửi thay đổi
- ✅ Dashboard quản lý webhooks trong Admin

## 🐳 Docker Commands

```bash
# Start all services
docker-compose up -d

# Stop all services  
docker-compose down

# Rebuild containers
docker-compose up -d --build

# View logs
docker-compose logs -f backend
docker-compose logs -f admin-php
docker-compose logs -f frontend

# Execute commands in backend container
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan db:seed
docker-compose exec backend php artisan cache:clear

# Access MySQL
docker-compose exec mysql mysql -u khodat -pkhodat123 khodat
```

## ⚙️ Cấu hình

### Backend (.env)
Sao chép `.env.example` thành `.env` và cấu hình:

```bash
# Social Login - Lấy từ Developer Console của mỗi nền tảng
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=

FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=

ZALO_APP_ID=
ZALO_APP_SECRET=

# Payment - Lấy từ đăng ký merchant
VNPAY_TMN_CODE=
VNPAY_HASH_SECRET=

MOMO_PARTNER_CODE=
MOMO_ACCESS_KEY=
MOMO_SECRET_KEY=

# Webhook
CONSIGNMENT_WEBHOOK_SECRET=your_secret_key_here
```

### OpenCart Admin
Cấu hình trong Admin → Extensions → Webhook:
- **Backend URL**: `http://khodat-nginx:80` (Docker internal) hoặc `http://localhost:8015` (external)
- **Webhook Secret**: Khóa bí mật để xác thực webhook

### Frontend (.env.local)
```
NEXT_PUBLIC_API_URL=http://localhost:8015/api
```

## 📝 API Endpoints

### Auth
- `POST /api/auth/register` - Đăng ký
- `POST /api/auth/login` - Đăng nhập
- `POST /api/auth/logout` - Đăng xuất
- `GET /api/auth/google` - Login Google
- `GET /api/auth/facebook` - Login Facebook
- `GET /api/auth/zalo` - Login Zalo

### Dashboard
- `GET /api/dashboard` - Tổng quan
- `GET /api/dashboard/stats` - Thống kê

### Consignments (Protected - Yêu cầu đăng nhập)
- `GET /api/consignments` - Danh sách của user
- `POST /api/consignments` - Tạo mới
- `GET /api/consignments/{id}` - Chi tiết
- `PUT /api/consignments/{id}` - Cập nhật
- `DELETE /api/consignments/{id}` - Xóa

### Public Consignments (Public - Không cần đăng nhập)
- `GET /api/public/consignments` - Danh sách tất cả bất động sản đã duyệt
  - Query params: `search`, `status`, `min_price`, `max_price`, `sort_by`, `sort_order`, `per_page`
- `GET /api/public/consignments/{id}` - Chi tiết bất động sản

### Payments
- `POST /api/payments/vnpay/create` - Tạo thanh toán VNPay
- `POST /api/payments/momo/create` - Tạo thanh toán Momo
- `POST /api/payments/bank-transfer/create` - Tạo chuyển khoản

### Support
- `GET /api/supports` - Danh sách ticket
- `POST /api/supports` - Tạo ticket mới
- `POST /api/supports/{id}/messages` - Gửi tin nhắn

### Posting Packages (Gói đăng bài)
- `GET /api/posting-packages` - Danh sách gói đăng bài
- `GET /api/posting-packages/{id}` - Chi tiết gói
- `POST /api/posting-packages/purchase` - Mua gói bằng ví (Protected)
- `GET /api/my-packages` - Lịch sử mua gói của user (Protected)
- `GET /api/my-packages/current` - Gói đang hoạt động (Protected)

### Webhooks

#### Webhook Receiver (OpenCart)
OpenCart nhận webhooks từ Backend qua endpoint:
- `POST /index.php?route=api/webhook/receive` - Nhận webhook events
- `GET /index.php?route=api/webhook/health` - Health check

#### Webhook Management (Backend API)

**Public Endpoint (nhận webhook từ bên ngoài):**
- `POST /api/webhooks/consignment` - Nhận webhook event từ hệ thống bên ngoài

**Protected Endpoints (quản lý webhooks):**
- `POST /api/webhooks/register` - Đăng ký webhook mới
- `GET /api/webhooks` - Danh sách webhooks đã đăng ký
- `DELETE /api/webhooks/{webhookId}` - Xóa webhook
- `POST /api/webhooks/test` - Test gửi webhook

#### Webhook Events
| Event | Mô tả |
|-------|-------|
| `consignment.created` | Khi ký gửi mới được tạo |
| `consignment.updated` | Khi ký gửi được cập nhật |
| `consignment.status_changed` | Khi trạng thái ký gửi thay đổi |
| `consignment.approved` | Khi ký gửi được duyệt |
| `consignment.rejected` | Khi ký gửi bị từ chối |
| `consignment.sold` | Khi ký gửi đã bán |
| `consignment.cancelled` | Khi ký gửi bị hủy |

#### Webhook Payload Example
```json
{
  "event": "consignment.created",
  "data": {
    "consignment_id": 1,
    "code": "KG20260108ABCD",
    "title": "Đất nền 100m2",
    "status": "pending",
    "price": 500000000,
    "address": "Quận 9, TP.HCM"
  },
  "timestamp": "2026-01-08T08:00:00+07:00"
}
```

### OpenCart Webhook Integration

#### Thiết lập Webhook trong OpenCart Admin

1. **Truy cập Admin Panel**: http://localhost:8088/quantri
2. **Vào Extensions → Webhook Integration**
3. **Cấu hình Backend URL**: `http://khodat-nginx:80`
4. **Click "Đăng ký Webhook"** để đăng ký nhận events

#### Luồng hoạt động

```
┌─────────────────┐     Webhook Event      ┌─────────────────┐
│  Laravel        │ ───────────────────►   │  OpenCart       │
│  Backend        │                        │  Admin          │
│  (Port 8015)    │  consignment.created   │  (Port 8088)    │
└─────────────────┘  consignment.approved  └─────────────────┘
        │                                          │
        │  1. User tạo ký gửi                     │  4. Tạo/Cập nhật
        │  2. Admin duyệt                         │     sản phẩm
        │  3. Gửi webhook                         │  5. Hiển thị trên
        │                                          │     website
        ▼                                          ▼
┌─────────────────┐                        ┌─────────────────┐
│  MySQL (khodat) │                        │  MySQL          │
│  Consignments   │                        │  (khodat_db)    │
│  table          │                        │  Products table │
└─────────────────┘                        └─────────────────┘
```

#### Các chức năng chính

| Chức năng | Mô tả |
|-----------|-------|
| **Đăng ký Webhook** | Đăng ký OpenCart nhận events từ Backend |
| **Đồng bộ Ký gửi** | Đồng bộ thủ công tất cả ký gửi đã duyệt |
| **Xem Logs** | Theo dõi lịch sử webhook |
| **Test Connection** | Kiểm tra kết nối với Backend |

## 🔒 Bảo mật

### Webhook Signature Verification
Mọi webhook đều được ký bằng HMAC-SHA256:
```
X-Webhook-Signature: hmac_sha256(payload, secret)
```

### Headers được gửi
```
Content-Type: application/json
X-Webhook-Event: consignment.created
X-Webhook-Timestamp: 2026-01-10T10:00:00+07:00
X-Webhook-Signature: abc123...
```

## 🛠️ Troubleshooting

### OpenCart không nhận được webhook
1. Kiểm tra Backend URL đúng: `http://khodat-nginx:80`
2. Kiểm tra Webhook Secret khớp giữa 2 hệ thống
3. Xem logs: `docker-compose logs admin-php`

### Lỗi kết nối MySQL trong Docker
1. Sử dụng hostname `mysql` thay vì `localhost`
2. Kiểm tra DB_HOST trong config.php

### PHP 8 Compatibility
OpenCart đã được cập nhật để tương thích với PHP 8:
- Xóa `create_function()` → dùng anonymous functions
- Xóa `mysql_*` → dùng `mysqli`
- Xóa `magic_quotes_gpc` check

## License

MIT License
