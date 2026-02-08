# Cấu trúc Dự án Khodat - Chi tiết

## Tổng quan

```
kyguidat/
├── api-gateway/          # Golang API (Port 8080)
├── san-dat/              # Laravel Sàn đất (Port 8088)
├── admin/                # Vue Admin (Port 8089)
├── clients/              # User Services
│   ├── backend/          # Laravel Backend User (Port 8015)
│   └── frontend/         # Next.js Frontend User (Port 3015)
├── docker-compose.yml
├── docker-compose.prod.yml
├── deploy.sh
└── README.md
```

---

## 1. Golang API (`api-gateway/`)

API Gateway cho Sàn đất và Admin, sử dụng Go + Gin framework.

```
api-gateway/
├── cmd/
│   └── server/
│       └── main.go                 # Entry point, khởi tạo server
│
├── internal/
│   ├── config/
│   │   └── config.go               # Load env, database config
│   │
│   ├── handlers/
│   │   ├── consignment.go          # CRUD consignments
│   │   ├── user.go                 # User management
│   │   ├── category.go             # Categories
│   │   ├── location.go             # Locations/Areas
│   │   └── admin.go                # Admin dashboard, reports
│   │
│   ├── middleware/
│   │   ├── auth.go                 # JWT authentication
│   │   ├── cors.go                 # CORS middleware
│   │   └── logger.go               # Request logging
│   │
│   ├── models/
│   │   ├── consignment.go          # Consignment struct
│   │   ├── user.go                 # User struct
│   │   ├── category.go             # Category struct
│   │   └── transaction.go          # Transaction struct
│   │
│   ├── repository/
│   │   ├── mysql.go                # MySQL connection
│   │   ├── consignment_repo.go     # Consignment queries
│   │   └── user_repo.go            # User queries
│   │
│   └── services/
│       ├── consignment_service.go  # Business logic
│       └── admin_service.go        # Admin business logic
│
├── pkg/
│   └── response/
│       └── response.go             # Standard API response
│
├── Dockerfile
├── Dockerfile.prod
├── go.mod
├── go.sum
└── .env.example
```

---

## 2. Laravel Sàn đất (`san-dat/`)

Trang công khai hiển thị bất động sản, gọi API từ Golang.

```
san-dat/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── HomeController.php           # Trang chủ
│   │   │   ├── ConsignmentController.php    # Danh sách & chi tiết BĐS
│   │   │   ├── SearchController.php         # Tìm kiếm
│   │   │   └── ContactController.php        # Liên hệ
│   │   │
│   │   └── Middleware/
│   │       └── CacheResponse.php            # Cache trang
│   │
│   ├── Services/
│   │   └── GolangApiService.php             # HTTP client to Golang API
│   │
│   └── View/
│       └── Components/
│           ├── ConsignmentCard.php
│           └── SearchFilter.php
│
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   └── app.blade.php                # Main layout
│   │   │
│   │   ├── home.blade.php                   # Trang chủ
│   │   │
│   │   ├── consignments/
│   │   │   ├── index.blade.php              # Danh sách BĐS
│   │   │   └── show.blade.php               # Chi tiết BĐS
│   │   │
│   │   ├── search/
│   │   │   └── results.blade.php            # Kết quả tìm kiếm
│   │   │
│   │   └── components/
│   │       ├── header.blade.php
│   │       ├── footer.blade.php
│   │       ├── consignment-card.blade.php
│   │       └── search-filter.blade.php
│   │
│   ├── css/
│   │   └── app.css
│   │
│   └── js/
│       └── app.js
│
├── routes/
│   └── web.php                              # Web routes
│
├── docker/
│   ├── nginx.conf
│   └── php.ini
│
├── Dockerfile
├── Dockerfile.prod
└── .env.example
```

---

## 3. Vue Admin (`admin/`)

Trang quản trị tổng, sử dụng Vue 3 + Vite + Pinia.

```
admin/
├── src/
│   ├── views/
│   │   ├── Dashboard.vue                    # Tổng quan
│   │   │
│   │   ├── consignments/
│   │   │   ├── List.vue                     # Danh sách bài đăng
│   │   │   ├── Detail.vue                   # Chi tiết bài đăng
│   │   │   └── Pending.vue                  # Chờ duyệt
│   │   │
│   │   ├── users/
│   │   │   ├── List.vue                     # Danh sách users
│   │   │   └── Detail.vue                   # Chi tiết user
│   │   │
│   │   ├── transactions/
│   │   │   └── List.vue                     # Lịch sử giao dịch
│   │   │
│   │   ├── reports/
│   │   │   └── Index.vue                    # Báo cáo & thống kê
│   │   │
│   │   └── auth/
│   │       └── Login.vue                    # Đăng nhập admin
│   │
│   ├── components/
│   │   ├── layout/
│   │   │   ├── Sidebar.vue
│   │   │   ├── Header.vue
│   │   │   └── Footer.vue
│   │   │
│   │   ├── common/
│   │   │   ├── DataTable.vue
│   │   │   ├── Pagination.vue
│   │   │   ├── Modal.vue
│   │   │   └── StatusBadge.vue
│   │   │
│   │   └── charts/
│   │       ├── LineChart.vue
│   │       └── BarChart.vue
│   │
│   ├── services/
│   │   └── api.js                           # Axios instance
│   │
│   ├── store/
│   │   ├── index.js                         # Pinia setup
│   │   ├── auth.js                          # Auth state
│   │   └── consignment.js                   # Consignment state
│   │
│   ├── router/
│   │   └── index.js                         # Vue Router
│   │
│   ├── composables/
│   │   └── useApi.js                        # API composable
│   │
│   ├── App.vue
│   └── main.js
│
├── public/
│   └── favicon.ico
│
├── Dockerfile
├── Dockerfile.prod
├── package.json
├── vite.config.js
├── tailwind.config.js
└── .env.example
```

---

## 4. User Services (`clients/`)

Thư mục chứa các services dành cho User đăng ký và đăng bài.

### 4.1 Laravel Backend (`clients/backend/`)

API cho User đăng ký, đăng bài, thanh toán.

```
clients/backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── ConsignmentController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── PaymentController.php
│   │   │   └── PostingPackageController.php
│   │   │
│   │   └── Middleware/
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Consignment.php
│   │   ├── Transaction.php
│   │   └── PostingPackage.php
│   │
│   └── Services/
│
├── database/
│   ├── migrations/
│   └── seeders/
│
├── routes/
│   └── api.php
│
├── docker/
├── Dockerfile
└── Dockerfile.prod
```

### 4.2 Next.js Frontend (`clients/frontend/`)

Dashboard cho User.

```
clients/frontend/
├── src/
│   ├── app/
│   │   ├── layout.tsx
│   │   ├── page.tsx
│   │   ├── dashboard/
│   │   ├── consignments/
│   │   ├── wallet/
│   │   └── packages/
│   │
│   ├── components/
│   ├── services/
│   ├── hooks/
│   └── types/
│
├── Dockerfile
├── Dockerfile.prod
└── package.json
```



---

## Docker Services

| Service | Container Name | Internal Port | External Port |
|---------|---------------|---------------|---------------|
| api-gateway | khodat-api-gateway | 8080 | 8080 |
| san-dat | khodat-san-dat | 80 | 8088 |
| admin | khodat-admin | 80 | 8089 |
| clients-backend | khodat-backend | 8000 | 8015 |
| clients-frontend | khodat-frontend | 3000 | 3015 |
| mysql | khodat-mysql | 3306 | 3321 |
| redis | khodat-redis | 6379 | 6394 |
| phpmyadmin | khodat-phpmyadmin | 80 | 8095 |
