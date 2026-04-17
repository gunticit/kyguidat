# 🚀 Hướng dẫn Deploy khodat.com lên Ubuntu VPS

## Yêu cầu VPS
- Ubuntu 22.04+
- Docker & Docker Compose
- Nginx (đã cài trên host)
- Certbot (đã cài trên host)
- RAM ≥ 4GB, Disk ≥ 40GB

---

## Kiến trúc

```
Internet → Nginx (Host) → Docker Containers
                ↓
    ┌───────────────────────────────────┐
    │  khodat.com    → :8088     │  san-dat (Laravel)
    │  api.khodat.com → :8080    │  api-gateway (Go)
    │  admin.khodat.com → :8089  │  admin (Vue)
    │  app.khodat.com → :3015   │  frontend (Next.js)
    │  backend.khodat.com → :8015│  backend-nginx → backend
    │  socket.khodat.com → :3020 │  socket (Node.js)
    │  storage.khodat.com → :9000│  minio (S3 Object Storage)
    └───────────────────────────────────┘
              ↓              ↓
          MySQL:3316    Redis (internal)
```

---

## Bước 1: Cài đặt Docker (nếu chưa có)

```bash
# Cài Docker
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER

# Đăng xuất và đăng nhập lại
exit
# ssh lại vào VPS

# Kiểm tra
docker --version
docker compose version
```

---

## Bước 2: Cài đặt Nginx & Certbot (nếu chưa có)

```bash
sudo apt update
sudo apt install -y nginx certbot python3-certbot-nginx

# Kiểm tra
nginx -v
certbot --version
```

---

## Bước 3: Cấu hình DNS

Tại nhà cung cấp domain, tạo các bản ghi DNS:

| Type | Name | Value |
|------|------|-------|
| A | @ | IP_VPS |
| A | www | IP_VPS |
| A | api | IP_VPS |
| A | admin | IP_VPS |
| A | app | IP_VPS |
| A | backend | IP_VPS |
| A | socket | IP_VPS |
| A | storage | IP_VPS |

⏰ Chờ DNS propagate (5-30 phút). Kiểm tra:

```bash
dig +short khodat.com
dig +short api.khodat.com
```

---

## Bước 4: Clone source code

```bash
cd /home/$USER
git clone https://github.com/YOUR_REPO/kyguidat.git
cd kyguidat
```

---

## Bước 5: Tạo file .env.prod

```bash
cp .env.prod.example .env.prod
nano .env.prod
```

### Điền các giá trị quan trọng:

```env
# --- Database (đặt mật khẩu MẠNH) ---
DB_DATABASE=khodat
DB_USERNAME=khodat
DB_PASSWORD=MatKhauRatManh2026!
MYSQL_ROOT_PASSWORD=RootMatKhauManh2026!

# --- Redis ---
REDIS_PASSWORD=RedisPass2026!

# --- Laravel APP_KEY (tạo random 32 ký tự) ---
APP_KEY=base64:$(openssl rand -base64 32)

# --- JWT Secret (tạo random) ---
JWT_SECRET=$(openssl rand -hex 32)

# --- SMTP (dùng Gmail App Password) ---
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-real-email@gmail.com
MAIL_PASSWORD=xxxx-xxxx-xxxx-xxxx     # Gmail App Password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@khodat.com
MAIL_FROM_NAME="Ky Gui Dat Vuon"

# --- MinIO / S3 Storage ---
MINIO_ROOT_USER=khodat_minio
MINIO_ROOT_PASSWORD=MatKhauMinIoManh2026!
MINIO_BUCKET=khodat
MINIO_PUBLIC_URL=https://storage.khodat.com/khodat
```

### Cách tạo Gmail App Password:
1. Vào https://myaccount.google.com/security
2. Bật **2-Step Verification**
3. Vào **App passwords** → Chọn "Mail" → Chọn "Other" → Nhập "kyguidatvuon"
4. Copy 16 ký tự → paste vào `MAIL_PASSWORD`

### Cách tạo APP_KEY:
```bash
# Chạy lệnh này trên VPS
echo "base64:$(openssl rand -base64 32)"
# Copy kết quả vào APP_KEY trong .env.prod
```

---

## Bước 6: Cấu hình Nginx

```bash
# Copy file config
sudo cp nginx/kyguidatvuon.conf /etc/nginx/sites-available/kyguidat.conf

# Enable site
sudo ln -sf /etc/nginx/sites-available/kyguidat.conf /etc/nginx/sites-enabled/

# Xóa default site (nếu có)
sudo rm -f /etc/nginx/sites-enabled/default

# Test config
sudo nginx -t

# Reload
sudo systemctl reload nginx
```

---

## Bước 7: Cài đặt SSL (Let's Encrypt)

```bash
sudo certbot --nginx \
  -d khodat.com \
  -d www.khodat.com \
  -d api.khodat.com \
  -d admin.khodat.com \
  -d app.khodat.com \
  -d backend.khodat.com \
  -d socket.khodat.com \
  --email your-email@gmail.com \
  --agree-tos \
  --no-eff-email \
  --redirect
```

> Certbot tự sửa file Nginx config để thêm SSL + redirect HTTP → HTTPS

### Kiểm tra auto-renew:
```bash
sudo certbot renew --dry-run
```

---

## Bước 8: Firewall (UFW)

