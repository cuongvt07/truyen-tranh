# VPS Lunelit - Thông tin vận hành

> Không ghi private key, mật khẩu root hoặc mật khẩu database vào Git.
> Private key đang được lưu tại `C:\Users\Admin\.ssh\id_rsa` trên máy quản trị.

## 1. Thông tin VPS

| Mục | Giá trị |
|---|---|
| Domain | `https://lunelit.net` |
| IPv4 | `178.105.232.21` |
| IPv6 | `2a01:4f8:1c16:fe5b::1` |
| SSH port | `22` |
| SSH user | `root` |
| SSH alias | `lunelit` |
| Hệ điều hành | Ubuntu 26.04 LTS |
| Thư mục website | `/opt/truyen-tranh` |
| Git repository | `https://github.com/cuongvt07/truyen-tranh.git` |
| Nhánh triển khai | `dev` |

Website đang chạy sau Cloudflare. Trên production, luôn truy cập bằng
`https://lunelit.net`; không đăng nhập bằng IP vì session cookie được giới hạn
cho HTTPS và domain `.lunelit.net`.

## 2. SSH và VS Code

Kết nối từ terminal:

```bash
ssh lunelit
```

Nếu không dùng alias:

```bash
ssh -i C:\Users\Admin\.ssh\id_rsa root@178.105.232.21
```

SSH config trên máy Windows:

```sshconfig
Host lunelit
    HostName 178.105.232.21
    User root
    Port 22
    IdentityFile C:\Users\Admin\.ssh\id_rsa
    IdentitiesOnly yes
```

Kết nối bằng VS Code:

1. Nhấn `Ctrl + Shift + P`.
2. Chọn `Remote-SSH: Connect to Host`.
3. Chọn `lunelit`.
4. Mở thư mục `/opt/truyen-tranh`.

Kiểm tra SSH:

```bash
ssh -o BatchMode=yes lunelit "hostname && id && uptime"
```

## 3. Docker Compose

Website sử dụng ba file Compose:

```text
docker-compose.yml
docker-compose.prod.yml
docker-compose.server.yml
```

Sau khi SSH vào VPS:

```bash
cd /opt/truyen-tranh

alias dc='docker compose -f docker-compose.yml -f docker-compose.prod.yml -f docker-compose.server.yml'
```

Alias `dc` chỉ tồn tại trong phiên terminal hiện tại.

Các service:

| Service | Chức năng |
|---|---|
| `nginx` | Web server, public cổng 80 |
| `app` | Laravel và PHP-FPM 8.3 |
| `queue` | Laravel queue worker |
| `redis` | Cache, session và queue |
| `db` | MariaDB 10.11 nội bộ |

Kiểm tra trạng thái:

```bash
dc ps
docker stats --no-stream
```

Khởi động toàn bộ:

```bash
dc up -d
```

Dừng container nhưng giữ database/volume:

```bash
dc down
```

Khởi động lại:

```bash
dc restart
```

Không chạy `docker compose down -v` trừ khi thực sự muốn xóa toàn bộ database
và dữ liệu trong Docker volumes.

## 4. Cập nhật code nhánh dev

Kiểm tra trước khi cập nhật:

```bash
cd /opt/truyen-tranh
git status
git branch --show-current
git log -1 --oneline
```

Cập nhật code không thay dependency hoặc Dockerfile:

```bash
git pull origin dev
dc up -d
dc exec -T app php artisan migrate --force
dc exec -T app php artisan optimize:clear
```

Nếu thay đổi `Dockerfile`, `composer.lock`, `package-lock.json` hoặc dependency:

```bash
git pull origin dev
dc build
dc up -d --force-recreate
dc exec -T app php artisan migrate --force
```

Sau khi recreate `app`, nên recreate hoặc restart `nginx` để Nginx nhận đúng IP
nội bộ mới của PHP container:

```bash
dc restart nginx
```

## 5. PHP, Composer và Artisan

PHP chạy bên trong container `app`, không chạy trực tiếp trên Ubuntu.

```bash
dc exec app php -v
dc exec app php artisan --version
dc exec app php artisan about
dc exec app php artisan route:list
dc exec app php artisan migrate:status
```

Chạy migrations:

```bash
dc exec -T app php artisan migrate --force
```

Xóa cache Laravel:

```bash
dc exec -T app php artisan optimize:clear
```

Tạo lại cache production:

```bash
dc exec -T app php artisan config:cache
dc exec -T app php artisan route:cache
dc exec -T app php artisan view:cache
```

Tinker:

```bash
dc exec app php artisan tinker
```

Composer:

```bash
dc exec app composer --version
```

Nếu image runtime không có Composer, chạy Composer qua container tạm hoặc rebuild
image theo Dockerfile thay vì cài package trực tiếp trong container đang chạy.

## 6. Database

Database production hiện chạy trong service `db` trên VPS:

```text
DB_HOST=db
DB_PORT=3306
DB_DATABASE=lunelit
DB_USERNAME=lunelit
```

Mật khẩu được lưu trong các file sau trên VPS và không nên đưa vào Git:

```text
/opt/truyen-tranh/.env
/opt/truyen-tranh/.env.production
```

