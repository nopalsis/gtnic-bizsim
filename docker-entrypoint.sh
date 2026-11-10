#!/bin/bash
set -e

# Selalu sinkronkan hasil build terbaru dari image ke host volume
if [ -d "/tmp/build" ]; then
    echo "Updating public/build from latest image build..."
    mkdir -p /var/www/html/public/build
    cp -rf /tmp/build/. /var/www/html/public/build/
    chmod -R 755 /var/www/html/public/build 2>/dev/null || true
fi

# Buat semua struktur folder storage yang dibutuhkan Laravel jika belum ada
mkdir -p /var/www/html/storage/app/public \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# Atur ownership dan permission agar web server (www-data) bisa read/write
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Pastikan symbolic link public/storage terhubung secara relatif agar valid di dalam container dan host
if [ ! -L /var/www/html/public/storage ]; then
    ln -sfn ../storage/app/public /var/www/html/public/storage
fi

# Jika di environment production, jalankan optimasi otomatis saat container start
if [ "$APP_ENV" = "production" ]; then
    echo "Production environment detected: running Laravel optimizations..."
    php artisan optimize:clear 2>/dev/null || true
    php artisan config:cache 2>/dev/null || true
    php artisan route:cache 2>/dev/null || true
    php artisan view:cache 2>/dev/null || true
fi

exec "$@"
