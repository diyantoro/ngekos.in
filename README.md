<div align="center">

<img src="https://img.icons8.com/fluency/96/home.png" width="72" alt="Ngekos.in logo" />

# Ngekos.in

### Satu Aplikasi untuk Semua Urusan Kos —  Booking, Bayar, Pantau Kamar, sampai Check-out

<br />

[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Livewire](https://img.shields.io/badge/Livewire-3-4E56A6?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![Tailwind](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Maps](https://img.shields.io/badge/Maps-Leaflet_%2B_OSM-199900?style=for-the-badge&logo=openstreetmap&logoColor=white)](https://leafletjs.com)

<br />

[![Status](https://img.shields.io/badge/status-in%20development-yellow?style=flat-square)]()
[![License](https://img.shields.io/badge/license-MIT-blue?style=flat-square)]()
[![Made with](https://img.shields.io/badge/made%20with-%E2%98%95%20%26%20Laravel-orange?style=flat-square)]()

</div>

<br />

<p align="center">
  <img src="https://raw.githubusercontent.com/catppuccin/catppuccin/main/assets/palette/macchiato.png" width="0" height="0" alt="" />
</p>

> **Ngekos.in** menggantikan buku catatan, grup WhatsApp, dan spreadsheet kos dengan satu sistem digital yang rapi — dari calon penghuni cari kamar, booking, bayar sewa, sampai check-out, semuanya tercatat otomatis.

<br />

## 📚 Daftar Isi

- [Tentang Project](#-tentang-project)
- [Fitur Utama](#-fitur-utama)
- [Peta & Lokasi](#️-peta--lokasi)
- [Role Pengguna](#-role-pengguna)
- [Alur Fitur SPK per Peran](#-alur-fitur-spk-per-peran)
- [Tech Stack](#%EF%B8%8F-tech-stack)
- [Alur Data](#-alur-data)
- [Instalasi](#-instalasi--menjalankan-secara-lokal)
- [Menjalankan Full Stack dengan Docker](#-menjalankan-full-stack-dengan-docker)
- [Akun Demo](#-akun-demo)
- [Progress Pengembangan](#-progress-pengembangan)
- [Kontributor](#-kontributor)

<br />

## 🏡 Tentang Project

Mengelola kos secara manual itu ribet — kamar mana yang masih kosong sering nggak jelas, tagihan telat gak ketahuan, dan booking cuma modal chat WhatsApp yang gampang kelewat.

**Ngekos.in** hadir sebagai jembatan digital antara **Pemilik kos**, **Admin**, dan **Anak Kos** — satu platform, satu sumber kebenaran, tanpa drama.

<br />

## ✨ Fitur Utama

<table>
<tr>
<td width="50%" valign="top">

### 🛏️ Booking Kamar
Cari kamar kosong, ajukan booking online, batalkan sendiri kalau berubah pikiran — sebelum di-ACC Admin.

### 💸 Notifikasi Pembayaran
Reminder otomatis H-3, H-1, jatuh tempo, sampai telat. Siklus tagihan fleksibel: **harian** atau **bulanan**.

### 📊 Status Hunian Real-time
Kosong, dipesan, terisi, atau maintenance — semua ke-update otomatis, gak perlu dicatat manual.

</td>
<td width="50%" valign="top">

### 🔑 Check-in & Check-out
Serah terima kamar tercatat digital, lengkap validasi tagihan sebelum check-out kelar.

### ⏰ Denda Otomatis
Telat bayar? Sistem hitung dendanya sendiri sesuai aturan tiap properti.

### 🏘️ Multi-Properti
Punya beberapa kos di lokasi berbeda? Satu akun Pemilik cukup untuk kelola semuanya.

</td>
</tr>
<tr>
<td width="50%" valign="top">

### 🗺️ Peta Interaktif
Semua peta bisa digeser & zoom (Leaflet + OpenStreetMap, gratis tanpa API key). Pemilik menandai lokasi kos dengan geser pin, klik peta, cari alamat, lokasi saat ini, atau ketik koordinat manual.

### ⭐ Ulasan & Favorit
Anak kos memberi rating/ulasan dan menyimpan kos favorit — pencari kos dapat info jujur sebelum booking.

</td>
<td width="50%" valign="top">

### 🤖 Chatbot Bantuan
Asisten chat di dalam aplikasi untuk menjawab pertanyaan umum seputar cari kos & fitur.

### 📱 API Mobile
REST API (Sanctum) untuk aplikasi pendamping: autentikasi, katalog, booking, hingga notifikasi perangkat.

</td>
</tr>
</table>

<br />

## 🗺️ Peta & Lokasi

Seluruh peta memakai **Leaflet + OpenStreetMap** — interaktif (geser, zoom, klik marker) dan **gratis tanpa API key**. Variabel `GOOGLE_MAPS_API_KEY` di `.env` sifatnya **opsional**: jika diisi, peta memakai Google Maps; jika kosong, otomatis memakai Leaflet. Geocoding (cari alamat & alamat otomatis dari titik) memakai Nominatim OpenStreetMap.

| Halaman | Perilaku peta |
|:---|:---|
| **Form Tambah/Edit Kos (Pemilik)** | 5 cara menandai titik: **geser pin**, **klik peta**, **"Cari dari Alamat"**, **"Gunakan lokasi saya"**, dan kolom **Latitude/Longitude manual** + tombol **Tampilkan di Peta** |
| **Beranda** | Peta semua kos aktif, marker bisa diklik (popup nama + tautan detail) |
| **Cari Kos (Katalog)** | Peta hasil pencarian dengan popup per kos |
| **Detail Kos** | Peta satu titik + popup terbuka otomatis + tautan "Buka di Google Maps" |

> Jika koordinat properti belum diisi, titik diambil dari pusat kota (`App\Support\Koordinat`).

<br />

## 👥 Role Pengguna

<div align="center">

| 🛡️ Role | Deskripsi Singkat |
|:---|:---|
| **Super Admin** | Kontrol penuh seluruh Pemilik & konfigurasi sistem |
| **Admin** | Operasional harian properti yang ditugaskan Pemilik |
| **Pemilik** | Kelola properti, kamar, harga, admin & laporan |
| **Anak Kos** | Booking, pantau tagihan, upload bukti bayar |

</div>

<br />

## 🧭 Alur Fitur SPK per Peran

Fitur prediksi harga AI (JST/MLP) dan pemeringkatan kos (SPK TOPSIS) dipakai
berbeda oleh setiap peran:

| Peran | Perannya di fitur | Mulai dari |
|:---|:---|:---|
| **Anak Kos** | Mencari & memeringkat kos | Menu **Rekomendasi SPK** (`/rekomendasi-spk`) |
| **Pemilik** | Mengisi data kamar + memakai saran harga AI | **Kelola Kos → Kamar → Hitung Saran Harga** |
| **Admin** | Merawat data latih & model AI | **Data Latih** → **Model AI** → **Jadikan Aktif** |
| **Super Admin** | Sama seperti admin + mengatur peran pengguna | **Pengguna** & menu pengelolaan lainnya |

Alur lengkap, diagram data, matriks akses, dan penjelasan istilah ada di
**[docs/alur-fitur-spk.md](docs/alur-fitur-spk.md)**.

<br />

## 🛠️ Tech Stack

<div align="center">

<img src="https://skillicons.dev/icons?i=laravel,php,tailwind,mysql,html,css,js" />

| Layer | Teknologi |
|:---|:---|
| **Backend** | Laravel 13 (PHP 8.3+) |
| **Frontend** | Livewire 3 · Volt · Alpine.js · Tailwind CSS · Vite |
| **Database** | SQLite (default lokal) · MySQL 8 didukung |
| **Auth & Role** | Laravel Breeze · Sanctum (API) · Spatie Laravel-Permission |
| **Peta** | Leaflet + OpenStreetMap (gratis, tanpa API key) · Google Maps opsional via `GOOGLE_MAPS_API_KEY` |
| **Queue & Scheduler** | Laravel Queue · Task Scheduling |
| **Lainnya** | Chart.js · Cropper.js · DomPDF · PhpSpreadsheet · FCM Push |

</div>

<br />

## 🔄 Alur Data

```
   Users ──┬── Properti ──── Kamar ──┬── Booking ──── Penyewaan ──┬── Tagihan ──── Pembayaran
           │                          │                            │
           └── Admin_Properti         └── status: kosong/terisi    └── Notifikasi
```

<br />

## 🚀 Instalasi & Menjalankan Secara Lokal

<details>
<summary><b>Klik untuk lihat langkah instalasi lengkap</b></summary>

<br />

**Prasyarat:** PHP ≥ 8.3 · Composer · Node.js & NPM · (MySQL 8 opsional — default memakai SQLite tanpa setup tambahan)

```bash
# 1️⃣ Clone repository
git clone https://github.com/diyantoro/ngekos.in.git
cd ngekos.in

# 2️⃣ Install dependency PHP
composer install

# 3️⃣ Salin file environment & generate app key
cp .env.example .env
php artisan key:generate

# 4️⃣ (Opsional) Atur koneksi database di file .env
# Default: SQLite (langsung jalan). Untuk MySQL:
# DB_CONNECTION=mysql
# DB_DATABASE=ngekos_in
# DB_USERNAME=root
# DB_PASSWORD=

# 5️⃣ Symlink penyimpanan publik (untuk foto kos, bukti bayar, dsb.)
php artisan storage:link

# 6️⃣ Jalankan migration & seeder
php artisan migrate --seed

# 7️⃣ Build aset frontend
npm install
npm run build   # saat development: npm run dev

# 8️⃣ Jalankan server lokal
php artisan serve

# 9️⃣ (Opsional) Aktifkan layanan Model AI / JST
#    Opsi A — Docker (paling praktis, satu perintah):
cd ml-service
docker compose up -d --build
#    Opsi B — tanpa Docker:
#    .\run-lokal.bat    (Windows; PowerShell: powershell -ExecutionPolicy Bypass -File .\run-lokal.ps1)
#    ./run-lokal.sh     (Linux/macOS)
# Layanan berjalan di http://127.0.0.1:8001 (port 8000 dipakai Laravel).
```

Aplikasi berjalan di **http://127.0.0.1:8000** 🎉

> 🤖 **Layanan AI bersifat opsional.** Tanpa Docker dan tanpa Python, aplikasi
> tetap berjalan penuh memakai estimator harga terkalibrasi internal. Untuk
> inferensi JST/MLP asli dan pelatihan ulang model, jalankan `ml-service`
> dengan Docker Compose atau skrip lokal (lihat [`ml-service/README.md`](ml-service/README.md)).

> 🗺️ **Peta langsung jalan tanpa setup tambahan** (Leaflet + OpenStreetMap, gratis).
> Isi `GOOGLE_MAPS_API_KEY=` di `.env` hanya jika ingin memakai Google Maps (key browser, batasi via HTTP referrer).

</details>

<br />

## 🐳 Menjalankan Full Stack dengan Docker

Satu perintah untuk menjalankan **Laravel + Nginx + MySQL + Queue Worker +
Scheduler + ML Service** tanpa memasang PHP/MySQL/Node/Python di komputer.

**Prasyarat:** Docker Desktop (Windows/macOS) atau Docker Engine + Compose (Linux).

```bash
# Dari root project
docker compose up -d --build
```

Akses aplikasi di **http://localhost:8081** (ML service: `http://localhost:8001`). Port 8081 dipilih karena 8080 sering dipakai proyek lain; ubah lewat `DOCKER_APP_PORT` bila perlu.

Perintah penting:

```bash
docker compose ps                  # status semua container
docker compose logs -f app         # log Laravel / entrypoint
docker compose logs -f nginx       # log web server
docker compose exec app php artisan migrate:status
docker compose down                # hentikan (data tetap tersimpan)
docker compose down -v             # hentikan + hapus database/volume
```

Konfigurasi default container:

| Variabel | Default | Keterangan |
| --- | --- | --- |
| `DOCKER_APP_PORT` | `8081` | Port aplikasi di host |
| `DOCKER_DB_HOST_PORT` | `3307` | Port MySQL di host (internal tetap 3306) |
| `DOCKER_ML_HOST_PORT` | `8001` | Port ML service di host |
| `DOCKER_DB_DATABASE` | `ngekos` | Nama database |
| `DOCKER_DB_USERNAME` | `ngekos` | User database |
| `DOCKER_DB_PASSWORD` | `ngekos_secret` | Password database |
| `DOCKER_RUN_SEEDERS` | `true` | Isi data demo saat database masih kosong |

Nilai di atas bisa diubah lewat file `.env.docker`:

```bash
cp .env.docker.example .env.docker
docker compose --env-file .env.docker up -d --build
```

**Ganti `DOCKER_APP_KEY` dan password database untuk produksi.**

> ℹ️ Container `app` otomatis menunggu MySQL siap, menjalankan migrasi, dan
> mengisi seeder demo hanya saat tabel `users` masih kosong. Foto unggahan
> disimpan di volume `laravel-storage`, sedangkan data MySQL di volume
> `mysql-data`, sehingga aman saat container dibuat ulang.

<br />

## 🔑 Akun Demo

Tersedia setelah `php artisan migrate --seed` atau saat container Docker
pertama kali dijalankan.

| Peran | Email | Password |
| --- | --- | --- |
| Super Admin | `superadmin.ngekos@gmail.com` | `Superadmin.ngekos123` |
| Admin | `admin.ngekos@gmail.com` | `Admin.ngekos123` |
| Pemilik Kos | `pemilik1@ngekos.test` s.d. `pemilik4@ngekos.test` | `password` |
| Anak Kos | `anak1@ngekos.test` s.d. `anak7@ngekos.test` | `password` |

> Login harus lewat peran yang sesuai. Jika sudah masuk dengan akun lain,
> keluar (logout) dulu sebelum masuk sebagai peran berbeda.

<br />

## 📌 Progress Pengembangan

- [x] Perencanaan produk (PRD) & skema database
- [x] Setup project & autentikasi multi-role (Super Admin, Admin, Pemilik, Anak Kos)
- [x] Manajemen properti & kamar (foto, fasilitas, harga harian/mingguan/bulanan)
- [x] Peta interaktif semua halaman (Leaflet + OSM, tanpa API key) + input koordinat manual
- [x] Modul booking
- [x] Modul check-in & check-out
- [x] Modul tagihan, pembayaran (termasuk QRIS) & denda otomatis
- [x] Notifikasi otomatis (push/FCM + pengingat tagihan)
- [x] Dashboard per role + laporan & rekap
- [x] Ulasan, favorit, langganan PRO, chatbot, REST API mobile
- [x] Modul Prediksi Harga Wajar (JST/MLP) & Pemeringkatan Kos (SPK TOPSIS)
  - Layanan model FastAPI di folder [`ml-service/`](ml-service/README.md)
  - Saran harga wajar + label kewajaran pasar pada form kamar pemilik
  - Halaman publik **Rekomendasi SPK** (`/rekomendasi-spk`)
  - Dasbor admin **Model AI** (`/admin/model-ai`) & **Data Latih** (`/admin/data-latih`)
- [ ] Aplikasi mobile (Flutter) — API sudah tersedia

<br />

## 👤 Kontributor

<div align="center">

**Diyantoro**
Informatics Student · Universitas Teknologi Digital Indonesia (UTDI), Yogyakarta

<br />

⭐ Jangan lupa kasih star kalau project ini membantu!

<br />

Made with ☕ in Yogyakarta

</div>
