---
name: Camisama
description: Tu diseño, tu ley. Tienda de prendas personalizables con impresión DTF en Perú.
colors:
  papel: "#F7F4EE"
  blanco: "#FFFFFF"
  lino: "#E4DED3"
  grafito: "#5F5E66"
  tinta: "#16161A"
  borde-campo: "#86868C"
  rojo-sello: "#D3261C"
  rojo-sello-oscuro: "#A51D15"
  indigo: "#26357F"
  mostaza: "#F5B700"
  calidad-optima-fondo: "#E8F4EC"
  calidad-optima-texto: "#1D6B3A"
  calidad-aceptable-fondo: "#FFF4D6"
  calidad-aceptable-texto: "#8A5A00"
  calidad-baja-fondo: "#FDE7E5"
typography:
  display:
    fontFamily: "\"Bricolage Grotesque\", ui-sans-serif, system-ui, sans-serif"
    fontSize: "clamp(3rem, 7vw, 5.5rem)"
    fontWeight: 700
    lineHeight: 1.1
    letterSpacing: "-0.02em"
  headline:
    fontFamily: "\"Bricolage Grotesque\", ui-sans-serif, system-ui, sans-serif"
    fontSize: "clamp(2.25rem, 5vw, 3.5rem)"
    fontWeight: 700
    lineHeight: 1.1
    letterSpacing: "-0.02em"
  title:
    fontFamily: "\"Bricolage Grotesque\", ui-sans-serif, system-ui, sans-serif"
    fontSize: "clamp(1.5rem, 3vw, 2rem)"
    fontWeight: 700
    lineHeight: 1.1
    letterSpacing: "-0.02em"
  body:
    fontFamily: "Inter, ui-sans-serif, system-ui, -apple-system, \"Segoe UI\", Roboto, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.6
  label:
    fontFamily: "Inter, ui-sans-serif, system-ui, -apple-system, \"Segoe UI\", Roboto, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 500
    lineHeight: 1.4
  caption:
    fontFamily: "Inter, ui-sans-serif, system-ui, -apple-system, \"Segoe UI\", Roboto, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 600
    lineHeight: 1.4
rounded:
  check: "4px"
  notice: "10px"
  field: "12px"
  tile: "14px"
  dropzone: "16px"
  card: "20px"
  gallery: "24px"
  pill: "999px"
spacing:
  2xs: "0.5rem"
  xs: "0.75rem"
  s: "1rem"
  m: "clamp(1.25rem, 2.5vw, 1.5rem)"
  l: "clamp(1.5rem, 4vw, 2.5rem)"
  xl: "clamp(2.5rem, 6vw, 4rem)"
  2xl: "clamp(3.5rem, 9vw, 6.5rem)"
components:
  button-primary:
    backgroundColor: "{colors.rojo-sello}"
    textColor: "{colors.blanco}"
    rounded: "{rounded.pill}"
    padding: "0.8em 1.6em"
  button-primary-hover:
    backgroundColor: "{colors.rojo-sello-oscuro}"
    textColor: "{colors.blanco}"
  button-outline:
    textColor: "{colors.tinta}"
    rounded: "{rounded.pill}"
    padding: "0.8em 1.6em"
  button-outline-hover:
    backgroundColor: "{colors.tinta}"
    textColor: "{colors.papel}"
  product-card-image:
    backgroundColor: "{colors.blanco}"
    rounded: "{rounded.card}"
  button-editor:
    backgroundColor: "{colors.tinta}"
    textColor: "{colors.papel}"
    rounded: "{rounded.pill}"
    padding: "0.4em 1em"
  chip:
    textColor: "{colors.tinta}"
    typography: "{typography.label}"
    rounded: "{rounded.pill}"
    padding: "0.4em 1em"
  chip-selected:
    backgroundColor: "{colors.tinta}"
    textColor: "{colors.papel}"
    rounded: "{rounded.pill}"
    padding: "0.4em 1em"
  editor-panel:
    backgroundColor: "{colors.blanco}"
    rounded: "{rounded.card}"
    padding: "{spacing.m}"
  editor-stage:
    backgroundColor: "{colors.blanco}"
    rounded: "{rounded.gallery}"
  field:
    backgroundColor: "{colors.blanco}"
    textColor: "{colors.tinta}"
    rounded: "{rounded.field}"
    padding: "0.5em 0.75em"
  design-tile:
    backgroundColor: "{colors.papel}"
    textColor: "{colors.tinta}"
    rounded: "{rounded.tile}"
    padding: "0.5rem"
  swatch:
    rounded: "50%"
    size: "2.25rem"
  quality-optimal:
    backgroundColor: "{colors.calidad-optima-fondo}"
    textColor: "{colors.calidad-optima-texto}"
    rounded: "{rounded.notice}"
    padding: "0.5em 0.75em"
  quality-acceptable:
    backgroundColor: "{colors.calidad-aceptable-fondo}"
    textColor: "{colors.calidad-aceptable-texto}"
    rounded: "{rounded.notice}"
    padding: "0.5em 0.75em"
  quality-low:
    backgroundColor: "{colors.calidad-baja-fondo}"
    textColor: "{colors.rojo-sello}"
    rounded: "{rounded.notice}"
    padding: "0.5em 0.75em"
