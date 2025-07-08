FROM php:8.1-fpm

# Cài các gói hệ thống cần thiết
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    libjpeg-dev libfreetype6-dev nginx \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# Cài Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /var/www

# Copy composer files trước để tận dụng cache khi không thay đổi code
COPY composer.json composer.lock ./

# Cài dependencies Laravel (chỉ install, không update)
RUN composer install --no-dev --optimize-autoloader

# Copy toàn bộ mã nguồn (sau khi composer xong để tránh rebuild khi code thay đổi)
COPY . .

# Chỉnh quyền thư mục
RUN chown -R www-data:www-data /var/www \
 && chmod -R 775 storage bootstrap/cache

# Copy file .env nếu cần (tùy bạn giữ sẵn trong project)
COPY .env .env

# Laravel optimize thủ công – KHÔNG chạy migrate ở đây
RUN php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache \
 || true

# User chạy PHP-FPM
USER www-data

# Expose port cho PHP-FPM
EXPOSE 9000

# Khởi động
CMD ["php-fpm"]
