# Arquitectura

## Piezas

| Pieza | Carpeta | Responsabilidad |
|---|---|---|
| Tema `camisama` | `themes/camisama` | Presentación: `theme.json` (tokens de diseño), plantillas, partes, patrones y estilos de WooCommerce |
| Plugin `camisama-customizer` | `plugins/camisama-customizer` | Funcionalidad: modelos, vistas, zonas, estampados, editor, precios, carrito y archivos de producción |
| WooCommerce | (plugin oficial) | Catálogo, carrito, checkout, pagos, pedidos, correos, cupones |

El personalizador vive en el plugin para que un cambio de tema no rompa la tienda ni los pedidos ya
hechos. El plugin expone el bloque `camisama/customizer`, que el tema coloca en la ficha de producto.

## Tema

```
themes/camisama/
├── theme.json        # paleta, tipografías (con @font-face local), espaciados, estilos de bloques
├── style.css         # solo lo que theme.json no expresa (estilos de listas, tarjetas de producto)
├── templates/        # portada, tienda, producto, carrito, checkout, página, entrada, 404
├── parts/            # cabecera, cabecera del checkout, pie (cada uno carga su patrón)
├── patterns/         # secciones en PHP: permiten traducir textos y generar URLs
└── assets/           # fuentes e imágenes
```

Las secciones viven en `patterns/*.php` y no en HTML estático para poder usar `__()` y las URLs
reales de WooCommerce (`camisama_wc_page_url()`). El checkout usa una cabecera mínima, sin menú, para
que el cliente no se distraiga al pagar.

## Idioma y país

La tienda vende en Perú, en soles. WordPress y WooCommerce no tienen traducción completa para
`es_PE`, así que se usa `es_MX` (español latinoamericano, completa y al día). Si algún término no
encaja en Perú (por ejemplo "colonia" en la dirección), se ajustará con un filtro de traducción.

## Plugin

```
plugins/camisama-customizer/
├── camisama-customizer.php   # cabecera del plugin y arranque
├── src/                      # PHP con PSR-4: Camisama\Customizer\…
│   ├── Config/               # vistas, zonas, plantillas (dominio puro) y su repositorio
│   ├── Pricing/              # escalones de precio, importes en céntimos
│   ├── Designs/              # catálogo de estampados (tipo de contenido y consultas)
│   ├── Security/, Media/     # saneado de SVG y su soporte en la biblioteca de medios
│   ├── Admin/                # caja "Personalizador" del producto y ajustes de precios
│   ├── Rest/                 # API camisama/v1 (solo lectura)
│   └── Production/           # conversión cm ↔ px para archivos de impresión
├── client/                   # JS/SCSS (React), compilado con @wordpress/scripts (webpack.config.js)
│   ├── blocks/customizer/    # bloque del personalizador en la tienda
│   └── admin/product-config/ # editor visual de vistas y zonas
├── build/                    # salida de la compilación (no se versiona)
└── tests/                    # PHPUnit (las pruebas JS van junto al código: *.test.js)
```

- PHP 8.2+, `declare(strict_types=1)`, WordPress Coding Standards, PHPStan nivel 6.
- Compatible con HPOS y con el carrito/checkout de bloques (declarado en `Plugin::declare_woocommerce_compatibility`).
- `Requires Plugins: woocommerce`: WordPress no deja activarlo sin WooCommerce.
- La lógica de dominio (`Config`, `Pricing`, `Security`, `Production`) no depende de WordPress y
  tiene pruebas unitarias; las clases de integración solo traducen entre WordPress y el dominio.

## Modelo de datos

Pensado como **producto personalizable** genérico; camisetas es solo el primer tipo.

| Concepto | Almacenamiento | Estado |
|---|---|---|
| Modelo (clásica, oversize…) | Producto variable de WooCommerce (atributos globales Color y Talla) | ✅ |
| Configuración del personalizador | JSON en el meta `_camisama_customizer` del producto (`ProductConfig`) | ✅ |
| Vistas | Frente, espalda…: una maqueta por color (`View::$mockups`, slug del color → adjunto) | ✅ |
| Zonas | Rectángulo relativo a la maqueta (0–1) + tamaño real en cm (`Zone`) | ✅ |
| Precios por tamaño | Opción `camisama_customizer_price_tiers`, importes en céntimos (`PriceTable`) | ✅ |
| Estampados | Tipo de contenido `camisama_design`: archivo = imagen destacada (SVG o PNG), categorías `camisama_design_cat`, recargo premium en `_camisama_premium` | ✅ |
| Diseño del cliente | JSON en una tabla propia (guardar, compartir, reimprimir) | fase 3–5 |
| Pedido | JSON + vistas previas + archivos de producción por zona en el artículo del pedido | fase 4 |

