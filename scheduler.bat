@echo off
cd /d C:\Apexv6
php artisan schedule:run >> storage\logs\scheduler.log 2>&1
