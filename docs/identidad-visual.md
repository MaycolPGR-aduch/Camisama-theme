# Identidad visual de Camisama (propuesta)

## Concepto: "el sello"

**Camisama** suena a *camisa* + *Kamisama* (神様, "dios" en japonés, con la terminación de respeto
*-sama*). La identidad se apoya en eso y en el **hanko**, el sello rojo japonés con el que se firma:
un sello que estampa es literalmente lo que hace la tienda. El resultado es una marca urbana y
moderna, con un guiño japonés sutil que no la encasilla en un solo estilo.

- **Personalidad:** creativa, directa, con humor; segura sin ser agresiva.
- **Tono:** de tú, frases cortas. *"Tu diseño, tu ley."*, *"Diseña. Estampa. Ponte."*
- **Visual:** mucho espacio en fondo "papel", tipografía grande y pesada, un único rojo protagonista
  y las fotos de las prendas como lo principal.

## Paleta

Definida como tokens en `themes/camisama/theme.json`. Contraste comprobado según WCAG 2.1.

| Token | Nombre | Hex | Uso |
|---|---|---|---|
| `base` | Papel | `#F7F4EE` | Fondo general (tono algodón) |
| `surface` | Blanco | `#FFFFFF` | Tarjetas, editor, campos |
| `border` | Lino | `#E4DED3` | Bordes y separadores |
| `muted` | Grafito | `#5F5E66` | Texto secundario (5.8:1 sobre Papel) |
| `contrast` | Tinta | `#16161A` | Texto principal (16.4:1), pie de página |
| `primary` | Rojo sello | `#D3261C` | Marca, botones principales (texto blanco 5.2:1) |
| `primary-dark` | Rojo sello oscuro | `#A51D15` | Hover de botones |
| `secondary` | Índigo | `#26357F` | Enlaces y foco (10.1:1) |
| `accent` | Mostaza | `#F5B700` | Ofertas y etiquetas, siempre con texto Tinta (10:1) |

Regla: el rojo **no** se usa para texto pequeño sobre fondo Tinta (3.5:1). Sobre fondos oscuros, el
acento es Mostaza o Papel.

## Tipografía

| Rol | Fuente | Por qué |
|---|---|---|
| Títulos y logotipo | **Bricolage Grotesque** (700–800) | Grotesca con carácter, de aire editorial y urbano. Variable, licencia OFL |
| Texto e interfaz | **Inter** (400–600) | Muy legible en pantalla y en tamaños pequeños (precios, tallas) |

Las dos son libres (OFL) y van alojadas dentro del tema (`themes/camisama/assets/fonts`, versiones
variables con el subconjunto latino, ~140 KB en total), sin cargarlas desde Google, por rendimiento y
privacidad.

## Logotipo

### Isotipo: el sello

Un **sello rojo cuadrado de esquinas redondeadas** (como un hanko) con la **silueta de una camiseta**
en negativo color Papel y un pequeño cuadrado rojo en el pecho: el estampado. Debe funcionar a
16 px (favicon) y en una etiqueta de cuello.

El tema ya incluye un isotipo provisional con esta idea: `themes/camisama/assets/images/logo-mark.svg`.

### Logotipo: la palabra

**camisama** en minúsculas, Bricolage Grotesque ExtraBold, con el espaciado entre letras apretado.
El punto de la **i** se sustituye por un pequeño cuadrado rojo: el mismo sello del isotipo. Así el
logotipo funciona solo, sin el isotipo, y ambos comparten el mismo detalle.

### Versiones necesarias

1. Horizontal: isotipo + palabra (cabecera de la web).
2. Solo palabra (pie de página, etiquetas, bolsas).
3. Solo isotipo (favicon, avatar de redes, etiqueta de cuello).
4. Monocromo en Tinta y en Papel (para estampar sobre prendas o en una sola tinta).

### Prompts para generarlo

Las herramientas de IA dibujan mal las letras, así que conviene generar **el isotipo** con IA y
componer la palabra con la fuente real (Figma, Illustrator o Inkscape). Para logos vectoriales
funcionan bien Recraft (exporta SVG) e Ideogram.

**Isotipo:**

> Minimal flat vector logo mark for a custom t-shirt printing brand. A rounded-square red stamp
> (Japanese hanko seal style), color #D3261C, with a simple t-shirt silhouette cut out in off-white
> #F7F4EE in the center, and a tiny red square printed on the chest of the t-shirt. Bold geometric
> shapes, no gradients, no text, no shadows, centered on a plain off-white background, works at
> favicon size.

**Logotipo completo (como referencia de composición):**

> Wordmark logo "camisama" in lowercase, heavy modern grotesque sans-serif (like Bricolage Grotesque
> ExtraBold), tight letter spacing, near-black #16161A on off-white #F7F4EE. The dot of the letter
> "i" is replaced by a small solid red square #D3261C like a stamp. To the left, a red rounded-square
> stamp icon with an off-white t-shirt silhouette. Flat vector, clean, streetwear brand, no
> gradients, no effects.

Cuando tengas el definitivo en SVG, se sustituye `logo-mark.svg` y se añade la versión horizontal.