El rectángulo de una zona representa exactamente su tamaño en cm: así el editor sabe a qué escala
dibujar cada diseño, y el escalón de precio se calcula con el lado mayor de lo que el cliente coloca.

## Administración

- **Productos → editar → caja "Personalizador":** activar, aplicar una plantilla (adulto o niños),
  elegir la maqueta de cada vista y color desde la biblioteca de medios, y mover/redimensionar las
  zonas sobre la imagen (también con el teclado: flechas, Mayús para pasos largos). Al cambiar el
  tamaño en cm, el rectángulo conserva la proporción real. El servidor valida todo al guardar; si algo
  no es válido, muestra el motivo y conserva la configuración anterior.
- **Estampados:** listado con miniatura, categorías y recargo premium.
- **WooCommerce → Personalizador:** tabla de escalones de precio.
- **SVG:** solo usuarios con `manage_woocommerce` pueden subirlos, y siempre se sanean
  (`enshrined/svg-sanitize`: sin scripts, eventos ni referencias remotas).

## API REST (`camisama/v1`, pública y de solo lectura)

| Ruta | Devuelve |
|---|---|
| `GET /designs?category=&page=&per_page=` | Estampados con archivo, medidas, categorías y recargo (cabeceras `X-WP-Total`, `X-WP-TotalPages`) |
| `GET /design-categories` | Categorías con estampados |
| `GET /products/{id}/customizer` | Colores, vistas con la URL de cada maqueta, zonas con sus escalones alcanzables, precio base y moneda. 404 si el producto no es personalizable. El bloque incrusta estos mismos datos en la página |
| `POST /uploads` (campo `file`) | Sube una imagen del cliente. 201 con `{id, url, width, height, mime}`; 422 si no es válida; 429 si supera el límite |
| `POST /orders/{pedido}/items/{línea}/zones/{zona}` (campo `file`) | Solo `edit_shop_orders`: guarda el PNG de impresión de una zona (comprueba su tamaño y le escribe los 300 DPI) |

## Editor de la tienda

El bloque `camisama/customizer` envuelve a la galería en la plantilla del producto. Si el producto
es personalizable, incrusta sus datos (`CustomizerData`) y `view.js` descarga el editor con
`import()`: unos 3 KB al cargar la página, y el editor (~30 KB) más Fabric.js (~290 KB) en
`build/chunks/` con hash en el nombre. Mientras tanto, o si falla, se ve la galería.

- **Fuente de verdad:** una lista de elementos (`design`, `text`, `image`) con centro, tamaño y
  giro en cm de su zona. El lienzo (Fabric.js) solo la dibuja y devuelve los cambios en cm, así que
  el tamaño de pantalla no afecta al diseño ni al precio.
- **Restricciones:** cada objeto queda recortado a su zona; arrastrar lo detiene en el borde, y
  escalar o girar más allá de la zona vuelve al último estado válido. El tamaño real lo mide el
  lienzo (un SVG agrupado mide lo que ocupan sus trazos, no su viewBox).
- **Historial:** deshacer y rehacer (Ctrl+Z / Ctrl+Y); los cambios continuos (arrastrar,
  deslizadores, escribir) se agrupan en un solo paso.
- **Accesible sin ratón:** zona activa elegible con botones, lista de capas, tamaño y giro con
  deslizadores, flechas para mover (Mayús para pasos de 1 cm) y Suprimir para borrar.
- **Precio en vivo:** `pricing.js`, la misma regla que `DesignPricer` (casos compartidos en
  `tests/fixtures/pricing-cases.json`), sobre el precio de la variante elegida en WooCommerce.
