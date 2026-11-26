# Plan de subida a Hostinger

Del 26 de noviembre de 2026. Los datos de Hostinger salen de sus páginas oficiales de esa semana
(enlaces al final); conviene confirmarlos en hPanel al contratar, porque cambian a menudo.

**Decidido:** plan **Cloud Startup** e **instalación limpia** (WordPress nuevo + script de
configuración). Instrucciones paso a paso del servidor en
[bin/produccion/LEEME.md](../bin/produccion/LEEME.md).

## Resumen

1. **Contratar** un plan con staging y copias diarias (Unlimited, o Cloud Startup si se espera
   tráfico), el dominio y una cuenta de pasarela de pago peruana.
2. **Preparar el código** para producción: paquete con lo compilado, ajustes de caché y un script
   que configure la tienda sin los datos de demostración.
3. **Montar el staging** en Hostinger, llevar el contenido y probar compras de verdad con la
   pasarela en modo de prueba.
4. **Completar lo que es del negocio** (legales, pagos, entrega, fotos) en el staging.
5. **Publicar**: copia de seguridad, staging → producción, dominio, SSL, un pedido real pequeño.
6. **Después de abrir**, la base de datos de producción manda: el staging solo sube archivos.

## 1. Qué contratar

| Plan | CPU / RAM | PHP workers | Staging | Copias | SSH y Git | Encaje |
|---|---|---|---|---|---|---|
| Premium | 1 / 2 GB | 40 | No | Semanales | Sí | Corto: sin staging ni copias diarias |
| Unlimited | 2 / 3 GB | 60 | Sí | Diarias | Sí | Suficiente para empezar |
| **Cloud Startup** | 4 / 4 GB | 100 | Sí | Diarias y a demanda | Sí | **Elegido**: más margen desde el día uno |

- La tienda pide PHP 8.2 o más, subidas de 15 MB y algo de CPU al generar vistas previas y archivos
  de impresión (eso se hace en el navegador del taller, no en el servidor). Los tres planes sobran
  en límites de PHP (1 GB o más de memoria y subida).
- **Dominio:** decidir si `.pe` o `.com`. Hostinger quizá no venda `.pe`: se puede comprar en otro
  registrador y apuntarlo.
- **Correo:** el envío por PHP de Hostinger está limitado a 100 correos al día. Para los correos de
  pedidos hace falta SMTP: el correo de Hostinger del propio dominio basta para empezar; un servicio
  transaccional (Brevo, Postmark…) entrega mejor. Configurar SPF, DKIM y DMARC del dominio.
- **Pasarela de pago:** cuenta comercial con Culqi, Izipay, Niubiz o Mercado Pago. Requisito
  técnico: su plugin debe ser compatible con el **pago en bloques** de WooCommerce, que es el que usa
  la tienda. Se comprueba en el staging antes de decidir. Para Yape, confirmar con cada proveedor.
- **Comprobantes SUNAT:** RUC y un proveedor de boletas y facturas electrónicas con plugin para
  WooCommerce (lo elige el negocio con su contador).

Lo que exige pagar, firmar o poner contraseñas lo haces tú; yo preparo y compruebo lo demás.

## 2. Preparar el código (hecho el 26 de noviembre, salvo lo marcado)

En el repositorio no están lo compilado (`plugins/camisama-customizer/build/`) ni las dependencias
(`vendor/`), y el despliegue con Git de Hostinger no compila. Por eso:

- [x] **Paquetes** (`bin/paquete.py` y la acción *Paquetes* de GitHub): plugin compilado con su
      `vendor/`, tema, y la carga (scripts, estampados y fotos), al etiquetar una versión o a mano.
- [x] **Script de instalación** (`bin/produccion/configurar.sh` y `configurar.php`): idioma,
      WooCommerce, plugin y tema, ajustes de Perú, entrega, páginas, los 4 modelos con estampados y
      fotos, y la tienda en "Próximamente". Probado de punta a punta en un WordPress nuevo fuera de
      `.test` (y repetido sin duplicar nada). `import-fotos-modelos.php` fuera de local pide
      "confirmar".
- [x] **Caché:** la ficha con `?camisama_edit=` envía "no guardar" (WordPress, LiteSpeed y proxies).
- [x] **Emojis de WordPress** desactivados en la tienda.
- [ ] **Despliegue automático por SSH** al staging desde GitHub (cuando haya servidor).
- [x] **Constantes de producción** documentadas (no se versionan):

```php
define( 'WP_ENVIRONMENT_TYPE', 'production' );   // staging en el staging
define( 'WP_DEBUG', false );
define( 'SCRIPT_DEBUG', false );                 // en local está activo: sirve JS sin minificar
define( 'DISALLOW_FILE_EDIT', true );
define( 'DISABLE_WP_CRON', true );               // con el cron real de hPanel
```

