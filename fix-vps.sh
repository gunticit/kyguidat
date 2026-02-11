#!/bin/bash
set -e

echo "=========================================="
echo "  FIX VPS - Ký Gửi Đất Vuôn"
echo "  Script này fix TẤT CẢ vấn đề trên VPS"
echo "=========================================="

# ---- MÀUUU ----
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

print_step() { echo -e "\n${GREEN}[STEP $1]${NC} $2"; }
print_warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
print_ok()   { echo -e "${GREEN}[OK]${NC} $1"; }
print_err()  { echo -e "${RED}[ERROR]${NC} $1"; }

# ============================================
# STEP 1: Fix DNS
# ============================================
print_step 1 "Fixing DNS resolution..."

# Fix /etc/hosts
if ! grep -q "hungdoanprod-vps" /etc/hosts; then
    echo "127.0.0.1 hungdoanprod-vps" >> /etc/hosts
    print_ok "Added hostname to /etc/hosts"
fi

# Fix /etc/resolv.conf - use Google DNS directly
cat > /etc/resolv.conf << 'RESOLV'
nameserver 8.8.8.8
nameserver 8.8.4.4
RESOLV
print_ok "Set Google DNS in /etc/resolv.conf"

# Test DNS
if nslookup registry-1.docker.io > /dev/null 2>&1; then
    print_ok "DNS resolution working"
else
    print_err "DNS still broken. Check network connectivity."
    exit 1
fi

# ============================================
# STEP 2: Fix Docker DNS
# ============================================
print_step 2 "Configuring Docker DNS..."

mkdir -p /etc/docker
cat > /etc/docker/daemon.json << 'DOCKERDNS'
{
  "dns": ["8.8.8.8", "8.8.4.4"]
}
DOCKERDNS
systemctl restart docker
sleep 3
print_ok "Docker daemon configured with Google DNS"

# ============================================
# STEP 3: Fix MySQL root password
# ============================================
print_step 3 "Fixing MySQL root password & user permissions..."

cd /var/www/html/kyguidat

# Get actual DB password from .env.prod
DB_PASS=$(grep "^DB_PASSWORD=" .env.prod | cut -d'=' -f2)
DB_USER=$(grep "^DB_USERNAME=" .env.prod | cut -d'=' -f2)
DB_NAME=$(grep "^DB_DATABASE=" .env.prod | cut -d'=' -f2)

if [ -z "$DB_PASS" ] || [ -z "$DB_USER" ] || [ -z "$DB_NAME" ]; then
    print_err ".env.prod missing DB_PASSWORD, DB_USERNAME, or DB_DATABASE"
    print_warn "Please edit .env.prod first: nano /var/www/html/kyguidat/.env.prod"
    exit 1
fi

echo "  DB_USER=$DB_USER, DB_DATABASE=$DB_NAME, DB_PASSWORD=****"

# Get volume name
VOLUME_NAME=$(docker volume ls --format '{{.Name}}' | grep mysql-data | head -1)

if [ -z "$VOLUME_NAME" ]; then
    print_warn "No MySQL volume found - will be created fresh on start"
else
    print_ok "Found MySQL volume: $VOLUME_NAME"
    
    # Stop current MySQL
    docker compose -f docker-compose.prod.yml --env-file .env.prod stop mysql 2>/dev/null || true
    sleep 2

    # Run temporary MySQL with skip-grant-tables
    echo "  Starting temporary MySQL for password reset..."
    docker run --rm -d \
        --name mysql-reset \
        -v ${VOLUME_NAME}:/var/lib/mysql \
        mysql:8.0 \
        --skip-grant-tables --skip-networking=OFF > /dev/null 2>&1

    # Wait for MySQL to be ready
    echo "  Waiting for MySQL to start..."
    for i in $(seq 1 30); do
        if docker exec mysql-reset mysqladmin ping -h localhost --silent 2>/dev/null; then
            break
        fi
        sleep 1
    done

    # Reset passwords and permissions
    docker exec mysql-reset mysql -u root -e "
    FLUSH PRIVILEGES;
    ALTER USER 'root'@'localhost' IDENTIFIED BY '${DB_PASS}';
    CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED BY '${DB_PASS}';
    GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;
    CREATE USER IF NOT EXISTS '${DB_USER}'@'%' IDENTIFIED BY '${DB_PASS}';
    GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'%';
    CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
    GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
    FLUSH PRIVILEGES;
    " 2>/dev/null

    if [ $? -eq 0 ]; then
        print_ok "MySQL passwords and permissions fixed"
    else
        print_err "Failed to reset MySQL. You may need to delete the volume and start fresh."
        print_warn "Run: docker volume rm $VOLUME_NAME"
    fi

    # Stop temporary MySQL
    docker stop mysql-reset > /dev/null 2>&1
    sleep 2

    # Update MYSQL_ROOT_PASSWORD in .env.prod
    sed -i "s/^MYSQL_ROOT_PASSWORD=.*/MYSQL_ROOT_PASSWORD=${DB_PASS}/" .env.prod
    print_ok "Updated MYSQL_ROOT_PASSWORD in .env.prod"
fi

# ============================================
# STEP 4: Pull latest code & rebuild
# ============================================
print_step 4 "Pulling latest code..."

cd /var/www/html/kyguidat
git pull
print_ok "Code updated"

# ============================================
# STEP 5: Rebuild & start all services
# ============================================
print_step 5 "Building and starting all services..."

docker compose -f docker-compose.prod.yml --env-file .env.prod up -d --build

# ============================================
# STEP 6: Wait & verify
# ============================================
print_step 6 "Waiting for services to start (30s)..."
sleep 30

echo ""
echo "=========================================="
echo "  SERVICE STATUS"
echo "=========================================="

# Check each container
for container in khodat-mysql khodat-redis khodat-api-gateway khodat-backend khodat-backend-nginx khodat-admin khodat-frontend khodat-san-dat khodat-socket; do
    STATUS=$(docker inspect --format='{{.State.Status}}' $container 2>/dev/null || echo "not found")
    RESTART=$(docker inspect --format='{{.RestartCount}}' $container 2>/dev/null || echo "?")
    
    if [ "$STATUS" = "running" ] && [ "$RESTART" -lt 3 ] 2>/dev/null; then
        echo -e "  ${GREEN}✓${NC} $container - $STATUS"
    elif [ "$STATUS" = "running" ]; then
        echo -e "  ${YELLOW}⚠${NC} $container - $STATUS (restarted $RESTART times)"
    else
        echo -e "  ${RED}✗${NC} $container - $STATUS"
    fi
done

echo ""
echo "=========================================="
echo "  QUICK HEALTH CHECK"
echo "=========================================="

# Test local endpoints
for endpoint in "localhost:3015|Frontend" "localhost:8080|API Gateway" "localhost:8088|San-dat" "localhost:8089|Backend Nginx" "localhost:3020|Socket" "localhost:3016|Admin"; do
    URL=$(echo $endpoint | cut -d'|' -f1)
    NAME=$(echo $endpoint | cut -d'|' -f2)
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" --max-time 5 http://$URL 2>/dev/null || echo "000")
    
    if [ "$HTTP_CODE" != "000" ] && [ "$HTTP_CODE" != "502" ]; then
        echo -e "  ${GREEN}✓${NC} $NAME (HTTP $HTTP_CODE)"
    else
        echo -e "  ${RED}✗${NC} $NAME (HTTP $HTTP_CODE)"
    fi
done

echo ""
echo "=========================================="
echo "  DONE! Check errors above."
echo "  If any service failed, run:"
echo "  docker logs <container-name>"
echo "=========================================="
