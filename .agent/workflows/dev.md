---
description: Các lệnh development thường dùng (logs, debug, restart)
---

# Dev Commands

## Xem logs

// turbo-all

1. Log backend:
```bash
cd /Users/hwg/Documents/kyguidat
docker compose logs -f backend --tail 50
```

2. Log API gateway:
```bash
docker compose logs -f api-gateway --tail 50
```

3. Log tất cả:
```bash
docker compose logs -f --tail 20
```

## Restart services

4. Restart backend sau khi sửa code PHP:
```bash
docker compose restart backend
```

5. Rebuild admin Vue sau khi sửa code:
```bash
docker compose up -d --build admin
```

6. Rebuild frontend Next.js:
```bash
docker compose up -d --build frontend
```

## Database

7. Chạy migration:
```bash
docker compose exec backend php artisan migrate
```

8. Rollback migration:
```bash
docker compose exec backend php artisan migrate:rollback
```

9. Laravel Tinker (REPL):
```bash
docker compose exec backend php artisan tinker
```

10. Seed data:
```bash
docker compose exec backend php artisan db:seed
```

## Cache & Queue

11. Clear cache:
```bash
docker compose exec backend php artisan cache:clear
docker compose exec backend php artisan config:clear
docker compose exec backend php artisan route:clear
```

## Image Upload

12. Migrate base64 images sang MinIO WebP:
```bash
docker compose exec backend php artisan images:migrate-base64 --dry-run
docker compose exec backend php artisan images:migrate-base64
```
