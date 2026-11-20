# Alur Fitur Prediksi Harga AI & SPK per Peran

Dokumen ini menjelaskan **siapa mengerjakan apa** pada fitur Prediksi Harga Wajar (JST/MLP)
dan Sistem Pendukung Keputusan (SPK TOPSIS), agar tidak tertukar antar peran.

## Ringkasan

| Peran | Posisi di fitur | Tugas utama |
| --- | --- | --- |
| **Pemilik Kos** | Pemasok data + pemakai saran harga | Mengisi data kamar & memakai AI untuk menentukan harga sewa |
| **Anak Kos** | Pemakai hasil | Mencari dan memeringkat kos sesuai preferensi |
| **Admin** | Operator model | Merawat data latih dan model AI yang dipakai SPK |
| **Super Admin** | Operator + pemegang kebijakan | Sama seperti admin, ditambah mengatur peran pengguna |

## Alur Data End-to-End

```
Pemilik mengisi kos/kamar
(harga, luas, fasilitas, lokasi, tipe hunian)
              │
              ▼
        Data Latih  ◄── sinkron dari kamar / input survei manual
              │
              ▼
   Admin melatih model JST (MLP)
              │
              ▼
     Model aktif (versi terpilih)
              │
              ▼
   Prediksi harga wajar  ──►  Kriteria C2 pada SPK
              │
              ▼
Anak kos membuka /rekomendasi-spk
(pilih kampus, budget, prioritas)
              │
              ▼
 TOPSIS menggabungkan 5 kriteria
              │
              ▼
Peringkat + % kecocokan + alasan + uji sensitivitas
```

## 1. Anak Kos — pengguna utama SPK

**Tujuan:** menemukan kos paling cocok tanpa menghitung manual.

**Langkah:**

1. Buka menu **Rekomendasi SPK** (`/rekomendasi-spk`). Bisa diakses tanpa login.
2. **Langkah 1 — Lokasi & Budget:** ketik nama kampus/titik acuan (saran otomatis
   muncul agar tidak salah ketik), lalu atur budget maksimal, tipe hunian
   (putra/putri/campur), dan fasilitas wajib.
3. **Langkah 2 — Prioritas:** pilih preset **Hemat / Seimbang / Lengkap**, atau buka
   "Atur bobot sendiri" untuk mengatur 5 kriteria secara detail. Hasil langsung
   diperbarui saat preset diubah.
4. **Langkah 3 — Cari Kos Terbaik:** rekomendasi awal sudah tampil saat halaman dibuka.
   Tekan tombol ini setelah mengubah budget, tipe, atau fasilitas untuk memperbarui hasil.
5. **Membaca hasil:**
   - persentase kecocokan tiap kos,
   - harga wajar menurut AI beserta label *lebih murah / wajar / lebih mahal dari pasar*,
   - alasan "kenapa cocok",
   - skor relatif 5 kriteria (0–100),
   - panel "Seberapa yakin rekomendasi ini?" (uji sensitivitas bobot ±25%).
6. **Opsional (perlu login):** simpan profil preferensi dengan nama, lalu muat kembali
   di kunjungan berikutnya. Tanpa login pencarian tetap berjalan, tetapi tidak tersimpan.
7. Setiap pencarian dicatat sebagai sesi (top 30 hasil) untuk evaluasi sistem.

**Batasan:** anak kos tidak dapat mengubah data kos maupun model AI.

## 2. Pemilik Kos — pemasok data + asisten harga

**Tujuan:** menentukan harga sewa yang wajar dan memastikan kamarnya layak masuk peringkat SPK.

**Langkah:**

1. Buka menu **Kelola Kos** → pilih properti → **Kamar**.
2. Saat menambah atau mengubah kamar, lengkapi luas, fasilitas, tipe hunian, dan lokasi.
3. Klik **Hitung Saran Harga**. AI menampilkan estimasi harga pasar dan label:
   - *Di Bawah Pasar (Murah)*,
   - *Wajar Pasar*,
   - *Di Atas Pasar (Tinggi)*.
4. Angka estimasi dapat langsung dipakai sebagai harga sewa.
5. Estimasi tersimpan dan tampil di daftar kamar sebagai **"Est. Pasar"**, sehingga
   bisa dipantau tanpa menghitung ulang.
6. Data kamar inilah yang dipakai sistem saat anak kos mencari kos. Semakin lengkap
   datanya, semakin akurat peringkat yang dihasilkan.

**Catatan:** menu SPK disembunyikan untuk pemilik karena posisinya bukan pencari kos.
Namun URL `/rekomendasi-spk` tetap terbuka untuk siapa pun.

## 3. Admin — operator model & data

**Tujuan:** menjaga AI tetap akurat dan tersedia, karena SPK bergantung pada prediksi harga.

### Alur A — Data Latih (`/admin/data-latih`)

