# 🚀 Hướng dẫn cài đặt Khodat trên VPS (Ubuntu)

Hướng dẫn chi tiết từng bước để deploy dự án Khodat lên VPS Ubuntu.

## 📋 Thông tin Ports

| Service | Port | Mô tả |
|---------|------|-------|
| OpenCart Admin | 8088 | Quản trị bất động sản (OpenCart) |
| Frontend (Next.js) | 3015 | Giao diện người dùng |
| Backend API (Nginx) | 8015 | Laravel API |
| phpMyAdmin | 8095 | Quản lý Database |
| MySQL | 3321 | Database (Internal) |
| Redis | 6394 | Cache & Sessions (Internal) |

---

## 1️⃣ Yêu cầu hệ thống

- **VPS**: Ubuntu 20.04/22.04 LTS
- **RAM**: Tối thiểu 2GB (khuyến nghị 4GB)
- **CPU**: 2 vCPU
- **Disk**: 20GB SSD
- **Truy cập**: SSH root hoặc user có sudo

---

## 2️⃣ Cài đặt Docker & Docker Compose

### 2.1. Cập nhật hệ thống

```bash
sudo apt update && sudo apt upgrade -y
```

### 2.2. Cài đặt các gói cần thiết

```bash
sudo apt install -y apt-transport-https ca-certificates curl software-properties-common git
```

### 2.3. Thêm Docker GPG key và repository

```bash
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
```

### 2.4. Cài đặt Docker

```bash
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin
```

### 2.5. Khởi động Docker và enable tự động chạy khi boot

```bash
sudo systemctl start docker
sudo systemctl enable docker
```

### 2.6. Thêm user vào group docker (để chạy docker không cần sudo)

```bash
sudo usermod -aG docker $USER
```

> ⚠️ **Lưu ý**: Bạn cần logout và login lại để group mới có hiệu lực.

### 2.7. Kiểm tra cài đặt

```bash
docker --version
docker compose version
```

---

## 3️⃣ Upload source code lên VPS

### Cách 1: Clone từ Git repository (khuyến nghị)

```bash
cd /home
git clone <YOUR_GIT_REPO_URL> khodat
cd khodat
```

### Cách 2: Upload qua SFTP

Sử dụng FileZilla hoặc scp:

```bash
# Từ máy local
scp -r ./khodat user@your-vps-ip:/home/khodat
```

---

## 4️⃣ Cấu hình môi trường

### 4.1. Cấu hình Backend (.env)

```bash
cd /home/khodat/backend
cp .env.example .env
nano .env
```

Cập nhật các thông tin sau với IP/Domain của VPS:

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=http://YOUR_VPS_IP:8015
FRONTEND_URL=http://YOUR_VPS_IP:3015

# Database (giữ nguyên vì dùng internal network)
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=khodat
DB_USERNAME=khodat
DB_PASSWORD=khodat123

# Redis (giữ nguyên vì dùng internal network)
REDIS_HOST=redis
REDIS_PORT=6379

# Social Login - Điền thông tin của bạn
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret

FACEBOOK_CLIENT_ID=your_facebook_client_id
FACEBOOK_CLIENT_SECRET=your_facebook_client_secret

ZALO_APP_ID=your_zalo_app_id
ZALO_APP_SECRET=your_zalo_app_secret

# Payment - Điền thông tin merchant
VNPAY_TMN_CODE=your_vnpay_code
VNPAY_HASH_SECRET=your_vnpay_secret

MOMO_PARTNER_CODE=your_momo_partner_code
MOMO_ACCESS_KEY=your_momo_access_key
MOMO_SECRET_KEY=your_momo_secret_key
```

### 4.2. Cấu hình Frontend (.env.local)

```bash
cd /home/khodat/frontend
cp .env.example .env.local
nano .env.local
```

Nội dung:

```bash
NEXT_PUBLIC_API_URL=http://YOUR_VPS_IP:8015/api
NEXT_PUBLIC_APP_NAME=Khodat

