# Bug Fix: Fitur Pilihan Mingguan

## Tanggal: 2026-09-10

## Summary
Perbaikan bug pada fitur sewa mingguan untuk memastikan validasi harga dan periode sewa berjalan konsisten antara web (Livewire) dan API.

## Bug yang Diperbaiki

### 1. Logika Pengecekan Ketersediaan Harga Salah
**Lokasi**: `resources/views/livewire/pages/katalog/detail.blade.php:750`

**Before**:
```php
@php $tersedia = ! empty($opsi['harga']) && (float) $opsi['harga'] > 0; @endphp
```

**After**:
```php
@php $tersedia = $opsi['harga'] !== null && (float) $opsi['harga'] > 0; @endphp
```

**Reason**: `! empty("0.00")` mengembalikan `true`, sehingga harga 0 dianggap tersedia. Perbaikan memastikan hanya harga > 0 yang dianggap valid.

---

### 2. Validasi API Tidak Konsisten dengan Web
**Lokasi**: `app/Http/Controllers/Api/PenyewaanController.php`

**Before**:
```php
'durasi_bulan' => ['sometimes', 'integer', 'min:1', 'max:12'],
'durasi_minggu' => ['sometimes', 'integer', 'min:1', 'max:12'],
'durasi_hari' => ['sometimes', 'integer', 'min:1', 'max:90'],
```

**After**:
```php
'durasi_bulan' => ['required_if:periode,bulanan', 'integer', 'min:1', 'max:12'],
'durasi_minggu' => ['required_if:periode,mingguan', 'integer', 'min:1', 'max:12'],
'durasi_hari' => ['required_if:periode,harian', 'integer', 'min:1', 'max:90'],
```

**Reason**: Validasi `sometimes` tidak memastikan durasi wajib ada ketika periode tertentu dipilih. Perbaikan menggunakan `required_if` untuk memastikan durasi sesuai dengan periode.

**Tambahan**: Ditambahkan validasi untuk memastikan periode yang dipilih tersedia:
```php
$periodeTersedia = $kamar->periodeTersedia();
if (! in_array($periode, $periodeTersedia, true)) {
    return response()->json([
        'message' => 'Periode sewa '.$periode.' tidak tersedia untuk kamar ini.',
    ], 422);
}
```

---

### 3. Default Periode Bisa Invalid
**Lokasi**: `resources/views/livewire/pages/katalog/detail.blade.php:99`

**Before**:
```php
$this->periodeSewa = $kamar->periodeTersedia()[0] ?? 'bulanan';
```

**After**:
```php
$periodeTersedia = $kamar->periodeTersedia();
if (empty($periodeTersedia)) {
    $this->galat = 'Kamar ini belum menetapkan harga sewa.';
    $this->pesan = null;
    return;
}
$this->periodeSewa = $periodeTersedia[0];
```

**Reason**: Default ke `'bulanan'` bisa bermasalah jika `harga_sewa_bulanan` null/0. Perbaikan memastikan kamar memiliki minimal satu periode valid sebelum modal dibuka.

---

### 4. Validasi Periode Sewa di Web Kurang Ketat
**Lokasi**: `resources/views/livewire/pages/katalog/detail.blade.php:158-164`

**Before**:
```php
if ($kamar && $kamar->hargaUntuk($this->periodeSewa) === null) {
    $this->addError('periodeSewa', 'Periode ini tidak tersedia untuk kamar tersebut.');
    return;
}
```

**After**:
```php
$hargaPeriode = $kamar ? $kamar->hargaUntuk($this->periodeSewa) : null;
if ($kamar && ($hargaPeriode === null || $hargaPeriode <= 0)) {
    $this->addError('periodeSewa', 'Periode ini tidak tersedia untuk kamar tersebut.');
    return;
}
```

**Reason**: Hanya mengecek null tidak cukup, karena harga 0 juga harus dianggap tidak valid.

**Tambahan di konfirmasiSewa()**: Ditambahkan validasi `periodeTersedia()` sebelum memproses booking:
```php
$periodeTersedia = $kamar->periodeTersedia();
if (! in_array($this->periodeSewa, $periodeTersedia, true)) {
    $this->resetForm();
    $this->galat = 'Periode sewa yang dipilih tidak tersedia untuk kamar ini.';
    return;
}
```

---

### 5. Pengecekan Harga di Service Tidak Ketat
**Lokasi**: `app/Services/PenyewaanService.php:73-79`

**Before**:
```php
if ($isHarian && ! $kamarTerkunci->harga_sewa_harian) {
    throw new DomainException('Kamar ini belum menetapkan harga sewa harian.');
}

if ($isMingguan && ! $kamarTerkunci->harga_sewa_mingguan) {
    throw new DomainException('Kamar ini belum menetapkan harga sewa mingguan.');
}
```

**After**:
```php
if ($isHarian && (! $kamarTerkunci->harga_sewa_harian || (float) $kamarTerkunci->harga_sewa_harian <= 0)) {
    throw new DomainException('Kamar ini belum menetapkan harga sewa harian.');
}

if ($isMingguan && (! $kamarTerkunci->harga_sewa_mingguan || (float) $kamarTerkunci->harga_sewa_mingguan <= 0)) {
    throw new DomainException('Kamar ini belum menetapkan harga sewa mingguan.');
}
```

**Reason**: Pengecekan truthy `! $harga` tidak menangkap kasus harga = 0. Perbaikan memastikan harga harus > 0.

---

## File yang Dimodifikasi

1. `app/Http/Controllers/Api/PenyewaanController.php`
   - Ubah validasi `sometimes` menjadi `required_if`
   - Tambahkan validasi `periodeTersedia()`
   - Tambahkan pesan error yang lebih deskriptif

2. `resources/views/livewire/pages/katalog/detail.blade.php`
   - Perbaiki logika `$tersedia` pada tombol periode
   - Tambahkan validasi di `pesanKamar()` untuk kamar tanpa harga
   - Perbaiki validasi di `lanjutReview()` dengan cek `> 0`
   - Tambahkan validasi di `konfirmasiSewa()` menggunakan `periodeTersedia()`

3. `app/Services/PenyewaanService.php`
   - Tambahkan pengecekan `> 0` untuk harga mingguan dan harian

---

## Testing Checklist

- [x] Harga = 0: Periode tidak tersedia (button disabled)
- [x] Harga = null: Periode tidak tersedia (button disabled)
- [x] API tanpa durasi_minggu: Error 422 saat `periode=mingguan`
- [x] Web: Kamar tanpa harga apapun menampilkan error
- [x] Web: Validasi periode tidak tersedia di step review
- [x] Web: Validasi periode tidak tersedia di konfirmasi akhir
- [x] API: Validasi periode tidak tersedia sebelum booking
- [x] Service: DomainException untuk harga <= 0

---

## Notes

- Semua validasi sekarang konsisten antara Web (Livewire) dan API
- Method `Kamar::periodeTersedia()` digunakan sebagai single source of truth
- Validasi berlapis: UI (button disabled) → Form validation → Business logic (Service)
- Error messages jelas dan user-friendly

---

## Commands untuk Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```
