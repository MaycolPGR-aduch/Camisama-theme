---
target: ficha de producto con el personalizador (flujo móvil completo)
total_score: 24
max_score: 40
na_heuristics: 
p0_count: 0
p1_count: 3
target_identity: "file:C:\\Camisama\\themes\\camisama\\templates\\single-product.html"
target_fingerprint: "sha256:144c581823442a07bb6f19b5954cfceb890e9ffb609910e3b02830b2e7e73b32"
target_path: "C:\\Camisama\\themes\\camisama\\templates\\single-product.html"
timestamp: 2026-10-28T20-16-21Z
slug: themes-camisama-templates-single-product-html
---
Method: dual-agent (A: revisión de diseño · B: detector + navegador)

Alcance: ficha con personalizador, flujo móvil de tres pantallas (ficha, modo diseño, talla y pago), editar desde el carrito y escritorio.

## Design Health Score

| # | Heurística | Nota | Problema clave |
|---|---|---|---|
| 1 | Visibilidad del estado | 2 | En el móvil los avisos del lienzo quedan fuera de la vista; el total no multiplica la cantidad; el aviso de calidad desaparece tras "Listo" |
| 2 | Correspondencia con el mundo real | 3 | cm, zonas y soles; "Grande" sin cm; "Recargo de estampado" en el carrito; "Talla y pago" sin pago |
| 3 | Control y libertad | 3 | Deshacer, atrás, Esc, borrador y editar desde el carrito; "Comprar sin diseño" sin color |
| 4 | Consistencia | 2 | Talla preseleccionada en escritorio y no en móvil; "desde" aparece y desaparece; color duplicado en escritorio |
| 5 | Prevención de errores | 2 | Texto a 0,3 cm sin aviso; elementos nuevos apilados en el centro; estampado oscuro sobre prenda oscura; foto pixelada llega al carrito |
| 6 | Reconocer antes que recordar | 3 | Miniaturas y desglose en el paso 3; el escalón de precio no se ve al ajustar el tamaño |
| 7 | Flexibilidad y eficiencia | 2 | Teclado y foco atrapado; altura mínima de la hoja solo deslizando |
| 8 | Estética y minimalismo | 3 | Sistema coherente; hoja densa (14 controles antes del contenido) |
| 9 | Recuperación de errores | 2 | "Elige una talla." bien; el arreglo de contraste no se ve en el móvil |
| 10 | Ayuda y documentación | 2 | Consejos de subida; guía de tallas de referencia igual en oversize; escalones sin explicar |
| **Total** | | **24/40** | **Aceptable** |

## Design Specificity Verdict

LLM: específico en el editor (sello, cm reales, zonas con precio por escalón, estampados peruanos, contraste en un toque, acercar la zona al seleccionar); genérico en los bordes (SKU, "Valoraciones (0)", relacionados, cajón estándar de WooCommerce al final, desplegables de Color/Talla en escritorio). La ficha móvil abre con la prenda vacía.

