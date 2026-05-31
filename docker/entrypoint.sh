#!/bin/bash
set -e

echo "==> Preparando directorios..."
mkdir -p /var/www/html/storage/framework/{sessions,views,cache/data}
mkdir -p /var/www/html/storage/app
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

if [ "${DB_CONNECTION:-}" = "sqlite" ] && [ -n "${DB_DATABASE:-}" ]; then
    echo "==> Asegurando archivo SQLite en ${DB_DATABASE}..."
    mkdir -p "$(dirname "$DB_DATABASE")"
    touch "$DB_DATABASE"
    chown www-data:www-data "$DB_DATABASE"
    chmod 664 "$DB_DATABASE"
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

echo "==> Cacheando configuración..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Ejecutando migraciones..."
php artisan migrate --force

echo "==> Enlazando storage..."
php artisan storage:link 2>/dev/null || true

echo "==> Listo. Iniciando servidor..."
exec "$@"
