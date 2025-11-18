@echo off
echo Starting Laravel Queue Worker...
echo.
echo Press Ctrl+C to stop the worker
echo.
php artisan queue:work --tries=3 --timeout=600
