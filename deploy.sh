#!/bin/bash
# ===========================================
# SAFE Deployment Script - khodat.com
# ===========================================
# Usage:
#   bash deploy.sh              → Deploy nhanh (có cache, có backup DB)
#   bash deploy.sh --build      → Full rebuild (no cache, có backup DB)
#   bash deploy.sh --backup     → Chỉ backup database
#   bash deploy.sh --restore    → Restore database từ backup gần nhất
#   bash deploy.sh --fix-mysql  → Sửa lỗi MySQL password mismatch (KHÔNG XÓA DATA)
#   bash deploy.sh --status     → Kiểm tra trạng thái services
#
# NGUYÊN TẮC: KHÔNG BAO GIỜ XÓA VOLUME. LUÔN BACKUP TRƯỚC KHI LÀM GÌ.

set -euo pipefail

# ===========================================
# CONFIG
# ===========================================
COMPOSE_FILE="docker-compose.prod.yml"
ENV_FILE=".env.prod"
BACKUP_DIR="./backups"
MYSQL_CONTAINER="khodat-mysql"
MAX_BACKUPS=10  # Giữ tối đa 10 file backup

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ===========================================
# HELPER FUNCTIONS
# ===========================================
log_info()  { echo -e "${BLUE}ℹ️  $1${NC}"; }
log_ok()    { echo -e "${GREEN}✅ $1${NC}"; }
log_warn()  { echo -e "${YELLOW}⚠️  $1${NC}"; }
log_error() { echo -e "${RED}❌ $1${NC}"; }

header() {
    echo ""
    echo "============================================="
    echo -e "  ${BLUE}$1${NC}"
    echo "  $(date '+%Y-%m-%d %H:%M:%S')"
    echo "============================================="
}

# Load DB password từ .env.prod
load_env() {
    if [ ! -f "$ENV_FILE" ]; then
        log_error "$ENV_FILE not found!"
        echo "   cp .env.prod.example .env.prod && nano .env.prod"
        exit 1
    fi
    # Source env vars (xử lý đặc biệt cho password có ký tự đặc biệt)
    export $(grep -v '^#' "$ENV_FILE" | grep -v '^\s*$' | sed 's/=\(.*\)/="\1"/' | xargs -0 2>/dev/null) 2>/dev/null || true
    
    # Parse trực tiếp các biến cần thiết
    DB_DATABASE=$(grep '^DB_DATABASE=' "$ENV_FILE" | cut -d'=' -f2)
    DB_USERNAME=$(grep '^DB_USERNAME=' "$ENV_FILE" | cut -d'=' -f2)
    DB_PASSWORD=$(grep '^DB_PASSWORD=' "$ENV_FILE" | cut -d'=' -f2)
    MYSQL_ROOT_PASSWORD=$(grep '^MYSQL_ROOT_PASSWORD=' "$ENV_FILE" | cut -d'=' -f2)
}

# ===========================================
# DATABASE BACKUP (BẮT BUỘC TRƯỚC MỌI THAY ĐỔI)
# ===========================================
backup_database() {
    header "💾 BACKUP DATABASE"
    
    # Kiểm tra MySQL container có đang chạy không
    if ! docker ps --format '{{.Names}}' | grep -q "^${MYSQL_CONTAINER}$"; then
        log_warn "MySQL container không chạy - bỏ qua backup"
        return 1
    fi

    # Kiểm tra kết nối MySQL
    if ! docker exec "$MYSQL_CONTAINER" mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "SELECT 1;" &>/dev/null; then
        log_warn "Không thể kết nối MySQL với password trong .env.prod"
        log_warn "Thử với root password..."
        if ! docker exec "$MYSQL_CONTAINER" mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e "SELECT 1;" &>/dev/null; then
            log_error "Không thể kết nối MySQL! Chạy: bash deploy.sh --fix-mysql"
            return 1
        fi
        # Dùng root để backup
        BACKUP_USER="root"
        BACKUP_PASS="$MYSQL_ROOT_PASSWORD"
    else
        BACKUP_USER="$DB_USERNAME"
        BACKUP_PASS="$DB_PASSWORD"
    fi

    # Tạo thư mục backup
    mkdir -p "$BACKUP_DIR"

    # Tạo file backup với timestamp
    TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
    BACKUP_FILE="${BACKUP_DIR}/khodat_${TIMESTAMP}.sql.gz"

    log_info "Đang backup database '${DB_DATABASE}' → ${BACKUP_FILE}"

    docker exec "$MYSQL_CONTAINER" \
        mysqldump -u"$BACKUP_USER" -p"$BACKUP_PASS" \
        --single-transaction \
        --routines \
        --triggers \
        --databases "$DB_DATABASE" 2>/dev/null \
        | gzip > "$BACKUP_FILE"

    # Kiểm tra file backup
    BACKUP_SIZE=$(du -sh "$BACKUP_FILE" 2>/dev/null | cut -f1)
    if [ -s "$BACKUP_FILE" ]; then
        log_ok "Backup thành công: ${BACKUP_FILE} (${BACKUP_SIZE})"
    else
        log_error "Backup file rỗng! DỪNG DEPLOY."
        rm -f "$BACKUP_FILE"
        exit 1
    fi

    # Xóa backup cũ, giữ MAX_BACKUPS file gần nhất
    BACKUP_COUNT=$(ls -1 "$BACKUP_DIR"/khodat_*.sql.gz 2>/dev/null | wc -l)
    if [ "$BACKUP_COUNT" -gt "$MAX_BACKUPS" ]; then
        ls -1t "$BACKUP_DIR"/khodat_*.sql.gz | tail -n +$((MAX_BACKUPS + 1)) | xargs rm -f
        log_info "Đã dọn backup cũ, giữ $MAX_BACKUPS file gần nhất"
    fi

    return 0
}

