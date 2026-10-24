FROM dunglas/frankenphp:php8.3-alpine

# Instal ekstensi dan dependensi sistem yang dibutuhkan Laravel
RUN install-php-extensions \
    pdo_mysql \
    gd \
    intl \
    zip \
    opcache

WORKDIR /app

# Salin file composer terlebih dahulu untuk memanfaatkan cache layer
COPY composer.json composer.lock ./

# Instal dependensi menggunakan Composer (gunakan image composer terpisah sebagai builder ringan)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Salin sisa source code aplikasi (pastikan kredensial aman via .dockerignore)
COPY . .

# Generate autoloader dan jalankan optimasi
RUN composer dump-autoload --optimize

# Beri akses write untuk storage dan cache
RUN chown -R root:root /app/storage /app/bootstrap/cache