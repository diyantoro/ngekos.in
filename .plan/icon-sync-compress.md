# Plan: Selaraskan Ikon Mobile-Web & Kompres PNG

## Temuan

### Masalah Utama
1. **Web icons = default Flutter** (logo hijau Flutter), bukan branding Ngekos.in (teal `#0D9488`)
2. **Android/iOS icons = default Flutter** juga, tidak ada custom branding
3. **Web manifest salah**: `background_color: "#0175C2"` (biru Flutter), `description: "A new Flutter project."`, `name: "ngekos_in"` (pakai underscore)
4. **Web index.html**: `<title>ngekos_in</title>`, description generic
5. **`Icon-maskable-512.png` = 20.5KB** — terlalu besar untuk icon
6. **`tamu.png` = 96x96** sedangkan semua facility icon lain = 58x58 — inkonsisten
7. **Tidak ada adaptive icon** di Android (hanya mipmap legacy)

### Ukuran & Kondisi Saat Ini

| File | Dimensi | Size | Catatan |
|------|---------|------|---------|
| **Facility Icons** (26 files) | 58x58 (1 file 96x96) | 0.4-2.4KB | `tamu.png` beda ukuran |
| **SVG Auth** (2 files) | vector | 11.9-15.9KB | Sudah OK |
| **Web favicon.png** | 16x16 | 0.9KB | Default Flutter |
| **Web Icon-192.png** | 192x192 | 5.2KB | Default Flutter |
| **Web Icon-512.png** | 512x512 | 8.1KB | Default Flutter |
| **Web Icon-maskable-192.png** | 192x192 | 5.5KB | Default Flutter |
| **Web Icon-maskable-512.png** | 512x512 | 20.5KB | Default Flutter, OVERSIZED |
| **Android mipmap** (5 files) | 48-192 | 1.7-5.8KB | Default Flutter |
| **iOS AppIcon** (15 files) | 20-1024 | 0.3-10.7KB | Default Flutter |

## Rencana Eksekusi

### Langkah 1: Kompres Semua PNG yang Ada
- Jalankan `sharp-cli` dengan optimasi lossless pada semua PNG di:
  - `assets/images/*.png` (26 files)
  - `web/icons/*.png` + `web/favicon.png` (5 files)
  - `android/app/src/main/res/mipmap-*/ic_launcher.png` (5 files)
  - `ios/Runner/Assets.xcassets/AppIcon.appiconset/*.png` (15 files)
- Flags: `--compressionLevel 9 --adaptiveFiltering`
- Target: kurangi ukuran ~20-40% tanpa loss kualitas

### Langkah 2: Perbaiki `tamu.png` Inkonsisten
- Resize `tamu.png` dari 96x96 → 58x58 (samakan dengan sibling icons)
- Agar rendering konsisten di `facility_icon.dart` dan `properti_form_screen.dart`

### Langkah 3: Generate Custom Branded Icons untuk Web
- Buat script Node.js/sharp untuk generate icon brand Ngekos.in:
  - Base: teal circle (`#0D9488`) dengan rumah icon (White house shape)
  - Generate: favicon (16x16), Icon-192, Icon-512, Icon-maskable-192, Icon-maskable-512
- Untuk maskable: tambahkan padding safe zone sesuai spec PWA
- Target ukuran: favicon <1KB, 192 <3KB, 512 <8KB, maskable512 <10KB

### Langkah 4: Generate Custom Android Adaptive Icons
- Buat `ic_launcher.xml` (adaptive icon) + foreground/background layers
- Foreground: teal house icon
- Background: solid teal `#0D9488`
- Generate mipmap-all density atau per-density
- Tambahkan `ic_launcher_round.xml` untuk round icon

### Langkah 5: Generate Custom iOS App Icons
- Dari source 1024x1024 yang di-generate di Langkah 3
- Generate semua ukuran yang dibutuhkan iOS (20, 29, 40, 60, 76, 83.5 @1x/2x/3x + 1024)
- Update `Contents.json`

### Langkah 6: Update Web Manifest & index.html
- `manifest.json`:
  - `background_color`: `"#0D9488"` (app teal)
  - `theme_color`: `"#0D9488"` (app teal)
  - `name`: `"Ngekos.in"`
  - `short_name`: `"Ngekos.in"`
  - `description`: `"Temukan kos impianmu"`
- `index.html`:
  - `<title>Ngekos.in</title>`
  - `<meta name="description" content="Temukan kos impianmu - Aplikasi Pencari Kos">`
  - `<meta name="apple-mobile-web-app-title" content="Ngekos.in">`

### Langkah 7: Kompres SVG Auth (opsional)
- `login-owner.svg` (15.9KB) dan `login-tenant.svg` (11.9KB) sudah relatif kecil
- Bisa dipangkas dengan menghapus metadata/editor-specific attributes

## Estimasi Hasil

| Kategori | Sebelum | Sesudah (est.) | Penghematan |
|----------|---------|-----------------|-------------|
| Facility PNGs (26) | ~24KB | ~16KB | ~33% |
| Web icons (5) | ~40KB | ~20KB | ~50% |
| Android icons (5) | ~17KB | ~12KB | ~30% |
| iOS icons (15) | ~20KB | ~15KB | ~25% |
| **Total** | ~101KB | ~63KB | ~38% |

## File yang Dimodifikasi

### Diedit
- `web/manifest.json` — warna, nama, deskripsi
- `web/index.html` — title, meta description, apple-mobile-web-app-title
- `assets/images/tamu.png` — resize 96→58

### Diganti (generated)
- `web/favicon.png` — branded icon
- `web/icons/*.png` — branded icons (4 files)
- `android/app/src/main/res/mipmap-*/ic_launcher.png` — branded icons (5 files)
- `android/app/src/main/res/mipmap-*/ic_launcher_round.png` — round variant (5 files, NEW)
- `android/app/src/main/res/mipmap-anydpi-v26/ic_launcher.xml` — adaptive icon (NEW)
- `android/app/src/main/res/mipmap-anydpi-v26/ic_launcher_round.xml` — adaptive round (NEW)
- `ios/Runner/Assets.xcassets/AppIcon.appiconset/*.png` — branded icons (15 files)
- `ios/Runner/Assets.xcassets/AppIcon.appiconset/Contents.json` — update paths

### Dikompres (lossless optimization)
- `assets/images/*.png` — 26 files
- `android/app/src/main/res/mipmap-*/ic_launcher.png` — 5 files

## Validasi
- `flutter analyze` — pasti clean
- `flutter run -d chrome` — icon di tab browser = branded teal
- Cek manifest di DevTools Application tab — warna correct
- Bandingkan file size sebelum/sesudah
