# ML-Service: Layanan Prediksi Harga Wajar Kamar Kos (JST MLP)

Layanan microservice cerdas berbasis Python FastAPI untuk Ngekos.in.

## Fitur
- **Jaringan Syaraf Tiruan (MLP Regresi)** untuk estimasi harga sewa bulanan kamar kos.
- **Label Kewajaran Pasar**:
  - `di_bawah_pasar`: selisih < -10% (kamar relatif murah / *undervalued*)
  - `wajar`: selisih antara -10% s.d. +10%
  - `di_atas_pasar`: selisih > +10% (kamar relatif mahal / *overpriced*)
- **Endpoint Terstandar**:
  - `GET /health`: Health check status layanan & model aktif.
  - `POST /predict`: Estimasi harga wajar 1 kamar.
  - `POST /predict/batch`: Estimasi batch untuk banyak kamar sekaligus (digunakan saat pemeringkatan SPK TOPSIS).
  - `GET /model/info`: Informasi versi model aktif beserta metrik evaluasi (MAE, RMSE, R², MAPE).
  - `POST /train`: Pelatihan ulang model dari dataset terverifikasi.

## Menjalankan dengan Docker (paling praktis)

Docker Compose menjalankan layanan dengan satu perintah, lengkap dengan
healthcheck, auto-restart, dan volume agar artefak model tidak hilang saat
container dibuat ulang.

```bash
cd ml-service
docker compose up -d --build
```

Cek status & log:

```bash
docker compose ps
docker compose logs -f ml-service
```

Hentikan layanan:

```bash
docker compose down
```

Layanan tersedia di `http://127.0.0.1:8001` (host) dan otomatis memakai
token `ngekos-secret-api-token`. Ubah lewat `ml-service/.env` bila perlu:

```env
ML_SERVICE_TOKEN=token-rahasia-anda
MODEL_VERSION=v1.0-mlp-baseline
ML_HOST_PORT=8001
```

Tanpa Compose (opsional):

```bash
docker build -t ngekos-ml-service .
docker run -d --name ngekos-ml -p 8001:8000 \
  -e ML_SERVICE_TOKEN="ngekos-secret-api-token" \
  -v ngekos-ml-artifacts:/app/artifacts \
  --restart unless-stopped ngekos-ml-service
```

## Menjalankan Tanpa Docker (Windows / Linux / macOS)

Docker **tidak wajib**, tetapi jika Docker belum tersedia gunakan skrip
berikut. Layanan dapat dijalankan langsung dengan Python 3.10+.

**Windows - cara termudah:** klik dua kali `run-lokal.bat`, atau lewat PowerShell:

```powershell
cd ml-service
powershell -ExecutionPolicy Bypass -File .\run-lokal.ps1
```

**Linux / macOS:**

```bash
cd ml-service
chmod +x run-lokal.sh
./run-lokal.sh
```

Skrip tersebut otomatis membuat virtual environment `.venv`, memasang
dependensi pada run pertama, lalu menjalankan layanan di
`http://127.0.0.1:8001`. Port `8000` sengaja tidak dipakai karena biasanya
sudah ditempati `php artisan serve`. Run berikutnya langsung cepat karena
dependensi sudah tersimpan.

Menjalankan manual (opsional):

```bash
cd ml-service
python -m venv .venv
.venv\Scripts\activate        # Windows
# source .venv/bin/activate   # Linux/macOS
pip install -r requirements.txt
python -m uvicorn app.main:app --host 0.0.0.0 --port 8001
```

> Bila layanan tidak dijalankan sama sekali, aplikasi Laravel tetap
> berfungsi penuh dengan estimator pasar terkalibrasi internal (FR-11).
> Layanan Python hanya diperlukan untuk inferensi JST/MLP asli dan
> pelatihan ulang model.

## Menjalankan dengan Docker

```bash
docker build -t ngekos-ml-service .
docker run -p 8001:8000 -e ML_SERVICE_TOKEN="ngekos-secret-api-token" ngekos-ml-service
```


## Konfigurasi Environment

| Variabel | Default | Keterangan |
| --- | --- | --- |
| `ML_SERVICE_TOKEN` | `ngekos-secret-api-token` | Token Bearer untuk endpoint selain `/health` |
| `MODEL_VERSION` | `v1.0-mlp-baseline` | Nama versi & nama berkas artefak model |
| `PORT` | `8000` | Port di dalam container (host dipetakan ke `8001` agar tidak bentrok dengan `php artisan serve`) |

Di sisi Laravel, atur variabel berikut pada `.env`:

```env
ML_SERVICE_URL=http://127.0.0.1:8001
ML_SERVICE_TOKEN=ngekos-secret-api-token
ML_SERVICE_TIMEOUT=3.0
```

Bila layanan tidak aktif atau melewati batas waktu, Laravel otomatis memakai
estimator pasar terkalibrasi internal (FR-11) sehingga halaman pemilik dan
SPK tetap berfungsi.

## Uji

```bash
# Uji logika preprocessor + estimator tanpa dependensi sklearn/FastAPI
python test_model_lokal.py

# Uji endpoint HTTP (setelah layanan berjalan)
curl http://127.0.0.1:8001/health
curl -X POST http://127.0.0.1:8001/predict \
  -H "Authorization: Bearer ngekos-secret-api-token" \
  -H "Content-Type: application/json" \
  -d '{"luas_m2":12,"jarak_km":1.0,"kawasan":"Coblong","tipe_penghuni":"Campur","fasilitas":["WiFi","AC"],"harga_aktual":1800000}'
```

## Artefak Model

Hasil pelatihan ulang (`POST /train`) disimpan sebagai
`artifacts/<MODEL_VERSION>.joblib` berisi model MLP, scaler, metrik, dan
hyperparameter. Berkas ini dimuat otomatis saat layanan dinyalakan. Folder
`artifacts/` diabaikan oleh Git dan Docker build context.

