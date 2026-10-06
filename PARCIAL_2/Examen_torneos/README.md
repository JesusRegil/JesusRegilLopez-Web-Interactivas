# Sistema de Torneos con Roles (Laravel)

App web donde un **administrador** crea torneos y los **jugadores** se inscriben. Los visitantes sin cuenta solo consultan.
Laravel 12 · SQLite · Blade + Bootstrap 5 (CDN) · autenticación manual (sin Breeze/Jetstream).

## Instalación con Docker (recomendado)

Solo necesitas Docker. La base de datos (SQLite) y el backend corren en el mismo contenedor.

```bash
docker compose up --build
# o, sin compose:
docker build -t torneos .
docker run -p 8000:8000 -v torneos-data:/data torneos
```

Abre <http://localhost:8000>. La primera vez se crean las tablas y los datos demo automáticamente.
Para empezar de cero: `docker compose down -v` (borra el volumen con la BD).

## Instalación sin Docker

Requisitos: PHP 8.2+ (con `pdo_sqlite`) y Composer.

```bash
composer run setup        # instala dependencias, .env, key, BD SQLite, migraciones y seeders
php artisan serve
```

## Cuentas demo

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | admin@torneos.test | admin123 |
| Jugador | ana@torneos.test | jugador123 |
| Jugador | beto@torneos.test | jugador123 |
| Jugador | carla@torneos.test, diego@torneos.test | jugador123 |

- **Admin**: lo crea el seeder (`database/seeders/DatabaseSeeder.php`). Se puede cambiar el rol de otra cuenta editando la columna `role` de la tabla `users`.
- **Registro**: el formulario `/registro` siempre crea un usuario con rol `jugador`.

## Datos demo (seeder)

Abierto: *Copa Relámpago de Fútbol*, *Liga FIFA 26*, *Básquet 3x3* · Lleno: *Duelo de Ajedrez* (cupo 2) · Cerrado por el admin: *Voleibol* · Ya pasado: *Tenis*.
Los últimos tres **no** aparecen en el listado, pero sí por URL directa (`/torneos/4`, `/torneos/5`, `/torneos/6`).

## Estructura

```
routes/web.php                              rutas (públicas, auth y admin)
app/Http/Middleware/EsAdmin.php             control de acceso por rol
app/Http/Controllers/AuthController.php     registro / login / logout
app/Http/Controllers/TorneoController.php   listado público y detalle
app/Http/Controllers/InscripcionController  inscribirse, "Mis torneos", cancelar
app/Http/Controllers/Admin/TorneoController CRUD, inscritos y bajas (solo admin)
app/Models/{User,Torneo,Inscripcion}.php    modelos y reglas (Torneo::disponibles, estadoReal)
database/migrations                         tablas users, torneos, inscripciones
lang/es/validation.php                      mensajes de validación en español
resources/views                             vistas Blade
```

Regla de cerrado (`Torneo::estaDisponible()`): cerrado si el admin lo marcó, si la fecha ya pasó o si el cupo está lleno.

## Cómo probar cada punto

1. **Registro / login / logout**: en `/registro` crea una cuenta (queda como jugador, el menú muestra el rol). Cierra sesión y entra con las cuentas demo. Correo repetido o contraseña corta muestran error junto al campo.
2. **CRUD de torneos (admin)**: entra como admin → *Administrar torneos*. Crea un torneo vacío o con fecha pasada o cupo 1/101: aparecen errores por campo. Edita y elimina (pide confirmación; borra sus inscripciones). Edita *Copa Relámpago* (3 inscritos) e intenta bajar el cupo a 2: error "no puede ser menor a los 3 jugadores ya inscritos".
3. **Listado y detalle público**: sin sesión, `/` muestra solo torneos abiertos, futuros y con plazas, ordenados por fecha. El buscador filtra por nombre o juego. Si no hay resultados sale un mensaje. El detalle muestra datos y participantes.
4. **Inscripciones**: como Ana o Beto pulsa *Inscribirme*. Intentar de nuevo → aviso de duplicado. Abre `/torneos/4` (lleno) o `/torneos/5` (cerrado) → botón deshabilitado; si se fuerza el POST, hay aviso. En *Mis torneos* cancela la inscripción (pide confirmación) y la plaza se libera.
5. **Permisos**: como jugador o invitado entra a `/admin/torneos` → redirección con aviso (invitado va al login). El admin ve inscritos de un torneo y puede *Dar de baja* a cualquier jugador.
6. **Mensajes**: todos los avisos de éxito/error y errores de campo están en español.