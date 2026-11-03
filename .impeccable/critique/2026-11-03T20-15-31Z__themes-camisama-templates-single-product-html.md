---
target: ficha de producto con el personalizador
total_score: 27
max_score: 40
na_heuristics: 
p0_count: 0
p1_count: 2
target_identity: "file:C:\\Camisama\\themes\\camisama\\templates\\single-product.html"
target_fingerprint: "sha256:144c581823442a07bb6f19b5954cfceb890e9ffb609910e3b02830b2e7e73b32"
target_path: "C:\\Camisama\\themes\\camisama\\templates\\single-product.html"
timestamp: 2026-11-03T20-15-31Z
slug: themes-camisama-templates-single-product-html
---
Method: dual-agent (A: revisión de diseño · B: detector + navegador)

Alcance: ficha con personalizador, flujo móvil de tres pantallas, editar desde el carrito y escritorio.

## Design Health Score

| # | Heurística | Nota | Problema clave |
|---|---|---|---|
| 1 | Visibilidad del estado | 3 | Total en vivo y anuncios; un texto largo sube el precio sin aviso; "Elige una talla." fuera de la vista en Talla y pago |
| 2 | Correspondencia con el mundo real | 3 | cm y escalones explicados; "Talla y pago" sin pago; "Por unidad: 2 × S/ 53.90"; "Manga izquierda" a la derecha de quien mira |
| 3 | Control y libertad | 3 | Deshacer, Esc, X, borrador, "Volver al carrito"; X y deshacer lejos del pulgar |
| 4 | Consistencia | 3 | Color de prenda arriba en escritorio y al final en móvil; pestañas sin flechas; carrito fuera del sistema |
| 5 | Prevención de errores | 2 | Texto cortado a 60 sin contador, letras fuera de la zona y texto que crece encima de lo anterior |
| 6 | Reconocer antes que recordar | 3 | "En esta vista" no enseña la otra cara |
| 7 | Flexibilidad y eficiencia | 2 | Atajos sí; foco perdido al añadir o con "Listo"; sin diseños de partida |
| 8 | Estética y minimalismo | 3 | Sereno y de marca; tres filas de píldoras idénticas con significados distintos en la hoja |
| 9 | Recuperación de errores | 3 | Arreglos de un toque: "Cambiar a blanco", "Prenda en…", "Corregir" |
| 10 | Ayuda y documentación | 2 | Solo "¿Qué talla?"; nada de impresión, entrega ni plazos |
| **Total** | | **27/40** | **Aceptable** |

## Design Specificity Verdict

LLM: específico en el centro (precio por zona en cm con escalón actual y siguiente, guardián de contraste con arreglo, paleta del sello, píldoras); genérico en los extremos (ficha que abre con la prenda vacía en una caja beige; minicarrito y carrito estándar de WooCommerce).

Detector: código, salida 0, sin hallazgos principales; 17 avisos de tokens, casi todos del admin. En el editor: #2e8b57 de la muestra Verde (style.scss:335, intencionado), radio de 2 px (style.scss:991) y 1.125rem fuera de escala (style.scss:1214). URL (escritorio y móvil): overused-font y cream-palette, falsos positivos (DESIGN.md). Medidas: lienzo y zonas alineados (escritorio diferencia 0; móvil 0,41 px; zoom idéntico); ningún texto <12 px ni contraste suspendido; móvil ≥44 px en el modo diseño y en Talla y pago; escritorio con controles de 31 px y muestras de 36×36; diálogos atrapan el tabulador (25 y 13 paradas) pero el fondo no es inert (28 enfocables); recuadro de zona ~4 px más grande que el área imprimible.

Overlay: no disponible (ERR_BLOCKED_BY_CLIENT en localhost:8400).

## Priority Issues

