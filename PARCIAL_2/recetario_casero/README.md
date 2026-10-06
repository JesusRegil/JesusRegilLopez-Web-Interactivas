# Recetario casero

Cada persona guarda **sus propias recetas de cocina**: las crea, las lista, las consulta, las edita y las elimina. Hay un solo tipo de usuario y una sola entidad principal (`Receta`); nadie puede ver las recetas de otra persona.

Práctica de Laravel: rutas, controlador de recurso, migraciones, validación y vistas con Tailwind. Hecha con **Laravel 13 + PostgreSQL 16**, y **todo corre dentro de Docker** (aplicación y base de datos), así que funciona igual en cualquier equipo.

| | |
|---|---|
| Aplicación | PHP 8.4 + Apache + Laravel 13 (contenedor `recetario-app`) |
| Base de datos | PostgreSQL 16 (contenedor `recetario-db`, volumen `pg_data`) |
| Estilos | Tailwind CSS v4 (clases utilitarias) compilado con Vite durante el build |
| Dirección | <http://localhost:8090> |

## Qué puede hacer

- Registrarse e iniciar sesión (un único tipo de usuario, sin roles).
- Crear recetas con **título, categoría, tiempo en minutos, dificultad, ingredientes, pasos y una nota personal** (opcional).
- Ver el listado en una tabla, abrir el detalle (ingredientes y pasos como listas), editar y eliminar (con confirmación).
- Buscar por título y filtrar por categoría (combinables), con aviso cuando no hay resultados.
- Todo en español, con los errores de validación junto a cada campo y mensajes de éxito.

## Requisitos

Solo **Docker Desktop** (con Docker Compose v2). No hace falta instalar PHP, Composer, Node ni PostgreSQL.

## Instalación

```bash
git clone <url-de-este-repositorio> recetario-casero
cd recetario-casero
docker compose up -d --build
```

Abre **<http://localhost:8090>**. La primera vez tarda varios minutos (descarga las imágenes y compila todo dentro de Docker).
En Windows también puedes dar doble clic a **`docker-iniciar.bat`**: arranca todo, espera a que la aplicación responda y abre el navegador.

Para comprobar que todo está bien:

```bash
docker compose ps        # recetario-app y recetario-db deben decir "healthy"
```

Para detener (los datos se conservan): `docker compose down` o `docker-detener.bat`.

## Cómo se crea la base de datos

No hay que crear nada a mano:

1. El contenedor **`db`** (PostgreSQL 16) crea la base `recetario` y el usuario `recetario` la primera vez que arranca, con las variables `POSTGRES_DB`, `POSTGRES_USER` y `POSTGRES_PASSWORD` de [`docker-compose.yml`](docker-compose.yml). Los datos viven en el volumen `pg_data`.
2. Al arrancar, el contenedor **`app`** espera a que PostgreSQL esté listo y ejecuta `php artisan migrate --force` ([`docker/entrypoint.sh`](docker/entrypoint.sh)): las **migraciones crean las tablas** (`users`, `recetas`, …). Si ya están creadas, no hace nada.
3. La clave de cifrado (`APP_KEY`) se genera la primera vez y se guarda en el volumen `app_storage`, así que las sesiones sobreviven a los reinicios.

Comandos manuales:

```bash
docker compose exec app php artisan migrate        # aplicar migraciones pendientes
docker compose exec app php artisan migrate:fresh  # BORRA todo y recrea las tablas vacías
docker compose exec db psql -U recetario -d recetario -c "\dt"      # ver las tablas
docker compose exec db psql -U recetario -d recetario -c "select count(*) from recetas"
```

La base de datos **no se publica** en tu equipo (solo la ve la aplicación dentro de Docker).

## Comandos útiles

```bash
docker compose logs -f app          # ver el arranque y los errores
docker compose restart app          # reiniciar la aplicación
docker compose up -d --build        # reconstruir tras cambiar código o vistas
docker compose down                 # detener (los datos se conservan)
docker compose down -v              # detener y BORRAR todos los datos (volúmenes)
```

### Variables opcionales

Copia [`.env.example`](.env.example) a `.env` (junto a `docker-compose.yml`) solo si quieres cambiar algo:

