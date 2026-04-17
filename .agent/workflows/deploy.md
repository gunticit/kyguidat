---
description: Deploy project lên VPS Ubuntu (hoặc rebuild local)
---

# Deploy Khodat

## Local (Development)

// turbo-all

1. Build và start tất cả containers:
```bash
cd /Users/hwg/Documents/kyguidat
docker compose up -d --build
```

2. Setup MinIO bucket (lần đầu):
```bash
docker exec khodat-minio mc alias set local http://localhost:9000 khodat_minio khodat_minio_secret
docker exec khodat-minio mc mb local/khodat --ignore-existing
docker exec khodat-minio mc anonymous set public local/khodat
```

3. Setup Laravel Backend (lần đầu):
```bash
docker compose exec backend cp .env.example .env
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate
```

4. Kiểm tra services:
```bash
docker compose ps
```

5. Truy cập:
- Sàn đất: http://localhost:8088
- Admin: http://localhost:8089
- User Dashboard: http://localhost:3015
- MinIO Console: http://localhost:9002
- phpMyAdmin: http://localhost:8095

## Production (VPS)

1. SSH vào VPS và pull code mới:
```bash
cd /home/$USER/kyguidat
git pull origin main
```

2. Deploy:
```bash
bash deploy.sh --build
```

3. Setup MinIO bucket (lần đầu trên VPS):
```bash
docker exec khodat-minio mc alias set local http://localhost:9000 $MINIO_ROOT_USER $MINIO_ROOT_PASSWORD
docker exec khodat-minio mc mb local/khodat --ignore-existing
docker exec khodat-minio mc anonymous set public local/khodat
```

4. Migrate base64 images (nếu cần):
```bash
docker compose -f docker-compose.yml --env-file .env.prod exec backend php artisan images:migrate-base64 --dry-run
docker compose -f docker-compose.yml --env-file .env.prod exec backend php artisan images:migrate-base64
```
