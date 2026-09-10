<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BantuanController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\KatalogController;
use App\Http\Controllers\Api\PengaturanController;
use App\Http\Controllers\Api\PengeluaranController;
use App\Http\Controllers\Api\PenggunaController;
use App\Http\Controllers\Api\PenyewaanController;
use App\Http\Controllers\Api\PropertiManageController;
use App\Http\Controllers\Api\StorageProxyController;
use Illuminate\Support\Facades\Route;

// Storage proxy (serve public files via API with CORS headers for Flutter web)
Route::get('/storage/{path}', StorageProxyController::class)
    ->where('path', '.*');

// Public routes (dibatasi rate untuk mencegah brute-force & spam)
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
Route::post('/reset-password/{token}', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');
Route::post('/forgot-password/otp', [AuthController::class, 'forgotPasswordOtp'])->middleware('throttle:5,1');
Route::post('/forgot-password/verify', [AuthController::class, 'verifyPasswordOtp'])->middleware('throttle:5,1');

// Public katalog
Route::get('/kos', [KatalogController::class, 'index']);
Route::get('/kos/{properti}', [KatalogController::class, 'show']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Profile
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::post('/user/delete', [AuthController::class, 'deleteAccount']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail']);

    // Push notification device token
    Route::post('/device-token', [DeviceTokenController::class, 'store']);
    Route::delete('/device-token', [DeviceTokenController::class, 'destroy']);

    // Chat
    Route::get('/chat', [ChatController::class, 'index']);
    Route::get('/chat/{propertiId}', [ChatController::class, 'show']);
    Route::post('/chat/{propertiId}', [ChatController::class, 'send']);

    // Sewa kamar langsung (tanpa menunggu konfirmasi)
    Route::post('/kos/{propertiId}/kamar/{kamarId}/sewa', [PenyewaanController::class, 'sewaKamar']);

    // Patungan: tambah teman sekamar & keluar partial
    Route::post('/penyewaan/{sewaanId}/anggota', [DashboardController::class, 'tambahAnggota']);
    Route::post('/penyewaan/{sewaanId}/anggota/keluar', [DashboardController::class, 'keluarAnggota']);

    // Dashboard - Anak Kos
    Route::get('/dashboard/anak-kos', [DashboardController::class, 'anakKos']);
    Route::get('/dashboard/anak-kos/penyewaan', [DashboardController::class, 'anakKosPenyewaan']);
    Route::get('/dashboard/anak-kos/tagihan', [DashboardController::class, 'anakKosTagihan']);
    Route::get('/dashboard/anak-kos/pembayaran', [DashboardController::class, 'anakKosPembayaran']);
    Route::get('/dashboard/anak-kos/pembayaran/{pembayaranId}/kwitansi', [DashboardController::class, 'kwitansiSaya']);
    Route::post('/dashboard/anak-kos/bayar', [DashboardController::class, 'anakKosBayar']);
    Route::post('/dashboard/anak-kos/{sewaanId}/keluar', [DashboardController::class, 'anakKosAjukanKeluar']);

    // Dashboard - Pemilik
    Route::get('/dashboard/pemilik', [DashboardController::class, 'pemilik']);
    Route::get('/dashboard/pemilik/grafik', [DashboardController::class, 'grafik'])
        ->middleware('role:pemilik|admin|super_admin');
    Route::get('/dashboard/pemilik/properti', [DashboardController::class, 'pemilikProperti']);
    Route::get('/dashboard/pemilik/sewaan', [DashboardController::class, 'pemilikSewaans']);
    Route::get('/dashboard/pemilik/sewaan/{sewaanId}/ktp', [DashboardController::class, 'ktpPenyewaan'])
        ->middleware('role:pemilik|admin|super_admin');
    Route::get('/dashboard/pemilik/rekap', [DashboardController::class, 'rekap']);
    Route::post('/dashboard/pemilik/sewaan/{sewaanId}/checkout', [DashboardController::class, 'pemilikCheckOut']);
    Route::post('/dashboard/pemilik/pembayaran/{pembayaranId}/verifikasi', [DashboardController::class, 'verifikasiPembayaran'])
        ->middleware('role:pemilik|admin|super_admin');

    // Dashboard - Admin & Super Admin
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])
        ->middleware('role:admin|super_admin');
    Route::get('/dashboard/super-admin', [DashboardController::class, 'superAdmin'])
        ->middleware('role:super_admin');
    Route::post('/dashboard/super-admin/pembayaran/{pembayaranId}/verifikasi', [DashboardController::class, 'verifikasiPembayaran'])
        ->middleware('role:admin|super_admin');

    // Pengaturan
    Route::get('/pengaturan', [PengaturanController::class, 'index']);
    Route::post('/pengaturan/situs', [PengaturanController::class, 'simpanSitus']);
    Route::post('/pengaturan/kos', [PengaturanController::class, 'simpanKos']);
    Route::post('/pengaturan/notifikasi', [PengaturanController::class, 'simpanNotifikasi']);
    Route::post('/pengaturan/ganti-password', [PengaturanController::class, 'gantiPassword']);

    // Bantuan
    Route::post('/bantuan', [BantuanController::class, 'kirim']);
    Route::get('/bantuan/riwayat', [BantuanController::class, 'riwayat']);
    Route::get('/bantuan/masuk', [BantuanController::class, 'masuk'])
        ->middleware('role:super_admin|admin');
    Route::post('/bantuan/{id}/balas', [BantuanController::class, 'balas'])
        ->middleware('role:super_admin|admin');
    Route::post('/bantuan/{id}/baca', [BantuanController::class, 'tandaiDibaca'])
        ->middleware('role:super_admin|admin');

    // Pengguna (super admin)
    Route::get('/pengguna', [PenggunaController::class, 'index'])
        ->middleware('role:super_admin');
    Route::put('/pengguna/{id}', [PenggunaController::class, 'update'])
        ->middleware('role:super_admin');
    Route::post('/pengguna/{id}', [PenggunaController::class, 'toggleStatus'])
        ->middleware('role:super_admin');

    // Kelola properti & kamar (pemilik / admin / super admin)
    Route::get('/pemilik/properti', [PropertiManageController::class, 'index'])
        ->middleware('role:pemilik|admin|super_admin');
    Route::post('/pemilik/properti', [PropertiManageController::class, 'store'])
        ->middleware('role:pemilik|admin|super_admin');
    Route::post('/pemilik/properti/{id}', [PropertiManageController::class, 'handleProperti'])
        ->middleware('role:pemilik|admin|super_admin');
    Route::put('/pemilik/properti/{id}', [PropertiManageController::class, 'update'])
        ->middleware('role:pemilik|admin|super_admin');
    Route::delete('/pemilik/properti/{id}', [PropertiManageController::class, 'destroy'])
        ->middleware('role:pemilik|admin|super_admin');
    Route::get('/pemilik/properti/{propertiId}/kamar', [PropertiManageController::class, 'indexKamar'])
        ->middleware('role:pemilik|admin|super_admin');
    Route::post('/pemilik/properti/{propertiId}/kamar', [PropertiManageController::class, 'storeKamar'])
        ->middleware('role:pemilik|admin|super_admin');
    Route::post('/pemilik/properti/{propertiId}/kamar/{kamarId}', [PropertiManageController::class, 'handleKamar'])
        ->middleware('role:pemilik|admin|super_admin');
    Route::put('/pemilik/properti/{propertiId}/kamar/{kamarId}', [PropertiManageController::class, 'updateKamar'])
        ->middleware('role:pemilik|admin|super_admin');
    Route::delete('/pemilik/properti/{propertiId}/kamar/{kamarId}', [PropertiManageController::class, 'destroyKamar'])
        ->middleware('role:pemilik|admin|super_admin');

    // Pengeluaran operasional kos (pemilik / admin / super admin)
    Route::get('/pemilik/pengeluaran', [PengeluaranController::class, 'index'])
        ->middleware('role:pemilik|admin|super_admin');
    Route::post('/pemilik/pengeluaran', [PengeluaranController::class, 'store'])
        ->middleware('role:pemilik|admin|super_admin');
    Route::post('/pemilik/pengeluaran/{id}', [PengeluaranController::class, 'handlePengeluaran'])
        ->middleware('role:pemilik|admin|super_admin');
    Route::put('/pemilik/pengeluaran/{id}', [PengeluaranController::class, 'update'])
        ->middleware('role:pemilik|admin|super_admin');
    Route::delete('/pemilik/pengeluaran/{id}', [PengeluaranController::class, 'destroy'])
        ->middleware('role:pemilik|admin|super_admin');
});
