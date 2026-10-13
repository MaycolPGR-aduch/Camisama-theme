# Camisama

Tienda online de prendas personalizables sobre WordPress + WooCommerce: el cliente elige el modelo,
el color y la talla, y diseña cada zona de la prenda con estampados del catálogo, texto e imágenes propias.

El repositorio contiene dos piezas:

- **`themes/camisama`**: tema de bloques (Full Site Editing).
- **`plugins/camisama-customizer`**: el personalizador (bloque, lógica de zonas, precios y producción).

Documentación: [arquitectura](docs/arquitectura.md) · [identidad visual](docs/identidad-visual.md) ·
[producción e impresión](docs/produccion-e-impresion.md) · [modelo de precios](docs/modelo-de-precios.md)

## Requisitos

- [Laragon](https://laragon.org) con PHP 8.2+ y MySQL (probado con PHP 8.3 y MySQL 8.4)
- Composer 2
- Node.js 20+ (ver `.nvmrc`)
- PowerShell 7

## Puesta en marcha

```bash
composer install
```

```bash
npm install
```

```bash
npm run build
```

Con Laragon iniciado ("Iniciar todo"):

```bash
pwsh bin/setup-local.ps1
```

El script descarga WordPress en `C:\laragon\www\camisama`, crea la base de datos, instala WordPress
y WooCommerce, enlaza el tema y el plugin del repositorio dentro de `wp-content` y los activa. Se puede
volver a ejecutar sin problema. Las credenciales del administrador quedan en `.env.local` (no se versiona).

Si Laragon ya estaba iniciado antes de crear la carpeta, pulsa **Recargar** en Laragon para que cree
el host `camisama.test`.

- Tienda: http://camisama.test
- Administración: http://camisama.test/wp-admin/

## Desarrollo

| Comando | Qué hace |
|---|---|
| `npm start` | Compila los assets del plugin en modo observación |
| `npm run build` | Compilación de producción |
| `npm run lint` | ESLint + Stylelint |
| `npm run format` | Formatea el JS con Prettier |
| `composer lint` | WordPress Coding Standards (`composer lint:fix` corrige lo automático) |
| `composer analyse` | PHPStan |
| `composer test` | PHPUnit |
| `composer check` | Lint + análisis + pruebas, igual que la CI |

WP-CLI está incluido como dependencia de desarrollo y lee la ruta del sitio de `wp-cli.local.yml`:

```bash
php vendor/wp-cli/wp-cli/php/boot-fs.php plugin list
```

Los cambios en `themes/` y `plugins/` se ven al instante en el sitio local, porque las carpetas de
`wp-content` son enlaces al repositorio.

## Flujo de trabajo

- `main` siempre estable; cada cambio en una rama (`feature/…`, `fix/…`) con pull request.
- La CI de GitHub Actions ejecuta estándar de código, PHPStan, PHPUnit, lint y compilación.

## Licencia

GPL-2.0-or-later, como WordPress.
