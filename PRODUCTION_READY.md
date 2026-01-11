# Level 5: Production Ready Documentation

## 1. Security Policy
Kebijakan keamanan yang diterapkan pada PUSTANI:
* **Network Isolation:** Database tidak terekspos ke internet publik, hanya bisa diakses oleh container App melalui jaringan internal `pustani-net`.
* **Secure Headers:** Nginx dikonfigurasi dengan X-Frame-Options dan X-XSS-Protection.
* **Environment Secrets:** Kredensial database tidak di-hardcode, melainkan menggunakan Environment Variables.

## 2. Incident Response Plan (Rencana Tanggap Darurat)
Jika server down atau database corropt, berikut langkah pemulihannya:
1.  **Deteksi:** Alert dari Grafana akan memberitahu admin jika CPU Usage > 80% atau Container mati.
2.  **Isolasi:** Matikan container yang bermasalah via `docker-compose stop`.
3.  **Recovery:**
    * Jalankan script `./backup_db.sh` secara rutin (Cronjob).
    * Jika data hilang, restore menggunakan perintah: `docker exec -i service-db mysql -u root -p pustani_db < backups/file_terakhir.sql`

## 3. Monitoring Dashboard
Infrastruktur dipantau menggunakan stack:
* **cAdvisor:** Mengumpulkan metrik container.
* **Prometheus:** Menyimpan time-series data.
* **Grafana:** Menampilkan visualisasi penggunaan Resource (CPU/RAM).