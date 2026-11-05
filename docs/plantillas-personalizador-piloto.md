# Piloto de plantillas fotográficas

Implementado el 5 de noviembre de 2026. El usuario confirmó que las medidas todavía son de referencia y pidió mantener el piloto.

## Resultado y alcance

- Producto local `CAM-CLASICA-PILOTO-V1`, ID 182, oculto del catálogo y buscador: `http://camisama.test/producto/camiseta-clasica-piloto-visual/`.
- Blanco/negro, frente/espalda y diez variantes de talla/color. Precios y atributos tomados del modelo clásico existente.
- La configuración de `CAM-CLASICA` conserva sus adjuntos, colores y zonas; se comprobó contra la copia inicial al importar y restaurar el piloto. No se reescribieron pedidos ni productos anteriores.
- Cuatro derivados WebP con alfa, 1254 × 1254, 270 464 bytes en conjunto: aproximadamente 94 % menos que los originales. Los originales permanecen en propuestas.
- Zonas comunes calibradas visualmente. Se conservan IDs y referencias: frente/espalda 30 × 40 cm, mangas 9 × 9 cm, nuca 8 × 5 cm. Cada rectángulo mantiene su proporción y queda completamente sobre píxeles con alfa ≥250 en ambos colores. No equivale a validación física.

El manifiesto en `assets/customizer/clasica/v1/manifest.json` registra imágenes, hashes y geometría. El importador reutiliza cuatro adjuntos y diez variantes al repetirse; guarda una copia inicial de la clásica en `_camisama_pilot_v1_source_backup` sin modificarla.

## Cambios del personalizador

- Editor y miniaturas JPEG usan el fondo papel del sitio; los PNG de impresión siguen transparentes.
- Selección estricta por color: una maqueta ausente ya no muestra silenciosamente otro color.
- El lienzo limpia el fondo anterior, informa carga/ausencia/error y permite reintentar una descarga fallida.
- La compra comprueba que estén configuradas todas las vistas del color y que se pueda generar la vista previa. El envío móvil captura errores de generación.
- Se corrigió un fallo descubierto al probar compras consecutivas: comprar una prenda lisa después de una personalizada conservaba campos ocultos del diseño anterior. El puente de WooCommerce sustituye o elimina exclusivamente sus tres campos en cada envío; conserva talla, color y cantidad.

Estos cambios del motor son compartidos por los productos que usan el personalizador. Las fotografías y geometría nuevas pertenecen únicamente al piloto.

## Validación

- 81 pruebas JS, incluidas selección exacta de color, imágenes inválidas/default y regresiones de envíos consecutivos.
- 126 pruebas PHP y 4238 aserciones; PHPStan y PHPCS correctos al cierre. El fallo inicial de `CardPrint` dejó de aparecer al actualizarse ese trabajo paralelo; este piloto no modificó ese código.
- Lint JS/CSS y build correctos. Webpack conserva avisos de tamaño de Fabric y del módulo administrativo de producción.
- Impeccable: un análisis manual de los tres archivos visuales modificados, cero antipatrones detectados por regex y tres avisos previos de tokens. No se ejecutó un detector con página parseada; se revisó la interfaz en navegador.
- Navegador: blanco/negro y ambas caras, selección de talla, estampado, texto, conservación del importe al cambiar color, edición desde el carrito y recuperación de diseño/color/talla. Anchos efectivos 320, 390, 768 y 1280 px; sin desbordamiento del documento en las mediciones realizadas.
- Exportación real con Fabric: cuatro JPEG 640 × 640 con esquina aproximadamente RGB 247/244/238 opaca. Cinco PNG de texto rojo, uno por zona, con alfa real, solo tinta y tamaño esperado a 300 DPI. Resultado en `assets/customizer/clasica/v1/verificacion/exportacion.txt`.
- Ausencia simulada de frente negro exclusivamente en el piloto: aviso visible, sin sustituirlo por blanco e impidiendo guardar la compra. Se restauró con el importador; mantuvo los mismos IDs de adjunto y producto.
- Regresión reproducida y corregida en navegador, sin recargar entre compras: diseño a S/ 53,90 y posterior camiseta lisa a S/ 39,90, en dos líneas distintas. La línea lisa no contiene personalización ni recargo; evidencia en `verificacion/carrito-regresion.png`.
- Se retiraron exclusivamente los artículos creados en estas pruebas y se verificó que el carrito volviera a estar vacío. El piloto quedó abierto con un ejemplo visual, sin añadirlo de nuevo al carrito.

## Pendientes antes de publicar

1. Confirmar las cinco áreas con el taller y la prenda física, incluidas costuras, posición y diferencias por talla.
2. Retocar el alfa del blanco: los nuevos intentos de ImageGen conservan flecos y quedaron archivados, sin activarse.
3. Preparar rojo y azul marino antes de sustituir la clásica comercial, que tiene cuatro colores.
4. Validar la carga de una fotografía desde el selector de archivos, pedidos previos y un pedido nuevo completo. El selector de archivos de la herramienta no respondió; no se afirma haber comprobado ese recorrido. No se creó un pedido ni se realizó un pago.
5. La representación sigue siendo plana: no hay deformación ni sombreado de la impresión según pliegues.
6. Las mangas están a otra escala que el frente: 9 cm ocupan 75 px frente a los 113 que les tocarían a 12,5 px/cm, y un diseño en la manga se ve un tercio más pequeño. Hay tela para 113 px (137-151 px centrados en la zona), pero falta comprobar las costuras antes de cambiar su `rect` a 0,09.

## Cómo comprobarlo

Abrir el enlace local del piloto, elegir colores y vistas, añadir un estampado o texto, elegir talla y añadir al carrito. Confirmar su miniatura, precio y «Editar diseño». Después de comprar un diseño, empezar otro y elegir «Comprar sin diseño»: esa línea debe costar solo la prenda, sin impresión anterior.

Para repetir la exportación:

```powershell
node node_modules/webpack-cli/bin/cli.js --config bin/qa/renderer.cjs
```

Abrir `http://camisama.test/wp-content/plugins/camisama-customizer/build/qa/index.html`, introducir el ID local 182 y pulsar «Comprobar vistas e impresión». Usa los datos de la API y las mismas funciones `renderPreview()` y `renderZone()` del editor; no crea pedidos. La página se compila fuera del bundle de tienda y un nuevo build puede eliminarla.

Para reproducir una ausencia, ejecutar `bin/qa/pilot-missing-mockup.php` con WP-CLI como administrador y recargar el piloto en negro/frente. Restaurar inmediatamente ejecutando `bin/import-customizer-pilot.php`; ambos scripts se limitan al sitio local `.test`.

Para retirar el piloto, pasarlo a borrador. No borrar medios referenciados por registros de prueba. La clásica original no necesita reversión porque su configuración no se cambió.
