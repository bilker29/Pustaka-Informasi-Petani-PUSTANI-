# Level 5: Production Ready Infrastructure Standards

## 1. Security Policy & Hardening
Implementasi keamanan berlapis (Defense in Depth) yang diterapkan:
* **Network Firewall:** Menggunakan AWS Security Group yang hanya membuka port 80 (HTTP) dan 22 (SSH) ke publik. Database (Port 3306) **DIBLOKIR** dari akses internet.
* **Application Firewall:** Nginx dikonfigurasi dengan header keamanan wajib:
    * `X-Frame-Options: SAMEORIGIN` (Anti-Clickjacking)
    * `X-XSS-Protection: 1; mode=block` (Anti-XSS script)
* **Secrets Management:** Password database tidak ditulis di kodingan, melainkan disuntikkan via Environment Variable (`APP_ENV`, `DB_PASSWORD`) saat runtime container.

## 2. Incident Response Plan (SOP Penanganan Insiden)
Prosedur standar jika terjadi gangguan sistem (Downtime/Peretasan):

| Severity Level | Definisi | Respon Wajib |
| :--- | :--- | :--- |
| **SEV-1 (Critical)** | Sistem mati total, Data hilang. | Hubungi Tim DevOps segera (15 menit), Restore Backup. |
| **SEV-2 (High)** | Fitur lambat, Error sebagian. | Investigasi Log Aplikasi, Restart Container. |
| **SEV-3 (Low)** | Bug tampilan, Typo. | Fix saat jam kerja (Next Deploy). |

**Skenario Pemulihan (Disaster Recovery):**
1.  **Serangan DDoS/Traffic Spike:**
    * **Deteksi:** Grafana alert (CPU Usage > 90%).
    * **Aksi:** Scale up instance AWS atau aktifkan Cloudflare Under Attack Mode.
2.  **Database Corrupt/Terhapus:**
    * **Aksi:** Jalankan script restore dari folder `/backups`.
    * `docker exec -i service-db mysql -u root -p pustani_db < ./backups/latest_backup.sql`

## 3. Observability & Monitoring Dashboard
Infrastruktur dipantau secara real-time 24/7 menggunakan stack:
1.  **Prometheus:** Mengambil data metrik setiap 15 detik dari seluruh container.
2.  **cAdvisor:** Memantau penggunaan RAM dan CPU per-container.
3.  **Grafana:** Visualisasi data untuk admin.
    * *URL Dashboard:* `http://<IP-SERVER>:3000`
    * *Alerting:* Notifikasi otomatis jika RAM Usage > 80%.

## 4. CI/CD & Deployment Strategy
* **Zero-Downtime Attempt:** Pipeline menggunakan strategi `rsync` dan swap container cepat.
* **Automated Testing:** Setiap push ke `main` otomatis memicu build Docker image baru untuk memastikan tidak ada error syntax sebelum live.