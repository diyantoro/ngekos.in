# Menjalankan layanan Model AI (FastAPI) TANPA Docker di Windows.
#
# Pemakaian (PowerShell):
#   cd ml-service
#   powershell -ExecutionPolicy Bypass -File .\run-lokal.ps1
#
# Virtual environment (.venv) dibuat otomatis dan dependensi dipasang
# pada saat pertama kali dijalankan.

$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $root

$venvPython = Join-Path $root '.venv\Scripts\python.exe'

if (-not (Test-Path $venvPython)) {
    Write-Host 'Membuat virtual environment di ml-service\.venv ...'
    python -m venv .venv
}

& $venvPython -c "import fastapi, uvicorn, sklearn" *> $null
if ($LASTEXITCODE -ne 0) {
    Write-Host 'Memasang dependensi (hanya sekali, butuh koneksi internet) ...'
    & $venvPython -m pip install --upgrade pip
    & $venvPython -m pip install -r (Join-Path $root 'requirements.txt')
}

$port = if ($env:PORT) { $env:PORT } else { '8001' }

Write-Host ''
Write-Host "Layanan ML berjalan di http://127.0.0.1:$port"
Write-Host 'Tekan Ctrl+C untuk menghentikan.'
Write-Host ''

& $venvPython -m uvicorn app.main:app --host 0.0.0.0 --port $port
