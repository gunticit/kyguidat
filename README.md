# Khodat - Nền tảng Ký gửi Bất động sản

Hệ thống quản lý ký gửi bất động sản với kiến trúc microservices.

## 🏗️ Kiến trúc Hệ thống

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           KHODAT PLATFORM                                │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│   ┌─────────────────────────────────────────────────────────────────┐   │
│   │                    USER SERVICES                                  │   │
│   │  ┌─────────────────┐         ┌─────────────────┐                 │   │
│   │  │  Next.js        │ ──────► │  Laravel        │                 │   │
│   │  │  Frontend       │         │  Backend API    │                 │   │
│   │  │  (Port 3015)    │         │  (Port 8015)    │                 │   │
│   │  └─────────────────┘         └────────┬────────┘                 │   │
│   └───────────────────────────────────────┼─────────────────────────┘   │
│                                           │                              │
│   ┌───────────────────────────────────────┼─────────────────────────┐   │
│   │                    PUBLIC & ADMIN SERVICES                       │   │
│   │  ┌─────────────────┐         ┌───────▼────────┐                 │   │
│   │  │  Laravel        │ ──────► │  Golang API    │ ◄───────┐       │   │
│   │  │  Sàn đất        │         │  (Port 8080)   │         │       │   │
│   │  │  (Port 8088)    │         └───────┬────────┘         │       │   │
│   │  └─────────────────┘                 │                   │       │   │
│   │                                      │    ┌──────────────┴──┐   │   │
│   │                                      │    │  Vue Admin      │   │   │
│   │                                      │    │  (Port 8089)    │   │   │
│   │                                      │    └─────────────────┘   │   │
│   └──────────────────────────────────────┼──────────────────────────┘   │
│                                          │                              │
│   ┌──────────────────────────────────────┼──────────────────────────┐   │
│   │                    DATA LAYER                                    │   │
│   │  ┌─────────────────┐         ┌───────▼────────┐                 │   │
│   │  │  Redis          │         │  MySQL         │                 │   │
│   │  │  (Port 6394)    │         │  (Port 3321)   │                 │   │
│   │  │  Cache/Sessions │         │  Database      │                 │   │
│   │  └─────────────────┘         └────────────────┘                 │   │
│   └─────────────────────────────────────────────────────────────────┘   │
│                                                                          │
└─────────────────────────────────────────────────────────────────────────┘
```

## 📦 Services

| Service | Công nghệ | Port | Mô tả |
|---------|-----------|------|-------|
| **User Frontend** | Next.js | 3015 | Dashboard người dùng đăng ký, đăng bài |
| **User Backend** | Laravel | 8015 | API cho người dùng (auth, consignments, payments) |
| **Sàn đất** | Laravel | 8088 | Trang công khai hiển thị bài đăng BĐS |
| **Golang API** | Go + Gin | 8080 | API cho Sàn đất và Admin |
| **Vue Admin** | Vue 3 | 8089 | Trang quản trị tổng |
| **MinIO** | S3-Compatible | 9000/9001 | Object Storage (hình ảnh WebP) |
| **Socket.IO** | Node.js | 3020 | Real-time support chat |
| **MySQL** | MySQL 8.0 | 3321 | Database chung |
| **Redis** | Redis | 6394 | Cache & Sessions |
| **phpMyAdmin** | - | 8095 | Quản lý database |

## 🚀 Khởi chạy nhanh

### Yêu cầu
- Docker Desktop
- Docker Compose

### Cài đặt và chạy

```bash
# Clone project
cd khodat

# Khởi động tất cả services
docker-compose up -d --build

# Setup Laravel Backend (User)
docker-compose exec backend composer install
docker-compose exec backend cp .env.example .env
docker-compose exec backend php artisan key:generate
docker-compose exec backend php artisan migrate

# Setup Laravel Sàn đất
docker-compose exec san-dat composer install
docker-compose exec san-dat cp .env.example .env
docker-compose exec san-dat php artisan key:generate
```

### Truy cập

| Service | URL |
|---------|-----|
| **Sàn đất** | http://localhost:8088 |
| **Vue Admin** | http://localhost:8089 |
| **User Dashboard** | http://localhost:3015 |
| **User API** | http://localhost:8015/api |
| **Golang API** | http://localhost:8080/api |
| **MinIO Console** | http://localhost:9001 |
| **phpMyAdmin** | http://localhost:8095 |

## ✨ Tính năng

### User Services (Next.js + Laravel)
- ✅ Đăng nhập/Đăng ký (Email, Google, Facebook, Zalo)
- ✅ Dashboard cá nhân
- ✅ Tạo & quản lý bài đăng ký gửi
- ✅ Nạp tiền (VNPay, Momo, Bank Transfer)
- ✅ Mua gói đăng bài
- ✅ Hỗ trợ & Chat

### Sàn đất (Laravel)
- ✅ Hiển thị danh sách BĐS đã duyệt
- ✅ Tìm kiếm & lọc theo khu vực, giá
- ✅ Chi tiết bất động sản
- ✅ Liên hệ người đăng

### Admin (Vue 3)
- ✅ Dashboard thống kê tổng quan
- ✅ Quản lý người dùng
- ✅ Duyệt/Từ chối bài đăng
- ✅ Quản lý giao dịch
- ✅ Báo cáo & Analytics

## 📁 Cấu trúc Thư mục

```
kyguidat/
├── api-gateway/              # Golang API
│   ├── cmd/server/           # Entry point
│   ├── internal/
│   │   ├── config/           # Configuration
│   │   ├── handlers/         # HTTP handlers
│   │   ├── middleware/       # Auth, CORS, etc.
│   │   ├── models/           # Data models
│   │   └── repository/       # Database layer
│   ├── Dockerfile
│   └── go.mod
│
├── san-dat/                  # Laravel Sàn đất
│   ├── app/
│   │   ├── Http/Controllers/
│   │   └── Services/         # Golang API client
│   ├── resources/views/
│   ├── routes/web.php
│   └── Dockerfile
│
├── admin/                    # Vue Admin
│   ├── src/
│   │   ├── views/            # Pages
│   │   ├── components/       # UI components
│   │   ├── services/         # API client
│   │   ├── store/            # Pinia store
│   │   └── router/
│   └── Dockerfile
│
├── clients/                  # User Services
│   ├── backend/              # Laravel Backend (User API)
│   │   ├── app/
│   │   ├── database/
│   │   ├── routes/api.php
│   │   └── Dockerfile
│   │
│   └── frontend/             # Next.js Frontend (User)
│       ├── src/
│       └── Dockerfile
│
├── docker-compose.yml        # Development
├── docker-compose.prod.yml   # Production
├── deploy.sh
└── README.md
```

## 🐳 Docker Commands

```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# Rebuild specific service
docker-compose up -d --build api-gateway
docker-compose up -d --build san-dat
docker-compose up -d --build admin

