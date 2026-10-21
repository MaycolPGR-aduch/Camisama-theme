# Producción e impresión

Guía de qué archivos genera la tienda para el taller y por qué. Es la base de las fases 3 y 4.

## Técnica recomendada: DTF

Para una tienda de personalización con pedidos de una sola unidad, la técnica más práctica es
**DTF (Direct To Film)**: se imprime el diseño sobre un film con tinta blanca de base, se le aplica
un polvo adhesivo y se transfiere a la prenda con plancha térmica.

| Técnica | Cuándo usarla | Encaja con el personalizador |
|---|---|---|
| **DTF** | 1 unidad o pocas, fotos, degradados, cualquier color de prenda, algodón o poliéster | ✅ Técnica por defecto |
| Sublimación | Solo poliéster claro (deportivas). Muy duradera, no se siente al tacto | ⚠️ Solo para modelos de poliéster blanco |
| Vinilo textil | Textos o logos de 1–2 colores planos | ⚠️ Útil para nombres y números |
| Serigrafía | Tiradas grandes (50+ unidades) de un mismo diseño | ❌ Pedidos de empresa, fuera del flujo online |

Con DTF el coste depende sobre todo del **área de film impreso**, por eso el
[modelo de precios](modelo-de-precios.md) cobra por zona y tamaño del estampado.

## Zonas de impresión estándar (adulto)

| Zona | Tamaño máximo | Píxeles a 300 DPI |
|---|---|---|
| Frente | 30 × 40 cm | 3544 × 4725 |
| Espalda | 30 × 40 cm | 3544 × 4725 |
| Pecho izquierdo (logo) | 10 × 10 cm | 1182 × 1182 |
| Manga izquierda / derecha | 9 × 9 cm | 1063 × 1063 |
| Nuca | 8 × 5 cm | 945 × 591 |

Son valores de partida: cada modelo de prenda podrá definir sus propias zonas en el administrador.
Las tallas de niño o las prendas pequeñas usarán zonas más pequeñas. La conversión está implementada
y probada en `plugins/camisama-customizer/src/Production/PrintResolution.php`.

## Qué recibe el taller por cada prenda del pedido

1. **Un PNG por zona impresa**
   - Fondo **transparente**: nada de maqueta ni color de la camiseta.
   - **300 DPI** al tamaño real de impresión (el archivo lleva el DPI en sus metadatos).
   - Color **sRGB**. El software de la impresora DTF (RIP) se encarga del perfil de color y de la capa de blanco.
   - Recortado al contenido: el tamaño del archivo es el tamaño real del estampado.
   - Nombre: `{pedido}-{línea}-{zona}-{ancho}x{alto}cm.png`, por ejemplo `129-1-front-18x25.6cm.png`.
2. **Una hoja de producción** imprimible (o guardable como PDF desde el navegador) con:
   - modelo, color y talla de cada prenda, y cantidad;
   - vista previa de la prenda con el diseño;
   - por cada zona: tamaño a imprimir, **posición dentro de la zona** (cm desde la izquierda y desde
     arriba) y contenido (estampados, textos con su fuente y color, imágenes del cliente).
3. **El diseño en JSON** guardado en el pedido, para poder regenerar los archivos o reimprimir.

### Cómo se generan (implementado)

1. En el pedido de WooCommerce, cada prenda personalizada muestra su vista previa y el botón
   **"Archivos de impresión y hoja de producción"**.
2. En esa página, **"Generar archivos de impresión (300 DPI)"** dibuja cada zona en el navegador del
   taller con el mismo motor que vio el cliente (Fabric.js): los estampados vectoriales salen nítidos
   a cualquier tamaño. No se hace en el móvil del cliente: un frente de 30 × 40 cm son 17 megapíxeles.
3. El servidor comprueba que el PNG tiene el tamaño esperado, le escribe los 300 DPI (GD los guarda a
   96 y el RIP imprimiría el diseño tres veces más grande) y lo guarda en una carpeta sin acceso
   directo. Se descargan desde la misma página, solo con sesión de quien gestiona pedidos.
4. Se pueden volver a generar cuando haga falta (reimpresión, cambio de fuente…).

> **Al pasar a producción:** la carpeta `uploads/camisama-production/` se protege con `.htaccess`
> (Apache). Si el servidor usa Nginx, hay que añadir una regla equivalente (`deny all` para esa ruta).

## Calidad de las imágenes que sube el cliente

El editor calcula la resolución real de cada imagen según el tamaño al que el cliente la estira:

| Resolución efectiva | Qué ve el cliente |
|---|---|
| ≥ 300 DPI | Nada: calidad óptima |
| 150–299 DPI | Aviso: "puede verse un poco suave" |
| < 150 DPI | Aviso fuerte: "se verá pixelada; usa una imagen más grande o reduce el tamaño" |

También se valida el tipo (PNG, JPG, WEBP, SVG), el peso máximo y se **limpian los SVG**, que pueden
contener código malicioso. El texto se convierte en imagen al generar el PNG, así que el taller no
necesita tener instaladas las fuentes; solo se ofrecen fuentes con licencia libre (OFL) que permite
este uso comercial.

## Decisión: impresión DTF propia

Camisama imprime sus propios transfers (impresora DTF + polvo + horno + plancha térmica). Flujo en el taller:

1. Descargar los PNG del pedido (o de varios pedidos) desde WooCommerce.
2. Abrirlos en el software RIP de la impresora, que aplica el espejo, la capa de tinta blanca y el
   perfil de color. La tienda **no** entrega los archivos en espejo ni con capa blanca.
3. Acomodar varias piezas en el ancho del film para no desperdiciarlo. La mayoría de los RIP lo hacen
   (*nesting*); si el suyo no, la tienda puede generar la hoja ya acomodada (función opcional).
4. Imprimir, aplicar el polvo, curar y planchar siguiendo la hoja de producción.

Cuando tengan el equipo, conviene anotar aquí:

- Modelo de impresora y software RIP (para comprobar que acepta PNG de 300 DPI con transparencia).
- Ancho útil del film (30 o 60 cm) y tamaño máximo real que imprimen en el frente.
- Temperatura, presión y tiempo de planchado por tipo de tela, para incluirlos en la hoja de producción.
