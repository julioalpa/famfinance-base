#!/bin/bash
set -e

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
