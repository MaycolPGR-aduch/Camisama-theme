# Instalar la tienda en el servidor (Hostinger)

Instalación limpia en el staging de Hostinger (y, al abrir, en producción). Plan general y por qué en
[docs/subida-hostinger.md](../../docs/subida-hostinger.md).

## 1. Paquetes

En GitHub → Actions → **Paquetes** → *Run workflow*, o etiquetando una versión (`git tag v0.3.0`
y `git push --tags`, que además la publica en *Releases*). Se descargan tres archivos:

| Archivo | Qué es | Dónde va |
|---|---|---|
| `camisama-customizer-<versión>.zip` | El plugin, ya compilado | Al servidor (lo instala el script) |
| `camisama-<versión>.zip` | El tema | Al servidor (lo instala el script) |
| `camisama-carga-<versión>.zip` | Scripts, estampados y fotos | Al servidor, **fuera** de `public_html` |

En local también se pueden crear con `npm run build` y `python bin/paquete.py`.

## 2. En hPanel

1. Instalar WordPress en el sitio (o en el staging), con un usuario administrador que **no** se
   llame `admin` y una contraseña larga.
2. En `wp-config.php` (Administrador de archivos), antes de `/* That's all, stop editing! */`:

   ```php
   define( 'WP_ENVIRONMENT_TYPE', 'staging' );   // 'production' en el sitio real
   define( 'WP_DEBUG', false );
   define( 'SCRIPT_DEBUG', false );
   define( 'DISALLOW_FILE_EDIT', true );
   define( 'DISABLE_WP_CRON', true );
   ```

3. Avanzado → **Cron Jobs**: cada 5 minutos, `php /home/USUARIO/domains/DOMINIO/public_html/wp-cron.php`
   (la ruta exacta sale en el administrador de archivos).
4. Avanzado → **SSH**: activarlo y anotar usuario, servidor y puerto.

## 3. Por SSH

Subir los tres zips (por ejemplo a `~/camisama/`) y:

```bash
cd ~/camisama
unzip -o camisama-carga-*.zip
cd camisama-carga
bash bin/produccion/configurar.sh ~/domains/DOMINIO/public_html ../camisama-customizer-*.zip ../camisama-*.zip
```

Deja: idioma `es_MX` y hora de Lima; WooCommerce; el plugin y el tema; la tienda para Perú en soles;
la entrega (Lima Metropolitana y Callao, y recojo, con datos **provisionales**); las páginas legales
como **borradores para revisar**; "Sigue tu pedido"; los 4 modelos con estampados y fotos. Quita el
contenido de ejemplo de WordPress y los plugins Hello Dolly y Akismet. La tienda queda en
**Próximamente**: solo la ve quien inicia sesión.

Se puede repetir, por ejemplo con una versión nueva: no duplica productos ni páginas y no pisa las
páginas ya publicadas ni la visibilidad de la tienda.

Si al final avisa de que no puede regenerar el `.htaccess` y los enlaces dan 404: Ajustes →
Enlaces permanentes → Guardar.

## 4. A mano, en el admin

- **LiteSpeed Cache** (viene en Hostinger):
  - Caché → Excluir: URI `/wp-json/wc/store/` y cadena de consulta `camisama_edit` (el carrito, el
    pago y mi cuenta ya los excluye solo).
  - Optimización de página → JS: **no** retrasar ni combinar `customizer/view.js` ni el script en
    línea que contiene `camisama-flow`. Si se activa "Retrasar JS", añadir ambos a sus excepciones.
  - Sin caché para usuarios con sesión.
- **Correo**: un plugin de SMTP (por ejemplo WP Mail SMTP) con el correo del dominio; Hostinger
  limita el envío por PHP a 100 correos al día. Probar los correos de WooCommerce.
- **Pagos**: datos de la cuenta en WooCommerce → Ajustes → Pagos → Transferencia bancaria, y la
  pasarela en modo de prueba.
- **Ajustes → Lectura**: en el staging, "Disuadir a los motores de búsqueda".

## 5. Comprobar

La lista está en el punto 3 de [docs/subida-hostinger.md](../../docs/subida-hostinger.md). Lo
imprescindible: una compra con envío y otra con recojo, subir una foto desde un móvil, editar desde
el carrito, los correos, y que `https://DOMINIO/wp-content/uploads/camisama-production/` responda
**403**.