| Variable | Por defecto | Para qué sirve |
|---|---|---|
| `APP_PORT` | `8090` | Puerto de la aplicación en tu equipo |
| `DB_PASSWORD` | `recetario` | Contraseña de PostgreSQL (se aplica al crear la base por primera vez) |
| `APP_KEY` | se genera sola | Clave de cifrado de Laravel (opcional; déjala comentada si no la necesitas) |

Después ejecuta `docker compose up -d`. Todo lo demás (idioma `es`, zona horaria `America/Mexico_City`, conexión a PostgreSQL…) va fijo en `docker-compose.yml`.

## Pruebas automáticas

Un solo comando, sin PHP en tu equipo:

```bash
docker compose --profile test run --rm --build test
```

Construye la etapa `test` del [`Dockerfile`](Dockerfile) (la aplicación + dependencias de desarrollo) y ejecuta PHPUnit. Debe terminar con `Tests: … passed` y ningún fallo. Las pruebas usan **SQLite en memoria** y nunca tocan el PostgreSQL de la aplicación (`phpunit.xml` lo fuerza y [`tests/TestCase.php`](tests/TestCase.php) aborta si la conexión no es SQLite). Los nombres de las pruebas están en español y describen lo que comprueban.

## Cómo probar cada fase

Cada fase quedó en su propio commit (`git log --oneline`). Con la aplicación en <http://localhost:8090>:

### Fase 0 — Preparación (proyecto, autenticación y base de datos)

1. `docker compose up -d --build` y `docker compose ps`: los dos contenedores en `healthy`.
2. Abre <http://localhost:8090>: te lleva a **`/login`**.
3. Pulsa **Regístrate**, escribe nombre, correo y contraseña (mínimo 8, confirmada). Entras a **Mis recetas**, vacío: *«Aún no tienes recetas»* (el botón para crear la primera receta llega en la Fase 1).
4. **Cerrar sesión** y vuelve a entrar con tu correo. Con una contraseña incorrecta aparece *«Estas credenciales no coinciden con nuestros registros.»* junto al campo.
5. Registra una segunda cuenta (otra ventana de incógnito): también entra a un recetario vacío.
6. `docker compose exec db psql -U recetario -d recetario -c "\d recetas"`: la tabla `recetas` con `user_id` ligado a `users`.

### Fase 1 — Crear y ver

1. **Nueva receta** (`/recetas/crear`). Envía el formulario vacío: aparece un error en español **junto a cada campo**. Prueba también tiempo `0`, `-3` o `abc`: *«El tiempo en minutos debe ser mayor a 0.»* / *«…debe ser un número entero.»* Lo que escribiste se conserva.
2. Completa título, categoría, tiempo, dificultad, ingredientes (uno por línea) y pasos (uno por línea). Al guardar vuelves al listado con *«Receta creada correctamente.»* y la receta en la tabla (título, categoría, tiempo, dificultad).
3. Haz clic en el título: el **detalle** muestra los ingredientes como lista con viñetas y los pasos como lista numerada (y la nota, si la escribiste).

### Fase 2 — Editar y eliminar

1. **Editar**: el formulario aparece precargado. Borra el título y guarda → error junto al campo; corrígelo y guarda → detalle con *«Receta actualizada correctamente.»*
2. **Eliminar**: el navegador pide confirmación (*«¿Eliminar la receta …?»*). Cancelar no hace nada; Aceptar vuelve al listado con *«Receta eliminada correctamente.»*

### Fase 3 — Buscar y filtrar

1. Crea varias recetas con títulos y categorías distintos.
2. Escribe parte de un título (da igual mayúsculas o minúsculas) y pulsa **Buscar**: solo salen las coincidencias.
3. Elige una **categoría**: solo esa categoría. Usa ambos a la vez: se combinan.
4. Busca algo que no exista: *«No se encontraron recetas con esa búsqueda o categoría.»* y el botón **Limpiar filtros**. El enlace **Limpiar** restablece el formulario.

### Aislamiento entre usuarios

Con la cuenta B (otra ventana de incógnito) abre la dirección de una receta de A, por ejemplo `/recetas/1`: responde **404**, igual que `/recetas/1/editar`. El listado de B solo muestra sus propias recetas.

## Comprobación final

