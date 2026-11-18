#!/bin/sh
set -e
cd /var/www/html

# Lần chạy đầu: cài CodeIgniter 4 (composer install cũng tự chép file cấu hình mặc định)
if [ ! -f vendor/autoload.php ]; then
    echo ">> Đang cài CodeIgniter 4 bằng Composer (chỉ lần đầu)..."
    composer install --no-interaction --prefer-dist --no-progress
fi

# Bảo đảm đủ file cấu hình + thư mục ghi
php bin/setup.php || true
mkdir -p writable/cache writable/logs writable/session writable/uploads writable/debugbar public/uploads/products
chown -R www-data:www-data writable public/uploads || true

exec docker-php-entrypoint "$@"
