#### Langkah 2: Pertajam Dokumen SOP (`PRODUCTION_READY.md`)
Pastikan file `PRODUCTION_READY.md` kamu berisi **Incident Response Plan** yang terlihat seperti dokumen SOP perusahaan asli.

**Aksi:** Cek file `PRODUCTION_READY.md`. Jika isinya belum lengkap, ganti seluruh isinya dengan teks berikut:

```markdown
# Level 5: Production Ready Infrastructure Standards

## 1. Security Policy & Hardening
Implementasi keamanan berlapis (Defense in Depth) yang diterapkan pada PUSTANI:

* **Network Firewall:** Menggunakan AWS Security Group yang hanya membuka port 80 (HTTP) dan 22 (SSH) ke publik. Akses Database (Port 3306) dan Monitoring (Port 3000/9090) **DIBLOKIR** dari internet publik.
* **Application Firewall:** Nginx dikonfigurasi dengan header keamanan wajib:
    * `X-Frame-Options: SAMEORIGIN` (Mencegah serangan Clickjacking).
    * `X-XSS-Protection: 1; mode=block` (Mencegah injeksi script berbahaya).
* **Secrets Management:** Kredensial database tidak di-hardcode. Password disuntikkan melalui Environment Variable (`DB_PASSWORD`) saat container dijalankan.

## 2. Incident Response Plan (SOP Penanganan Insiden)
Prosedur standar operasional (SOP) jika terjadi gangguan sistem:

| Severity Level | Definisi Masalah | Respon Wajib (Action Plan) |
| :--- | :--- | :--- |
| **SEV-1 (Critical)** | Sistem mati total (Downtime), Data hilang. | 1. Hubungi Tim DevOps segera.<br>2. SSH ke Server.<br>3. Jalankan `docker-compose up -d`.<br>4. Jika DB rusak, restore dari folder `/backups`. |
| **SEV-2 (High)** | Fitur lambat, Error pada sebagian halaman. | 1. Cek Grafana Dashboard (CPU/RAM spike?).<br>2. Cek logs container: `docker logs pustani-app`.<br>3. Restart container spesifik. |
| **SEV-3 (Low)** | Bug tampilan, Typo, Fitur minor error. | 1. Catat di GitHub Issues.<br>2. Perbaiki di local environment.<br>3. Deploy fix pada rilis berikutnya. |

## 3. Disaster Recovery (Backup & Restore)
* **Backup:** Dilakukan otomatis menggunakan script `infra/backup_db.sh`.
* **Restore:** Jika database corrupt, jalankan perintah restore:
    ```bash
    cat ./backups/backup_terbaru.sql | docker exec -i pustani-db mysql -u root -p pustani_db
    ```

## 4. Observability Dashboard
Kesehatan infrastruktur dipantau 24/7 menggunakan:
* **Prometheus:** Mengumpulkan metrik penggunaan CPU, RAM, dan Network setiap 15 detik.
* **Grafana:** Visualisasi data dalam bentuk grafik. Admin dapat melihat load server secara real-time untuk keputusan scaling.