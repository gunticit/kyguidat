#!/bin/bash

# ============================================================
# KHODAT VPS DOMAIN CONFIGURATION SCRIPT
# ============================================================
# Script cấu hình Nginx reverse proxy cho các services Docker
# Yêu cầu: VPS đã cài Nginx và Certbot
# 
# Services được cấu hình (từ docker-compose.yml):
#   - frontend (Next.js)    : Port 3015 -> khachhang.khodat.com
#   - backend (Laravel API) : Port 8015 -> api.khodat.com
#   - admin-php (OpenCart)  : Port 8088 -> admin.khodat.com
#   - phpmyadmin            : Port 8095 -> pma.khodat.com
# ============================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Function to print colored output
print_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
print_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
print_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
print_error() { echo -e "${RED}[ERROR]${NC} $1"; }
print_step() { echo -e "${CYAN}[STEP]${NC} $1"; }

# ============================================================
# CONFIGURATION - THAY ĐỔI CÁC GIÁ TRỊ NÀY THEO NHU CẦU
# ============================================================

# Domain chính
MAIN_DOMAIN="khodat.com"

# Sub-domains cho các services
FRONTEND_DOMAIN="khachhang.${MAIN_DOMAIN}"    # Next.js Frontend
API_DOMAIN="api.${MAIN_DOMAIN}"               # Laravel Backend API  
ADMIN_DOMAIN="admin.${MAIN_DOMAIN}"           # OpenCart Admin
PMA_DOMAIN="pma.${MAIN_DOMAIN}"               # phpMyAdmin

# Server local ports (từ docker-compose.yml - KHÔNG CÓ NGINX)
FRONTEND_PORT=3015    # Next.js dev server
API_PORT=8015         # php artisan serve
ADMIN_PORT=8088       # PHP built-in server
PMA_PORT=8095         # phpMyAdmin

# Email cho SSL certificate
SSL_EMAIL="admin@${MAIN_DOMAIN}"

# Nginx config directory
NGINX_AVAILABLE="/etc/nginx/sites-available"
NGINX_ENABLED="/etc/nginx/sites-enabled"

# ============================================================
# FUNCTIONS
# ============================================================

check_root() {
    if [ "$EUID" -ne 0 ]; then
        print_error "Script cần chạy với quyền root. Sử dụng: sudo $0"
        exit 1
    fi
}

check_nginx() {
    if ! command -v nginx &> /dev/null; then
        print_error "Nginx chưa được cài đặt. Cài đặt với: apt install nginx"
        exit 1
    fi
    print_success "Nginx đã được cài đặt"
}

check_certbot() {
    if ! command -v certbot &> /dev/null; then
        print_warning "Certbot chưa được cài đặt. Cài đặt ngay..."
        apt update
        apt install -y certbot python3-certbot-nginx
    fi
    print_success "Certbot đã sẵn sàng"
}

