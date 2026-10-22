---
target: ficha de producto con el personalizador
total_score: 25
max_score: 40
na_heuristics: 
p0_count: 0
p1_count: 3
target_identity: "file:C:\\Camisama\\themes\\camisama\\templates\\single-product.html"
target_fingerprint: "sha256:144c581823442a07bb6f19b5954cfceb890e9ffb609910e3b02830b2e7e73b32"
target_path: "C:\\Camisama\\themes\\camisama\\templates\\single-product.html"
timestamp: 2026-10-22T21-45-17Z
slug: themes-camisama-templates-single-product-html
---
Method: dual-agent (A: revisión de diseño · B: detector + navegador)

## Design Health Score

| # | Heurística | Nota | Problema clave |
|---|---|---|---|
| 1 | Visibilidad del estado | 3 (antes 2) | Precio en vivo y barra móvil bien; el texto no se ve hasta pulsar "Añadir texto" y los cambios de escalón son silenciosos |
| 2 | Correspondencia con el mundo real | 3 | "Frente · Grande" sin decir qué es Grande; "Manga derecha" a la izquierda; SKU visible |
| 3 | Control y libertad | 2 (antes 3) | El diseño se pierde al recargar y tras añadir al carrito; no hay "editar" desde el carrito |
| 4 | Consistencia | 2 | Dos controles de color; "Recargo de estampado" en el carrito; alert() con otra voz; botones rojos en relacionados |
| 5 | Prevención de errores | 3 (antes 2) | Aviso de contraste y de calidad; talla M preseleccionada; "Limpiar" desincroniza el color; doble toque duplica |
| 6 | Reconocer antes que recordar | 3 | Escalones invisibles; en escritorio los deslizadores quedan lejos del lienzo |
| 7 | Flexibilidad y eficiencia | 2 | Atajos de teclado, pero el lienzo no se puede enfocar; sin zoom a la zona |
| 8 | Estética y minimalismo | 3 | Sistema sólido; en móvil se apilan selección, herramientas y capas |
| 9 | Recuperación de errores | 2 | alert() nativo si falta la talla; "No pudimos preparar tu diseño" sin pista |
| 10 | Ayuda y documentación | 2 (antes 1) | Consejos de subida, pero sin guía de tallas ni de escalones ni primer uso |
| **Total** | | **25/40** (antes 23/40) | **Aceptable** |

## Design Specificity Verdict

LLM: el editor es de Camisama (cm reales, estampados peruanos, aviso de contraste con arreglo, un solo rojo para comprar). Lo que lo rodea sigue siendo WooCommerce genérico: desplegables de color y talla con "Limpiar", SKU, "Valoraciones (0)", alert() nativo, relacionados con botones rojos y el aviso estándar tras añadir al carrito. En móvil parece una ficha de WooCommerce con un editor dentro, sin invitación a empezar.

