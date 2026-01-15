# 🚀 HƯỚNG DẪN DEPLOY PRODUCTION - KHODAT

## 📋 Mục lục

1. [Yêu cầu hệ thống](#-yêu-cầu-hệ-thống)
2. [Chuẩn bị trước khi deploy](#-chuẩn-bị-trước-khi-deploy)
3. [Quick Start](#-quick-start)
4. [Cấu hình chi tiết](#-cấu-hình-chi-tiết)
5. [Commands](#-commands)
6. [Monitoring & Logs](#-monitoring--logs)
7. [Backup & Restore](#-backup--restore)
8. [Troubleshooting](#-troubleshooting)
9. [Security Checklist](#-security-checklist)

---

## 💻 Yêu cầu hệ thống

### Server Requirements
| Resource | Minimum | Recommended |
|----------|---------|-------------|
| CPU | 2 cores | 4+ cores |
| RAM | 4 GB | 8+ GB |
| Disk | 40 GB SSD | 100+ GB SSD |
| OS | Ubuntu 20.04+ | Ubuntu 22.04 LTS |

### Software Requirements
- Docker 20.10+
- Docker Compose 2.0+
- Git
- Nginx (cho reverse proxy external)
- Certbot (cho SSL)

### Cài đặt Docker (Ubuntu)
```bash
# Update packages
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Add user to docker group
sudo usermod -aG docker $USER
newgrp docker

# Install Docker Compose
sudo apt install docker-compose-plugin

# Verify
docker --version
docker compose version
```

---

## 🔧 Chuẩn bị trước khi deploy

### Bước 1: Clone repository
```bash
cd /var/www
git clone <your-repo-url> khodat
cd khodat
```

### Bước 2: Tạo file environment
```bash
# Copy template
cp .env.production.example .env.production

# Tạo passwords mạnh
MYSQL_PASS=$(openssl rand -base64 32)
REDIS_PASS=$(openssl rand -base64 32)
ROOT_PASS=$(openssl rand -base64 32)

echo "Generated passwords:"
echo "MYSQL_ROOT_PASSWORD=$ROOT_PASS"
echo "MYSQL_PASSWORD=$MYSQL_PASS"
echo "REDIS_PASSWORD=$REDIS_PASS"
```

### Bước 3: Cập nhật .env.production
```bash
nano .env.production
```

```env
# Database - sử dụng passwords đã generate
MYSQL_ROOT_PASSWORD=<paste_root_pass>
MYSQL_DATABASE=khodat
MYSQL_USER=khodat
MYSQL_PASSWORD=<paste_mysql_pass>

# Redis
REDIS_PASSWORD=<paste_redis_pass>

# Frontend
NEXT_PUBLIC_API_URL=https://api.khodat.com/api
```

### Bước 4: Cập nhật Laravel .env.production
```bash
cp backend/.env backend/.env.production
nano backend/.env.production
```

**Các giá trị quan trọng cần thay đổi:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.khodat.com

DB_HOST=mysql
DB_DATABASE=khodat
DB_USERNAME=khodat
DB_PASSWORD=<same_as_MYSQL_PASSWORD>

REDIS_HOST=redis
REDIS_PASSWORD=<same_as_REDIS_PASSWORD>

SESSION_DRIVER=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Bước 5: Permissions
```bash
chmod +x deploy.sh
chmod -R 775 backend/storage backend/bootstrap/cache
```

---

## ⚡ Quick Start

### Deploy lần đầu
```bash
./deploy.sh deploy
```

Script sẽ tự động:
1. ✅ Kiểm tra environment files
2. ✅ Build tất cả Docker images
3. ✅ Start containers
4. ✅ Chạy migrations
5. ✅ Optimize Laravel cache

### Kiểm tra status
```bash
./deploy.sh status
```

Output mẫu:
```
[INFO] Container status:
NAME                  STATUS                  PORTS
khodat-frontend       Up 2 minutes (healthy)  0.0.0.0:3015->3000/tcp
khodat-backend-nginx  Up 2 minutes (healthy)  0.0.0.0:8015->80/tcp
khodat-backend        Up 2 minutes (healthy)  9000/tcp
khodat-admin-nginx    Up 2 minutes (healthy)  0.0.0.0:8088->80/tcp
khodat-admin-php      Up 2 minutes (healthy)  9000/tcp
khodat-mysql          Up 2 minutes (healthy)  3306/tcp
khodat-redis          Up 2 minutes (healthy)  6379/tcp
```

---

## 📝 Cấu hình chi tiết

### Ports được expose

| Service | Internal Port | External Port | URL |
|---------|---------------|---------------|-----|
| Frontend | 3000 | 3015 | http://server:3015 |
| Backend API | 80 | 8015 | http://server:8015 |
| Admin Panel | 80 | 8088 | http://server:8088 |

**Lưu ý:** MySQL và Redis KHÔNG expose ra ngoài trong production!

### Nginx External Reverse Proxy

Trên VPS, tạo file `/etc/nginx/sites-available/khodat`:

```nginx
# Frontend - khachhang.khodat.com
server {
    listen 80;
    server_name khachhang.khodat.com;
    
    location / {
        proxy_pass http://127.0.0.1:3015;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }
}

# Backend API - api.khodat.com
server {
    listen 80;
    server_name api.khodat.com;
    
    location / {
        proxy_pass http://127.0.0.1:8015;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        # CORS headers
        add_header 'Access-Control-Allow-Origin' 'https://khachhang.khodat.com' always;
        add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
        add_header 'Access-Control-Allow-Headers' 'Authorization, Content-Type, Accept' always;
    }
}

# Admin Panel - khodat.com
server {
    listen 80;
    server_name khodat.com www.khodat.com;
    
    location / {
        proxy_pass http://127.0.0.1:8088;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

Enable và SSL:
```bash
sudo ln -s /etc/nginx/sites-available/khodat /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx

# SSL với Certbot
sudo certbot --nginx -d khachhang.khodat.com -d api.khodat.com -d khodat.com -d www.khodat.com
```

---

## 🛠 Commands

### Build & Deploy
```bash
./deploy.sh build              # Build tất cả images
./deploy.sh build frontend     # Build chỉ frontend
./deploy.sh up                 # Start containers
./deploy.sh down               # Stop containers
./deploy.sh restart            # Restart tất cả
./deploy.sh restart backend    # Restart chỉ backend
```

### Monitoring
```bash
./deploy.sh status             # Xem trạng thái
./deploy.sh logs               # Xem tất cả logs
./deploy.sh logs backend       # Xem logs backend
./deploy.sh logs frontend      # Xem logs frontend
```

### Laravel Commands
```bash
./deploy.sh optimize           # Cache config, routes, views
./deploy.sh migrate            # Chạy migrations

# Chạy artisan commands khác
docker exec khodat-backend php artisan tinker
docker exec khodat-backend php artisan queue:work
docker exec khodat-backend php artisan schedule:run
```

### Database
```bash
./deploy.sh backup                              # Backup database
./deploy.sh restore backups/khodat_db_xxx.sql.gz  # Restore

# Truy cập MySQL trực tiếp
docker exec -it khodat-mysql mysql -u root -p
```

---

## 📊 Monitoring & Logs

### Xem logs real-time
```bash
# Tất cả services
./deploy.sh logs

# Service cụ thể
./deploy.sh logs backend
./deploy.sh logs frontend
./deploy.sh logs mysql

# Với filter
docker compose -f docker-compose.prod.yml logs -f backend 2>&1 | grep -i error
```

### Health checks
```bash
# Check container health
docker inspect --format='{{.State.Health.Status}}' khodat-backend
docker inspect --format='{{.State.Health.Status}}' khodat-mysql

# Check endpoints
curl -I http://localhost:8015/health
curl -I http://localhost:3015/api/health
curl -I http://localhost:8088/health
```

### Resource usage
```bash
# Container stats
docker stats --no-stream

# Disk usage
docker system df
```

---

## 💾 Backup & Restore

### Automated Backup
Thêm vào crontab:
```bash
crontab -e
```

```cron
# Backup database hàng ngày lúc 2:00 AM
0 2 * * * cd /var/www/khodat && ./deploy.sh backup >> /var/log/khodat-backup.log 2>&1

# Cleanup old backups (giữ 7 ngày gần nhất)
0 3 * * * find /var/www/khodat/backups -name "*.gz" -mtime +7 -delete
```

### Manual Backup
```bash
./deploy.sh backup
# Output: backups/khodat_db_20260116_020000.sql.gz
```

### Restore
```bash
# Từ file backup gần nhất
./deploy.sh restore backups/khodat_db_20260116_020000.sql.gz

# Script sẽ yêu cầu xác nhận trước khi restore
```

---

## 🔧 Troubleshooting

### Container không start
```bash
# Check logs
./deploy.sh logs [service]

# Rebuild image
./deploy.sh build [service]

# Restart
./deploy.sh restart [service]
```

### MySQL connection refused
```bash
# Check MySQL health
docker exec khodat-mysql mysqladmin ping -h localhost -u root -p

# Check logs
./deploy.sh logs mysql

# Restart MySQL
./deploy.sh restart mysql
sleep 30  # Đợi MySQL khởi động
./deploy.sh restart backend
```

### Laravel 500 error
```bash
# Xem error logs
docker exec khodat-backend cat storage/logs/laravel.log | tail -100

# Clear cache
docker exec khodat-backend php artisan cache:clear
docker exec khodat-backend php artisan config:clear

# Check permissions
docker exec khodat-backend ls -la storage/
```

### Frontend build failed
```bash
# Check logs
./deploy.sh logs frontend

# Rebuild
./deploy.sh build frontend
./deploy.sh restart frontend
```

### Out of disk space
```bash
# Cleanup Docker
./deploy.sh cleanup

# Check disk
df -h

# Remove old backups
ls -la backups/
rm backups/old_backup.sql.gz
```

---

## 🔒 Security Checklist

### Before Production Deploy

- [ ] **Passwords mạnh**: Sử dụng `openssl rand -base64 32` để generate
- [ ] **APP_DEBUG=false**: Trong `backend/.env.production`
- [ ] **.env files**: Không commit vào git (đã có trong .gitignore)
- [ ] **SSL/HTTPS**: Sử dụng Certbot để cài đặt SSL
- [ ] **Firewall**: Chỉ mở ports 80, 443, 22
- [ ] **SSH Key**: Disable password authentication

### Firewall setup (ufw)
```bash
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable
sudo ufw status
```

### SSH Security
```bash
# Disable password auth
sudo nano /etc/ssh/sshd_config
# Set: PasswordAuthentication no

sudo systemctl restart sshd
```

### Regular Updates
```bash
# System updates
sudo apt update && sudo apt upgrade -y

# Docker images
docker compose -f docker-compose.prod.yml pull
./deploy.sh deploy
```

---

## 📞 Support

Nếu gặp vấn đề:
1. Kiểm tra logs: `./deploy.sh logs`
2. Xem status: `./deploy.sh status`  
3. Restart services: `./deploy.sh restart`
4. Full redeploy: `./deploy.sh deploy`

---

*Last updated: 2026-01-16*