# ===========================================
# RESTORE DATABASE
# ===========================================
restore_database() {
    header "🔄 RESTORE DATABASE"

    # Tìm file backup gần nhất
    if [ -n "${1:-}" ] && [ -f "$1" ]; then
        RESTORE_FILE="$1"
    else
        RESTORE_FILE=$(ls -1t "$BACKUP_DIR"/khodat_*.sql.gz 2>/dev/null | head -1)
    fi

    if [ -z "$RESTORE_FILE" ] || [ ! -f "$RESTORE_FILE" ]; then
        log_error "Không tìm thấy file backup nào!"
        echo "   Các file backup có sẵn:"
        ls -lh "$BACKUP_DIR"/khodat_*.sql.gz 2>/dev/null || echo "   (không có)"
        exit 1
    fi

    RESTORE_SIZE=$(du -sh "$RESTORE_FILE" | cut -f1)
    echo ""
    log_warn "SẼ RESTORE TỪ: ${RESTORE_FILE} (${RESTORE_SIZE})"
    log_warn "TOÀN BỘ DATA HIỆN TẠI SẼ BỊ GHI ĐÈ!"
    echo ""
    read -p "Bạn có chắc chắn? (yes/no): " CONFIRM
    if [ "$CONFIRM" != "yes" ]; then
        log_info "Đã hủy restore."
        exit 0
    fi

    # Backup data hiện tại trước khi restore
    log_info "Backup data hiện tại trước khi restore..."
    backup_database || log_warn "Không thể backup data hiện tại"

    log_info "Đang restore từ ${RESTORE_FILE}..."
    gunzip -c "$RESTORE_FILE" | docker exec -i "$MYSQL_CONTAINER" \
        mysql -uroot -p"$MYSQL_ROOT_PASSWORD" 2>/dev/null

    if [ $? -eq 0 ]; then
        log_ok "Restore thành công!"
    else
        log_error "Restore thất bại!"
        exit 1
    fi
}

