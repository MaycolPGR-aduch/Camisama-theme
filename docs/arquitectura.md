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
├── client/                   # JS/SCSS (React + Fabric.js), compilado con @wordpress/scripts
│   └── blocks/customizer/    # block.json, editor (index.js), tienda (view.js), render.php
├── build/                    # salida de la compilación (no se versiona)
└── tests/                    # PHPUnit
```

- PHP 8.2+, `declare(strict_types=1)`, WordPress Coding Standards, PHPStan nivel 6.
- Compatible con HPOS y con el carrito/checkout de bloques (declarado en `Plugin::declare_woocommerce_compatibility`).
- `Requires Plugins: woocommerce`: WordPress no deja activarlo sin WooCommerce.

## Modelo de datos (previsto)

Pensado como **producto personalizable** genérico; camisetas es solo el primer tipo.

| Concepto | Almacenamiento |
|---|---|
| Modelo (clásica, oversize…) | Producto variable de WooCommerce (color, talla) + configuración del personalizador en metadatos |
| Vistas | Frente, espalda, mangas…: una imagen de maqueta por color |
| Zonas | Rectángulos por vista con tamaño máximo en cm y escalones de precio |
| Estampados | Tipo de contenido `camisama_design` con taxonomía de categorías |
| Diseño del cliente | JSON en una tabla propia (guardar, compartir, reimprimir) |
| Pedido | JSON + vistas previas + archivos de producción por zona en el artículo del pedido |

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
| 2. Datos y administración | Vistas y zonas por producto, catálogo de estampados, reglas de precio | |
| 3. Personalizador básico | Elegir modelo y color, colocar estampados, texto e imágenes, precio en vivo | |
| 4. Pedido | Diseño en carrito, correos y administración; archivos de producción | |
| 5. Extras | Guardar y compartir diseños, móvil, rendimiento, pruebas de punta a punta | |
