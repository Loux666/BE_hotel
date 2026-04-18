@echo off
cd /d "D:\DATN\hotel_booking"
C:\xampp\php\php.exe artisan schedule:run
echo Schedule completed at %date% %time%
pause