```bash
sudo ufw allow 22/tcp     # SSH
sudo ufw allow 80/tcp     # HTTP
sudo ufw allow 443/tcp    # HTTPS
sudo ufw enable
sudo ufw status
```

> ⚠️ KHÔNG mở port 3316, 8080, 8015, etc. — chỉ Nginx truy cập qua 127.0.0.1

---

## Bước 9: Deploy lần đầu 🚀

```bash
# Cấp quyền
chmod +x deploy.sh

# Deploy (build tất cả images)
bash deploy.sh --build
```

### Quá trình deploy sẽ:
1. ✅ Pull latest code từ Git
2. ✅ Build tất cả Docker images
3. ✅ Start containers
4. ✅ Chờ MySQL ready
5. ✅ Chạy database migrations
6. ✅ Reload Nginx
7. ✅ Dọn dẹp Docker images cũ

---

## Bước 10: Kiểm tra

### Kiểm tra containers:
```bash
docker compose -f docker-compose.yml --env-file .env.prod ps
```

Tất cả phải ở trạng thái `Up`:
```
khodat-api-gateway    Up
khodat-san-dat        Up
khodat-admin          Up
khodat-backend        Up
khodat-backend-nginx  Up
khodat-frontend       Up
khodat-minio          Up (healthy)
khodat-mysql          Up (healthy)
khodat-redis          Up
khodat-socket         Up
```

### Setup MinIO bucket:
```bash
# Cách 1: Dùng mc CLI
docker exec khodat-minio mc alias set local http://localhost:9000 khodat_minio 'MatKhauMinIoManh2026!'
docker exec khodat-minio mc mb local/khodat --ignore-existing
docker exec khodat-minio mc anonymous set public local/khodat

# Cách 2: Dùng MinIO Console
# Dùng ssh tunnel để truy cập MinIO Console:
ssh -L 9002:127.0.0.1:9002 user@vps
# Mở http://localhost:9002
# Tạo bucket 'khodat' và set policy = public
```

### Migrate dữ liệu hình ảnh cũ (base64 → MinIO WebP):
```bash
# Xem trước (không thay đổi data)
docker compose -f docker-compose.yml --env-file .env.prod exec backend \
  php artisan images:migrate-base64 --dry-run

# Thực hiện migrate
docker compose -f docker-compose.yml --env-file .env.prod exec backend \
  php artisan images:migrate-base64
```

### Kiểm tra website:
```bash
curl -I https://khodat.com       # → 200
curl -I https://api.khodat.com    # → 200
curl -I https://app.khodat.com    # → 200
curl -I https://admin.khodat.com  # → 200
curl -I https://backend.khodat.com/api/public/consignments  # → 200
```

### Kiểm tra logs nếu lỗi:
```bash
# Log từng container
docker logs khodat-backend --tail 50
docker logs khodat-frontend --tail 50
docker logs khodat-api-gateway --tail 50
docker logs khodat-mysql --tail 50

# Log Nginx host
sudo tail -f /var/log/nginx/error.log
```

---

## Lệnh thường dùng

| Lệnh | Mô tả |
|-------|--------|
| `bash deploy.sh` | Deploy nhanh (dùng cache) |
| `bash deploy.sh --build` | Deploy full rebuild |
| `docker compose -f docker-compose.yml --env-file .env.prod ps` | Xem status |
| `docker compose -f docker-compose.yml --env-file .env.prod logs -f backend` | Xem log backend |
| `docker compose -f docker-compose.yml --env-file .env.prod exec backend php artisan migrate` | Chạy migration |
| `docker compose -f docker-compose.yml --env-file .env.prod exec backend php artisan tinker` | Laravel tinker |
| `docker compose -f docker-compose.yml --env-file .env.prod exec backend php artisan images:migrate-base64` | Migrate base64 → WebP |
| `docker compose -f docker-compose.yml --env-file .env.prod restart backend backend-nginx` | Restart backend |
| `sudo certbot renew` | Gia hạn SSL |
| `sudo nginx -t && sudo systemctl reload nginx` | Test & reload Nginx |

---

## Xử lý sự cố

### Container không start?
```bash
docker logs khodat-backend --tail 100
# Thường là do: thiếu env var, sai DB password, hoặc APP_KEY chưa set
```

### 502 Bad Gateway?
```bash
# Container có đang chạy không?
docker ps | grep khodat

# Port có đang listen?
ss -tlnp | grep 8015

# Nginx config có đúng?
sudo nginx -t
```

### Email verification không gửi?
```bash
# Kiểm tra SMTP config
docker compose -f docker-compose.yml --env-file .env.prod exec backend \
  php artisan tinker --execute="Mail::raw('Test', fn(\$m) => \$m->to('your@email.com')->subject('Test'));"

# Xem log
docker logs khodat-backend --tail 50 | grep -i mail
```

### Tạo Admin user:
```bash
docker compose -f docker-compose.yml --env-file .env.prod exec backend \
  php artisan tinker --execute="
    \$user = App\Models\User::where('email', 'admin@khodat.com')->first();
    if (\$user) {
      \$role = App\Models\Role::firstOrCreate(['name' => 'admin', 'display_name' => 'Administrator']);
      \$user->assignRole(\$role);
      echo 'Admin role assigned!';
    }
  "
```

### Seed dữ liệu ban đầu:
```bash
docker compose -f docker-compose.yml --env-file .env.prod exec backend \
  php artisan db:seed --force
```
