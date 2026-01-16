# Analisis Cost & Benefit - Infrastruktur Cloud PUSTANI

## 1. Perbandingan Biaya (Cost Analysis)
Analisis ini membandingkan estimasi biaya infrastruktur PUSTANI menggunakan Cloud (AWS) vs On-Premise (Server Fisik Sendiri) untuk durasi 1 tahun.

| Komponen Biaya | Opsi A: Cloud (AWS EC2 t3.micro) | Opsi B: On-Premise (Server Kantor) |
| :--- | :--- | :--- |
| **Investasi Awal (CAPEX)** | **$0** (Gratis Setup) | **Rp 8.500.000** (Beli Server Mini PC) |
| **Sewa Server (OPEX)** | ~$10/bln x 12 = Rp 1.800.000 | Rp 0 |
| **Listrik & Internet** | Termasuk biaya sewa | ~Rp 300.000/bln x 12 = Rp 3.600.000 |
| **Maintenance Hardware** | Ditanggung AWS (0 Biaya) | Rp 1.000.000 (Estimasi kerusakan/part) |
| **TOTAL TAHUN PERTAMA** | **Rp 1.800.000** | **Rp 13.100.000** |

## 2. Analisis Manfaat (Benefit Analysis)
Mengapa memilih Cloud (AWS) untuk proyek PUSTANI?
1.  **Cost Efficiency:** Hemat biaya >80% di tahun pertama karena tidak perlu membeli hardware mahal di depan.
2.  **Scalability:** Jika trafik petani meningkat saat musim panen, kapasitas server bisa dinaikkan (Upgrade Instance) hanya dalam hitungan menit tanpa beli alat baru.
3.  **Reliability:** Cloud Provider menjamin ketersediaan listrik dan jaringan (Uptime SLA) yang jauh lebih stabil dibandingkan listrik rumahan/kantor.
4.  **Security:** Keamanan fisik data center dijamin oleh AWS, kita hanya fokus mengamankan aplikasi.

## 3. Kesimpulan
Berdasarkan analisis di atas, deployment ke **Cloud Environment** adalah pilihan terbaik untuk PUSTANI karena biaya awal yang rendah dan fleksibilitas yang tinggi.