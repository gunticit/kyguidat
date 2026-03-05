#!/bin/bash
# ===========================================
# Deployment Script - khodat.com
# ===========================================
# Usage: bash deploy.sh [--build]

set -e

COMPOSE_FILE="docker-compose.prod.yml"
ENV_FILE=".env.prod"

echo "============================================="
echo "  🚀 Deploying khodat.com"
echo "  $(date '+%Y-%m-%d %H:%M:%S')"
echo "============================================="

# Check .env.prod
if [ ! -f "$ENV_FILE" ]; then
    echo "❌ Error: $ENV_FILE not found!"
    echo "   cp .env.prod.example .env.prod && nano .env.prod"
    exit 1
fi

# Pull latest code
echo ""
echo "📥 Pulling latest code..."
git pull origin main

# Build images
if [ "$1" = "--build" ]; then
    echo ""
    echo "🔨 Building all images (no cache)..."
    docker compose -f $COMPOSE_FILE --env-file $ENV_FILE build --no-cache
else
    echo ""
    echo "🔨 Building images..."
    docker compose -f $COMPOSE_FILE --env-file $ENV_FILE build
fi

# Start/restart all services
echo ""
echo "🔄 Starting services..."
docker compose -f $COMPOSE_FILE --env-file $ENV_FILE up -d

# Wait for MySQL to be healthy
echo ""
echo "⏳ Waiting for MySQL..."
sleep 15

# Run database migrations
echo ""
echo "🗄️  Running database migrations..."
docker compose -f $COMPOSE_FILE --env-file $ENV_FILE exec backend \
    php artisan migrate --force 2>/dev/null || echo "⚠️  Migration skipped"

# Show status
echo ""
echo "📊 Service Status:"
docker compose -f $COMPOSE_FILE --env-file $ENV_FILE ps

# Reload nginx
echo ""
echo "🔄 Reloading host Nginx..."
sudo nginx -t && sudo systemctl reload nginx

# Clean up
echo ""
echo "🧹 Cleaning up unused Docker images..."
docker image prune -f

echo ""
echo "============================================="
echo "  ✅ Deployment complete!"
echo "============================================="
echo ""
echo "  🌐 https://khodat.com"
echo "  🔧 https://api.khodat.com"
echo "  👤 https://app.khodat.com"
echo "  🛡️  https://admin.khodat.com"
echo "  📡 https://socket.khodat.com"
echo ""
