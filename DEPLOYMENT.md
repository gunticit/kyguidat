# 🚀 Hướng dẫn Deploy kyguidatvuon.com lên Ubuntu VPS

> VPS đã cài sẵn Nginx. Sử dụng host Nginx làm reverse proxy + Certbot cho SSL.

---

## Bước 1: Cài Docker trên VPS

```bash
# Cài Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER

# Cài Docker Compose plugin
sudo apt install -y docker-compose-plugin

# Đăng xuất rồi SSH lại
exit
```

Kiểm tra:
```bash
docker --version
docker compose version
```

---

## Bước 2: Cấu hình DNS

Tạo **A Record** trỏ về IP VPS cho tất cả domains:

| Name | Value |
|------|-------|
| `@` | `IP_VPS` |
| `www` | `IP_VPS` |
| `api` | `IP_VPS` |
| `admin` | `IP_VPS` |
| `app` | `IP_VPS` |
| `backend` | `IP_VPS` |
| `socket` | `IP_VPS` |

Kiểm tra DNS: `ping kyguidatvuon.com`

---

## Bước 3: Clone repo & cấu hình

```bash
cd /var/www
git clone https://github.com/YOUR_USERNAME/kyguidat.git
cd kyguidat

# Tạo .env.prod
cp .env.prod.example .env.prod
nano .env.prod
```

**Sửa các giá trị trong `.env.prod`:**
```env
DOMAIN=kyguidatvuon.com
EMAIL=email@gmail.com
DB_PASSWORD=MatKhauManh123!
MYSQL_ROOT_PASSWORD=RootManh456!
REDIS_PASSWORD=RedisManh789!
JWT_SECRET=JwtSecret321!
APP_KEY=  # ← tạo bằng lệnh dưới
```

Tạo APP_KEY:
```bash
docker run --rm php:8.4-cli php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
```

---

## Bước 4: Cấu hình Host Nginx

```bash
# Copy config vào nginx
sudo cp nginx/kyguidatvuon.conf /etc/nginx/sites-available/kyguidatvuon.conf

# Enable site
sudo ln -sf /etc/nginx/sites-available/kyguidatvuon.conf /etc/nginx/sites-enabled/

# Xoá default nếu cần
sudo rm -f /etc/nginx/sites-enabled/default

# Test & reload
sudo nginx -t
sudo systemctl reload nginx
```

---

## Bước 5: Cấu hình Firewall

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

---

## Bước 6: Khởi tạo SSL

```bash
chmod +x init-ssl.sh
sudo bash init-ssl.sh email@gmail.com
```

Certbot sẽ tự động:
- Xin certificate Let's Encrypt cho tất cả 7 domains
- Sửa nginx config để thêm HTTPS + redirect HTTP → HTTPS
- Setup auto-renew

Test renewal: `sudo certbot renew --dry-run`

---

## Bước 7: Deploy

```bash
chmod +x deploy.sh

# Lần đầu: build tất cả images
bash deploy.sh --build
```

Kiểm tra:
```bash
# Tất cả containers running?
docker compose -f docker-compose.prod.yml --env-file .env.prod ps

# Xem logs nếu có lỗi
docker compose -f docker-compose.prod.yml --env-file .env.prod logs -f [service_name]
```

---

## Bước 8: Kiểm tra trên browser

| URL | Kỳ vọng |
|-----|---------|
| https://kyguidatvuon.com | Trang Sàn Đất |
| https://api.kyguidatvuon.com | API Gateway |
| https://admin.kyguidatvuon.com | Admin Dashboard |
| https://app.kyguidatvuon.com | User Dashboard |
| https://backend.kyguidatvuon.com/api | Backend API |
| https://socket.kyguidatvuon.com | Socket.IO |

---

## Lệnh thường dùng

```bash
# Alias ngắn gọn (thêm vào ~/.bashrc)
alias dc="docker compose -f docker-compose.prod.yml --env-file .env.prod"

# Sau đó dùng:
dc ps                    # Xem status
dc logs -f api-gateway   # Xem logs
dc restart backend       # Restart service
dc exec backend bash     # Vào container

# Deploy bản mới
bash deploy.sh

# Rebuild hoàn toàn
bash deploy.sh --build
```

---

## Troubleshooting

| Vấn đề | Giải pháp |
|---------|-----------|
| 502 Bad Gateway | Container chưa chạy → `dc ps` & `dc logs [service]` |
| SSL error | `sudo certbot renew --force-renewal` |
| Permission denied (Laravel) | `dc exec backend chmod -R 775 storage bootstrap/cache` |
| MySQL refused | Đợi healthcheck → `dc logs mysql` |
| Port đã bị dùng | `sudo lsof -i :8080` → kill process |
