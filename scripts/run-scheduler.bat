@echo off
REM Run Laravel scheduled tasks (triggers daily DB backup at configured time, e.g. 02:00 AM IST)
REM Can be registered in Windows Task Scheduler every 1 minute (or 5 minutes) if running on Windows Server/XAMPP.
cd /d "%~dp0.."
php artisan schedule:run