---

<!-- Extraído del tema (themes/camisama/theme.json, style.css) y del editor
     (plugins/camisama-customizer/client/blocks/customizer/style.scss). Estrella polar, carácter de
     los componentes y estilos a evitar confirmados por el usuario el 2026-10-22. -->

# Design System: Camisama

## Overview

**Creative North Star: "El sello"** (concepto de docs/identidad-visual.md)

Papel cálido, tinta casi negra y un rojo de sello que marca lo importante: la acción de comprar,
los checks, el estado activo. La tienda se siente como un taller de estampado ordenado: fondo
claro y tranquilo, tipografía de titulares con carácter (Bricolage Grotesque, ajustada y pesada)
y un cuerpo neutro y legible (Inter). La prenda y el diseño del cliente son lo protagonista; la
interfaz los enmarca.

Densidad media, formas redondeadas generosas en imágenes y botones en píldora. Plano por defecto:
la profundidad viene del contraste entre papel y blanco, no de sombras. Los controles son
**táctiles y seguros**: fichas con borde firme que se rellenan al elegirlas, con estados que no
dejan dudas.

No debe parecer una **tienda genérica** (plantilla de WooCommerce o marketplace: blanco, gris y
banners) ni estar **sobrecargada** (muchos colores, degradados, sombras y animaciones compitiendo).

**Key Characteristics:**
- Fondo papel (no blanco puro); las imágenes de producto van sobre blanco.
- Un solo acento fuerte, el rojo sello; índigo para enlaces y foco; mostaza como apoyo puntual.
- Titulares en Bricolage Grotesque 700 con tracking negativo; texto en Inter.
- Botones en píldora; tarjetas e imágenes con esquinas de 20–24px.
- Sin sombras. Movimiento con propósito: se mueve lo que enseña el producto (la prenda que
  cambia, el precio que se suma), nunca adorno; siempre con pausa y alternativa para
  `prefers-reduced-motion`.

## Colors

Neutros cálidos de papel y lino con un rojo de sello como único acento dominante.

### Primary
- **Rojo sello**: botón principal, checks de listas, hover de enlaces y títulos. Es la marca.
- **Rojo sello oscuro**: estado hover del botón principal.

### Secondary
- **Índigo**: color de los enlaces en reposo y del anillo de foco (`:focus-visible`, 2px).

### Tertiary
- **Mostaza**: acento puntual de apoyo; nunca para texto sobre papel (contraste insuficiente).

### Semantic (solo en el editor)
- **Calidad óptima / aceptable / baja**: avisos de resolución de las imágenes del cliente, en
  verde, ámbar y rojo sello sobre fondos muy claros. Son estados, no decoración.

### Neutral
- **Papel**: fondo de toda la página.
- **Blanco**: superficie de tarjetas, galería e imágenes de producto; texto sobre rojo.
- **Lino**: bordes y divisores.
- **Borde de campo**: grafito aclarado (`--camisama-field-border`), solo para el borde de los
  campos de la tienda: sobre el papel un campo tiene que verse (3,3:1; el lino se queda en 1,3:1).
- **Grafito**: texto secundario (migas de pan, notas).
- **Tinta**: texto principal, títulos, navegación y relleno del botón con borde en hover.