1. [P1] El texto largo se rompe en silencio: corte a 60 caracteres sin contador, una línea de 30 cm con letras cortadas en los bordes de la zona, encima del estampado y con subida de escalón (S/ 53.90 → 59.90) sin aviso. Fix: contador, salto a 2-3 líneas antes de salirse, recolocar al crecer y aviso de cambio de escalón en el panel. Comando: /impeccable harden.
2. [P1] El móvil pierde su sitio: el diálogo de diseño se desplaza 41 px (la etiqueta sr-only "Otro color", absoluta respecto al diálogo, alarga el contenido de 812 a 1145 px) y tapa la barra superior; el foco cae al body al añadir con teclado o con "Listo"; en Talla y pago, con "Revisa antes de añadir", la talla empieza en el pliegue (y=738) y "Elige una talla." se pinta fuera de la vista. Fix: contener la etiqueta, devolver el foco, llevar el error a la vista o la talla antes del resumen con riesgos. Comando: /impeccable harden + adapt.
3. [P2] Herramientas sin jerarquía: color de prenda al final de la hoja (~1067 px en una hoja de 381), tres filas de píldoras iguales con significados distintos, fila de zonas cortada ("Mang…"), pestañas sin flechas y con aria-controls a paneles inexistentes. Fix: color compacto al principio, pestañas como control segmentado con patrón de teclado, zona con estilo propio o tocando la prenda. Comando: /impeccable layout.
4. [P2] Escritorio encajonado: lienzo fijo de 558 de 900 px que tapa hasta 19 controles al desplazar; controles de 31 px y muestras de 36×36; columna de compra que acaba a ~620 px mientras el editor mide ~1100. Fix: dos columnas dentro del editor (lienzo fijo y herramientas al lado) o lienzo fijo más bajo; 44 px también en escritorio. Comando: /impeccable adapt.
5. [P2] Extremos genéricos: la ficha abre vacía y "Comprar sin diseño" pesa como "Personalizar"; el final es el minicarrito de WooCommerce. Fix: 3 estampados para empezar, "Comprar sin diseño" como enlace, confirmación propia con el diseño en grande y siguientes pasos, carrito con datos en lista y miniatura mayor. Comando: /impeccable onboard + delight + polish.

## Persona Red Flags

- Casey: X, deshacer y "Listo" arriba; aviso del borrador ocupa ~90 de 381 px; el diálogo se desplaza y esconde la barra superior.
- Riley: corte a 60 caracteres; letras fuera de la zona; texto que crece encima del estampado; "Fren / te" partido en la lista con "Revisar"; lo de la espalda no se ve desde el frente.
- Jordan: "Añadir en:" parece una acción; "Total desde"; "Poner encima" desactivado sin explicación; "Talla y pago" sin pago.
- Sam: foco perdido al añadir y con "Listo"; pestañas sin flechas; fondo de los diálogos enfocable; en escritorio el foco puede quedar bajo el lienzo fijo.
- Quien regala con foto: aviso de calidad bien pensado; falta plazo y lugar de entrega (pendiente de datos del negocio: reservar el sitio).

## Minor Observations

- "Por unidad: 2 × S/ 53.90" → "2 unidades × S/ 53.90"; "desde" repetido en Prenda y Total.
- Recuadro de zona ~4 px más grande que el área imprimible (borde de 1 px en content-box).
- Acercar una manga pixela la prenda: limitar a ~2×.
- Hoja mínima con ~280 px vacíos; hoja completa con el lienzo a ~80 px.
- Aviso de contraste del panel de texto sin región viva.
- "100 %" partido en la ficha móvil.
- "Añadir al carrito" sin talla en rosa al 50 % en escritorio.
- Por confirmar: el borrador no se guardó en una prueba móvil con el panel del navegador oculto (en escritorio sí).
- Editor listo 60-100 ms después de la respuesta del servidor; el servidor local tarda ~1,5 s y no comprime.

## Questions to Consider

- ¿Y si el editor abriera con un estampado peruano ya puesto en la prenda?
- ¿Por qué el color de la prenda es lo último que se ve en el móvil?
- Si el mejor momento es ver tu diseño terminado, ¿por qué la última pantalla es la de WooCommerce?
- ¿Quien regala necesita "Giro", "Duplicar" y "Poner debajo", o saber si llega antes del sábado?
