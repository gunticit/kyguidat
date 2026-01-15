#!/bin/bash

# ===========================================
# KHODAT PRODUCTION DEPLOYMENT SCRIPT
# ===========================================
# Usage: ./deploy.sh [command]
# Commands: build, up, down, restart, logs, status, optimize, backup

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
COMPOSE_FILE="docker-compose.prod.yml"
ENV_FILE=".env.production"
BACKUP_DIR="./backups"
PROJECT_NAME="khodat"

# Helper functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if .env.production exists
check_env() {
    if [ ! -f "$ENV_FILE" ]; then
        log_error "File $ENV_FILE không tồn tại!"
        log_info "Chạy: cp .env.production.example .env.production"
        log_info "Sau đó cập nhật các giá trị trong file .env.production"
        exit 1
    fi
}

# Check if docker is running
check_docker() {
    if ! docker info > /dev/null 2>&1; then
        log_error "Docker daemon không chạy!"
        exit 1
    fi
}

# Build all images
build() {
    log_info "Building production images..."
    docker compose -f $COMPOSE_FILE --env-file $ENV_FILE build --no-cache
    log_success "Build hoàn tất!"
}

# Build specific service
build_service() {
    local service=$1
    log_info "Building $service..."
    docker compose -f $COMPOSE_FILE --env-file $ENV_FILE build --no-cache $service
    log_success "Build $service hoàn tất!"
}

# Start all containers
up() {
    log_info "Starting production containers..."
    docker compose -f $COMPOSE_FILE --env-file $ENV_FILE up -d
    log_success "Containers đã khởi động!"
    
    log_info "Đợi services khởi động..."
    sleep 10
    
    # Show status
    status
}

# Stop all containers
down() {
    log_info "Stopping containers..."
    docker compose -f $COMPOSE_FILE --env-file $ENV_FILE down
    log_success "Containers đã dừng!"
}

# Restart all containers
restart() {
    log_info "Restarting containers..."
    docker compose -f $COMPOSE_FILE --env-file $ENV_FILE restart
    log_success "Containers đã restart!"
}

# Restart specific service
restart_service() {
    local service=$1
    log_info "Restarting $service..."
    docker compose -f $COMPOSE_FILE --env-file $ENV_FILE restart $service
    log_success "$service đã restart!"
}

# View logs
logs() {
    local service=$1
    if [ -z "$service" ]; then
        docker compose -f $COMPOSE_FILE --env-file $ENV_FILE logs -f --tail=100
    else
        docker compose -f $COMPOSE_FILE --env-file $ENV_FILE logs -f --tail=100 $service
    fi
}

# Show status
status() {
    log_info "Container status:"
    docker compose -f $COMPOSE_FILE --env-file $ENV_FILE ps
    
    echo ""
    log_info "Health checks:"
    docker compose -f $COMPOSE_FILE --env-file $ENV_FILE ps --format "table {{.Name}}\t{{.Status}}"
}

# Laravel optimization
optimize_laravel() {
    log_info "Optimizing Laravel..."
    
    # Clear old caches
    docker exec ${PROJECT_NAME}-backend php artisan cache:clear
    docker exec ${PROJECT_NAME}-backend php artisan config:clear
    docker exec ${PROJECT_NAME}-backend php artisan route:clear
    docker exec ${PROJECT_NAME}-backend php artisan view:clear
    
    # Rebuild caches
    docker exec ${PROJECT_NAME}-backend php artisan config:cache
    docker exec ${PROJECT_NAME}-backend php artisan route:cache
    docker exec ${PROJECT_NAME}-backend php artisan view:cache
    docker exec ${PROJECT_NAME}-backend php artisan event:cache
    
    log_success "Laravel đã được optimize!"
}

# Run Laravel migrations
migrate() {
    log_info "Running migrations..."
    docker exec ${PROJECT_NAME}-backend php artisan migrate --force
    log_success "Migrations hoàn tất!"
}

