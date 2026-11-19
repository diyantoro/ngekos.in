#!/usr/bin/env bash
# Menjalankan layanan Model AI (FastAPI) TANPA Docker di Linux/macOS.
#
# Pemakaian:
#   cd ml-service
#   ./run-lokal.sh
#
# Virtual environment (.venv) dibuat otomatis dan dependensi dipasang
# pada saat pertama kali dijalankan.

set -euo pipefail
cd "$(dirname "$0")"

if [ ! -x ".venv/bin/python" ]; then
  echo "Membuat virtual environment di ml-service/.venv ..."
  python3 -m venv .venv
fi

if ! .venv/bin/python -c "import fastapi, uvicorn, sklearn" >/dev/null 2>&1; then
  echo "Memasang dependensi (hanya sekali, butuh koneksi internet) ..."
  .venv/bin/python -m pip install --upgrade pip
  .venv/bin/python -m pip install -r requirements.txt
fi

PORT="${PORT:-8001}"

echo ""
echo "Layanan ML berjalan di http://127.0.0.1:${PORT}"
echo "Tekan Ctrl+C untuk menghentikan."
echo ""

exec .venv/bin/python -m uvicorn app.main:app --host 0.0.0.0 --port "${PORT}"
