<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KtpPenyewaanController;
use App\Http\Controllers\KwitansiController;
use App\Http\Controllers\PemilikLaporanPremiumController;
use App\Http\Controllers\PemilikRekapExportController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Beranda publik: halaman pembuka (landing).
Volt::route('/', 'pages.beranda')->name('home');

// Katalog kos (publik).
Volt::route('kos', 'pages.katalog.kos')->name('kos.index');

// Detail kos (publik).
Volt::route('kos/{properti}', 'pages.katalog.detail')->name('kos.detail');

// Pusat bantuan: chatbot & hubungi admin.
Volt::route('bantuan', 'pages.bantuan.index')->name('bantuan');
Volt::route('bantuan/riwayat', 'pages.bantuan.riwayat')
    ->middleware('auth')
    ->name('bantuan.riwayat');
Volt::route('bantuan/masuk', 'pages.bantuan.masuk')
    ->middleware(['auth', 'verified', 'role:super_admin|admin'])
    ->name('bantuan.masuk');

// Chat penyewa <-> pemilik kos.
Volt::route('chat', 'pages.chat.index')
    ->middleware('auth')
    ->name('chat.index');
Volt::route('chat/{properti}', 'pages.chat.room')
    ->middleware('auth')
    ->name('chat.room');
Volt::route('chat/{properti}/anak-kos/{anakKos}', 'pages.chat.room')
    ->middleware('auth')
    ->name('chat.room.anak');

// Kelola pengguna & peran (khusus super admin).
Volt::route('pengguna', 'pages.super-admin.pengguna')
    ->middleware(['auth', 'verified', 'role:super_admin'])
    ->name('pengguna');

// Langganan premium (aktivasi manual oleh admin).
Volt::route('langganan', 'pages.langganan.subscription')
    ->middleware(['auth', 'verified'])
    ->name('langganan.subscription');
Volt::route('langganan/paket', 'pages.langganan.plans')
    ->middleware(['auth', 'verified'])
    ->name('langganan.plans');
Volt::route('langganan/bayar/{plan}', 'pages.langganan.bayar')
    ->middleware(['auth', 'verified'])
    ->name('langganan.bayar');
Volt::route('langganan/kelola', 'pages.super-admin.subscriptions')
    ->middleware(['auth', 'verified', 'role:super_admin'])
    ->name('langganan.kelola');

// Kelola kos (pemilik pemiliknya sendiri; admin/super admin mengelola semuanya).
Volt::route('pemilik/properti', 'pages.pemilik.properti')
    ->middleware(['auth', 'verified', 'role:pemilik|admin|super_admin'])
    ->name('pemilik.properti');
Volt::route('pemilik/properti/buat', 'pages.pemilik.properti-form')
    ->middleware(['auth', 'verified', 'role:pemilik|admin|super_admin'])
    ->name('pemilik.properti.buat');
Volt::route('pemilik/properti/{properti}/ubah', 'pages.pemilik.properti-form')
    ->middleware(['auth', 'verified', 'role:pemilik|admin|super_admin'])
    ->name('pemilik.properti.ubah');
Volt::route('pemilik/properti/{properti}/kamar', 'pages.pemilik.kamar')
    ->middleware(['auth', 'verified', 'role:pemilik|admin|super_admin'])
    ->name('pemilik.kamar');
Volt::route('pemilik/pengeluaran', 'pages.pemilik.pengeluaran')
    ->middleware(['auth', 'verified', 'role:pemilik|admin|super_admin'])
    ->name('pemilik.pengeluaran');
Volt::route('pemilik/grafik', 'pages.pemilik.grafik')
    ->middleware(['auth', 'verified', 'role:pemilik|admin|super_admin'])
    ->name('pemilik.grafik');

// Laporan premium (fitur PRO/BUSINESS: advanced_report untuk halaman,
// export_report untuk mengunduh PDF/Excel). Enforcement server-side.
Volt::route('pemilik/laporan', 'pages.pemilik.laporan-premium')
    ->middleware(['auth', 'verified', 'role:pemilik|admin|super_admin'])
    ->name('pemilik.laporan');

// Ekspor laporan premium wajib anggota paket dengan fitur export_report.
Route::middleware(['auth', 'verified', 'role:pemilik|admin|super_admin'])->group(function () {
    Route::get('pemilik/laporan/pdf', [PemilikLaporanPremiumController::class, 'pdf'])
        ->middleware('premium:export_report')
        ->name('pemilik.laporan.pdf');
    Route::get('pemilik/laporan/excel', [PemilikLaporanPremiumController::class, 'excel'])
        ->middleware('premium:export_report')
        ->name('pemilik.laporan.excel');
});

// Ekspor rekap bulanan pemilik: PDF & Excel boleh semua paket (free/trial ada watermark).
Route::middleware(['auth', 'verified', 'role:pemilik|admin|super_admin'])->group(function () {
    Route::get('pemilik/rekap/pdf', [PemilikRekapExportController::class, 'pdf'])
        ->name('pemilik.rekap.pdf');
    Route::get('pemilik/rekap/excel', [PemilikRekapExportController::class, 'excel'])
        ->name('pemilik.rekap.excel');
});

// Unduh kwitansi pembayaran terverifikasi + lihat KTP penyewa.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('pembayaran/{pembayaran}/kwitansi', [KwitansiController::class, 'unduh'])
        ->name('pembayaran.kwitansi');
    Route::get('penyewaan/{sewaan}/ktp', [KtpPenyewaanController::class, 'lihat'])
        ->name('penyewaan.ktp');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Entry point: arahkan ke dashboard sesuai role.
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::view('dashboard/super-admin', 'dashboard.super-admin')
        ->middleware('role:super_admin')
        ->name('dashboard.super-admin');
    Route::view('dashboard/pemilik', 'dashboard.pemilik')
        ->middleware('role:pemilik')
        ->name('dashboard.pemilik');
    Route::view('dashboard/admin', 'dashboard.admin')
        ->middleware('role:admin|super_admin')
        ->name('dashboard.admin');
    Route::view('dashboard/anak-kos', 'dashboard.anak-kos')
        ->middleware('role:anak_kos')
        ->name('dashboard.anak-kos');
});

// Pengaturan aplikasi: situs & kos (super admin), profil, notifikasi (semua role).
Volt::route('pengaturan', 'pages.pengaturan')
    ->middleware(['auth', 'verified'])
    ->name('pengaturan');

// Daftar kos favorit (khusus anak kos).
Volt::route('favorit', 'pages.favorit')
    ->middleware(['auth', 'verified', 'role:anak_kos'])
    ->name('favorit');

// Halaman profile lama diarahkan ke pengaturan agar tautan lama tetap jalan.
Route::redirect('profile', '/pengaturan')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
