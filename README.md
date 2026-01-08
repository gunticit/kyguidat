# Khodat - Nền tảng Ký gửi

Dự án sử dụng **Laravel** (Backend API) và **Next.js** (Frontend) với Docker.

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

# Chạy migrations và setup
docker-compose exec backend composer install
docker-compose exec backend cp .env.example .env
docker-compose exec backend php artisan key:generate
docker-compose exec backend php artisan migrate
```

### Truy cập

| Service | URL |
|---------|-----|
| **Frontend (Next.js)** | http://localhost:3015 |
| **Backend API** | http://localhost:8015/api |
| **phpMyAdmin** | http://localhost:8095 |

## 📁 Cấu trúc dự án

```
khodat/
├── docker-compose.yml          # Docker configuration
├── backend/                    # Laravel API
│   ├── Dockerfile
│   ├── docker/
│   │   ├── nginx/default.conf
│   │   ├── php/local.ini
│   │   └── mysql/init.sql
│   ├── app/
│   │   ├── Http/Controllers/
│   │   ├── Models/
│   │   └── Services/
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
```

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
Webhooks cho phép nhận thông báo real-time khi có sự kiện xảy ra với consignment.

**Public Endpoint (nhận webhook từ bên ngoài):**
- `POST /api/webhooks/consignment` - Nhận webhook event từ hệ thống bên ngoài

**Protected Endpoints (quản lý webhooks):**
- `POST /api/webhooks/register` - Đăng ký webhook mới
- `GET /api/webhooks` - Danh sách webhooks đã đăng ký
- `DELETE /api/webhooks/{webhookId}` - Xóa webhook
- `POST /api/webhooks/test` - Test gửi webhook

**Webhook Events:**
- `consignment.created` - Khi ký gửi mới được tạo
- `consignment.updated` - Khi ký gửi được cập nhật
- `consignment.status_changed` - Khi trạng thái ký gửi thay đổi
- `consignment.approved` - Khi ký gửi được duyệt
- `consignment.rejected` - Khi ký gửi bị từ chối
- `consignment.sold` - Khi ký gửi đã bán
- `consignment.cancelled` - Khi ký gửi bị hủy

**Webhook Payload Example:**
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

## License

MIT License
# kyguidat