# ===========================================
# FIX MYSQL PASSWORD (KHÔNG XÓA DATA)
# ===========================================
fix_mysql_password() {
    header "🔧 FIX MYSQL PASSWORD (SAFE - KHÔNG XÓA DATA)"

    load_env

    # Kiểm tra MySQL container
    if ! docker ps --format '{{.Names}}' | grep -q "^${MYSQL_CONTAINER}$"; then
        log_error "MySQL container không chạy!"
        exit 1
    fi

    # Test kết nối hiện tại
    if docker exec "$MYSQL_CONTAINER" mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "SELECT 1;" &>/dev/null; then
        log_ok "MySQL password đã đúng, không cần sửa."
        return 0
    fi

    log_warn "Password trong .env.prod không khớp với MySQL"
    log_info "Đang sửa bằng phương pháp skip-grant-tables (GIỮ NGUYÊN DATA)..."

    # Bước 1: Stop MySQL
    docker stop "$MYSQL_CONTAINER"

    # Bước 2: Start MySQL với skip-grant-tables
    MYSQL_VOLUME=$(docker inspect "$MYSQL_CONTAINER" --format '{{range .Mounts}}{{if eq .Destination "/var/lib/mysql"}}{{.Name}}{{end}}{{end}}' 2>/dev/null || echo "kyguidat_mysql-data")
    
    log_info "Khởi động MySQL tạm thời với skip-grant-tables..."
    docker run --rm -d \
        --name mysql-fix-temp \
        -v "${MYSQL_VOLUME}:/var/lib/mysql" \
        -e MYSQL_ROOT_PASSWORD="temp" \
        mysql:8.0 \
        --skip-grant-tables --skip-networking=off 2>/dev/null

    # Chờ MySQL sẵn sàng
    log_info "Chờ MySQL khởi động..."
    for i in $(seq 1 30); do
        if docker exec mysql-fix-temp mysql -uroot -e "SELECT 1;" &>/dev/null; then
            break
        fi
        sleep 2
    done

    # Bước 3: Reset password
    log_info "Đang reset password..."
    docker exec mysql-fix-temp mysql -uroot -e "
        FLUSH PRIVILEGES;
        ALTER USER 'root'@'localhost' IDENTIFIED BY '${MYSQL_ROOT_PASSWORD}';
        ALTER USER 'root'@'%' IDENTIFIED BY '${MYSQL_ROOT_PASSWORD}' ;
        CREATE USER IF NOT EXISTS '${DB_USERNAME}'@'%' IDENTIFIED BY '${DB_PASSWORD}';
        ALTER USER '${DB_USERNAME}'@'%' IDENTIFIED BY '${DB_PASSWORD}';
        GRANT ALL PRIVILEGES ON ${DB_DATABASE}.* TO '${DB_USERNAME}'@'%';
        FLUSH PRIVILEGES;
    " 2>/dev/null

    if [ $? -eq 0 ]; then
        log_ok "Password đã được reset thành công!"
    else
        log_error "Reset password thất bại!"
    fi

    # Bước 4: Dọn dẹp container tạm
    docker stop mysql-fix-temp 2>/dev/null || true
    docker rm mysql-fix-temp 2>/dev/null || true

    # Bước 5: Start lại MySQL container chính
    log_info "Khởi động lại MySQL container chính..."
    docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d mysql

    # Chờ healthy
    log_info "Chờ MySQL healthy..."
    for i in $(seq 1 30); do
        STATUS=$(docker inspect "$MYSQL_CONTAINER" --format '{{.State.Health.Status}}' 2>/dev/null || echo "unknown")
        if [ "$STATUS" = "healthy" ]; then
            break
        fi
        sleep 2
    done

    # Verify
    if docker exec "$MYSQL_CONTAINER" mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "SELECT 1;" &>/dev/null; then
        log_ok "MySQL hoạt động bình thường với password mới!"
    else
        log_error "Vẫn không kết nối được MySQL. Kiểm tra thủ công."
        exit 1
    fi
}

# ===========================================
# CHECK SERVICE STATUS
# ===========================================
check_status() {
    header "📊 SERVICE STATUS"

    docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" ps 2>/dev/null

    echo ""
    log_info "Health checks:"
    
    # Kiểm tra từng service
    for SERVICE in "khodat.com:8088" "api.khodat.com:8080" "backend:8015" "frontend:3015" "admin:8089"; do
        NAME=$(echo "$SERVICE" | cut -d: -f1)
        PORT=$(echo "$SERVICE" | cut -d: -f2)
        HTTP_CODE=$(curl -sI -o /dev/null -w '%{http_code}' "http://127.0.0.1:${PORT}/" --max-time 3 2>/dev/null || echo "000")
        if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
            log_ok "${NAME} → :${PORT} → HTTP ${HTTP_CODE}"
        else
            log_error "${NAME} → :${PORT} → HTTP ${HTTP_CODE}"
        fi
    done

    # MySQL
    if docker exec "$MYSQL_CONTAINER" mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "SELECT 1;" &>/dev/null; then
        log_ok "MySQL → kết nối OK"
    else
        log_error "MySQL → không kết nối được!"
    fi

    # Backups
    echo ""
    log_info "Backup files:"
    ls -lh "$BACKUP_DIR"/khodat_*.sql.gz 2>/dev/null || echo "   (chưa có backup nào)"
}

# ===========================================
# VERIFY MYSQL BEFORE DEPLOY
# ===========================================
verify_mysql() {
    log_info "Kiểm tra kết nối MySQL..."

    if ! docker ps --format '{{.Names}}' | grep -q "^${MYSQL_CONTAINER}$"; then
        log_warn "MySQL container chưa chạy, sẽ được khởi động khi deploy"
        return 0
    fi

    # Test connection
    if docker exec "$MYSQL_CONTAINER" mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "SELECT 1;" &>/dev/null; then
        log_ok "MySQL kết nối OK"
        return 0
    fi

    # Nếu không kết nối được, KHÔNG TỰ ĐỘNG XÓA gì cả
    log_error "MySQL password không khớp!"
    log_error "KHÔNG tự động xóa data. Chạy lệnh sau để sửa an toàn:"
    echo ""
    echo "   bash deploy.sh --fix-mysql"
    echo ""
    exit 1
}

