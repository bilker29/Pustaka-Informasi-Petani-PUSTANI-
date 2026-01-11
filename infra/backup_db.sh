#!/bin/bash
# Script Backup Database PUSTANI

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="./backups"
FILENAME="pustani_backup_$TIMESTAMP.sql"

# Buat folder backup jika belum ada
mkdir -p $BACKUP_DIR

echo "Mulai membackup database..."

# Perintah docker untuk dump database dari container
docker exec service-db /usr/bin/mysqldump -u docker_user --password=docker_pass pustani_db > "$BACKUP_DIR/$FILENAME"

echo "Backup selesai! File tersimpan di: $BACKUP_DIR/$FILENAME"