| Prueba | Resultado esperado | Prueba automática |
|---|---|---|
| Registrarse con un usuario nuevo | Entra a un recetario vacío propio | [`AutenticacionTest`](tests/Feature/AutenticacionTest.php): `test_un_usuario_nuevo_se_registra_y_entra_a_un_recetario_vacio` |
| Crear una receta válida | Aparece en el listado, con mensaje de éxito | [`RecetasTest`](tests/Feature/RecetasTest.php): `test_crear_una_receta_valida_la_muestra_en_el_listado_con_mensaje_de_exito` |
| Crear con datos inválidos | Errores junto a cada campo y no se guarda | `RecetasTest`: `test_crear_con_datos_invalidos_muestra_el_error_junto_al_campo_y_no_guarda_nada` (15 casos) y `test_los_errores_aparecen_junto_a_cada_campo_y_se_conserva_lo_escrito` |
| Abrir el detalle de una receta | Se ven ingredientes y pasos como listas | `RecetasTest`: `test_el_detalle_muestra_ingredientes_y_pasos_como_listas` |
| Editar y guardar | Cambios reflejados, con mensaje | `RecetasTest`: `test_el_formulario_de_edicion_aparece_precargado`, `test_guardar_los_cambios_actualiza_la_receta_y_muestra_el_mensaje`, `test_editar_con_datos_invalidos_muestra_el_error_junto_al_campo_y_no_cambia_nada` |
| Eliminar con confirmación | Desaparece, con mensaje de éxito | `RecetasTest`: `test_eliminar_pide_confirmacion_en_el_listado_y_en_el_detalle`, `test_eliminar_una_receta_la_quita_del_listado_y_muestra_el_mensaje` |
| Buscar por título y filtrar por categoría | Solo coinciden los resultados; aviso si no hay ninguno | [`BusquedaTest`](tests/Feature/BusquedaTest.php): `test_buscar_por_titulo_encuentra_coincidencias_parciales`, `test_filtrar_por_categoria_muestra_solo_esa_categoria`, `test_el_buscador_y_el_filtro_de_categoria_se_combinan`, `test_sin_resultados_muestra_el_aviso_y_un_enlace_para_limpiar` |
| Entrar con otro usuario distinto | No ve las recetas del primero (y recibe 404 en sus direcciones) | [`AislamientoTest`](tests/Feature/AislamientoTest.php): `test_cada_usuario_ve_solo_sus_propias_recetas`, `test_otro_usuario_recibe_404_al_ver_la_receta` (y las de editar, actualizar y eliminar) |

Otras reglas con prueba propia: inicio y cierre de sesión, credenciales inválidas, invitados enviados a `/login`, límite de 10 intentos de inicio de sesión por minuto, correo en minúsculas y único, contraseña con *hash*, textos escapados (sin inyección de HTML) y el lector de listas ([`tests/Unit/RecetaTest.php`](tests/Unit/RecetaTest.php)).

## Estructura del proyecto

El recorrido de una petición: **ruta → controlador → request (validación) → modelo → migración → vistas**.

| Capa | Archivo |
|---|---|
| Rutas | [`routes/web.php`](routes/web.php): `/login`, `/registro`, `POST /logout` y `Route::resource('recetas', …)` |
| Controladores | [`AuthController`](app/Http/Controllers/AuthController.php) (login, registro, logout) y [`RecetaController`](app/Http/Controllers/RecetaController.php) (CRUD + buscador/filtro) |
| Validación | [`RecetaRequest`](app/Http/Requests/RecetaRequest.php): las mismas reglas para crear y editar |
| Modelos | [`Receta`](app/Models/Receta.php) (categorías, dificultades, listas de ingredientes y pasos) y [`User`](app/Models/User.php) (`hasMany` recetas) |
| Migración | [`create_recetas_table`](database/migrations/2026_10_05_130000_create_recetas_table.php); además las migraciones por defecto de Laravel |
| Vistas (Tailwind) | [`resources/views`](resources/views): `layouts/`, `auth/` y `recetas/` (`index`, `create`, `edit`, `show`, `_formulario`) |
| Idioma | [`lang/es`](lang/es) (`auth.php`, `validation.php`) y [`lang/es.json`](lang/es.json) (páginas de error) |
| URLs en español y aislamiento | [`AppServiceProvider`](app/Providers/AppServiceProvider.php): `/recetas/crear`, `/recetas/{receta}/editar` y `{receta}` solo entre las recetas del usuario |
| Docker | [`Dockerfile`](Dockerfile) (etapas `vendor`, `vendor-dev`, `assets`, `base`, `test`, `runtime`), [`docker-compose.yml`](docker-compose.yml) y [`docker/`](docker) |
| Pruebas | [`tests/Feature`](tests/Feature) y [`tests/Unit`](tests/Unit) |

