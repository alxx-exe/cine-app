# CineApp

Aplicación web en Laravel 12 para la administración de un cine y la consulta y reserva de entradas numeradas. La interfaz es adaptable a móviles y escritorio; los textos y mensajes están en español.

## Requisitos

- PHP **8.2 o superior**, con `pdo_pgsql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo` y `session` habilitadas.
- Composer 2.
- PostgreSQL 15 o superior.
- Git es opcional. Node.js/npm no son necesarios: los estilos se sirven directamente desde `public/css`.

Verifica el entorno con `php -v`, `composer --version` y `psql --version`. En Windows, asegúrate de que PHP, Composer y `psql` estén disponibles en `PATH`.

## Instalación en Windows (PowerShell)

1. Instala PHP y habilita la extensión PostgreSQL `pdo_pgsql` en `php.ini`. Instala Composer y PostgreSQL si todavía no están instalados. Reinicia PowerShell y comprueba los comandos del apartado anterior.
2. Crea una base de datos vacía llamada `cine_app` desde pgAdmin o con tu cuenta de administración de PostgreSQL:

   ```sql
   CREATE DATABASE cine_app;
   ```

   Usa un usuario de base de datos propio y con permisos sobre `cine_app`; no es necesario usar ni se proporciona una contraseña predeterminada.
3. En esta carpeta, copia la configuración de ejemplo y completa localmente los datos de conexión:

   ```powershell
   Copy-Item .env.example .env
   notepad .env
   ```

   Define `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` con los valores de tu instalación PostgreSQL. **No compartas ni confirmes el archivo `.env`**: está excluido de Git.
4. Instala Laravel y genera la clave de cifrado de esta instalación:

   ```powershell
   composer install
   php artisan key:generate
   ```

5. Crea las tablas y carga el catálogo de demostración:

   ```powershell
   php artisan migrate --seed
   ```

   El ejemplo añade el cine, una sala con asientos numerados, una película, sus géneros, reparto ordenado, clasificación, tarifa, precios para todos los días y una función para el día siguiente. No crea usuarios de demostración ni credenciales compartidas. Los tres roles se cargan mediante la migración.
6. Crea tu cuenta de administrador. El comando solicita la contraseña de forma oculta y la guarda únicamente como hash:

   ```powershell
   php artisan cine:admin
   ```

   Para el personal y los clientes, el administrador puede usar **Usuarios → Nuevo usuario**. Los registros públicos solo crean cuentas de cliente.
7. (Opcional, recomendado para probar recuperación por correo) Configura los valores SMTP `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` y `MAIL_FROM_NAME` en `.env`. En modo local, la configuración `MAIL_MAILER=log` escribe los enlaces de recuperación en `storage/logs/laravel.log` y no envía correo.
8. Inicia el servidor local:

   ```powershell
   php artisan serve
   ```

   Abre <http://127.0.0.1:8000>. Inicia sesión con la cuenta de administrador creada en el paso 6.

## Pruebas

La suite usa una base de datos PostgreSQL separada llamada `cine_app_test`. Créala y configura sus credenciales en las variables de entorno `DB_*` antes de ejecutar:

```powershell
php artisan test
```

La prueba de registro verifica que nunca se puede asignar el rol de administrador desde el formulario público y que las contraseñas no se almacenan en texto plano. No ejecutes las pruebas contra datos que necesites conservar: `RefreshDatabase` recrea las tablas de la base de pruebas.

## Funcionalidades

- Inicio/cierre de sesión, registro de clientes y recuperación de contraseña con enlaces temporales de Laravel. Formularios con CSRF, límites de intentos, política mínima de contraseña y mensajes de validación.
- Roles `administrador`, `empleado` y `cliente`, aplicados en las rutas del servidor. El comando interactivo permite crear el primer administrador sin credenciales predeterminadas.
- CRUD de usuarios (administrador), películas (administrador) y funciones (administrador/empleado), además de cartelera, selección gráfica de asientos y consulta de reservas.
- Una transacción bloquea la función al confirmar una reserva y PostgreSQL impone una restricción `UNIQUE (screening_id, seat_id)`. Así, dos clientes no pueden reservar el mismo asiento para la misma función aunque envíen solicitudes concurrentes. Cada entrada conserva el precio pagado en ese momento.
- Catálogo normalizado de cines, salas, asientos, películas, géneros, directores, actores, clasificaciones, tarifas, funciones y reservas.

## Esquema relacional

El modelo incorpora el diagrama de referencia: `roles` define los permisos y `users` enlaza el rol mediante una clave foránea. Los datos específicos de clientes, empleados y administradores viven en `customer_profiles`, `employee_profiles` y `admin_profiles`; el cliente guarda fecha de nacimiento, carné estudiantil y condición de jubilación, y el personal puede asociarse a un cine. `activity_logs` registra usuario, acción, tabla afectada, ruta y fecha, sin almacenar los datos enviados en los formularios.

Las relaciones de muchos a muchos (películas/géneros, películas/directores y películas/actores) usan tablas puente; el reparto conserva el orden de aparición. Se mantienen relaciones múltiples de géneros, directores y actores, en lugar de limitar cada película a un director o género como en el dibujo. Las salas pertenecen a un cine; los asientos, a una sala; las películas refieren su clasificación. Cada función enlaza película, sala y tarifa, y el cine se deriva de la sala. `price_rules` asigna un importe a cada combinación cine/tarifa/día de la semana (domingo = 0, sábado = 6); el precio de cada entrada queda guardado como histórico. Las reservas enlazan cliente y entradas, y pueden incluir al empleado que las registró. Una restricción única impide duplicar asiento/función y un trigger de PostgreSQL comprueba que el asiento pertenezca a la sala programada. El total se calcula sumando los precios de sus entradas, sin guardar una copia derivada. Esta organización busca conservar la tercera forma normal.

Las migraciones PostgreSQL viven en `database/migrations`. Las tablas incluyen `roles`, `users`, los perfiles, `activity_logs`, `cinemas`, `screens`, `seats`, `classifications`, `movies`, `genres`, `directors`, `actors`, `genre_movie`, `director_movie`, `actor_movie`, `tariffs`, `price_rules`, `screenings`, `reservations` y `reservation_seats`.

## Notas de despliegue

`APP_DEBUG` debe estar en `false` en producción. Usa HTTPS, configura un servicio SMTP real y guarda la clave de `APP_KEY` y las credenciales de PostgreSQL solo en variables de entorno seguras. Las credenciales, correos y contraseñas de demostración no se incluyen en el repositorio.
