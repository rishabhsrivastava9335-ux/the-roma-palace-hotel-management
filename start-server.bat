@echo off
title The Roma Palace - Hotel Management System
echo =======================================================================
echo              THE ROMA PALACE - LUXURY HOTEL MANAGEMENT SYSTEM
echo                   BTech CSE DBMS Mini Project Launcher
echo =======================================================================
echo.
echo [1/3] Detecting PHP Runtime Environment...

set PHP_BIN=php

where php >nul 2>nul
if %errorlevel% neq 0 (
    if exist "C:\Users\risha\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" (
        set PHP_BIN="C:\Users\risha\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
        echo [INFO] Detected WinGet PHP 8.4 runtime binary.
    ) else (
        echo [ERROR] PHP executable not found in system PATH.
        echo Please ensure PHP is installed or start through XAMPP.
        pause
        exit /b 1
    )
) else (
    echo [INFO] System PHP found on PATH.
)

echo [2/3] Starting Local Web Server at http://127.0.0.1:8000 ...
echo [INFO] Dual-mode Database Engine active (MySQL 3306 auto-detect or zero-config SQLite fallback).
echo [INFO] Press Ctrl+C at any time in this console window to stop the server.
echo.
echo =======================================================================
echo  PUBLIC LUXURY WEBSITE:   http://127.0.0.1:8000
echo  GUEST LOGIN:             http://127.0.0.1:8000/login.php
echo  ADMIN CONTROL CENTER:    http://127.0.0.1:8000/admin/admin-login.php
echo  VIVA & DEMO DASHBOARD:   http://127.0.0.1:8000/admin/demo-presentation.php
echo =======================================================================
echo.
echo [3/3] Launching Default Web Browser...
start http://127.0.0.1:8000

%PHP_BIN% -S 127.0.0.1:8000