Detector: código limpio salvo 1 aviso (design-system-color #2e8b57 en el aro de "Otro color", style.scss:301, decorativo). URL escritorio: overused-font y cream-palette (falsos positivos: decisiones de DESIGN.md) y first-viewport-column-overflow (editor 154% del alto frente a 79% de la compra). URL móvil: solo los dos falsos positivos. Antes: 4 y 4. Medidas: en táctil, todos los controles del editor cumplen 44 px (antes 18 de 24 no); solo quedan pequeños los de WooCommerce ("Borrar opciones" 46×16). Ningún texto por debajo de 12 px. Precios: rango y variante ocultos; los visibles coinciden. Solape: la etiqueta "Nuca" invade la zona Espalda (19 px en móvil).

Overlay: no disponible; el panel del navegador bloquea localhost:8400 (ERR_BLOCKED_BY_CLIENT).

## Priority Issues

1. [P1] El diseño se pierde al recargar, al interrumpir y justo después de comprar. Historial solo en memoria (App.js:137); añadir al carrito recarga (wc-bridge.js requestSubmit) y el editor vuelve vacío con el aviso genérico. Fix: borrador en sessionStorage por producto (diseño, color, vista, zona) y restaurar; confirmación con la miniatura de renderPreview ("Tu diseño está en el carrito", "Ver carrito", "Diseñar otra"); "Editar diseño" desde el carrito. Comando: /impeccable harden + onboard.
2. [P1] Lienzo y herramientas no conviven. Móvil: lienzo de 38svh con la maqueta llena de margen (zona Frente ~83×109 px, mangas 26 px), texto de 60 caracteres ilegible, tiradores tapan el estampado. Escritorio: lienzo de 669 px no fijo; herramientas desde y=1016, se ajusta tamaño o giro sin ver la prenda. Fix: encuadrar la zona activa cuando hay selección (con botón "ver prenda entera"); en escritorio, columna del lienzo fija o herramientas en la columna derecha. Comando: /impeccable layout + adapt.
3. [P1] Texto sin vista previa y modo añadir/editar confuso. Lo escrito no aparece hasta "Añadir texto" (TextPanel.js); después el panel edita en silencio y no hay "Nuevo texto" (en táctil hay que tocar un hueco del lienzo); lo nuevo cae en el centro encima del estampado. Fix: crear el elemento al escribir y editar en vivo; botón "Nuevo texto" siempre visible; colocar lo nuevo debajo de lo que ya ocupa la zona. Comando: /impeccable clarify + harden.
4. [P2] Precio por escalones opaco y que arranca caro. "Frente · Grande + S/ 20" sin decir qué es Grande; el estampado entra en Grande (18 × 25,6 cm); añadir un texto sube de 53.90 a 59.90 sin aviso. Fix: "Grande (hasta 30 cm)", pista junto a "Tamaño" del escalón inferior, anunciar cambios de escalón, estampados en Mediano por defecto. Comando: /impeccable clarify.
5. [P2] Talla y validación al estilo WooCommerce. Talla M preseleccionada (la barra dice "Talla M · Añadir al carrito"), sin guía de tallas, "Limpiar" vacía el color, alert() nativo. Fix: sin talla por defecto, chips con "¿Qué talla?", ocultar "Limpiar" y el desplegable de color, mensaje en línea. Comando: /impeccable clarify + layout.

## Persona Red Flags

- Casey (móvil): pierde el diseño al recargar; el panel de selección salta sobre la cuadrícula; la parte fija ocupa ~370 de 812 px (hasta ~490 con el aviso de contraste); tiradores y mangas de 26 px.
- Riley (límites): doble toque apila el estampado; texto de 60 caracteres a 1,4 cm; zona partida "Frent / e" en capas (falta nowrap); Retroceso con el foco en un botón borra el elemento (App.js:333, se puede deshacer); sin aviso de contraste para estampados oscuros sobre prenda oscura; lienzo no enfocable.
- Jordan (primera vez): sin título ni invitación a empezar; "Añadir en:" y "zona" sin explicar; "Manga derecha" a la izquierda; "Grande", "Traer adelante", "Enviar atrás"; no sabe soltar un elemento.
- Lucía (regalo con foto, móvil): "Tu imagen" tercera pestaña y en segunda línea; "o arrástrala aquí" en un teléfono; foco invisible en la subida (input sr-only sin :focus-within); aviso de calidad lejos del lienzo; nada sobre entrega en Lima o recojo.

## Minor Observations

- "Precios en soles, pago seguro" (product-assurances.php) promete una pasarela que aún no hay.
- "Valoraciones (0)" y SKU visibles.
- Relacionados con tres botones rojos rellenos.
- Etiqueta "Nuca" sobre la zona Espalda.
- "Pecho (logo)" anunciado en print-zones.php sin zona en el editor.
- Pestañas sin tabindex itinerante ni flechas.
- En móvil los h3 del editor van antes del h1.
- Botones de estampado se anuncian "Corazón", no "Añadir Corazón en Frente".
- La barra móvil puede quedarse visible si la página salta por encima del formulario sin cruzarlo.

## Questions to Consider

- Si casi todos compran desde el móvil, ¿por qué el diseño vive dentro de una ficha y no en un flujo de tres pasos (prenda → diseño a pantalla completa → talla y pago)?
- ¿Quieres que el primer precio con diseño sea el escalón Grande?
- ¿Qué debería ver quien regala un segundo después de "Añadir al carrito"?
- Si el caso estrella es "una foto especial", ¿por qué "Tu imagen" es la última pestaña?