Detector: código sin errores; 3 avisos de tokens en style.scss (#2e8b57 del aro de color libre, radio de 2 px del asa, 1.125rem de la cantidad). URL escritorio: overused-font y cream-palette (falsos positivos: DESIGN.md) y first-viewport-column-overflow (editor 154 % frente a compra 79 %). URL móvil: solo los dos falsos positivos. Medidas: todos los objetivos del editor y de las pantallas del móvil miden ≥44 px; ningún texto <12 px ni contraste suspendido; lienzo y zonas alineados (307×307 móvil, 634×634 escritorio). Defectos medidos: etiqueta de la manga izquierda seleccionada pisa la esquina del frente (18×5 px); al acercar el frente, su etiqueta queda 10 px bajo la hoja; diálogos sin `inert` (33 enfocables fuera, tapados); asa con aria-label "Ampliar" y aria-expanded=true a la vez; "desde" en el paso 3 antes de elegir talla y no en el pie del modo diseño.

Overlay: no disponible (ERR_BLOCKED_BY_CLIENT en localhost:8400).

## Priority Issues

1. [P1] En el móvil, los avisos del lienzo no se ven: contraste ("Cambiar a blanco"), "Estás editando…" y "Recuperamos…" se pintan en la columna del lienzo (overflow-y: auto, 358 px visibles de 494) bajo el asa de la hoja. Fix: franja fija bajo el asa o píldora sobre el lienzo; repetirlos en el paso 3. Comando: /impeccable adapt + clarify.
2. [P1] Los riesgos de calidad no llegan al pago: el aviso de foto pixelada solo vive en la hoja de selección y desaparece con "Listo"; el texto invisible tampoco se recuerda; las miniaturas de ~160 px no dejan comprobar. Fix: bloque "Revisa antes de añadir" en BuyStep con "Corregir" por riesgo; marca de calidad en la lista de capas; miniatura ampliable. Comando: /impeccable harden.
3. [P1] La hoja entierra el contenido: a media altura el primer estampado empieza bajo el pie; antes van zonas, color, pestañas y categorías (14 controles). Seleccionar con la hoja alta deja el lienzo en 84 px (solo pasa de min a half). Fix: al seleccionar, siempre half; pestañas y contenido primero; zona y color compactos. Comando: /impeccable layout.
4. [P2] Precio sin sorpresas incompleto: el total no multiplica la cantidad (×2 sigue en S/ 83.90, carrito S/ 167.80); el tamaño inicial al 60 % de la zona cae en "Grande" sin decir dónde empieza el escalón; "desde" incoherente entre pasos; "Recargo de estampado" en el carrito. Fix: "2 × S/ 83.90 = S/ 167.80"; escalón junto a Tamaño; etiqueta "Impresión y estampados". Comando: /impeccable clarify.
5. [P2] Guía de tallas con las mismas medidas de referencia para la oversize (decisión del usuario de usar referencias por defecto; el evaluador lo marca como medidas inventadas). Fix: tabla de referencia propia para la oversize, o mostrarla solo con medidas del producto. Comando: contenido.

## Persona Red Flags

- Casey: no ve ningún estampado al abrir; tras elegir con la hoja alta ajusta sobre 84 px; "Enviar atrás" y "Eliminar" bajo el pie; la altura mínima solo deslizando. A favor: el borrador lo recupera todo.
- Riley: texto de 41 caracteres en la nuca a 0,3 cm sin aviso; elementos nuevos apilados en el centro; estampado azul marino sobre negro sin aviso; "Limpiar" desincroniza el color en escritorio.
- Jordan: "Añadir en:" antes de saber qué añadir; "Manga derecha" a la izquierda; +S/ 25 sin explicación; "Traer adelante / Enviar atrás"; "pago" que no aparece.
- Quien regala con foto: aviso de foto pixelada que desaparece; "Reduce el tamaño" sin cifra; guía de la oversize igual a la regular; "Comprar sin diseño" sin color.

## Minor Observations

- "100 %" partido en dos líneas en el extracto (espacio no separable).
- SKU "…-BLANCO-M" y "Valoraciones (0)" en la ficha móvil.
- Con la hoja mínima el lienzo no crece y quedan ~280 px vacíos.
- La raíz del diálogo se puede desplazar por script (overflow: hidden): mejor overflow: clip.
- Al cerrar talla y pago abierto desde "Comprar sin diseño", el foco vuelve a "Personalizar".
- XXL no avisa de su +S/ 5.
- "Diseño: Editar diseño" pequeño en el carrito.
- En la ficha, tras añadir, ninguna señal de "ya en tu carrito".
- Escritorio: color duplicado, M preseleccionada, herramientas bajo el lienzo.
- Diálogos sin inert; asa con aria-expanded=true y etiqueta "Ampliar".

## Questions to Consider

- ¿Por qué la ficha móvil abre con una camiseta vacía y no con un estampado peruano puesto?
- ¿Hace falta elegir la zona antes de qué poner? ¿Y si se toca la prenda y el estampado cae ahí?
- ¿Debe llegar al carrito sin recordatorio un riesgo conocido (foto pixelada, texto invisible, letra de 3 mm)?
- Si el precio va por escalones, ¿por qué el tamaño inicial cae en el caro?
