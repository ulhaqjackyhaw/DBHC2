@echo off
cd /d C:\Project\v2\DBHC
start php artisan serve
timeout /t 5 /nobreak > nul
start http://127.0.0.1:8000