- [x] **Cron real** (documentado; se pone en hPanel) cada 5 minutos: `php /home/USUARIO/public_html/wp-cron.php`. Lo usan
      WooCommerce (Action Scheduler) y la limpieza diaria de subidas sin pedido.

## 3. Staging en Hostinger

1. Instalar WordPress con el instalador de hPanel y crear el staging.
2. Seguir [bin/produccion/LEEME.md](../bin/produccion/LEEME.md): constantes, cron, SSH y el script.
3. Llevar el contenido: **instalación limpia** con `bin/produccion/configurar.sh` (decidido). No
   viajan los pedidos de prueba, el producto piloto ni las maquetas dibujadas.
4. **LiteSpeed Cache** (viene en Hostinger):
   - no cachear carrito, pago, mi cuenta ni `/wp-json/wc/store/`, ni `?camisama_edit=`;
   - no retrasar ni combinar el script en línea que añade `camisama-flow` ni
     `build/blocks/customizer/view.js` (si se retrasan, la ficha vuelve a saltar);
   - sin caché para usuarios con sesión iniciada.
5. **SMTP** con un plugin (por ejemplo WP Mail SMTP) y prueba de cada correo de WooCommerce.
6. **Comprobaciones** en el staging (las mismas que se hacen en local, más las del servidor):
   - [ ] compra completa con envío y con recojo, con la pasarela en modo de prueba y con
         transferencia;
   - [ ] subir una foto de 15 MB desde un móvil real; editar desde el carrito; "Sigue tu pedido";
   - [ ] archivos de impresión a 300 DPI desde un pedido, y que
         `/wp-content/uploads/camisama-production/…` devuelva **403** desde fuera (la protección
         es un `.htaccess`; Hostinger usa LiteSpeed, que lo lee, pero hay que comprobarlo);
   - [ ] la limpieza de subidas a los 30 días programada en el cron;
   - [ ] correos de pedido nuevo, en espera, completado y al administrador;
   - [ ] crítica de toda la web sobre el staging y prueba en un móvil real;
   - [ ] el staging no se indexa (Ajustes → Lectura) y pide contraseña si es posible.

## 4. Lo que es del negocio (antes de abrir)

Sale de `docs/estado-de-la-web.md`; nada de esto lo puede inventar la web:

- [ ] Pasarela de pago real y, mientras tanto, datos de la cuenta para transferencias.
- [ ] Textos legales revisados por un abogado (razón social, RUC, dirección, plazos, cambios y
      devoluciones), **Libro de Reclamaciones virtual** y lo que pida la ley de datos personales
      (Ley 29733). Quitar el aviso de borrador.
- [ ] Contacto: correo, WhatsApp, dirección y horario del taller.
- [ ] Tarifas de envío por distrito y plazos reales (`DeliveryRates.php`, `PaymentTimes.php`).
- [ ] IGV y comprobantes electrónicos.
- [ ] Fotos de las prendas: reales, o comprobar prenda en mano las generadas con IA; zonas de
      impresión confirmadas por el taller; medidas reales de las tallas.
- [ ] Logotipo definitivo.
- [ ] Qué hacer con los 8 pedidos de prueba locales (no viajan si se instala limpio).

## 5. Publicar

1. Copia de seguridad de producción (aunque esté vacía) y del staging.
2. Primera vez: publicar el staging completo (archivos y base de datos).
3. Apuntar el dominio, SSL y redirección a `https`; comprobar que no queda ninguna URL del staging.
4. Ajustes → Lectura: permitir indexar. Alta en Google Search Console y mapa del sitio.
5. Un pedido real pequeño con la pasarela, y su reembolso.
6. Vigilar el primer día: errores de PHP, correos que llegan, pedidos y subidas.

## 6. Después de abrir

- **La base de datos de producción manda.** Publicar desde el staging la base de datos borraría los
  pedidos y clientes nuevos. Desde el primer pedido, el staging solo sube **archivos** (código), y
  la configuración se cambia en producción o con scripts.
- Código: cada versión, paquete nuevo → staging → comprobación → producción.
- Copias diarias de Hostinger y una copia propia semanal fuera del servidor.
- Actualizaciones de WordPress, WooCommerce y la pasarela primero en el staging.

## Fuentes

- Límites de los planes (v6, actualizada el 24/11/2026):
  <https://www.hostinger.com/support/6976044-parameters-and-limits-of-hosting-plans-in-hostinger/>
- Staging: <https://www.hostinger.com/support/5720286-how-to-create-a-wordpress-staging-environment/>
  y <https://docs.hostinger.com/wordpress/staging>
- Cron: <https://docs.hostinger.com/websites/cron-jobs>
- Planes: <https://www.hostinger.com/pricing>
- Git: <https://docs.hostinger.com/websites/git>; SSH:
  <https://www.hostinger.com/support/1583245-how-to-enable-ssh-access-at-hostinger/>
- Correo de Hostinger: <https://www.hostinger.com/support/4625828-parameters-and-limits-of-hostinger-email/>
