@echo off
setlocal
cd /d "%~dp0"

if not exist ".venv\Scripts\python.exe" (
    echo Membuat virtual environment di ml-service\.venv ...
    python -m venv .venv
    if errorlevel 1 goto :gagal
)

".venv\Scripts\python.exe" -c "import fastapi, uvicorn, sklearn" >nul 2>&1
if errorlevel 1 (
    echo Memasang dependensi ^(hanya sekali, butuh koneksi internet^) ...
    ".venv\Scripts\python.exe" -m pip install --upgrade pip
    ".venv\Scripts\python.exe" -m pip install -r requirements.txt
    if errorlevel 1 goto :gagal
)

if "%PORT%"=="" set PORT=8001

echo.
echo Layanan ML berjalan di http://127.0.0.1:%PORT%
echo Tekan Ctrl+C untuk menghentikan.
echo.

".venv\Scripts\python.exe" -m uvicorn app.main:app --host 0.0.0.0 --port %PORT%
goto :eof

:gagal
echo.
echo Gagal menyiapkan layanan ML. Pastikan Python 3.10+ terpasang dan terhubung internet.
pause