- **Color:** sincronizado en los dos sentidos con el desplegable de variaciones (`wc-bridge.js`).
- **Texto:** 8 fuentes libres para uso comercial (`Design\Fonts`, archivos en `assets/fonts/`).
- **Imágenes propias:** aviso de calidad según la resolución efectiva (300 / 150 DPI).

## Del carrito al taller

1. **Añadir al carrito:** el editor envía con el formulario de WooCommerce el diseño (solo los
   campos que el servidor necesita) y una vista previa JPEG por cada vista con algo impreso.
2. **Validación en el servidor** (`DesignValidator`): zonas del producto, medidas finitas y dentro de
   la zona, fuentes y colores permitidos, estampados publicados e imágenes subidas que existen. Las
   URLs, medidas y recargos salen de la base de datos, y el color del diseño debe coincidir con la
   variante. Cualquier fallo se muestra al cliente y la prenda no se añade.
3. **Precio:** `DesignPricer` calcula el recargo; la línea cuesta el precio de la variante (leído de
   nuevo en cada cálculo, para no acumular) más el recargo.
4. **Pedido:** la línea guarda el diseño normalizado con copia de las zonas, el desglose y las vistas
   previas (`_camisama_design`, `_camisama_quote`, `_camisama_previews`, ocultos en la
   administración). Las imágenes y vistas previas del pedido dejan de borrarse automáticamente.
5. **Producción** (`ProductionPage`, `ProductionStore`): hoja imprimible y archivos PNG por zona a
   300 DPI generados en el navegador del taller y guardados en una carpeta protegida. Ver
   [producción e impresión](produccion-e-impresion.md).

## Imágenes de los clientes

Abierto a visitantes sin cuenta, por eso es la parte con más controles:

- **Formatos:** solo PNG, JPG y WEBP reconocidos por su contenido, no por la extensión. Hasta 15 MB,
  entre 100 px por lado y 40 megapíxeles. Los SVG de clientes no se aceptan.
- **Recodificación:** cada imagen se vuelve a guardar con GD. Desaparece cualquier contenido oculto y
  los metadatos EXIF, incluida la **ubicación GPS** de las fotos del móvil, y se corrige la rotación.
- **Almacenamiento:** `uploads/camisama-uploads/AAAA/MM/` con nombres aleatorios de 128 bits, fuera
  de la biblioteca de medios. La carpeta prohíbe listar archivos y ejecutar PHP (`.htaccess`).
- **Límite:** 30 subidas por hora y visitante (usuario o IP; se guarda un hash, no la IP). El
  contador va en el manejador y no en `permission_callback`, porque WordPress llama a este último
  más de una vez por petición.
- **Retención:** una tarea diaria borra las subidas de más de 30 días que no están en un pedido. El
  borrado comprueba que la ruta real esté dentro de la carpeta de subidas (`SafePath`).

## Flujo de un pedido

1. El cliente elige modelo, color y talla, y diseña en el editor (Fabric.js).
2. El navegador muestra el precio en vivo y avisa de imágenes con poca resolución.
3. Al añadir al carrito se envía el diseño (JSON) a la API REST `camisama/v1`; el servidor lo valida
   y **recalcula el precio**.
4. Al pagar, el pedido guarda el diseño; se generan los PNG de producción y la hoja de producción.

Ver [producción e impresión](produccion-e-impresion.md) y [modelo de precios](modelo-de-precios.md).

## Fases

| Fase | Entregable | Estado |
|---|---|---|
| 0. Entorno | Repositorio, sitio local, WooCommerce, tema y plugin base, linters, pruebas, CI | ✅ |
| 1. Tema | Fuentes, portada, catálogo, ficha de producto, carrito y checkout con diseño propio; tienda configurada para Perú (PEN, `es_MX`) | ✅ |
| 2. Datos y administración | Vistas y zonas por producto con editor visual, catálogo de estampados con SVG seguros, precios por tamaño, API REST | ✅ |
| 3. Personalizador | Editor en la ficha del producto: estampados, texto, imágenes propias seguras, precio en vivo, deshacer, teclado y móvil | ✅ |
| 4. Pedido | Diseño validado en el servidor, carrito con vista previa y precio recalculado, pedido, correos, hoja de producción y PNG a 300 DPI | ✅ |
| 5. Extras | Guardar y compartir diseños, móvil, rendimiento, pruebas de punta a punta | |