1. Lihat daftar data latih, cari, dan filter berdasarkan sumber.
2. Tambah data survei manual (luas, jarak, kawasan, tipe, fasilitas, harga aktual).
3. Gunakan **Sinkron dari Kamar Aktif** untuk menarik data kamar yang sudah ada.
4. Tandai data **outlier** agar tidak dipakai saat training, atau hapus data yang salah.

### Alur B — Model AI (`/admin/model-ai`)

1. Cek status layanan: **Cloud ML aktif** atau **Mode Fallback Internal**.
2. Lihat metrik model aktif: MAE, RMSE, R², dan MAPE (target MAPE ≤ 15%).
3. Klik **Latih Ulang Model Sekarang** (minimal 5 data non-outlier).
   Sistem membuat versi baru beserta metrik evaluasinya.
4. Pilih versi dengan metrik terbaik pada **Riwayat Versi Model**, lalu klik
   **Jadikan Aktif**.
5. Model aktif otomatis dipakai untuk prediksi harga pada SPK dan saran harga pemilik.

**Penting:** model JST (MLP) baru berjalan penuh setelah tersedia **minimal 30 data
non-outlier**. Di bawah jumlah itu, sistem memakai estimator pasar terkalibrasi agar
hasil tetap stabil dan tidak overfit.

**Akses tambahan:** admin juga dapat membuka halaman SPK untuk memverifikasi hasil, dan
boleh mengelola properti/kamar seluruh pemilik untuk merapikan data.

## 4. Super Admin — sama seperti admin, plus kendali peran

Untuk fitur AI/SPK, kemampuan Super Admin **identik dengan Admin**: halaman
`/admin/model-ai` dan `/admin/data-latih` memakai middleware yang sama
(`role:super_admin|admin`) tanpa fitur tambahan di dalamnya.

Pembeda Super Admin ada di lapisan kebijakan:

1. **Kelola Pengguna & Peran** (`/pengguna`) — menentukan siapa yang menjadi admin,
   pemilik, atau anak kos. Ini yang mengatur siapa boleh masuk ke alur mana.
2. **Landing** (`/landing/kelola`) — mengatur halaman depan, termasuk promosi fitur.
3. **Pasar Kos** (`/pasar-kos/kelola`) — kurasi akun penjual.
4. **Langganan** (`/langganan/kelola`) — aktivasi paket pemilik.

Jadi perbedaan Admin vs Super Admin bukan pada cara memakai SPK, melainkan pada
kewenangan mengatur orang dan konten.

## Matriks Akses

| Bagian | Tamu | Anak Kos | Pemilik | Admin | Super Admin |
| --- | --- | --- | --- | --- | --- |
| `/rekomendasi-spk` | Ya | Ya | Ya (via URL) | Ya | Ya |
| Menu "Rekomendasi SPK" | – | Ya | Tidak | Ya | Ya |
| Simpan profil preferensi | Tidak | Ya | Ya | Ya | Ya |
| Saran harga AI di kamar | Tidak | Tidak | Ya | Ya* | Ya* |
| `/admin/data-latih` | Tidak | Tidak | Tidak | Ya | Ya |
| `/admin/model-ai` | Tidak | Tidak | Tidak | Ya | Ya |
| Kelola pengguna & peran | Tidak | Tidak | Tidak | Tidak | Ya |

\* Admin/Super Admin dapat membuka halaman kamar karena boleh mengelola properti semua pemilik.

## Istilah Singkat

| Istilah | Arti |
| --- | --- |
| **TOPSIS** | Metode pemeringkatan yang memilih alternatif paling dekat ke solusi ideal. |
| **C1** | Harga sewa (cost — makin murah makin baik). |
| **C2** | Kewajaran harga menurut AI (benefit — makin wajar/murah dari pasar makin baik). |
| **C3** | Jarak ke kampus (cost — makin dekat makin baik). |
| **C4** | Kelengkapan fasilitas (benefit). |
| **C5** | Luas kamar (benefit). |
| **MAE** | Rata-rata selisih harga prediksi dengan harga asli (makin kecil makin baik). |
| **RMSE** | Seperti MAE, tetapi lebih menghukum kesalahan prediksi yang besar. |
| **R²** | Seberapa besar variasi harga yang bisa dijelaskan model (0–1). |
| **MAPE** | Rata-rata galat dalam persen; target PRD ≤ 15%. |
| **Fallback** | Mode cadangan memakai estimator internal saat layanan ML tidak tersedia. |

## Pertanyaan Umum

**Apakah anak kos harus login untuk memakai SPK?**
Tidak. Login hanya diperlukan untuk menyimpan profil preferensi.

**Kenapa pemilik tidak melihat menu SPK?**
Karena posisinya penyedia kos, bukan pencari. Pemilik memakai AI untuk saran harga kamar.

**Apakah admin bisa memakai SPK?**
Bisa, untuk memverifikasi hasil dan kualitas data.

**Kapan model MLP benar-benar dipakai?**
Setelah ada minimal 30 data latih non-outlier dan model aktif tersedia.
