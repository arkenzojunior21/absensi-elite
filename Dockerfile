FROM php:8.2-apache

# Install ekstensi mysqli & pdo_mysql untuk koneksi database
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli

# Aktifkan mod_rewrite Apache
RUN a2enmod rewrite

# Salin source code project ke direktori web Apache
COPY . /var/www/html/

# Konfigurasi Apache port agar dinamis sesuai port Render ($PORT)
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Set permission folder uploads jika diperlukan
RUN chown -R www-data:www-data /var/www/html/uploads || true

EXPOSE 80
