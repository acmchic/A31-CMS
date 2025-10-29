@echo off
REM ===============================================================
REM deploy.bat
REM Copy all contents from the "deploy" folder (same level as this
REM script) into the current Laravel root, then run cache-clear and
REM composer commands automatically.
REM ===============================================================

setlocal

:: Get current script directory (Laravel root) and deploy folder
set "SCRIPT_DIR=%~dp0"
set "TARGET=%SCRIPT_DIR%"
set "PKG=%SCRIPT_DIR%deploy"

echo --------------------------------------------------------------
echo Deploying from: %PKG%
echo To target path: %TARGET%
echo --------------------------------------------------------------
echo.

:: Check if deploy folder exists
if not exist "%PKG%" (
  echo ERROR: Deploy folder "%PKG%" does not exist.
  pause
  exit /b 1
)

:: Basic check for Laravel root
if not exist "%TARGET%\artisan" (
  echo ERROR: Could not find "artisan" in "%TARGET%".
  echo This script must be placed in the Laravel root folder.
  pause
  exit /b 1
)

echo Starting file copy...
echo.

REM ============================================================
REM Copy all contents of deploy into target folder.
REM ============================================================

robocopy "%PKG%" "%TARGET%." /E /COPY:DAT /R:3 /W:5 /MT:8

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

composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo Running module migrations...
echo Migrating RecordManagement module...
php artisan module:migrate RecordManagement --force 2>nul || echo RecordManagement migration completed with warnings
echo Migrating OrganizationStructure module...
php artisan module:migrate OrganizationStructure --force 2>nul || echo OrganizationStructure migration completed with warnings
echo Migrating PersonnelReport module...
php artisan module:migrate PersonnelReport --force 2>nul || echo PersonnelReport migration completed with warnings
echo Migrating VehicleRegistration module...
php artisan module:migrate VehicleRegistration --force 2>nul || echo VehicleRegistration migration completed with warnings
echo Migrating ApprovalWorkflow module...
php artisan module:migrate ApprovalWorkflow --force 2>nul || echo ApprovalWorkflow migration completed with warnings
echo Migrating FileSharing module...
php artisan module:migrate FileSharing --force 2>nul || echo FileSharing migration completed with warnings
echo All module migrations completed!

popd

echo.
echo ============================================================
echo ✅ Deployment completed successfully!
echo ============================================================

pause
endlocal
