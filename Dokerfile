# Gunakan image PHP dengan Apache
FROM php:8.1-apache

# Install ekstensi PHP yang dibutuhkan Pustani (mysqli)
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Aktifkan mod_rewrite Apache (agar URL rapi)
RUN a2enmod rewrite

# Copy kodingan Pustani ke dalam Container
COPY Tubes/ /var/www/html/

# Ubah hak akses agar Apache bisa baca
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Buka Port 80
EXPOSE 80