### Named Rules
**The Un Solo Sello Rule.** El rojo marca la acción principal y poco más; si todo es rojo, nada
destaca.

## Typography

**Display Font:** Bricolage Grotesque (con ui-sans-serif, system-ui)
**Body Font:** Inter (con ui-sans-serif, system-ui, Segoe UI, Roboto)

**Character:** un grotesco expresivo y apretado para titulares, frente a un sans neutro y muy
legible para todo lo que se lee o se opera.

### Hierarchy
- **Display** (700, fluida hasta 5.5rem, 1.1): portada y momentos de marca.
- **Headline** (700, fluida hasta 3.5rem, 1.1): h1 de página.
- **Title** (700, fluida hasta 2rem, 1.1): h2 de sección; h3 a 1.25rem.
- **Body** (400, 1rem, 1.6): texto general; columnas de contenido de 760px como máximo.
- **Label** (500, 0.875rem): navegación, migas de pan y metadatos. El nombre del sitio va en
  Bricolage 800 con -0.03em.
- **Caption** (600, 0.75rem): solo las etiquetas de zona sobre el lienzo, en píldora de tinta; es
  el único texto por debajo de 0.875rem, porque va dentro de la prenda y no debe tapar el diseño.

### Named Rules
**The Dos Voces Rule.** Bricolage Grotesque habla en titulares, precios totales y la marca; todo lo
que se lee o se opera va en Inter.

## Layout

Contenido a 760px y bloques anchos a 1280px, con relleno lateral fluido (1.25–1.5rem). Ritmo de
espaciado en siete pasos, de 0.5rem a clamp(3.5rem, 9vw, 6.5rem); el espacio entre bloques por
defecto es 1rem. Las secciones de portada usan los pasos XL y 2XL para respirar.

## Elevation & Depth

Sistema plano: no hay sombras de elevación. La única `box-shadow` es funcional: el doble contorno
(blanco discontinuo con halo de tinta al 35%) que hace visibles las zonas de impresión sobre
prendas de cualquier color. La profundidad se consigue con el contraste tonal entre papel
(fondo) y blanco (superficies e imágenes). En la tienda, la respuesta de movimiento es un zoom de 1.03 en la imagen de la tarjeta de producto al
pasar el cursor, desactivado con movimiento reducido. La portada tiene más movimiento (ver The
Movimiento Que Enseña Rule).

### Named Rules
**The Papel y Blanco Rule.** La profundidad es tonal: papel al fondo, blanco para lo que se toca o se

**The Movimiento Que Enseña Rule.** (2026-11-17, a pedido del usuario: portada más llamativa y
dinámica.) Lo que se mueve demuestra el producto: el hero turna ejemplos sobre la prenda con su
zona y su recargo, el simulador rellena zonas y suma el total, y el sello de marca gira despacio.
Transiciones cortas (0.25–0.6 s) con un pequeño rebote de "estampar" al aparecer algo. Cualquier
cosa que avance sola tiene pausa visible, se detiene con el cursor, el foco o fuera de pantalla, y
con movimiento reducido no avanza ni gira. Siguen fuera los degradados y las sombras decorativas.
mira de cerca. Si algo necesita destacar, cambia de superficie, no gana una sombra.

## Shapes

Esquinas generosas y amables, que crecen con el tamaño del elemento: 10px en avisos, 12px en
campos, 14px en miniaturas de estampados, 16px en la zona de subida, 20px en paneles y tarjetas,
24px en el lienzo y la galería, píldora completa en botones y chips, círculo en los colores y 4px
en los checks y los contornos de zona. Divisores discontinuos en la lista de zonas de
impresión (1px al 35% del color del texto), que recuerdan a una línea de corte.

## Components

### Buttons
- **Shape:** píldora (999px).
- **Primary:** rojo sello con texto blanco, peso 600, relleno 0.8em × 1.6em.
- **Hover / Focus:** hover a rojo sello oscuro; foco con anillo índigo de 2px y separación de 2px.
- **Outline:** borde de 2px del color del texto, fondo transparente; en hover se rellena de tinta
  con texto papel.
