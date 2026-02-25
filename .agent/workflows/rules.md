---
description: Quy tắc và cấu trúc code cho dự án Khodat
---

# Coding Rules

## Kiến trúc tổng quan

- **Go API Gateway** (`api-gateway/`): Proxy requests đến Laravel backend, handle JWT auth cho admin
- **Laravel Backend** (`clients/backend/`): User API (auth, consignments, payments, upload)
- **Laravel Sàn đất** (`san-dat/`): Public website, gọi API từ Go gateway
- **Vue Admin** (`admin/`): Admin dashboard, gọi API qua Go gateway
- **Next.js Frontend** (`clients/frontend/`): User dashboard, gọi trực tiếp Laravel Backend

## Naming Conventions

### Backend (Laravel)
- Controllers: `PascalCase` + `Controller` suffix (e.g., `ConsignmentController`)
- Models: `PascalCase` singular (e.g., `Consignment`)
- Migrations: snake_case với prefix timestamp
- Routes: RESTful naming (`GET /consignments`, `POST /consignments`)

### Frontend (Vue / Next.js)
- Components: `PascalCase.vue` / `PascalCase.tsx`
- Views: thư mục feature + `List.vue`, `Detail.vue`
- API service: dùng `adminApi` object trong `admin/src/services/api.js`
- Store: Pinia stores trong `admin/src/store/`

## Image Handling

- **KHÔNG** dùng `FileReader.readAsDataURL()` cho image upload
- **PHẢI** dùng `FormData` upload qua `/api/upload/image-optimized`
- Ảnh được convert sang **WebP** tự động bởi `ImageOptimizer`
- Lưu trữ trên **MinIO** (S3-compatible), KHÔNG lưu base64 vào database
- `HasFileUpload` trait tự động detect S3 vs local disk

## API Patterns

### Go Gateway routes (admin)
- Prefix: `/api/admin/...`
- Auth: JWT token trong header `Authorization: Bearer {token}`
- Go proxy forward mọi header (kể cả Content-Type multipart/form-data)

### Laravel Backend routes (user)
- Prefix: `/api/...`
- Auth: Laravel Sanctum token
- Protected routes: middleware `auth:sanctum`

## Environment Variables

- Dev: `.env` trong mỗi service directory
- Prod: `.env.prod` ở root, dùng với `docker-compose.prod.yml`
- MinIO config: `AWS_*` variables trong backend `.env`

## Docker

- Dev: `docker-compose.yml` — ports expose ra host
- Prod: `docker-compose.prod.yml` — ports bind `127.0.0.1` only, Nginx proxy phía trước
