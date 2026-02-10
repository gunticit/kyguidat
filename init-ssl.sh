#!/bin/bash
# ===========================================
# SSL Certificate Setup - kyguidatvuon.com
# ===========================================
# Sử dụng certbot trên host (không dùng Docker)
# Usage: sudo bash init-ssl.sh

set -e

DOMAIN="kyguidatvuon.com"
EMAIL="${1:-admin@kyguidatvuon.com}"

echo "============================================="
echo "  SSL Certificate Setup for $DOMAIN"
echo "============================================="

# Kiểm tra certbot
if ! command -v certbot &> /dev/null; then
    echo "📦 Installing certbot..."
    apt update
    apt install -y certbot python3-certbot-nginx
fi

# Kiểm tra nginx config
echo "🔍 Testing nginx configuration..."
nginx -t

# Xin SSL certificate cho tất cả domains
echo "📜 Requesting SSL certificates..."
certbot --nginx \
    -d $DOMAIN \
    -d www.$DOMAIN \
    -d api.$DOMAIN \
    -d admin.$DOMAIN \
    -d app.$DOMAIN \
    -d backend.$DOMAIN \
    -d socket.$DOMAIN \
    --email "$EMAIL" \
    --agree-tos \
    --no-eff-email \
    --redirect

# Setup auto-renew
echo "⏰ Setting up auto-renewal..."
systemctl enable certbot.timer
systemctl start certbot.timer

echo ""
echo "============================================="
echo "  ✅ SSL certificates installed!"
echo "============================================="
echo ""
echo "Auto-renewal is configured via systemd timer."
echo "Test renewal: sudo certbot renew --dry-run"
