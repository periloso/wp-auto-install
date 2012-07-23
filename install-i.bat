@echo off
set /p siteTitle=Enter site title:
set /p siteUrl=Enter site url:
set /p installDir=Enter installation directory:
set /p dbName=Enter DB name:
set /p dbUser=Enter DB user:

@php.exe %~dp0lib/install.php --config config-i.php --site-title %siteTitle% --site-url %siteUrl% --install-dir %installDir% --db-name %dbName% --db-user %dbUser%

%localappdata%\Google\Chrome\Application\chrome.exe %siteUrl%

pause
