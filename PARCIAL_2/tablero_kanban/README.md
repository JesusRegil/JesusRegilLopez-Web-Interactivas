# Kanban (Laravel 12 + PostgreSQL + Docker)

Gestor de tareas estilo Kanban (Por hacer / En curso / Hechas) con prioridad, vencimiento, filtros y búsqueda.
Backend Laravel (Composer) + PostgreSQL, interfaz con Bootstrap y jQuery.

## Ejecutar

Solo necesitas Docker (con acceso a internet la primera vez):

```
git clone <URL-DEL-REPO> kanban
cd kanban
docker compose up --build
```

Abre http://localhost:8000

- `composer install` se ejecuta durante el build; no hace falta instalar PHP ni Composer.
- Los datos se guardan en PostgreSQL (volumen `pgdata`) y se conservan al reiniciar.
- Detener: `docker compose down` (con `-v` también borra los datos).

## Red corporativa / sin acceso a Docker Hub

Si Docker no puede descargar imágenes, carga antes `postgres:16-alpine` y `composer:2` con `docker load`.
Para reutilizar una imagen PHP 8.2 con `pdo_pgsql` que ya tengas, define `PHP_IMAGE=<imagen>` en un archivo `.env` junto al compose.
