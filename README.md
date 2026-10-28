<div align="center">
  <img src="Tubes/public/img/img1/Logo.png" alt="Logo PUSTANI" width="120">
  <h1>PUSTANI</h1>
  <p><strong>Pusat Informasi Petani</strong></p>
  <p>Ruang berbagi pengetahuan pertanian yang membantu petani menemukan informasi, membaca artikel, dan terhubung dengan ahli tani.</p>

  <img src="https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white" alt="PHP 8.1+">
  <img src="https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white" alt="Docker Compose">
  <img src="https://img.shields.io/badge/Database-MariaDB-003545?logo=mariadb&logoColor=white" alt="MariaDB">
</div>

---

## Tentang PUSTANI

PUSTANI adalah aplikasi web informasi pertanian yang dibuat sebagai proyek mata kuliah Rekayasa Perangkat Lunak. Aplikasi ini menyediakan artikel pertanian dan fitur komunitas dengan alur verifikasi untuk konten serta ahli tani.

### Fitur utama

- Membaca, mencari, dan menjelajahi artikel pertanian.
- Membuat akun, mengelola profil, dan mengajukan verifikasi sebagai ahli tani.
- Mengirim artikel untuk ditinjau admin sebelum diterbitkan.
- Menyediakan area pengelolaan untuk admin.
- Menjalankan aplikasi, layanan informasi, database, dan monitoring menggunakan Docker Compose.

## Teknologi

| Bagian | Teknologi |
| --- | --- |
| Aplikasi web | PHP 8.1, Apache |
| Database | MariaDB 10.6 |
| Gateway | Nginx |
| Monitoring | Prometheus, Grafana, cAdvisor |
| Deployment | Docker Compose, GitHub Actions |

## Menjalankan secara lokal

### Prasyarat

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) atau Docker Engine dengan plugin Compose.
- Git untuk mengambil kode sumber.

### Langkah

```bash
git clone https://github.com/bilker29/Pustaka-Informasi-Petani-PUSTANI-.git
cd Pustaka-Informasi-Petani-PUSTANI-
docker compose up --build -d
```

Tunggu sampai container database siap, lalu buka:

- Aplikasi: [http://localhost](http://localhost)
- Info service: [http://localhost/info](http://localhost/info)
- Prometheus: [http://localhost:9090](http://localhost:9090)
- Grafana: [http://localhost:3000](http://localhost:3000)
- cAdvisor: [http://localhost:8080](http://localhost:8080)

Skema awal database dimuat dari `Tubes/database/pustani_db.sql` saat volume database pertama kali dibuat. Jika database tidak tersedia setelah container berjalan, periksa log dengan `docker compose logs pustani-db` dan pastikan port yang diperlukan tidak sedang digunakan.

### Perintah Docker yang berguna

```bash
docker compose ps
docker compose logs -f pustani-app
docker compose down
```

`docker compose down` mempertahankan data pada volume database. Jangan gunakan `docker compose down -v` kecuali memang ingin menghapus data lokal.

## Struktur proyek

```text
.
├── Tubes/                  # Aplikasi PHP, tampilan, dan skema database
├── info-service/           # Endpoint status layanan informasi
├── infra/                  # Konfigurasi Nginx, Prometheus, dan backup
├── .github/workflows/      # Otomasi deployment
└── docker-compose.yml      # Definisi layanan lokal
```

## Catatan keamanan

Konfigurasi kredensial di `docker-compose.yml` ditujukan untuk pengembangan lokal. Ganti kredensial contoh dan batasi akses jaringan ke database maupun dashboard monitoring sebelum deployment publik. Jangan memasukkan kredensial produksi atau file rahasia ke repositori.

## Kontribusi

Perubahan dan perbaikan dapat diajukan melalui issue atau pull request. Sertakan ringkasan perubahan dan langkah untuk menguji fitur yang ditambahkan.

---

<div align="center">
  Dibuat untuk mendukung pertanian Indonesia 🌱
</div>
