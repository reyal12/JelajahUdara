@echo off
:: Batch file to perform daily automatic backups of the JelajahUdara database.
:: Scheduled to run every day at 23:00 via Windows Task Scheduler.

:: Navigate to project backup directory (relative to script directory)
cd /d "%~dp0"

:: Set filename with current date (format YYYYMMDD)
set FILENAME=backup_harian_%date:~10,4%%date:~4,2%%date:~7,2%.sql

:: Run mysqldump command
:: Adjust user/pass if necessary. Empty password by default.
mysqldump -u root jelajahudara > hasilbackup\%FILENAME%

echo Backup completed successfully: %FILENAME%
