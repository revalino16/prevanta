@echo off
setlocal enabledelayedexpansion

echo ========================================================
echo       PREVANTA: DEPLOYMENT OTOMATIS KE STAGING LOKAL    
echo ========================================================
echo.

set STAGING_DIR=D:\laragon\www\prevanta-staging

if not exist "%STAGING_DIR%" (
    echo [ERROR] Folder %STAGING_DIR% tidak ditemukan!
    echo Silakan clone repositori ke folder tersebut terlebih dahulu dengan perintah:
    echo git clone -b develop https://github.com/revalino16/prevanta.git D:\laragon\www\prevanta-staging
    echo.
    pause
    exit /b 1
)

cd /d "%STAGING_DIR%"

echo [1/6] Mengaktifkan Maintenance Mode...
call php artisan down --render="errors::503" || true

echo [2/6] Menarik update terbaru dari GitHub (branch: develop)...
call git fetch origin develop
call git reset --hard origin/develop

echo [3/6] Memperbarui dependensi PHP (Composer)...
call composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo [4/6] Mengompilasi frontend (NPM & Vite)...
call npm install --no-audit
call npm run build

echo [5/6] Menjalankan migrasi database staging...
call php artisan migrate --force

echo [6/6] Membersihkan dan memuat ulang cache Laravel...
call php artisan optimize:clear
call php artisan optimize

echo Mematikan Maintenance Mode...
call php artisan up

echo.
echo ========================================================
echo [BERHASIL] Aplikasi Staging berhasil di-update!
echo Silakan buka: http://prevanta-staging.test
echo ========================================================
echo.
pause