NEXT_PUBLIC_GOOGLE_LOGIN_URL=http://YOUR_VPS_IP:8015/api/auth/google
NEXT_PUBLIC_FACEBOOK_LOGIN_URL=http://YOUR_VPS_IP:8015/api/auth/facebook
NEXT_PUBLIC_ZALO_LOGIN_URL=http://YOUR_VPS_IP:8015/api/auth/zalo
```

---

## 5️⃣ Khởi động dự án

### 5.1. Build và chạy các containers

```bash
cd /home/khodat
docker compose up -d --build
```

### 5.2. Kiểm tra trạng thái containers

```bash
docker compose ps
```

Kết quả mong đợi - tất cả containers đều có status `Up`:

```
NAME                 IMAGE                  STATUS          PORTS
khodat-admin-nginx   nginx:alpine           Up              0.0.0.0:8088->80/tcp
khodat-admin-php     khodat-admin-php       Up              9000/tcp
khodat-backend       khodat-backend         Up              9000/tcp
khodat-frontend      khodat-frontend        Up              0.0.0.0:3015->3000/tcp
khodat-mysql         mysql:8.0              Up              0.0.0.0:3321->3306/tcp
khodat-nginx         nginx:alpine           Up              0.0.0.0:8015->80/tcp
khodat-phpmyadmin    phpmyadmin:latest      Up              0.0.0.0:8095->80/tcp
khodat-redis         redis:alpine           Up              0.0.0.0:6394->6379/tcp
```

### 5.3. Cài đặt Laravel dependencies và chạy migrations

```bash
# Cài đặt Composer dependencies
docker compose exec backend composer install --optimize-autoloader --no-dev

# Generate application key
docker compose exec backend php artisan key:generate

# Chạy migrations
docker compose exec backend php artisan migrate --force

# (Tùy chọn) Chạy seeders nếu cần data mẫu
docker compose exec backend php artisan db:seed

# Tạo symbolic link cho storage
docker compose exec backend php artisan storage:link

# Clear và cache config để tối ưu
docker compose exec backend php artisan config:cache
docker compose exec backend php artisan route:cache
docker compose exec backend php artisan view:cache
```

---

## 6️⃣ Cấu hình Firewall (UFW)

```bash
# Bật UFW
sudo ufw enable

# Cho phép SSH
sudo ufw allow 22/tcp

# Cho phép các ports của ứng dụng
sudo ufw allow 8088/tcp   # OpenCart Admin
sudo ufw allow 3015/tcp   # Frontend (Next.js)
sudo ufw allow 8015/tcp   # Backend API
sudo ufw allow 8095/tcp   # phpMyAdmin (có thể bỏ qua nếu không muốn public)

# Kiểm tra trạng thái
sudo ufw status
```

> ⚠️ **Bảo mật**: Không nên mở port phpMyAdmin (8095) trên production. Nếu cần truy cập, sử dụng SSH tunnel.

---

## 7️⃣ Truy cập ứng dụng

Sau khi hoàn tất, bạn có thể truy cập:

| Service | URL |
|---------|-----|
| **OpenCart Frontend** | http://YOUR_VPS_IP:8088 |
| **OpenCart Admin** | http://YOUR_VPS_IP:8088/quantri |
| **Next.js Frontend** | http://YOUR_VPS_IP:3015 |
| **Backend API** | http://YOUR_VPS_IP:8015/api |
| **phpMyAdmin** | http://YOUR_VPS_IP:8095 |

---

## 8️⃣ Cấu hình Domain và SSL (Tùy chọn)

### 8.1. Trỏ domain về VPS

Tại nơi quản lý domain, thêm A record trỏ về IP của VPS:

```
A    @       YOUR_VPS_IP
A    www     YOUR_VPS_IP
A    api     YOUR_VPS_IP
```

### 8.2. Cài đặt Nginx Proxy Manager (Khuyến nghị)

Để có SSL miễn phí với Let's Encrypt:

```bash
# Tạo thư mục
mkdir -p /home/nginx-proxy-manager
cd /home/nginx-proxy-manager