- **Editor:** acciones internas del editor ("Añadir texto", "Elegir imagen"): píldora rellena de
  tinta con texto papel. El rojo queda para "Añadir al carrito".

### Cards / Containers
- **Corner Style:** 20px en la imagen de la tarjeta de producto.
- **Background:** imagen sobre blanco, tarjeta sobre papel.
- **Shadow Strategy:** ninguna (ver Elevation & Depth).

### Inputs / Fields
- **Style:** fondo blanco, borde de campo de 1.5px, radio 12px, 44px de alto y letra de 16px
  (con menos, Safari en iPhone amplía la página); etiqueta encima en Inter 600 a 0.875rem. Los
  desplegables y la cantidad del formulario de WooCommerce llevan el mismo aspecto. (Los paneles
  del editor siguen con borde lino.)
- **Carrito y pago (bloques de WooCommerce):** mismo campo, de 56px, con la etiqueta flotando
  dentro: 16px en vacío y 14px una vez escrito el dato. Errores en rojo sello (4,7:1 sobre papel)
  con el borde y la etiqueta del campo en rojo; el dato sigue en tinta. Total del pedido en Bricolage 700 a
  x-large; títulos de cada paso en Bricolage 700 a large.
- **Checkbox:** 24px, borde de tinta de 1.5px, radio 4px; marcada se rellena de tinta con la marca
  en blanco. La fila entera se pulsa (44px).
- **Focus:** anillo índigo de 2px, igual que el resto.
- **Range:** `accent-color` tinta.

### Chips (editor)
- **Style:** píldora con borde de tinta de 1.5px, fondo transparente, Inter 600 a 0.875rem.
  Se usan para vistas, zonas, pestañas, deshacer/rehacer y acciones.
- **State:** seleccionado (`aria-pressed` / `aria-selected`) se rellena de tinta con texto papel;
  deshabilitado al 40% de opacidad. La acción destructiva lleva borde y texto rojo sello.

### Navigation
- Inter 500 a 0.875rem, enlaces en tinta sin subrayado y rojo sello en hover.

### Listas con estilo
- **Checks:** marca cuadrada de 1.05em en rojo sello (máscara SVG) para ventajas y garantías.
- **Zonas:** filas flexibles con el nombre de la zona en Bricolage y la medida a la derecha,
  separadas por divisores discontinuos.

### Editor del personalizador (signature)
- **Lienzo:** papel, como la página, con la foto recortada de la prenda; encima, los contornos
  de zona con su etiqueta en píldora de tinta translúcida (sólida cuando la zona está activa).
- **Paneles:** herramientas, selección, capas y resumen en tarjetas blancas con borde lino y
  radio 20px.
- **Colores de prenda:** círculos de 2.25rem con borde lino y aro interior blanco; el
  seleccionado cambia el borde a tinta.
- **Estampados:** cuadrícula de miniaturas (mínimo 96px) sobre papel con radio 14px; el recargo
  premium en rojo sello.
- **Resumen de precio:** lista de conceptos en grafito y total en Bricolage a tamaño x-large,
  separado por una línea lino.

## Do's and Don'ts

### Do:
- **Do** usar papel como fondo y blanco solo para superficies e imágenes de producto.
- **Do** reservar el rojo sello para la acción principal y las marcas de estado.
- **Do** usar índigo de 2px para el foco visible en todos los elementos interactivos.
- **Do** usar el chip en píldora (tinta, relleno al seleccionar) para toda elección dentro del
  editor, y reservar el relleno rojo para la acción que avanza.
- **Do** acompañar cualquier animación con su alternativa para `prefers-reduced-motion`.

### Don't:
- **Don't** caer en la tienda genérica: nada de fondos blancos planos con todo en gris, banners
  de oferta ni la apariencia por defecto de los bloques de WooCommerce sin el tema aplicado.
- **Don't** sobrecargar: un acento fuerte por pantalla, sin degradados ni animaciones de adorno;
  en el editor nada se mueve que compita con el diseño del cliente.
- **Don't** añadir sombras para dar profundidad; usa el contraste papel/blanco.
- **Don't** usar mostaza para texto sobre papel o blanco.
- **Don't** mezclar otras tipografías en la interfaz de la tienda (las 8 fuentes del editor son
  contenido del cliente, no de la interfaz).
