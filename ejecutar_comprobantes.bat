@echo off
REM ==========================================================
REM  Completa la carga: aplica migración SUNAT + 10 comprobantes
REM ==========================================================

cd /d "%~dp0"

echo ============================================================
echo  Paso 1/2 - Aplicando migraciones pendientes (SUNAT)
echo ============================================================
php artisan migrate --force

echo.
echo ============================================================
echo  Paso 2/2 - Insertando 10 comprobantes electronicos
echo ============================================================
php artisan db:seed --class=ComprobantesExtraSeeder

echo.
echo ============================================================
echo  Listo. Recarga el dashboard para ver los datos.
echo ============================================================
pause
