#!/bin/bash
# Script Backup Database PUSTANI (SECURE VERSION)

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="./backups"
FILENAME="pustani_backup_$TIMESTAMP.sql"

# Buat folder backup jika belum ada
mkdir -p $BACKUP_DIR

echo "Mulai membackup database..."

# === BAGIAN INI SUDAH DIPERBAIKI ===
# Menggunakan perintah 'sh -c' agar script membaca variabel environment (MYSQL_USER, MYSQL_PASSWORD) 
# milik container itu sendiri. Jadi aman karena password tidak tertulis di sini.

docker exec service-db sh -c 'exec mysqldump -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' > "$BACKUP_DIR/$FILENAME"

echo "Backup selesai! File tersimpan di: $BACKUP_DIR/$FILENAME"