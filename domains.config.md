# 🌐 Cấu hình Domain - Khodat Project

Hướng dẫn chi tiết về cách cấu hình domain cho dự án Khodat.

---

## 📋 Thông tin Domain mặc định

### Development (Local)

| Service          | Domain/URL                    | Port |
|------------------|-------------------------------|------|
| Frontend         | http://localhost:3015         | 3015 |
| Backend API      | http://localhost:8015         | 8015 |
| phpMyAdmin       | http://localhost:8095         | 8095 |

### Production (Recommended)

| Service          | Domain                        | SSL  |
|------------------|-------------------------------|------|
| Frontend         | https://khodat.com            | ✅   |
| Backend API      | https://api.khodat.com        | ✅   |
| Admin Panel      | https://admin.khodat.com      | ✅   |

---

## 🔧 Cấu hình DNS Records

Tại panel quản lý DNS của domain (Cloudflare, Namecheap, GoDaddy, etc.), thêm các records sau:

```
# A Records - Trỏ về IP VPS
Type    Name    Value           TTL
A       @       YOUR_VPS_IP     Auto
A       www     YOUR_VPS_IP     Auto
A       api     YOUR_VPS_IP     Auto
A       admin   YOUR_VPS_IP     Auto

# CNAME Records (Optional - nếu sử dụng www)
Type    Name    Value           TTL
CNAME   www     khodat.com      Auto
```

---

## 🏠 Cấu hình /etc/hosts (Development Local)

Thêm vào file `/etc/hosts` trên máy local:

### macOS / Linux:
```bash
sudo nano /etc/hosts
```

### Windows:
```
Mở Notepad as Administrator
File → Open: C:\Windows\System32\drivers\etc\hosts
```

### Nội dung thêm vào:
```
# Khodat Development
127.0.0.1   khodat.local
127.0.0.1   api.khodat.local
127.0.0.1   admin.khodat.local
```

---

## 🔒 Cấu hình Nginx Reverse Proxy với SSL

### 1. Cài đặt Nginx và Certbot

```bash
sudo apt update
sudo apt install nginx certbot python3-certbot-nginx -y
```

### 2. Tạo file cấu hình Nginx cho Frontend

```bash
sudo nano /etc/nginx/sites-available/khodat.com
```

```nginx
# Frontend - khodat.com
server {
    listen 80;
    listen [::]:80;
    server_name khodat.com www.khodat.com;
    
    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name khodat.com www.khodat.com;

    # SSL Configuration (sẽ được tự động thêm bởi Certbot)
    # ssl_certificate /etc/letsencrypt/live/khodat.com/fullchain.pem;
    # ssl_certificate_key /etc/letsencrypt/live/khodat.com/privkey.pem;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    location / {
        proxy_pass http://localhost:3015;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
        proxy_read_timeout 86400s;
        proxy_send_timeout 86400s;
    }

    # Static files caching
    location /_next/static {
        proxy_pass http://localhost:3015;
        proxy_cache_valid 60m;
        add_header Cache-Control "public, max-age=31536000, immutable";
    }
}
```

### 3. Tạo file cấu hình Nginx cho Backend API

```bash
sudo nano /etc/nginx/sites-available/api.khodat.com
```

```nginx
# Backend API - api.khodat.com
server {
    listen 80;
    listen [::]:80;
    server_name api.khodat.com;
    
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name api.khodat.com;

    # SSL Configuration
    # ssl_certificate /etc/letsencrypt/live/api.khodat.com/fullchain.pem;
    # ssl_certificate_key /etc/letsencrypt/live/api.khodat.com/privkey.pem;

    # Security Headers
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # CORS Headers
    add_header 'Access-Control-Allow-Origin' 'https://khodat.com' always;
    add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
    add_header 'Access-Control-Allow-Headers' 'Accept, Authorization, Content-Type, X-Requested-With' always;
    add_header 'Access-Control-Allow-Credentials' 'true' always;

    client_max_body_size 100M;

    location / {
        proxy_pass http://localhost:8015;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        # Handle preflight requests
        if ($request_method = 'OPTIONS') {
            add_header 'Access-Control-Allow-Origin' 'https://khodat.com';
            add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS';
            add_header 'Access-Control-Allow-Headers' 'Accept, Authorization, Content-Type, X-Requested-With';
            add_header 'Access-Control-Max-Age' 3600;
            add_header 'Content-Type' 'text/plain; charset=utf-8';
            add_header 'Content-Length' 0;
            return 204;
        }
    }
}
```

### 4. Kích hoạt cấu hình