backup_nginx_config() {
    local backup_dir="/etc/nginx/backup_$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$backup_dir"
    cp -r /etc/nginx/sites-available/* "$backup_dir/" 2>/dev/null || true
    cp -r /etc/nginx/sites-enabled/* "$backup_dir/" 2>/dev/null || true
    print_info "Đã backup config Nginx tại: $backup_dir"
}

# ============================================================
# NGINX CONFIG TEMPLATES
# ============================================================

create_frontend_config() {
    local config_file="${NGINX_AVAILABLE}/khodat-frontend"

    print_step "Tạo config cho Frontend: $FRONTEND_DOMAIN -> localhost:$FRONTEND_PORT"

    cat > "$config_file" <<'EOF'
# ============================================================
# Nginx Configuration for Next.js Frontend
# Domain: FRONTEND_DOMAIN_PLACEHOLDER
# Port: FRONTEND_PORT_PLACEHOLDER
# ============================================================

limit_req_zone $binary_remote_addr zone=frontend_limit:10m rate=20r/s;

upstream frontend_upstream {
    server 127.0.0.1:FRONTEND_PORT_PLACEHOLDER;
    keepalive 32;
}

server {
    listen 80;
    listen [::]:80;
    server_name FRONTEND_DOMAIN_PLACEHOLDER;

    # Logging
    access_log /var/log/nginx/frontend_access.log;
    error_log /var/log/nginx/frontend_error.log;

    # ACME challenge for Let's Encrypt
    location /.well-known/acme-challenge/ {
        root /var/www/html;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/json application/xml image/svg+xml;

    # Rate limiting
    limit_req zone=frontend_limit burst=40 nodelay;

    # Client body size
    client_max_body_size 10M;

    # Next.js Proxy
    location / {
        proxy_pass http://frontend_upstream;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        # WebSocket support (for Next.js HMR in dev)
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";

        # Timeouts
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }

    # Next.js static files
    location /_next/static {
        proxy_pass http://frontend_upstream;
        proxy_set_header Host $host;
        expires 365d;
        add_header Cache-Control "public, immutable";
    }

    # Public static files
    location /images {
        proxy_pass http://frontend_upstream;
        proxy_set_header Host $host;
        expires 30d;
        add_header Cache-Control "public";
    }

    # Health check
    location /health {
        access_log off;
        return 200 "OK";
        add_header Content-Type text/plain;
    }
}
EOF

    # Replace placeholders
    sed -i "s/FRONTEND_DOMAIN_PLACEHOLDER/${FRONTEND_DOMAIN}/g" "$config_file"
    sed -i "s/FRONTEND_PORT_PLACEHOLDER/${FRONTEND_PORT}/g" "$config_file"

    print_success "Đã tạo config: $config_file"
}

create_api_config() {
    local config_file="${NGINX_AVAILABLE}/khodat-api"

    print_step "Tạo config cho API: $API_DOMAIN -> localhost:$API_PORT"

    cat > "$config_file" <<'EOF'
# ============================================================
# Nginx Configuration for Laravel API
# Domain: API_DOMAIN_PLACEHOLDER
# Port: API_PORT_PLACEHOLDER (php artisan serve)
# ============================================================

limit_req_zone $binary_remote_addr zone=api_limit:10m rate=30r/s;
limit_req_zone $binary_remote_addr zone=auth_limit:10m rate=5r/s;

upstream api_upstream {
    server 127.0.0.1:API_PORT_PLACEHOLDER;
    keepalive 64;
}

server {
    listen 80;
    listen [::]:80;
    server_name API_DOMAIN_PLACEHOLDER;

    # Logging
    access_log /var/log/nginx/api_access.log;
    error_log /var/log/nginx/api_error.log;

    # ACME challenge for Let's Encrypt
    location /.well-known/acme-challenge/ {
        root /var/www/html;
    }

    # Gzip
    gzip on;
    gzip_vary on;
    gzip_min_length 256;
    gzip_proxied any;
    gzip_types application/json application/javascript text/css text/plain text/xml application/xml;

    # Rate limiting
    limit_req zone=api_limit burst=50 nodelay;

    # Client body size (cho upload files)
    client_max_body_size 100M;

    # CORS Headers (thêm ở đây nếu backend chưa xử lý)
    # add_header Access-Control-Allow-Origin "https://khachhang.khodat.com" always;
    # add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always;
    # add_header Access-Control-Allow-Headers "Authorization, Content-Type, X-Requested-With" always;

    # API Proxy
    location / {
        proxy_pass http://api_upstream;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        # Timeouts dài hơn cho API
        proxy_connect_timeout 120s;
        proxy_send_timeout 120s;
        proxy_read_timeout 120s;

        # Buffer settings
        proxy_buffer_size 128k;
        proxy_buffers 8 256k;
        proxy_busy_buffers_size 512k;
    }

    # Auth endpoints - rate limit chặt hơn
    location ~ ^/api/(login|register|password|forgot-password) {
        limit_req zone=auth_limit burst=3 nodelay;
        
        proxy_pass http://api_upstream;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        proxy_connect_timeout 30s;
        proxy_send_timeout 30s;
        proxy_read_timeout 30s;
    }

    # OAuth callbacks
    location ~ ^/(oauth|auth/callback) {
        limit_req zone=auth_limit burst=5 nodelay;
        
        proxy_pass http://api_upstream;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # Storage/uploads - caching
    location /storage {
        proxy_pass http://api_upstream;
        proxy_set_header Host $host;
        expires 7d;
        add_header Cache-Control "public";
    }

    # Health check
    location /health {
        access_log off;
        proxy_pass http://api_upstream;
    }
}
EOF

    # Replace placeholders
    sed -i "s/API_DOMAIN_PLACEHOLDER/${API_DOMAIN}/g" "$config_file"
    sed -i "s/API_PORT_PLACEHOLDER/${API_PORT}/g" "$config_file"

    print_success "Đã tạo config: $config_file"
}

create_admin_config() {
    local config_file="${NGINX_AVAILABLE}/khodat-admin"

    print_step "Tạo config cho Admin: $ADMIN_DOMAIN -> localhost:$ADMIN_PORT"

    cat > "$config_file" <<'EOF'
# ============================================================
# Nginx Configuration for OpenCart Admin
# Domain: ADMIN_DOMAIN_PLACEHOLDER
# Port: ADMIN_PORT_PLACEHOLDER (PHP built-in server)
# ============================================================

limit_req_zone $binary_remote_addr zone=admin_limit:10m rate=10r/s;

upstream admin_upstream {
    server 127.0.0.1:ADMIN_PORT_PLACEHOLDER;
    keepalive 16;
}

server {
    listen 80;
    listen [::]:80;
    server_name ADMIN_DOMAIN_PLACEHOLDER;

    # Logging
    access_log /var/log/nginx/admin_access.log;
    error_log /var/log/nginx/admin_error.log;

    # ACME challenge for Let's Encrypt
    location /.well-known/acme-challenge/ {
        root /var/www/html;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/json application/xml image/svg+xml;

    # Rate limiting
    limit_req zone=admin_limit burst=20 nodelay;

    # Client body size (cho upload sản phẩm)
    client_max_body_size 100M;

    # OpenCart Proxy
    location / {
        proxy_pass http://admin_upstream;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        # Timeouts
        proxy_connect_timeout 60s;
        proxy_send_timeout 120s;
        proxy_read_timeout 120s;

        # Buffer settings
        proxy_buffer_size 128k;
        proxy_buffers 4 256k;
        proxy_busy_buffers_size 256k;
    }

    # Static files - images, css, js
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|pdf|woff|woff2|ttf|svg|eot)$ {
        proxy_pass http://admin_upstream;
        proxy_set_header Host $host;
        expires 30d;
        add_header Cache-Control "public";
    }

    # Product images
    location /image {
        proxy_pass http://admin_upstream;
        proxy_set_header Host $host;
        expires 7d;
        add_header Cache-Control "public";
    }

    # Health check
    location /health {
        access_log off;
        return 200 "OK";
        add_header Content-Type text/plain;
    }
}
EOF

    # Replace placeholders
    sed -i "s/ADMIN_DOMAIN_PLACEHOLDER/${ADMIN_DOMAIN}/g" "$config_file"
    sed -i "s/ADMIN_PORT_PLACEHOLDER/${ADMIN_PORT}/g" "$config_file"

    print_success "Đã tạo config: $config_file"
}

create_pma_config() {
    local config_file="${NGINX_AVAILABLE}/khodat-pma"

    print_step "Tạo config cho phpMyAdmin: $PMA_DOMAIN -> localhost:$PMA_PORT"

    cat > "$config_file" <<'EOF'
# ============================================================
# Nginx Configuration for phpMyAdmin
# Domain: PMA_DOMAIN_PLACEHOLDER
# Port: PMA_PORT_PLACEHOLDER
# ============================================================

limit_req_zone $binary_remote_addr zone=pma_limit:10m rate=5r/s;

upstream pma_upstream {
    server 127.0.0.1:PMA_PORT_PLACEHOLDER;
    keepalive 8;
}

server {
    listen 80;
    listen [::]:80;
    server_name PMA_DOMAIN_PLACEHOLDER;

    # Logging
    access_log /var/log/nginx/pma_access.log;
    error_log /var/log/nginx/pma_error.log;

    # ACME challenge for Let's Encrypt
    location /.well-known/acme-challenge/ {
        root /var/www/html;
    }

    # Rate limiting (stricter for security)
    limit_req zone=pma_limit burst=10 nodelay;

    # Client body size (cho import database)
    client_max_body_size 200M;

    # phpMyAdmin Proxy
    location / {
        proxy_pass http://pma_upstream;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        # Timeouts (longer for large queries)
        proxy_connect_timeout 60s;
        proxy_send_timeout 300s;
        proxy_read_timeout 300s;

        # Buffer settings
        proxy_buffer_size 128k;
        proxy_buffers 4 256k;
        proxy_busy_buffers_size 256k;
    }

    # Block access from outside (optional - uncomment if needed)
    # allow 123.45.67.89;  # Your IP
    # deny all;

    # Health check
    location /health {
        access_log off;
        return 200 "OK";
        add_header Content-Type text/plain;
    }
}
EOF

    # Replace placeholders
    sed -i "s/PMA_DOMAIN_PLACEHOLDER/${PMA_DOMAIN}/g" "$config_file"
    sed -i "s/PMA_PORT_PLACEHOLDER/${PMA_PORT}/g" "$config_file"

    print_success "Đã tạo config: $config_file"
}

# ============================================================
# UTILITY FUNCTIONS
# ============================================================

enable_site() {
    local config_name=$1
    local source="${NGINX_AVAILABLE}/${config_name}"
    local target="${NGINX_ENABLED}/${config_name}"

    if [ -L "$target" ]; then
        rm "$target"
    fi
    
    ln -s "$source" "$target"
    print_success "Đã enable site: $config_name"
}

test_nginx_config() {
    print_info "Kiểm tra cấu hình Nginx..."
    if nginx -t; then
        print_success "Cấu hình Nginx hợp lệ"
        return 0
    else
        print_error "Cấu hình Nginx có lỗi!"
        return 1
    fi
}

reload_nginx() {
    print_info "Reload Nginx..."
    systemctl reload nginx
    print_success "Nginx đã được reload"
}

setup_ssl() {
    local domain=$1
    print_info "Cài đặt SSL certificate cho: $domain"
    
    certbot --nginx -d "$domain" --non-interactive --agree-tos --email "$SSL_EMAIL" --redirect
    
    if [ $? -eq 0 ]; then
        print_success "SSL certificate đã được cài đặt cho: $domain"
    else
        print_warning "Không thể cài SSL cho $domain. Có thể domain chưa trỏ về server."
    fi
}

setup_auto_renew() {
    print_info "Thiết lập tự động gia hạn SSL..."
    
    if ! crontab -l 2>/dev/null | grep -q "certbot renew"; then
        (crontab -l 2>/dev/null; echo "0 3 * * * certbot renew --quiet --post-hook 'systemctl reload nginx'") | crontab -
        print_success "Đã thiết lập auto-renew SSL (chạy lúc 3:00 AM hàng ngày)"
    else
        print_info "Auto-renew SSL đã được thiết lập trước đó"
    fi
}

create_firewall_rules() {
    print_info "Cấu hình firewall (ufw)..."
    
    if command -v ufw &> /dev/null; then
        ufw allow 'Nginx Full' >/dev/null 2>&1 || true
        ufw allow OpenSSH >/dev/null 2>&1 || true
        print_success "Đã mở ports cho Nginx (80, 443)"
    else
        print_warning "UFW không được cài đặt. Bỏ qua cấu hình firewall."
    fi
}

# ============================================================
# DISPLAY FUNCTIONS
# ============================================================

show_summary() {
    echo ""
    echo "============================================================"
    echo -e "${GREEN}  ✅ HOÀN TẤT CẤU HÌNH DOMAIN${NC}"
    echo "============================================================"
    echo ""
    echo "Các domain đã được cấu hình:"
    echo ""
    echo -e "  ${CYAN}Frontend (Next.js):${NC}"
    echo -e "    URL:  https://${FRONTEND_DOMAIN}"
    echo -e "    Port: ${FRONTEND_PORT}"
    echo ""
    echo -e "  ${CYAN}API (Laravel):${NC}"
    echo -e "    URL:  https://${API_DOMAIN}"
    echo -e "    Port: ${API_PORT}"
    echo ""
    echo -e "  ${CYAN}Admin (OpenCart):${NC}"
    echo -e "    URL:  https://${ADMIN_DOMAIN}"
    echo -e "    Port: ${ADMIN_PORT}"
    echo ""
    echo -e "  ${CYAN}phpMyAdmin:${NC}"
    echo -e "    URL:  https://${PMA_DOMAIN}"
    echo -e "    Port: ${PMA_PORT}"
    echo ""
    echo "============================================================"
    echo -e "${YELLOW}BƯỚC TIẾP THEO:${NC}"
    echo "============================================================"
    echo ""
    echo "1. Trỏ các domain về IP của VPS thông qua DNS:"
    echo ""
    echo "   Type: A    Name: khachhang   Value: YOUR_VPS_IP"
    echo "   Type: A    Name: api         Value: YOUR_VPS_IP"
    echo "   Type: A    Name: admin       Value: YOUR_VPS_IP"
    echo "   Type: A    Name: pma         Value: YOUR_VPS_IP"
    echo ""
    echo "2. Sau khi DNS đã propagate, chạy lại script với option --ssl"
    echo "   để cài đặt SSL certificates:"
    echo ""
    echo "   sudo ./setup-domain.sh --ssl"
    echo ""
    echo "3. Đảm bảo Docker containers đang chạy:"
    echo ""
    echo "   docker-compose up -d"
    echo ""
    echo "============================================================"
}

show_docker_status() {
    echo ""
    echo "============================================================"
    echo -e "${BLUE}DOCKER SERVICES STATUS${NC}"
    echo "============================================================"
    echo ""
    echo "Ports được expose từ docker-compose.yml:"
    echo ""
    echo "  Service       Port      Domain"
    echo "  ─────────────────────────────────────────────"
    echo "  frontend      ${FRONTEND_PORT}      ${FRONTEND_DOMAIN}"
    echo "  backend       ${API_PORT}      ${API_DOMAIN}"
    echo "  admin-php     ${ADMIN_PORT}      ${ADMIN_DOMAIN}"
    echo "  phpmyadmin    ${PMA_PORT}      ${PMA_DOMAIN}"
    echo "  mysql         3321      (internal)"
    echo "  redis         6394      (internal)"
    echo ""
}

show_help() {
    echo ""
    echo "============================================================"
    echo -e "${BLUE}KHODAT VPS DOMAIN SETUP${NC}"
    echo "============================================================"
    echo ""
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Options:"
    echo "  --install     Cài đặt config Nginx (không SSL)"
    echo "  --ssl         Cài đặt SSL certificates"
    echo "  --all         Cài đặt config + SSL"
    echo "  --test        Kiểm tra cấu hình Nginx"
    echo "  --status      Hiển thị thông tin services"
    echo "  --help        Hiển thị trợ giúp"
    echo ""
    echo "Ví dụ:"
    echo "  sudo $0 --install    # Bước 1: Cài config"
    echo "  sudo $0 --ssl        # Bước 2: Sau khi DNS ok, cài SSL"
    echo "  sudo $0 --all        # Cài tất cả (config + SSL)"
    echo ""
    show_docker_status
}

# ============================================================
# MAIN EXECUTION
# ============================================================

main_install() {
    check_root
    check_nginx
    
    echo ""
    echo "============================================================"
    echo -e "${BLUE}  🚀 KHODAT VPS DOMAIN CONFIGURATION${NC}"
    echo "============================================================"
    echo ""

    # Backup existing configs
    backup_nginx_config

    # Create Nginx configurations
    create_frontend_config
    create_api_config
    create_admin_config
    create_pma_config

    # Enable sites
    enable_site "khodat-frontend"
    enable_site "khodat-api"
    enable_site "khodat-admin"
    enable_site "khodat-pma"

    # Test and reload Nginx
    if test_nginx_config; then
        reload_nginx
    else
        print_error "Có lỗi trong config. Vui lòng kiểm tra lại."
        exit 1
    fi

    # Setup firewall
    create_firewall_rules

    # Show summary
    show_summary
}

main_ssl() {
    check_root
    check_certbot

    echo ""
    echo "============================================================"
    echo -e "${BLUE}  🔒 INSTALLING SSL CERTIFICATES${NC}"
    echo "============================================================"
    echo ""

    setup_ssl "$FRONTEND_DOMAIN"
    setup_ssl "$API_DOMAIN"
    setup_ssl "$ADMIN_DOMAIN"
    setup_ssl "$PMA_DOMAIN"

    setup_auto_renew

    echo ""
    print_success "Hoàn tất cài đặt SSL!"
    echo ""
    echo "Tất cả domains đã có HTTPS:"
    echo "  - https://${FRONTEND_DOMAIN}"
    echo "  - https://${API_DOMAIN}"
    echo "  - https://${ADMIN_DOMAIN}"
    echo "  - https://${PMA_DOMAIN}"
    echo ""
}

# Parse command line arguments
case "${1:-}" in
    --install)
        main_install
        ;;
    --ssl)
        main_ssl
        ;;
    --all)
        main_install
        main_ssl
        ;;
    --test)
        check_root
        test_nginx_config
        ;;
    --status)
        show_docker_status
        ;;
    --help|-h)
        show_help
        ;;
    *)
        show_help
        ;;
esac
