@echo off
cd /d "%~dp0"
echo Deteniendo los contenedores. Los datos de la base de datos se conservan.
docker compose down
pause