FROM php:8.4-fpm

# ---- Args para el usuario app (desde docker-compose) ----
ARG user
ARG uid

# ---- Sistema + deps -dev para compilar extensiones ----
# Notas:
# - PHPIZE_DEPS: herramientas para compilar (autoconf, make, gcc, etc.)
# - libjpeg62-turbo-dev en Debian (evita conflictos con libjpeg-dev)
# - libonig-dev NO es necesario en PHP 8.x
RUN set -eux; \
  apt-get update; \
  apt-get install -y --no-install-recommends \
    git curl unzip \
    pkg-config \
    libicu-dev \
    libxml2-dev \
    libonig-dev \
    libzip-dev zlib1g-dev \
    libjpeg62-turbo-dev libpng-dev libwebp-dev libfreetype6-dev \
    $PHPIZE_DEPS \
  ; \
  rm -rf /var/lib/apt/lists/*

# ---- Extensiones core: gd (con jpeg/webp/freetype), intl, etc. ----
RUN set -eux; \
  docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype; \
  docker-php-ext-install -j"$(nproc)" \
    zip mysqli soap pdo_mysql mbstring exif pcntl bcmath gd intl

# ---- Composer (desde imagen oficial) ----
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ---- Configuración optimizada de PHP/OPcache ----
COPY docker-compose/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# ---- Usuario de aplicación ----
RUN set -eux; \
  useradd -G www-data,root -u "$uid" -d "/home/$user" "$user"; \
  mkdir -p "/home/$user/.composer"; \
  chown -R "$user:$user" "/home/$user" /var/www

# ---- Working dir y permisos ----
WORKDIR /var/www

USER $user

# Comandos útiles para mantener el rendimiento:
# Limpiar cache cuando cambies configuración
# docker-compose exec app php artisan config:clear
# docker-compose exec app php artisan cache:clear

# Regenerar cache optimizado
# docker-compose exec app php artisan config:cache
# docker-compose exec app php artisan optimize

# Verificar OPcache
# docker-compose exec app php -i | grep opcache
# ⚠️ Nota importante:
# En desarrollo: Si cambias archivos .env o configuración, ejecuta php artisan config:clear para ver los cambios
# En producción: Siempre usa config:cache para máximo rendimiento

