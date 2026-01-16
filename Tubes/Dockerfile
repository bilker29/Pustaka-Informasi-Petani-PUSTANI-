FROM php:8.1-apache

# Install ekstensi (biarkan sama)
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN a2enmod rewrite

# --- BAGIAN YANG DIPERBAIKI ---
# Gunakan TITIK (.) artinya "folder ini", bukan "Tubes/"
COPY . /var/www/html/
# ------------------------------

# Ubah hak akses (biarkan sama)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80