## Decisiones

- **Autenticación por sesión, hecha a mano** y mínima (un `AuthController` y dos vistas): sin Breeze, JWT ni paquetes extra. Sin verificación de correo ni recuperación de contraseña.
- **Ingredientes y pasos son texto, uno por línea**; la vista parte el texto en líneas y las muestra como `<ul>` / `<ol>` (sin tablas adicionales). Ingredientes y pasos son obligatorios (si no, no habría listas que mostrar); solo la nota es opcional.
- **Valores guardados sin acento** (`facil`, `media`, `dificil`; `desayuno`, `almuerzo`, `cena`, `postre`, `bebida`) y etiquetas con acento en pantalla, definidas como constantes en el modelo `Receta`.
- **Recetas de otras personas dan 404** (no 403): `{receta}` solo se busca entre las del usuario con sesión, y `user_id` nunca viene del formulario.
- **Formularios con `novalidate`** y sin atributos HTML5 de validación: los errores salen del servidor, en español, junto a cada campo.
- **Tiempo**: entero de 1 a 10 080 minutos (una semana).
- **Búsqueda**: coincidencia parcial sin distinguir mayúsculas (`ILIKE` en PostgreSQL), escapando `%`, `_` y `\`.
- Sin paginación, seeders, API, roles ni JavaScript propio (solo el `confirm()` del navegador para eliminar).

## Problemas frecuentes

| Síntoma | Solución |
|---|---|
| `error during connect` / *«Docker Desktop no está corriendo»* | Abre Docker Desktop, espera a que diga *Engine running* y repite el comando. |
| `port is already allocated` (puerto 8090 ocupado) | Copia `.env.example` a `.env`, cambia `APP_PORT` (por ejemplo `8091`) y ejecuta `docker compose up -d`. |
| No descarga las imágenes (`context deadline exceeded`) | Sin conexión directa a Docker Hub (proxy de la escuela o empresa): configura el proxy en Docker Desktop → *Settings* → *Resources* → *Proxies*, o mira abajo cómo [llevar las imágenes de otro equipo](#sin-acceso-a-docker-hub). |
| `407 Proxy Authentication Required` o errores de red durante el build (`apt-get`, `composer`, `npm`) | Los contenedores de construcción usan el proxy de Docker (`proxies` en `~/.docker/config.json` y los ajustes de Docker Desktop). Configúralo con un proxy accesible desde los contenedores. |
| Página en blanco o error 500 | Mira `docker compose logs app`. |
| `app` se reinicia sin parar | `docker compose logs app`: normalmente PostgreSQL no arrancó. Prueba `docker compose down` y `docker compose up -d`. |
| Cambié `DB_PASSWORD` y la aplicación no conecta | PostgreSQL guarda la contraseña al crear el volumen. Vuelve a poner la anterior, o empieza de cero con `docker compose down -v` (borra los datos). |
| Cambié una vista o el código y no se nota | La aplicación va dentro de la imagen: ejecuta `docker compose up -d --build`. |
| Quiero empezar de cero | `docker compose down -v` y luego `docker compose up -d --build` (borra todos los datos). |
| `docker compose` no existe | Actualiza Docker Desktop: este proyecto usa Docker Compose v2. |

### Sin acceso a Docker Hub

Si un equipo no puede descargar imágenes, en otro equipo con acceso ejecuta:

```bash
docker pull composer:2
docker pull node:22-alpine
docker pull php:8.4-apache
docker pull postgres:16-alpine
docker save -o recetario.tar composer:2 node:22-alpine php:8.4-apache postgres:16-alpine
```

Copia `recetario.tar` al equipo sin acceso y, en la carpeta del proyecto:

```bash
docker load -i recetario.tar
docker compose up -d --build
```
