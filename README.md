<div align="center">

<img src="https://img.icons8.com/fluency/96/home.png" width="72" alt="Ngekos.in logo" />

# Ngekos.in

### Satu Aplikasi untuk Semua Urusan Kos —  Booking, Bayar, Pantau Kamar, sampai Check-out

<br />

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Livewire](https://img.shields.io/badge/Livewire-3-4E56A6?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![Tailwind](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)

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
- [Role Pengguna](#-role-pengguna)
- [Tech Stack](#%EF%B8%8F-tech-stack)
- [Alur Data](#-alur-data)
- [Instalasi](#-instalasi--menjalankan-secara-lokal)
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
</table>

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

## 🛠️ Tech Stack

<div align="center">

<img src="https://skillicons.dev/icons?i=laravel,php,tailwind,mysql,html,css,js" />

| Layer | Teknologi |
|:---|:---|
| **Backend** | Laravel 11 (PHP 8.3+) |
| **Frontend** | Livewire 3 · Alpine.js · Tailwind CSS |
| **Database** | MySQL 8 |
| **Auth & Role** | Laravel Breeze · Spatie Laravel-Permission |
| **Queue & Scheduler** | Laravel Queue · Task Scheduling |

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

**Prasyarat:** PHP ≥ 8.3 · Composer · Node.js & NPM · MySQL 8

```bash
# 1️⃣ Clone repository
git clone https://github.com/diyantoro/ngekos.in.git
cd ngekos.in

# 2️⃣ Install dependency PHP
composer install

# 3️⃣ Salin file environment & generate app key
cp .env.example .env
php artisan key:generate

# 4️⃣ Atur koneksi database di file .env
DB_DATABASE=ngekos_in
DB_USERNAME=root
DB_PASSWORD=

# 5️⃣ Jalankan migration & seeder
php artisan migrate --seed

# 6️⃣ Install dependency frontend
npm install
npm run dev

# 7️⃣ Jalankan server lokal
php artisan serve
```

Aplikasi berjalan di **http://127.0.0.1:8000** 🎉

</details>

<br />

## 📌 Progress Pengembangan

- [x] Perencanaan produk (PRD) & skema database
- [ ] Setup project & autentikasi multi-role
- [ ] Manajemen properti & kamar
- [ ] Modul booking
- [ ] Modul check-in & check-out
- [ ] Modul tagihan, pembayaran & denda otomatis
- [ ] Notifikasi otomatis
- [ ] Dashboard per role

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
