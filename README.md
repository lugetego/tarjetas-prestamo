# Tarjetas de préstamo

Sistema web para generar las fichas bibliográficas (3×5", 7.62 × 12.7 cm) que
reemplaza al `.pub` de Microsoft Publisher usado anteriormente. Permite
importar remisiones desde un Excel o capturarlas a mano, editar los datos antes
de imprimir, y descargar un PDF listo para imprimir en tarjetas físicas.

Varias bibliotecas pueden usar el mismo sistema: cada usuario pertenece a una
biblioteca y solo ve/administra las remisiones de la suya.

**Stack:** Symfony 7.4 LTS, MySQL, PhpSpreadsheet (lectura de `.xlsx`), Dompdf
(generación del PDF).

## Instalación en un servidor nuevo

```bash
git clone https://github.com/lugetego/tarjetas-prestamo.git
cd tarjetas-prestamo
composer install --no-dev --optimize-autoloader
```

Crear `.env.local` (no se versiona) con la base de datos y el secreto de la
aplicación:

```bash
APP_SECRET=$(php -r "echo bin2hex(random_bytes(16));")
echo "APP_SECRET=${APP_SECRET}" > .env.local
echo 'DATABASE_URL="mysql://usuario:contraseña@127.0.0.1:3306/tarjetas_prestamo?serverVersion=8.0&charset=utf8mb4"' >> .env.local
```

Crear la base de datos y correr las migraciones (requiere un usuario de MySQL
con permisos para crear/alterar tablas, aunque sea solo para este paso):

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
```

Crear el primer usuario (y su biblioteca):

```bash
php bin/console app:create-user <usuario> <contraseña> "<nombre de la biblioteca>"
```

Para una biblioteca adicional, usa el mismo comando con un nombre de
biblioteca distinto; para un segundo usuario de una biblioteca ya existente,
repite el nombre exactamente igual (no distingue mayúsculas/minúsculas).

## Permisos de archivos

El servidor web necesita poder **escribir** en `var/` y `storage/uploads/`
(caché, logs, y los Excel que se suben). Una forma de lograrlo sin abrir
permisos de más es agregar al usuario del servidor web (p. ej. `www-data`) al
grupo del dueño del proyecto:

```bash
sudo usermod -aG <grupo-del-proyecto> www-data
```

## Apache

Plantilla de vhost en [deploy/tarjetas-prestamo.conf](deploy/tarjetas-prestamo.conf)
— reemplaza `CAMBIAR-por-dominio-o-ip` y las rutas antes de instalarla:

```bash
sudo cp deploy/tarjetas-prestamo.conf /etc/apache2/sites-available/tarjetas-prestamo.conf
sudo a2ensite tarjetas-prestamo
sudo apache2ctl configtest
sudo systemctl reload apache2
```

El `DocumentRoot` **debe** apuntar a `public/`, nunca a la raíz del proyecto
— ahí es donde vive `.env.local`, `storage/uploads/` y el código fuente.

## Base de datos en producción

El usuario de MySQL que usa la aplicación en el día a día (`DATABASE_URL` en
`.env.local`) solo debería tener `SELECT, INSERT, UPDATE, DELETE` sobre su
base — **no** permisos para crear/alterar tablas. Cuando se agregue una
migración nueva, se corre una sola vez con un usuario con esos permisos:

```bash
DATABASE_URL="mysql://usuario-admin:...@127.0.0.1:3306/tarjetas_prestamo?..." \
  php bin/console doctrine:migrations:migrate --no-interaction
```

## Actualizar el código en producción

```bash
git pull
composer install --no-dev --optimize-autoloader
php bin/console cache:clear --env=prod
# si hay migraciones nuevas, correrlas como se indica arriba
sudo systemctl reload apache2
```