# Tạo docker-compose.yml
cat > docker-compose.yml << 'EOF'
version: "3.9"
services:
  npm:
    image: 'jc21/nginx-proxy-manager:latest'
    restart: unless-stopped
    ports:
      - '80:80'
      - '443:443'
      - '81:81'
    volumes:
      - ./data:/data
      - ./letsencrypt:/etc/letsencrypt
    networks:
      - khodat-network

networks:
  khodat-network:
    external: true
EOF

# Khởi động
docker compose up -d
```

Truy cập Nginx Proxy Manager tại `http://YOUR_VPS_IP:81`

- **Email**: admin@example.com
- **Password**: changeme

Sau đó thêm Proxy Host cho frontend và backend với SSL.

---

## 9️⃣ Các lệnh hữu ích

### Xem logs

```bash
# Logs tất cả services
docker compose logs -f

# Logs từng service
docker compose logs -f backend
docker compose logs -f frontend
docker compose logs -f nginx
docker compose logs -f mysql
```

### Restart services

```bash
# Restart tất cả
docker compose restart

# Restart từng service
docker compose restart backend
docker compose restart frontend
```

### Dừng và xóa containers

```bash
# Dừng tất cả
docker compose down

# Dừng và xóa luôn volumes (DATA SẼ BỊ MẤT!)
docker compose down -v
```

### Cập nhật source code

```bash
cd /home/khodat

# Pull code mới
git pull origin main

# Rebuild containers
docker compose up -d --build

# Chạy migrations (nếu có)
docker compose exec backend php artisan migrate --force

# Clear cache
docker compose exec backend php artisan config:cache
docker compose exec backend php artisan cache:clear
```

### Truy cập MySQL

```bash
docker compose exec mysql mysql -u khodat -pkhodat123 khodat
```

### Backup Database

```bash
# Backup
docker compose exec mysql mysqldump -u khodat -pkhodat123 khodat > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore
docker compose exec -T mysql mysql -u khodat -pkhodat123 khodat < backup_file.sql
```

---

## 🔧 Troubleshooting

### Container không khởi động

```bash
# Xem logs chi tiết
docker compose logs backend
docker compose logs mysql

# Kiểm tra resources
docker stats
```

### Port đã được sử dụng

```bash
# Kiểm tra port đang được sử dụng
sudo lsof -i :8015
sudo lsof -i :3015

# Kill process nếu cần
sudo kill -9 <PID>
```

### Database connection error

```bash
# Kiểm tra MySQL đã sẵn sàng chưa
docker compose exec mysql mysqladmin ping -h localhost -u root -proot

# Restart MySQL
docker compose restart mysql

# Đợi MySQL khởi động xong rồi restart backend
sleep 10
docker compose restart backend
```

### Permission denied

```bash
# Cấp quyền cho thư mục storage
docker compose exec backend chmod -R 775 storage bootstrap/cache
docker compose exec backend chown -R www-data:www-data storage bootstrap/cache
```

---

## 📞 Hỗ trợ

Nếu gặp vấn đề, vui lòng tạo issue trên repository hoặc liên hệ:
- Email: support@khodat.com
- Website: https://khodat.com

---

## 📝 Changelog Ports

Các ports đã được thay đổi để tránh xung đột:

| Service | Port cũ | Port mới |
|---------|---------|----------|
| OpenCart Admin | - | 8088 |
| Frontend (Next.js) | 3000 | 3015 |
| Backend API | 8000 | 8015 |
| phpMyAdmin | 8080 | 8095 |
| MySQL | 3306 | 3321 |
| Redis | 6379 | 6394 |


# Xem các endpoints có sẵn
curl http://localhost:8015/api/ipn/endpoints

# Tạo cấu hình IPN mới (cần auth token)
curl -X POST http://localhost:8015/api/ipn-config \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "VNPay Production",
    "provider": "vnpay",
    "ipn_url": "https://your-domain.com/api/ipn/vnpay",
    "merchant_id": "YOUR_MERCHANT_ID",
    "secret_key": "YOUR_SECRET_KEY",
    "is_active": true
  }'