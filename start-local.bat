@echo off
REM M2 Platform - Start local development server (Windows)
REM Requires PHP 8.x in PATH (https://windows.php.net/download)
cd /d "%~dp0"
echo ============================================
echo   M2 Platform - Local Development Server
echo ============================================
echo.
echo   Website : http://localhost:8080
echo   CRM     : http://localhost:8080/crm/login.php
echo             (admin@m2team.se / admin123)
echo.
echo   Stop with Ctrl+C
echo ============================================
php -S localhost:8080 router.php