```bash
# Tạo symbolic links
sudo ln -s /etc/nginx/sites-available/khodat.com /etc/nginx/sites-enabled/
sudo ln -s /etc/nginx/sites-available/api.khodat.com /etc/nginx/sites-enabled/

# Test cấu hình
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

### 5. Cài đặt SSL với Let's Encrypt

```bash
# Cài SSL cho frontend
sudo certbot --nginx -d khodat.com -d www.khodat.com

# Cài SSL cho API
sudo certbot --nginx -d api.khodat.com

# Tự động renew SSL
sudo systemctl enable certbot.timer
```

---

## ⚙️ Cập nhật Environment Variables

### Backend (.env)

```bash
# Production
APP_URL=https://api.khodat.com
FRONTEND_URL=https://khodat.com

# CORS - Đảm bảo cho phép domain frontend
CORS_ALLOWED_ORIGINS=https://khodat.com,https://www.khodat.com

# Session Domain (nếu sử dụng cookie-based auth)
SESSION_DOMAIN=.khodat.com
SANCTUM_STATEFUL_DOMAINS=khodat.com,www.khodat.com
```

### Frontend (.env.local)

```bash
# Production
NEXT_PUBLIC_API_URL=https://api.khodat.com/api
NEXT_PUBLIC_APP_URL=https://khodat.com
NEXT_PUBLIC_APP_NAME=Khodat

# Social Login URLs
NEXT_PUBLIC_GOOGLE_LOGIN_URL=https://api.khodat.com/api/auth/google
NEXT_PUBLIC_FACEBOOK_LOGIN_URL=https://api.khodat.com/api/auth/facebook
NEXT_PUBLIC_ZALO_LOGIN_URL=https://api.khodat.com/api/auth/zalo
```

---

## 🔄 Cloudflare Configuration (Optional)

Nếu sử dụng Cloudflare:

### DNS Settings
- Proxy status: **Proxied** (Orange Cloud)
- SSL/TLS: **Full (Strict)**

### SSL/TLS Settings
```
SSL/TLS encryption mode: Full (Strict)
Always Use HTTPS: On
Automatic HTTPS Rewrites: On
Minimum TLS Version: TLS 1.2
```

### Page Rules
```
# Force HTTPS
URL: http://*khodat.com/*
Setting: Always Use HTTPS

# Cache static assets
URL: *khodat.com/_next/static/*
Settings:
  - Cache Level: Cache Everything
  - Edge Cache TTL: 1 month
```

### Firewall Rules
```
# Block known bad bots
(cf.threat_score gt 50) or (cf.client.bot and not cf.verified_bot_category)
Action: Block

# Rate limiting for API
(http.request.uri.path contains "/api/")
Action: Rate Limit - 100 requests per minute
```

---

## 📱 Multi-Environment Domain Setup

### Staging Environment

| Service          | Domain                           |
|------------------|----------------------------------|
| Frontend         | https://staging.khodat.com       |
| Backend API      | https://api-staging.khodat.com   |

### Development Environment

| Service          | Domain                           |
|------------------|----------------------------------|
| Frontend         | https://dev.khodat.com           |
| Backend API      | https://api-dev.khodat.com       |

---

## 🛡️ Security Best Practices

1. **Luôn sử dụng HTTPS** cho production
2. **Không expose** phpMyAdmin ra public (sử dụng SSH tunnel)
3. **Cấu hình CORS** đúng cách, chỉ cho phép domain cần thiết
4. **Rate limiting** cho API endpoints
5. **Firewall rules** để chặn các request không hợp lệ

---

## 📞 Troubleshooting

### SSL Certificate không hoạt động
```bash
# Kiểm tra trạng thái Certbot
sudo certbot certificates

# Force renew
sudo certbot renew --force-renewal

# Check Nginx config
sudo nginx -t
```

### Domain trỏ sai IP
```bash
# Kiểm tra DNS
dig khodat.com
nslookup khodat.com

# Clear DNS cache (macOS)
sudo dscacheutil -flushcache; sudo killall -HUP mDNSResponder
```

### CORS errors
```bash
# Test CORS headers
curl -I -X OPTIONS https://api.khodat.com/api/test \
  -H "Origin: https://khodat.com" \
  -H "Access-Control-Request-Method: GET"
```

---

## 📝 Quick Reference

```bash
# Development URLs
Frontend:   http://localhost:3015
Backend:    http://localhost:8015
API:        http://localhost:8015/api
phpMyAdmin: http://localhost:8095

# Production URLs
Frontend:   https://khodat.com
API:        https://api.khodat.com/api
```
