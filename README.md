# gastos — Plan mensual

Aplicación PHP (MVC ligero, sin frameworks ni Composer) + MariaDB para planear
gastos mensuales: ingresos, pagos fijos, compras, gastos semanales/diarios y
deudas, con recálculo instantáneo, gráfico día a día y exportación a PDF.

Es la migración del prototipo standalone `docs/plan-mensual.html` (que se
conserva como referencia) a una app con persistencia real en base de datos.

## Estructura

```
public/                 <- document root (esto es lo que apunta el subdominio)
  index.php             <- front controller / router
  .htaccess             <- reescritura de rutas (Apache)
  assets/css/app.css    <- todo el CSS del proyecto
  assets/js/app.js      <- lógica dinámica (recalculo + llamadas a la API)
app/
  Config/
    config.example.php  <- plantilla de credenciales de BD
    config.php           <- (no se sube a git) credenciales reales
  Core/                  <- Router, Controller, Model, Database (PDO)
  Models/                <- Plan, IngresoExtra, Item
  Controllers/           <- PlanController, IngresoExtraController, ItemController
  Views/                 <- layout.php + vistas del plan
  routes.php
database/
  schema.sql             <- crea las tablas
  seed.sql                <- datos de ejemplo (los del PDF)
```

## Requisitos

- PHP 8.2+ (con extensión `pdo_mysql`)
- MariaDB 10.4+
- Apache con `mod_rewrite` (lo típico en Plesk)

No requiere Composer, Node ni paso de build: Tailwind se carga por CDN y el
resto es PHP y JS planos, listos para subir tal cual.

## Despliegue en Plesk (subdominio)

1. **Crear el subdominio** en Plesk (Dominios > Añadir subdominio), por ejemplo
   `plan.tudominio.com`.
2. **Document root**: en la configuración del subdominio (Hosting Settings),
   cambia el *document root* para que apunte a la carpeta `public/` del
   proyecto (ej. `plan.tudominio.com/public`), no a la raíz del repo. Así el
   código de `app/` queda fuera del alcance del navegador.
3. **Subir los archivos**: sube todo el contenido del repo (vía Git, FTP o el
   Administrador de archivos de Plesk) a la carpeta del subdominio.
4. **Crear la base de datos**: en Plesk > Bases de datos > Añadir base de
   datos, crea una base MariaDB y un usuario con todos los permisos sobre
   ella. Anota host, nombre, usuario y contraseña.
5. **Importar el esquema**: entra a phpMyAdmin desde Plesk y ejecuta, en este
   orden:
   - `database/schema.sql`
   - `database/seed.sql` (opcional, carga los datos de ejemplo del PDF)
6. **Configurar credenciales**: copia `app/Config/config.example.php` a
   `app/Config/config.php` y coloca ahí los datos reales de la base de datos
   (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`). Este archivo no se versiona en
   git, así que hazlo directamente en el servidor la primera vez.
7. **Versión de PHP**: en Plesk > PHP Settings del subdominio, selecciona
   PHP 8.2 o superior.
8. **Comprobar `mod_rewrite`**: Plesk lo trae activo por defecto en Apache; si
   usas solo Nginx como servidor web, añade en "Additional nginx directives"
   algo equivalente a:
   ```
   location / {
     try_files $uri $uri/ /index.php?$query_string;
   }
   ```
9. Abre el subdominio en el navegador: debería redirigir a `/plan/1` con el
   plan de ejemplo cargado (o uno vacío si no ejecutaste `seed.sql`).

## Desarrollo local

```bash
cp app/Config/config.example.php app/Config/config.php
# edita config.php con tus credenciales locales de MariaDB
php -S localhost:8000 -t public
```

## Notas

- Cada "plan" es un mes independiente (puedes crear varios desde "+ Nuevo
  plan" y cambiar entre ellos con el selector). El standalone original solo
  manejaba un mes en memoria del navegador; aquí queda guardado en la base de
  datos.
- Todos los cambios (agregar/editar/eliminar ítems, ingresos) se guardan
  automáticamente contra la API (`/api/...`) sin recargar la página, igual de
  fluido que el prototipo original.
- "Exportar a PDF" sigue usando `html2pdf.js` desde el navegador, igual que en
  el standalone.