# ===========================================
# MAIN DEPLOY
# ===========================================
deploy() {
    local BUILD_FLAG=""
    if [ "${1:-}" = "--build" ]; then
        BUILD_FLAG="--no-cache"
    fi

    header "🚀 DEPLOYING KHODAT.COM"
    load_env

    # ===== BƯỚC 0: KIỂM TRA MYSQL =====
    verify_mysql

    # ===== BƯỚC 1: BACKUP DATABASE (BẮT BUỘC) =====
    backup_database || log_warn "Bỏ qua backup (MySQL chưa có data hoặc chưa chạy)"

    # ===== BƯỚC 2: PULL CODE =====
    echo ""
    log_info "Pulling latest code..."
    git pull origin main || {
        log_warn "git pull thất bại, tiếp tục deploy với code hiện tại"
    }

    # ===== BƯỚC 3: BUILD IMAGES =====
    echo ""
    if [ -n "$BUILD_FLAG" ]; then
        log_info "Building all images (no cache)..."
        docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" build $BUILD_FLAG
    else
        log_info "Building images..."
        docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" build
    fi

    # ===== BƯỚC 4: START SERVICES =====
    echo ""
    log_info "Starting services..."
    docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d

    # ===== BƯỚC 5: WAIT FOR MYSQL =====
    echo ""
    log_info "Chờ MySQL healthy..."
    for i in $(seq 1 30); do
        STATUS=$(docker inspect "$MYSQL_CONTAINER" --format '{{.State.Health.Status}}' 2>/dev/null || echo "starting")
        if [ "$STATUS" = "healthy" ]; then
            log_ok "MySQL healthy!"
            break
        fi
        echo -n "."
        sleep 2
    done
    echo ""

    # ===== BƯỚC 6: VERIFY MYSQL CONNECTION =====
    if ! docker exec "$MYSQL_CONTAINER" mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "SELECT 1;" &>/dev/null; then
        log_error "MySQL không kết nối được sau deploy!"
        log_error "Chạy: bash deploy.sh --fix-mysql"
        log_warn "CÁC SERVICE KHÁC VẪN CHẠY, DATA VẪN CÒN."
        exit 1
    fi

    # ===== BƯỚC 7: RUN MIGRATIONS =====
    echo ""
    log_info "Running database migrations..."
    docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" exec -T backend \
        php artisan migrate --force 2>/dev/null || log_warn "Migration skipped"

    # ===== BƯỚC 8: RELOAD NGINX =====
    echo ""
    log_info "Reloading host Nginx..."
    sudo nginx -t && sudo systemctl reload nginx || log_warn "Nginx reload failed"

    # ===== BƯỚC 9: CLEANUP =====
    echo ""
    log_info "Cleaning up unused Docker images..."
    docker image prune -f 2>/dev/null

    # ===== BƯỚC 10: VERIFY =====
    echo ""
    check_status

    echo ""
    echo "============================================="
    log_ok "DEPLOYMENT COMPLETE!"
    echo "============================================="
    echo ""
    echo "  🌐 https://khodat.com"
    echo "  🔧 https://api.khodat.com"
    echo "  👤 https://app.khodat.com"
    echo "  🛡️  https://admin.khodat.com"
    echo "  📡 https://socket.khodat.com"
    echo ""
    echo "  💾 Backup: $(ls -1t "$BACKUP_DIR"/khodat_*.sql.gz 2>/dev/null | head -1 || echo 'N/A')"
    echo ""
}

# ===========================================
# MAIN - ROUTE COMMANDS
# ===========================================
load_env

case "${1:-deploy}" in
    --backup)
        backup_database
        ;;
    --restore)
        restore_database "${2:-}"
        ;;
    --fix-mysql)
        fix_mysql_password
        ;;
    --status)
        check_status
        ;;
    --build)
        deploy --build
        ;;
    --help|-h)
        echo "Usage: bash deploy.sh [COMMAND]"
        echo ""
        echo "Commands:"
        echo "  (none)        Deploy nhanh (có cache, backup DB trước)"
        echo "  --build       Full rebuild (no cache, backup DB trước)"
        echo "  --backup      Chỉ backup database"
        echo "  --restore     Restore database từ backup gần nhất"
        echo "  --fix-mysql   Sửa lỗi MySQL password (AN TOÀN, không mất data)"
        echo "  --status      Kiểm tra trạng thái services"
        echo "  --help        Hiển thị help"
        echo ""
        echo "Backups lưu tại: ${BACKUP_DIR}/"
        echo "Giữ tối đa ${MAX_BACKUPS} file backup gần nhất."
        ;;
    *)
        deploy
        ;;
esac
