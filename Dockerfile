FROM php:8.1-fpm

# Cài các gói hệ thống cần thiết
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    nginx \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Cài PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Cài Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /var/www

# Copy code và phân quyền
COPY . /var/www
RUN chown -R www-data:www-data /var/www

# Đánh dấu an toàn cho Git (nếu repo bị warning)
RUN git config --global --add safe.directory /var/www

RUN composer update --no-dev --optimize-autoloader || true

# Laravel optimize (chạy dưới quyền root, hoặc chuyển USER xuống dưới nếu cần chạy artisan)
RUN php artisan config:cache \
 && php artisan view:cache || true

# Phân quyền cho storage & cache
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Chạy bằng user www-data
USER www-data

# Expose port 9000 (PHP-FPM)
EXPOSE 9000
CMD ["php-fpm"]