# View logs
docker-compose logs -f api-gateway
docker-compose logs -f san-dat
docker-compose logs -f admin
docker-compose logs -f backend
docker-compose logs -f frontend

# Access containers
docker-compose exec api-gateway sh
docker-compose exec san-dat bash
docker-compose exec admin sh
```

## ⚙️ Cấu hình

### Golang API (.env)
```env
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=khodat
DB_USERNAME=khodat
DB_PASSWORD=khodat123
JWT_SECRET=your_jwt_secret
```

### Laravel Backend (.env)
```env
DB_HOST=mysql
DB_DATABASE=khodat
DB_USERNAME=khodat
DB_PASSWORD=khodat123

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=

VNPAY_TMN_CODE=
VNPAY_HASH_SECRET=
```

### Laravel Sàn đất (.env)
```env
GOLANG_API_URL=http://api-gateway:8080
```

### Vue Admin (.env)
```env
VITE_API_URL=http://localhost:8080/api
```

### Next.js Frontend (.env.local)
```env
NEXT_PUBLIC_API_URL=http://localhost:8015/api
```

### MinIO Storage
```env
# Trong clients/backend/.env
AWS_ACCESS_KEY_ID=khodat_minio
AWS_SECRET_ACCESS_KEY=khodat_minio_secret
AWS_BUCKET=khodat
AWS_URL=http://localhost:9000/khodat
AWS_ENDPOINT=http://minio:9000
AWS_USE_PATH_STYLE_ENDPOINT=true
IMAGE_QUALITY=80
IMAGE_FORMAT=webp
```
> MinIO Console: `http://localhost:9001` — Login: `khodat_minio` / `khodat_minio_secret`
> Cần tạo bucket `khodat` với Access Policy = `public` trước khi dùng.

## 📝 API Endpoints

### Golang API (Port 8080)

**Public:**
```
GET  /api/consignments          # Danh sách BĐS đã duyệt
GET  /api/consignments/:id      # Chi tiết BĐS
GET  /api/categories            # Danh mục
GET  /api/locations             # Khu vực
```

**Admin (Protected):**
```
GET    /api/admin/dashboard     # Thống kê
GET    /api/admin/users         # Danh sách users
GET    /api/admin/consignments  # Tất cả bài đăng
PUT    /api/admin/consignments/:id/approve  # Duyệt bài
PUT    /api/admin/consignments/:id/reject   # Từ chối bài
GET    /api/admin/transactions  # Giao dịch
```

### Laravel Backend (Port 8015)

**Auth:**
```
POST /api/auth/register
POST /api/auth/login
POST /api/auth/logout
GET  /api/auth/google
GET  /api/auth/facebook
```

**User (Protected):**
```
GET    /api/dashboard
GET    /api/consignments
POST   /api/consignments
PUT    /api/consignments/:id
DELETE /api/consignments/:id
POST   /api/payments/vnpay/create
GET    /api/posting-packages
POST   /api/posting-packages/purchase
```

**Upload (Protected):**
```
POST   /api/upload/image-optimized     # Upload ảnh → WebP → MinIO
POST   /api/upload/images-optimized    # Upload nhiều ảnh → WebP → MinIO
POST   /api/upload/image               # Upload ảnh không optimize
POST   /api/upload/base64              # Upload từ base64
```

**Artisan Commands:**
```bash
php artisan images:migrate-base64           # Migrate base64 → MinIO WebP
php artisan images:migrate-base64 --dry-run # Xem trước (không thay đổi data)
```

## 🔒 Bảo mật

- Laravel Sanctum cho User API authentication
- JWT tokens cho Golang API
- CORS configuration cho cross-origin requests
- Rate limiting trên tất cả APIs

## 🛠️ Troubleshooting

### Golang API không kết nối được MySQL
```bash
docker-compose logs api-gateway
# Đảm bảo DB_HOST=mysql (không phải localhost)
```

### Sàn đất không gọi được Golang API
```bash
# Trong container san-dat, kiểm tra kết nối:
docker-compose exec san-dat curl http://api-gateway:8080/api/health
```

### Vue Admin CORS error
- Kiểm tra Golang API đã enable CORS cho `http://localhost:8089`

## License

MIT License


admin@khodat.com
admin123	Admin	✅
moderator@khodat.com
mod123	Kiểm duyệt	✅
publisher@khodat.com
pub123	Đăng bài	✅
it@khodat.comtạo 
admin@123	IT	✅