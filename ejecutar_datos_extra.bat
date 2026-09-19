@echo off
REM ==========================================================
REM  Ejecuta DatosExtraSeeder en el proyecto CRM TPV FastFood
REM  Añade 10 registros nuevos a cada módulo del sistema con
REM  fechas variadas para alimentar paneles y graficos del
REM  dashboard.
REM ==========================================================

cd /d "%~dp0"

echo ============================================================
echo  CRM TPV FastFood - Carga de datos extra para dashboard
echo ============================================================
echo.
echo Ejecutando: php artisan db:seed --class=DatosExtraSeeder
echo.

php artisan db:seed --class=DatosExtraSeeder

echo.
echo ============================================================
echo  Proceso terminado. Revisa el mensaje superior.
echo ============================================================
pause
