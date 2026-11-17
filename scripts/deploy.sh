#!/usr/bin/env bash
set -e

# ==============================================================================
# NetSaz VM Deployment Script
# Sinkronisasi otomatis repositori VM dengan branch main di GitHub
# ==============================================================================

PROJECT_DIR="/var/www/netsaz"
cd "$PROJECT_DIR" || exit 1

echo "==> [1/5] Mengambil update terbaru dari GitHub origin/main..."
git fetch origin main

LOCAL_COMMIT=$(git rev-parse HEAD)
REMOTE_COMMIT=$(git rev-parse origin/main)

echo "    Commit lokal:  $LOCAL_COMMIT"
echo "    Commit GitHub: $REMOTE_COMMIT"

if [ "$LOCAL_COMMIT" = "$REMOTE_COMMIT" ] && [ "$1" != "--force" ]; then
    echo "==> Server sudah sinkron dengan GitHub. Tidak ada perubahan."
    exit 0
fi

echo "==> [2/5] Reset ke origin/main..."
git reset --hard origin/main
git clean -fd

echo "==> [3/5] Menjalankan migrasi database..."
php artisan migrate --force

echo "==> [4/5] Memperbarui cache Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> [5/5] Memastikan permissions storage & bootstrap/cache..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

LATEST_LOG=$(git log -1 --pretty=format:"%h - %s (%ci)")
echo "==> Deployment sukses! Versi aktif sekarang: $LATEST_LOG"