# Backup database
backup_db() {
    mkdir -p $BACKUP_DIR
    
    local timestamp=$(date +%Y%m%d_%H%M%S)
    local backup_file="$BACKUP_DIR/khodat_db_$timestamp.sql"
    
    log_info "Backing up database to $backup_file..."
    
    # Get password from .env.production
    source $ENV_FILE
    
    docker exec ${PROJECT_NAME}-mysql mysqldump \
        -u root \
        -p"$MYSQL_ROOT_PASSWORD" \
        --single-transaction \
        --routines \
        --triggers \
        khodat > $backup_file
    
    # Compress
    gzip $backup_file
    
    log_success "Backup hoàn tất: ${backup_file}.gz"
    
    # Clean old backups (keep last 7)
    ls -t $BACKUP_DIR/*.gz 2>/dev/null | tail -n +8 | xargs -r rm
    log_info "Đã giữ lại 7 backup gần nhất"
}

# Restore database
restore_db() {
    local backup_file=$1
    
    if [ -z "$backup_file" ]; then
        log_error "Vui lòng chỉ định file backup!"
        log_info "Usage: ./deploy.sh restore_db backups/khodat_db_xxx.sql.gz"
        exit 1
    fi
    
    if [ ! -f "$backup_file" ]; then
        log_error "File backup không tồn tại: $backup_file"
        exit 1
    fi
    
    log_warning "Bạn có chắc muốn restore database? (y/n)"
    read -r confirm
    
    if [ "$confirm" != "y" ]; then
        log_info "Đã hủy restore"
        exit 0
    fi
    
    source $ENV_FILE
    
    log_info "Restoring database from $backup_file..."
    
    if [[ "$backup_file" == *.gz ]]; then
        gunzip -c $backup_file | docker exec -i ${PROJECT_NAME}-mysql mysql \
            -u root \
            -p"$MYSQL_ROOT_PASSWORD" \
            khodat
    else
        docker exec -i ${PROJECT_NAME}-mysql mysql \
            -u root \
            -p"$MYSQL_ROOT_PASSWORD" \
            khodat < $backup_file
    fi
    
    log_success "Restore hoàn tất!"
}

# Full deployment
deploy() {
    log_info "=== FULL DEPLOYMENT ==="
    
    check_env
    check_docker
    
    # Backup before deploy
    log_info "Step 1: Backup database..."
    if docker ps | grep -q "${PROJECT_NAME}-mysql"; then
        backup_db
    else
        log_warning "MySQL chưa chạy, bỏ qua backup"
    fi
    
    # Pull latest code (if using git)
    log_info "Step 2: Pull latest code..."
    if [ -d ".git" ]; then
        git pull origin main || git pull origin master || log_warning "Git pull failed, continuing..."
    fi
    
    # Build images
    log_info "Step 3: Build images..."
    build
    
    # Start containers
    log_info "Step 4: Start containers..."
    up
    
    # Wait for MySQL to be healthy
    log_info "Step 5: Waiting for MySQL..."
    sleep 15
    
    # Run migrations
    log_info "Step 6: Run migrations..."
    migrate
    
    # Optimize Laravel
    log_info "Step 7: Optimize Laravel..."
    optimize_laravel
    
    log_success "=== DEPLOYMENT HOÀN TẤT ==="
    status
}

# Quick update (no build)
update() {
    log_info "=== QUICK UPDATE ==="
    
    check_env
    check_docker
    
    # Pull latest code
    if [ -d ".git" ]; then
        git pull origin main || git pull origin master
    fi
    
    # Restart containers
    restart
    
    # Optimize Laravel
    sleep 5
    optimize_laravel
    
    log_success "=== UPDATE HOÀN TẤT ==="
}

# Clean up unused resources
cleanup() {
    log_info "Cleaning up unused Docker resources..."
    docker system prune -f
    docker image prune -f
    log_success "Cleanup hoàn tất!"
}

# Show help
show_help() {
    echo "=== KHODAT DEPLOYMENT SCRIPT ==="
    echo ""
    echo "Usage: ./deploy.sh [command]"
    echo ""
    echo "Commands:"
    echo "  build              Build tất cả images"
    echo "  build [service]    Build một service cụ thể"
    echo "  up                 Start tất cả containers"
    echo "  down               Stop tất cả containers"
    echo "  restart            Restart tất cả containers"
    echo "  restart [service]  Restart một service cụ thể"
    echo "  logs               Xem logs (Ctrl+C để thoát)"
    echo "  logs [service]     Xem logs của một service"
    echo "  status             Xem trạng thái containers"
    echo "  optimize           Optimize Laravel cache"
    echo "  migrate            Chạy Laravel migrations"
    echo "  backup             Backup database"
    echo "  restore [file]     Restore database từ backup"
    echo "  deploy             Full deployment (backup, build, up, migrate, optimize)"
    echo "  update             Quick update (pull, restart, optimize)"
    echo "  cleanup            Dọn dẹp Docker resources"
    echo "  help               Hiển thị help này"
    echo ""
    echo "Services: frontend, backend, backend-nginx, admin-php, admin-nginx, mysql, redis"
}

# Main
check_docker

case "$1" in
    build)
        check_env
        if [ -n "$2" ]; then
            build_service $2
        else
            build
        fi
        ;;
    up)
        check_env
        up
        ;;
    down)
        down
        ;;
    restart)
        if [ -n "$2" ]; then
            restart_service $2
        else
            restart
        fi
        ;;
    logs)
        logs $2
        ;;
    status)
        status
        ;;
    optimize)
        optimize_laravel
        ;;
    migrate)
        migrate
        ;;
    backup)
        check_env
        backup_db
        ;;
    restore)
        check_env
        restore_db $2
        ;;
    deploy)
        deploy
        ;;
    update)
        check_env
        update
        ;;
    cleanup)
        cleanup
        ;;
    help|--help|-h)
        show_help
        ;;
    *)
        show_help
        ;;
esac
