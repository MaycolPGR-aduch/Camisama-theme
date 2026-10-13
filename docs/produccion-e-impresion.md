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
   - Nombre: `{pedido}-{línea}-{zona}-{ancho}x{alto}cm.png`, por ejemplo `1045-1-frente-28x35cm.png`.
2. **Una hoja de producción en PDF** (una por pedido) con:
   - modelo, color y talla de cada prenda, y cantidad;
   - vista previa de la prenda con el diseño (frente/espalda/mangas);
   - por cada zona: tamaño en cm y **posición de referencia** (por ejemplo "centrado, 8 cm bajo el cuello")
     para que el operario coloque el transfer con la plancha siempre igual.
3. **El diseño en JSON** guardado en el pedido, para poder regenerar los archivos o reimprimir.

Todo se descarga desde la pantalla del pedido en WooCommerce (fase 4), y más adelante como ZIP.

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