Mở MariaDB console bằng biến môi trường trong container:

```bash
dc exec db sh -lc 'mariadb -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE"'
```

Kiểm tra dữ liệu nhanh:

```bash
dc exec db sh -lc 'mariadb -N -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE" -e "SELECT COUNT(*) AS users FROM users; SELECT COUNT(*) AS articles FROM articles; SELECT COUNT(*) AS chapters FROM chapters;"'
```

Không public cổng MariaDB `3306` ra Internet.

## 7. Backup và restore database

Tạo thư mục backup trên VPS:

```bash
mkdir -p /opt/truyen-tranh/backups/runtime
```

Backup database:

```bash
cd /opt/truyen-tranh
dc exec -T db sh -lc 'mariadb-dump -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" --single-transaction --routines --triggers "$MARIADB_DATABASE"' \
  | gzip > "backups/runtime/lunelit-$(date +%Y%m%d-%H%M%S).sql.gz"
```

Kiểm tra file backup:

```bash
ls -lh backups/runtime
gzip -t backups/runtime/lunelit-YYYYMMDD-HHMMSS.sql.gz
```

Restore database sẽ ghi đè dữ liệu hiện tại. Luôn backup trước khi restore:

```bash
gzip -cd backups/runtime/lunelit-YYYYMMDD-HHMMSS.sql.gz \
  | dc exec -T db sh -lc 'mariadb -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE"'

dc exec -T app php artisan migrate --force
dc exec -T app php artisan optimize:clear
```

## 8. Log và chẩn đoán

Xem toàn bộ log:

```bash
dc logs --tail=200
```

Theo dõi log realtime:

```bash
dc logs -f app nginx queue
```

Log từng service:

```bash
dc logs --tail=200 app
dc logs --tail=200 nginx
dc logs --tail=200 queue
dc logs --tail=200 db
dc logs --tail=200 redis
```

Kiểm tra website từ VPS:

```bash
curl -I https://lunelit.net
curl -I https://lunelit.net/login
curl -I -H 'Host: lunelit.net' http://127.0.0.1
```

Kiểm tra DNS Cloudflare:

```bash
getent ahostsv4 lunelit.net
getent ahostsv6 lunelit.net
```

## 9. Session, HTTPS và lỗi 419

Cấu hình production cần có:

```dotenv
APP_URL=https://lunelit.net
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=.lunelit.net
SESSION_DRIVER=redis
REDIS_HOST=redis
```

Nếu login báo `419 Page Expired`:

1. Chỉ dùng `https://lunelit.net/login`, không dùng IP hoặc HTTP.
2. Xóa cookie cũ của `lunelit.net` hoặc thử cửa sổ ẩn danh.
3. Kiểm tra Redis và app:

```bash
dc ps
dc exec redis redis-cli ping
dc logs --tail=100 app
```

4. Xóa cache và recreate các container nhận environment:

```bash
dc exec -T app php artisan optimize:clear
dc up -d --force-recreate app queue nginx
```

Lưu ý: `docker compose restart` không nạp lại giá trị mới từ `.env.production`.
Phải dùng `up -d --force-recreate` khi thay đổi biến môi trường.

## 10. Lỗi 502 Bad Gateway

Kiểm tra app và cổng PHP-FPM:

```bash
dc ps
dc logs --tail=100 app nginx
dc exec nginx getent hosts app
dc exec nginx sh -c 'nc -zvw3 app 9000'
```

Nếu app đang chạy nhưng Nginx vẫn giữ IP container cũ:

```bash
dc restart nginx
```

## 11. Quyền thư mục Laravel

Container PHP Alpine sử dụng UID/GID `82` cho `www-data`.

Nếu Laravel báo không ghi được `storage` hoặc `bootstrap/cache`:

```bash
cd /opt/truyen-tranh
chown -R 82:82 storage bootstrap/cache
find storage bootstrap/cache -type d -exec chmod 775 {} \;
find storage bootstrap/cache -type f -exec chmod 664 {} \;
dc restart app queue
dc restart nginx
```

## 12. Cloudflare

Các record origin cần trỏ tới:

```text
A     @     178.105.232.21
AAAA  @     2a01:4f8:1c16:fe5b::1
```

`www` có thể dùng CNAME:

```text
CNAME  www  lunelit.net
```

Website hiện hoạt động qua HTTPS Cloudflare. Khi thay đổi DNS hoặc SSL, kiểm tra:

```bash
curl -I https://lunelit.net
```

## 13. Kiểm tra nhanh sau deploy

```bash
cd /opt/truyen-tranh
alias dc='docker compose -f docker-compose.yml -f docker-compose.prod.yml -f docker-compose.server.yml'

git branch --show-current
git log -1 --oneline
dc ps
dc exec -T app php artisan migrate:status
dc exec -T redis redis-cli ping
curl -I https://lunelit.net
```

Kết quả mong đợi:

- Git branch là `dev`.
- `app`, `nginx`, `queue`, `redis`, `db` đều `Up`.
- `db` và `redis` healthy/hoạt động.
- Migrations đều ở trạng thái `Ran`.
- Website trả HTTP `200` hoặc redirect HTTPS hợp lệ.
