@echo off
REM ===============================================================
REM deploy.bat
REM Copy all contents from the "deploy_package" folder (same level
REM as this script) into the correct structure inside a Laravel root,
REM then run cache-clear and composer commands automatically.
REM
REM Usage:
REM   deploy.bat [PathToLaravelRoot]
REM If no argument is given, the script will ask for the path.
REM ===============================================================

setlocal

:: Get current script directory and deploy_package folder
set "SCRIPT_DIR=%~dp0"
set "PKG=%SCRIPT_DIR%deploy_package"

:: Get target Laravel root path
if "%~1"=="" (
  set /P "TARGET=Enter full path to Laravel root (e.g. C:\inetpub\wwwroot\myapp): "
) else (
  set "TARGET=%~1"
)

echo --------------------------------------------------------------
echo Deploying from: %PKG%
echo To target path: %TARGET%
echo --------------------------------------------------------------
echo.

:: Check if package folder exists
if not exist "%PKG%" (
  echo ERROR: Package folder "%PKG%" does not exist.
  pause
  exit /b 1
)

:: Check if target folder exists
if not exist "%TARGET%" (
  echo ERROR: Target path "%TARGET%" does not exist.
  pause
  exit /b 1
)

:: Basic check for Laravel root
if not exist "%TARGET%\artisan" (
  echo WARNING: Could not find "artisan" in target folder.
  set /P "CONT=Are you sure this is the correct Laravel root? (Y/N): "
  if /I not "%CONT%"=="Y" (
    echo Aborting deployment.
    exit /b 1
  )
)

echo Starting file copy...
echo.

REM ============================================================
REM Copy all contents of deploy_package into target folder.
REM /E   = include subfolders (even empty)
REM /COPY:DAT = copy data, attributes, timestamps
REM /R:3 /W:5 = retry 3 times, wait 5 seconds
REM /MT:8 = use 8 threads
REM ============================================================

robocopy "%PKG%" "%TARGET%" *.* /E /COPY:DAT /R:3 /W:5 /MT:8

if %ERRORLEVEL% GEQ 8 (
  echo ERROR: Robocopy reported a failure. ErrorLevel=%ERRORLEVEL%
  pause
  exit /b %ERRORLEVEL%
) else (
  echo SUCCESS: Files copied successfully. (Robocopy exit code %ERRORLEVEL%)
)

echo.
echo ============================================================
echo Running Laravel maintenance commands...
echo ============================================================
echo.

pushd "%TARGET%"

:: Rebuild Composer autoload files
composer dump-autoload

:: Clear Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear



popd

echo.
echo ============================================================
echo ✅ Deployment completed successfully!
echo ============================================================

pause
endlocal
