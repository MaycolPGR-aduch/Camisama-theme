---
name: Camisama
description: Tu diseño, tu ley. Tienda de prendas personalizables con impresión DTF en Perú.
colors:
  papel: "#F7F4EE"
  blanco: "#FFFFFF"
  lino: "#E4DED3"
  grafito: "#5F5E66"
  tinta: "#16161A"
  rojo-sello: "#D3261C"
  rojo-sello-oscuro: "#A51D15"
  indigo: "#26357F"
  mostaza: "#F5B700"
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
rounded:
  check: "4px"
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
---

<!-- Extraído del tema existente (themes/camisama/theme.json y style.css). El lenguaje descriptivo
     (estrella polar, nombres de reglas) es provisional y falta confirmarlo con el usuario. El
     editor del plugin camisama-customizer no se ha revisado todavía para este documento. -->

# Design System: Camisama

## Overview

**Creative North Star: "El sello"** (provisional; concepto aceptado en docs/identidad-visual.md)

Papel cálido, tinta casi negra y un rojo de sello que marca lo importante: la acción de comprar,
los checks, el estado activo. La tienda se siente como un taller de estampado ordenado: fondo
claro y tranquilo, tipografía de titulares con carácter (Bricolage Grotesque, ajustada y pesada)
y un cuerpo neutro y legible (Inter). La prenda y el diseño del cliente son lo protagonista; la
interfaz los enmarca.

Densidad media, formas redondeadas generosas en imágenes y botones en píldora. Plano por defecto:
la profundidad viene del contraste entre papel y blanco, no de sombras.

**Key Characteristics:**
- Fondo papel (no blanco puro); las imágenes de producto van sobre blanco.
- Un solo acento fuerte, el rojo sello; índigo para enlaces y foco; mostaza como apoyo puntual.
- Titulares en Bricolage Grotesque 700 con tracking negativo; texto en Inter.
- Botones en píldora; tarjetas e imágenes con esquinas de 20–24px.
- Sin sombras; movimiento mínimo y respetuoso con `prefers-reduced-motion`.

## Colors

Neutros cálidos de papel y lino con un rojo de sello como único acento dominante.

### Primary
- **Rojo sello**: botón principal, checks de listas, hover de enlaces y títulos. Es la marca.
- **Rojo sello oscuro**: estado hover del botón principal.

### Secondary
- **Índigo**: color de los enlaces en reposo y del anillo de foco (`:focus-visible`, 2px).

### Tertiary
- **Mostaza**: acento puntual de apoyo; nunca para texto sobre papel (contraste insuficiente).

### Neutral
- **Papel**: fondo de toda la página.
- **Blanco**: superficie de tarjetas, galería e imágenes de producto; texto sobre rojo.
- **Lino**: bordes y divisores.
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

## Layout

Contenido a 760px y bloques anchos a 1280px, con relleno lateral fluido (1.25–1.5rem). Ritmo de
espaciado en siete pasos, de 0.5rem a clamp(3.5rem, 9vw, 6.5rem); el espacio entre bloques por
defecto es 1rem. Las secciones de portada usan los pasos XL y 2XL para respirar.

## Elevation & Depth

Sistema plano: no hay sombras. La profundidad se consigue con el contraste tonal entre papel
(fondo) y blanco (superficies e imágenes). La única respuesta de movimiento es un zoom de 1.03 en
la imagen de la tarjeta de producto al pasar el cursor, desactivado con movimiento reducido.

## Shapes

Esquinas generosas y amables: 20px en imágenes de tarjetas, 24px en la galería del producto,
píldora completa en botones y 4px en los checks. Divisores discontinuos en la lista de zonas de
impresión (1px al 35% del color del texto), que recuerdan a una línea de corte.

## Components

### Buttons
- **Shape:** píldora (999px).
- **Primary:** rojo sello con texto blanco, peso 600, relleno 0.8em × 1.6em.
- **Hover / Focus:** hover a rojo sello oscuro; foco con anillo índigo de 2px y separación de 2px.
- **Outline:** borde de 2px del color del texto, fondo transparente; en hover se rellena de tinta
  con texto papel.

### Cards / Containers
- **Corner Style:** 20px en la imagen de la tarjeta de producto.
- **Background:** imagen sobre blanco, tarjeta sobre papel.
- **Shadow Strategy:** ninguna (ver Elevation & Depth).

### Navigation
- Inter 500 a 0.875rem, enlaces en tinta sin subrayado y rojo sello en hover.

### Listas con estilo
- **Checks:** marca cuadrada de 1.05em en rojo sello (máscara SVG) para ventajas y garantías.
- **Zonas:** filas flexibles con el nombre de la zona en Bricolage y la medida a la derecha,
  separadas por divisores discontinuos.

## Do's and Don'ts

### Do:
- **Do** usar papel como fondo y blanco solo para superficies e imágenes de producto.
- **Do** reservar el rojo sello para la acción principal y las marcas de estado.
- **Do** usar índigo de 2px para el foco visible en todos los elementos interactivos.
- **Do** acompañar cualquier animación con su alternativa para `prefers-reduced-motion`.

### Don't:
- **Don't** añadir sombras para dar profundidad; usa el contraste papel/blanco.
- **Don't** usar mostaza para texto sobre papel o blanco.
- **Don't** mezclar otras tipografías en la interfaz de la tienda (las 8 fuentes del editor son
  contenido del cliente, no de la interfaz).
