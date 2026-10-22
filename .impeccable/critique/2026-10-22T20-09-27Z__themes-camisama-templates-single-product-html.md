---
target: ficha de producto con el personalizador
total_score: 23
max_score: 40
na_heuristics: 
p0_count: 0
p1_count: 3
target_identity: "file:C:\\Camisama\\themes\\camisama\\templates\\single-product.html"
target_fingerprint: "sha256:dd91c3cd698f81e769de7a0ad69ea69048148e740deec34ec984d71ec09fd3fa"
target_path: "C:\\Camisama\\themes\\camisama\\templates\\single-product.html"
timestamp: 2026-10-22T20-09-27Z
slug: themes-camisama-templates-single-product-html
---
Method: dual-agent (A: revisión de diseño · B: detector + navegador)

## Design Health Score

| # | Heurística | Nota | Problema clave |
|---|---|---|---|
| 1 | Visibilidad del estado | 2 | En móvil, al añadir un estampado el lienzo queda fuera de pantalla; tras añadir al carrito la camiseta vuelve vacía |
| 2 | Correspondencia con el mundo real | 3 | cm y zonas claros; "Mediano/Grande" sin explicar; el carrito dice "Recargo de estampado" por la impresión por zonas |
| 3 | Control y libertad | 3 | Deshacer/rehacer y Esc; el diseño se pierde al recargar o al añadir al carrito |
| 4 | Consistencia | 2 | Tres precios distintos, dos controles de color, chips frente a desplegables nativos, dos botones rojos a la vez |
| 5 | Prevención de errores | 2 | Texto negro por defecto sobre prenda negra; talla M preseleccionada; elementos nuevos siempre en el centro |
| 6 | Reconocer antes que recordar | 3 | Capas y etiquetas visibles; en móvil hay que recordar el total al llegar al botón |
| 7 | Flexibilidad y eficiencia | 2 | Atajos de teclado, pero nada para quien no sabe diseñar (frases, colocaciones rápidas) |
| 8 | Estética y minimalismo | 3 | Limpio y de marca; miniaturas de alturas desiguales y etiquetas de zona que se pisan |
| 9 | Recuperación de errores | 2 | Buenos avisos de calidad; falta de talla con alert() nativo genérico |
| 10 | Ayuda y documentación | 1 | Sin guía de tallas, sin explicación de escalones de precio ni de zonas |
| **Total** | | **23/40** | **Aceptable** |

## Design Specificity Verdict

LLM: el editor es de Camisama (chips de tinta, papel, un acento rojo, cm reales, estampados peruanos, desglose por zona). La columna de compra es WooCommerce sin adaptar (desplegables nativos, rango de precio, "Limpiar", alert(), "Valoraciones (0)"): la "tienda genérica" que DESIGN.md prohíbe, y la costura cae en el precio y el botón de compra.

Detector: 0 hallazgos primarios en el marcado; 3 avisos design-system-font-size en style.scss (102: 1.1rem, 148: 0.7rem, 290: 0.75rem). En la URL: undersized-ui-text ("+ S/ 5.00" a 10px), first-viewport-column-overflow (columna del editor 187% del viewport), straggler en la lista de garantías (móvil). overused-font (Inter) y cream-palette son falsos positivos: decisiones de marca en DESIGN.md. Medidas: 18 de 24 controles del editor por debajo de 44×44 px; etiquetas de zona solapadas en la vista Espalda (Nuca sobre Espalda; zona Nuca de 23×15 px en móvil).

Overlay: no disponible; el panel del navegador bloquea localhost:8400 (ERR_BLOCKED_BY_CLIENT). Se sustituyó por escaneo de URL con la CLI.

## Priority Issues

