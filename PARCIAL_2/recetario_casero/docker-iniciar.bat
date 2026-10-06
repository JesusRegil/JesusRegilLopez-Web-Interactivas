@echo off
setlocal EnableExtensions
cd /d "%~dp0"
title Recetario casero - Docker

where docker >nul 2>nul
if errorlevel 1 (
    echo [X] Docker no esta instalado. Descargalo de https://www.docker.com/products/docker-desktop/
    pause
    exit /b 1
)
docker info >nul 2>nul
if errorlevel 1 (
    echo [X] Docker Desktop no esta corriendo. Abrelo, espera a que diga "Engine running" y vuelve a ejecutar este archivo.
    pause
    exit /b 1
)

rem Puerto de la aplicacion: 8090, o el APP_PORT del archivo .env si existe
set "APP_PORT=8090"
if exist ".env" (
    for /f "usebackq tokens=1,* delims==" %%A in (`findstr /b /c:"APP_PORT=" ".env"`) do set "APP_PORT=%%B"
)

echo.
echo  Construyendo y arrancando la aplicacion y PostgreSQL...
echo  La primera vez tarda varios minutos: descarga imagenes y compila todo dentro de Docker.
echo.
docker compose up -d --build
if errorlevel 1 (
    echo.
    echo [X] Fallo "docker compose". Revisa el mensaje de arriba.
    echo     Si dice "context deadline exceeded", "407" o "unauthorized": mira "Problemas frecuentes" en el README.
    pause
    exit /b 1
)

echo.
echo  Esperando a que la aplicacion este lista. Crea las tablas en PostgreSQL al arrancar...
powershell -NoProfile -Command "$t=0; while($t -lt 600){ try { $r = Invoke-WebRequest -UseBasicParsing -Uri 'http://localhost:%APP_PORT%/up' -TimeoutSec 3; if($r.StatusCode -eq 200){ exit 0 } } catch {}; Start-Sleep -Seconds 3; $t+=3; Write-Host -NoNewline '.' }; exit 1"
if errorlevel 1 (
    echo.
    echo [!] Aun no responde. Mira el progreso con:  docker compose logs -f app
    pause
    exit /b 1
)

echo.
echo  ==========================================================
echo    LISTO:  http://localhost:%APP_PORT%
echo  ==========================================================
echo.
echo  Registrate con tu nombre y tu correo para crear tu recetario.
echo  Para detener todo: docker-detener.bat
start "" "http://localhost:%APP_PORT%"
pause
endlocal