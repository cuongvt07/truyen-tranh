# Deploy (Docker) — build 1 lần, update bằng `git pull`

Cấu hình prod cho **code live**: source được bind-mount, nên sau lần đầu **không cần build lại image** mỗi khi sửa code — chỉ `git pull` + clear cache.

## Lần đầu trên server
```bash
git clone https://github.com/cuongvt07/truyen-tranh.git
cd truyen-tranh

# Tạo ./.env (prod). Tối thiểu:
#   APP_ENV=production
#   APP_DEBUG=false
#   APP_KEY=base64:...            # docker compose ... exec app php artisan key:generate --show
#   APP_URL=https://your-domain
#   DB_HOST=...  DB_DATABASE=...  DB_USERNAME=...  DB_PASSWORD=...
#   REDIS_HOST=redis
#   CACHE_STORE=redis  SESSION_DRIVER=redis  QUEUE_CONNECTION=redis
#   HTTP_PORT=80
nano .env

bash scripts/deploy.sh        # build image 1 lần + up -d (migrate tự chạy)
```

## Update sau này (KHỎI build lại)
```bash
bash scripts/update.sh        # git pull + composer install (nếu cần) + migrate + clear cache
```

## Khi nào BẮT BUỘC build lại image
Chỉ khi đổi: `Dockerfile`, PHP extension, hoặc thêm/bớt **composer/npm dependency**.
```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

## Lưu ý
- **Database**: base compose KHÔNG có service MySQL. Dùng DB managed/ngoài (set `DB_HOST` trong `./.env`) hoặc tự thêm service mysql.
- Dùng `-f` tường minh nên `docker-compose.override.yml` (dev) **không bị nạp** trên server.
- Migrate tự chạy khi app khởi động (`RUN_MIGRATIONS=true`). Cache không warm (code live) để pull là hiện ngay.
- Clear cache nhanh tại chỗ: vào URL bất kỳ kèm `?clear_cache=1` (khi đăng nhập admin).