1. [P1] Precio y compra separados del editor; tres precios contradictorios. Total al final del editor (App.js:614), talla/cantidad/botón en el bloque de WooCommerce (single-product.html) con precio de variante S/ 39.90 junto al botón; en móvil el botón está a ~2078 px sin barra fija. Fix: ocultar precio de variante y rango en productos personalizables, unir resumen + talla + cantidad + botón, barra inferior fija en móvil "Total · Añadir al carrito". Comando: /impeccable layout (+ adapt).
2. [P1] En móvil, al añadir algo no se ve el resultado y el lienzo bloquea el desplazamiento. El panel de selección se inserta encima del catálogo (App.js:494) y el lienzo sube fuera de pantalla; touch-action: none (style.scss:117) en ~335 de 812 px. Fix: lienzo compacto fijo arriba en <768px, herramientas en hoja inferior, touch-action solo con elemento seleccionado; objetivos táctiles de 44 px. Comando: /impeccable adapt.
3. [P1] Texto invisible sobre la prenda: color inicial COLORS[0] negro (TextPanel.js:30) aunque la prenda sea negra. Fix: color inicial por contraste con la prenda y aviso "Poco contraste con la prenda". Comando: /impeccable harden.
4. [P2] Añadir al carrito borra el diseño y la confirmación no tranquiliza; etiqueta "Recargo de estampado" (CartIntegration.php:203,272) por la impresión por zonas. Fix: guardar borrador en sessionStorage, aviso con vista previa y total, renombrar a "Impresión por zonas". Comando: /impeccable harden + /impeccable clarify.
5. [P2] Talla preseleccionada en M, sin guía y con alert() nativo; desplegable de Color duplicado. Fix: chips de talla sin preselección, "¿Qué talla?" con medidas, mensaje en línea, quitar el desplegable de color. Comando: /impeccable clarify + layout.

## Persona Red Flags

- Casey (móvil): chips de 31 px, colores de 36 px, deslizadores de 16 px, tiradores de Fabric diminutos; pierde el diseño si recarga; botón a 2078 px.
- Riley (límites): negro sobre negro; texto corto que cambia de escalón sin explicación; elementos apilados en el centro; etiquetas de zona solapadas; "Total desde" tras "Limpiar"; textarea desbordada.
- Jordan (primera vez): "Añadir en:" antes de saber qué añadir; "Traer adelante / Enviar atrás"; fuentes sin vista previa; tras "Añadir texto" el formulario edita en silencio, no hay "Nuevo texto".
- Lucía (regalo con foto, móvil): "Tu imagen" en segunda fila; consejo de fondo en gris pequeño; aviso de calidad lejos de la prenda; talla del destinatario en M por defecto; camiseta vacía tras añadir al carrito.

## Minor Observations

- .woocommerce-page img { height: auto } anula height: 64px de las miniaturas (style.scss:300).
- "Valoraciones (0)" en product-details: prueba social vacía.
- print-zones.php:14 promete "Pecho (logo) hasta 10 × 10 cm", zona que el editor no ofrece.
- Selector de color libre parece un cuadrado negro sin etiqueta visible.
- aria-live sobre todo el resumen (Summary.js:21): anunciar solo el total.
- ↶ ↷ en píldora de 53×39; deshabilitados casi indistinguibles.
- La camiseta ocupa ~50% del lienzo en móvil.
- "Añadir texto" y "Elegir imagen" en rojo compiten con "Añadir al carrito" (regla Un Solo Sello).
- Sin texto "Preparando tu diseño…" mientras se generan las vistas previas.
- "+ S/ 5.00" a 10px; etiquetas de zona a 0.7rem.

## Questions to Consider

- ¿Por qué el título y el botón aparecen en móvil después de 1700 px de herramientas, en vez de enmarcar el editor?
- ¿Y si la primera pregunta fuera "¿Qué quieres poner? Una frase / una foto / un estampado" y la zona se eligiera después, sobre la prenda?
- ¿Mostrar el escalón de precio dentro de la zona ("hasta 20 cm → S/ 14")?
- ¿Qué tendría que ver quien acaba de añadir un regalo al carrito para sentir que lo hizo bien?
