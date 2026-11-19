#!/bin/sh
# Entrypoint container aplikasi Ngekos.in.
# Menyiapkan permission, aset publik, menunggu MySQL, lalu migrasi/seeding
# pada container utama. Container queue/scheduler menjalankan entrypoint yang
# sama dengan RUN_MIGRATIONS=false.

set -eu

cd /var/www/html

# ---------------------------------------------------------------------------
# 1. Sinkronkan aset publik dari template image ke volume bersama nginx.
#    (Volume menutupi folder public bawaan image, jadi template disalin ulang.)
# ---------------------------------------------------------------------------
if [ "${SYNC_PUBLIC:-true}" = "true" ] && [ -d /opt/public-template ]; then
    mkdir -p public
    cp -a /opt/public-template/. public/
fi

mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
fi
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

# ---------------------------------------------------------------------------
# 2. Tunggu database, migrasi, dan seeding awal.
# ---------------------------------------------------------------------------
if [ "${RUN_MIGRATIONS:-true}" = "true" ] && [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
    echo "[entrypoint] Menunggu MySQL di ${DB_HOST:-mysql}:${DB_PORT:-3306} ..."

    percobaan=0
    until php -r '
        $host = getenv("DB_HOST") ?: "mysql";
        $port = getenv("DB_PORT") ?: "3306";
        $db   = getenv("DB_DATABASE") ?: "ngekos";
        $user = getenv("DB_USERNAME") ?: "ngekos";
        $pass = getenv("DB_PASSWORD") ?: "";
        try {
            new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass);
            exit(0);
        } catch (Throwable $e) {
            exit(1);
        }
    '; do
        percobaan=$((percobaan + 1))
        if [ "$percobaan" -ge 60 ]; then
            echo "[entrypoint] Database tidak siap setelah 120 detik. Keluar."
            exit 1
        fi
        sleep 2
    done

    echo "[entrypoint] Menjalankan migrasi ..."
    php artisan migrate --force

    # Seeding hanya saat tabel users masih kosong agar tidak menduplikasi data.
    if [ "${RUN_SEEDERS:-true}" = "true" ]; then
        jumlah_pengguna=$(php -r '
            $host = getenv("DB_HOST") ?: "mysql";
            $port = getenv("DB_PORT") ?: "3306";
            $db   = getenv("DB_DATABASE") ?: "ngekos";
            $user = getenv("DB_USERNAME") ?: "ngekos";
            $pass = getenv("DB_PASSWORD") ?: "";
            $pdo  = new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass);
            echo (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        ')

        if [ "$jumlah_pengguna" -eq 0 ]; then
            echo "[entrypoint] Database kosong, menjalankan seeder demo ..."
            php artisan db:seed --force
        else
            echo "[entrypoint] Data sudah ada (${jumlah_pengguna} pengguna), seeding dilewati."
        fi
    fi

    php artisan storage:link >/dev/null 2>&1 || true
    php artisan optimize:clear >/dev/null 2>&1 || true
fi

echo "[entrypoint] Siap. Menjalankan: $*"

exec "$@"
