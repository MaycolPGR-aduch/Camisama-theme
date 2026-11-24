---
target: "toda la web: portada, tienda, ficha y editor, carrito, pago y resto"
total_score: 25
max_score: 40
na_heuristics: 
p0_count: 0
p1_count: 5
target_identity: "file:C:\\Camisama\\themes\\camisama\\templates"
timestamp: 2026-11-24T22-07-41Z
slug: themes-camisama-templates
---
Method: dual-agent (A: afec676e8dc1192b0 · B: a5ab1d54c5c09933a)

Alcance: toda la web pública (portada, tienda, ficha y editor, carrito, pago, seguimiento, contacto, mi cuenta, 404) a 320, 390, 768 y 1280 px. Cada evaluador con su propio Chrome sin ventana y su sesión. Sin pedidos. ✔ = reproducido también por quien sintetiza.

#### Design Health Score

| # | Heurística | Nota | Problema |
|---|---|---|---|
| 1 | Visibilidad del estado | 3 | El total sube sin decir por qué; "Recojo GRATIS" sin elegirlo |
| 2 | Lenguaje del cliente | 3 | "Dirección de facturación" para recoger, SKU, notas internas visibles |
| 3 | Control y libertad | 3 | El texto encogido no vuelve a crecer |
| 4 | Consistencia | 2 | Márgenes 24/48 px, tres formatos de precio, "Mi cuenta" ajena al sistema |
| 5 | Prevención de errores | 2 | Segundo formulario al recargar el pago; la foto nace al borde del escalón |
| 6 | Reconocer, no recordar | 3 | Colores no visibles en tienda ni ficha móvil |
| 7 | Flexibilidad | 2 | Ejemplos y estampados de la portada no llevan al editor |
| 8 | Estética | 3 | Pesan el panel de selección y el detalle del carrito |
| 9 | Recuperación de errores | 2 | "Revisa este dato: nombre"; aviso que sobrevive al cambiar a recojo |
| 10 | Ayuda | 2 | Plazo solo al final del pago, contradicho por la FAQ |
| **Total** | | **25/40** | **Aceptable** (A dio 26; la 5 baja a 2 por lo medido por B y confirmado) |

#### Design Specificity Verdict

Editor y portada inconfundibles (centímetros, escalones, precio dentro del diseño, voz propia). Del carrito en adelante, piel de WooCommerce y la prenda a 80 px. Detector: salida 0, 3 avisos design-system-color (404.html, archive-product.html, index.html), los 3 falsos positivos: lee HTML sin theme.json. Superposición omitida (ERR_BLOCKED_BY_CLIENT); axe-core sin violaciones en diseño, texto, imagen, talla y confirmación.

#### Priority Issues

1. [P1] El precio sube sin avisar ✔. Clásica a 390: foto vertical 1200×1600, Listo, Texto "A": S/ 53.90 → S/ 59.90 sin "pasó a…". La foto nace a 20.0 cm. Causa (código): initialSize/tierFit ajustan cada elemento por separado; en SelectionPanel el importe de partida se fija después de añadir. Fix: comparar el escalón de la zona antes y después de añadir y avisar junto al total; "Ajustar para seguir en Mediano"; foto inicial de unos 16 cm. App.js (add/placeFrom), elements.js, SelectionPanel.js. Comando: /impeccable harden.
2. [P1] Entrega y pago se contradicen ✔. (a) Carrito nuevo con "Recojo en el taller GRATIS" y pago abierto en recojo: regresión de Delivery::rates_in_zone del 19-11, que sin distrito quita el flat_rate y deja solo el recojo. (b) Envío → Recojo → Envío → recargar: "Usar la misma dirección" desmarcada, 8 → 15 campos (2 de 2). (c) Recojo exige billing_address_1. (d) "necesita una opción de envío" persiste al pasar a recojo; "No hay opción de entrega disponible" a 12,25 px antes de elegir distrito; con Cusco, tres mensajes y total sin envío. Comando: /impeccable harden.
3. [P1] No se sabe cuándo llega. Plazo solo en el bloque de transferencia; FAQ "Confirma el plazo…"; contacto y legales muestran la nota interna "Quita este aviso al terminar". Fix: un solo plazo (PaymentTimes) en ficha, paso de talla, carrito y pago; avisos internos solo a administradores. Comando: /impeccable clarify.
4. [P1] La ficha salta al cargar ✔. CLS 0,248–0,255 a 1280 y 1,12–1,305 a 390 con CPU ×4 y 3 MB/s; a 1280 por encima de 0,1 en 8 de 8. El bloque .is-product-buy va de y642 → 0 → 165 → 315 → 515 y .wp-block-woocommerce-product-details de 0 → 789. Comando: /impeccable optimize.
5. [P1] Pasos no activos de la portada a opacity 0,35 ✔: p 1,66:1, h3 2,20:1, también con movimiento reducido. Comando: /impeccable polish.
6. [P2] Texto y emojis en el editor. wp-emoji sustituye los emojis por imágenes de s.w.org y React pierde el nodo ✔ (lista "Texto «»"). El texto encogido no crece al acortarlo. En móvil, al escribir, "Listo" sale de la vista, fuente y color bajo el pliegue, aviso de contraste de 125 px. Emoji en la impresión: puede salir con otro dibujo (sin verificar). Comando: /impeccable harden.
7. [P2] Camino desde la idea cortado: chips del hero, estampados y ejemplos sin enlace; "Personalizar" con dos significados; el editor abre siempre en Estampados; colores no visibles antes del editor. Comando: /impeccable shape.
8. [P2] Carrito y pago encogen la prenda: miniatura de 80 px, precio repetido, "Diseño: Editar diseño" entre atributos, dos prendas con el mismo nombre. Comando: /impeccable layout.

#### Persona Red Flags

- Casey: pierde "Listo" al escribir; cerrar, vistas y deshacer lejos del pulgar; en horizontal no se ve la prenda al escribir.
- Riley: emojis, texto que no crece, formulario duplicado al recargar; foto de 180×240 colocada a 15 cm aunque avisa.
- Jordan: "Personalizar" dos veces; "desde S/ 59.90" antes de pagar; DTF sin explicar hasta la FAQ.
- Lucía (regalo): sin fecha de llegada; guía de tallas que no sirve para otra persona; sin "es un regalo"; paga sin ver cuenta ni fecha.

#### Minor Observations

- "Personalizar" con nombre accesible "Elige las opciones para…" (WCAG 2.5.3). Carrusel .camisama-designs__viewport sin tabindex (2.1.1).
- Mi cuenta: campos a 14 px, mostrar contraseña 22×22, casilla 13×13, borde y radio de WooCommerce. Casilla del pago con foco de 1 px.
- Bordes lino del editor 1,22:1. Textos de 12,25 px en totales; 12 px en chips de talla; 13 px en la portada. Muestras de color de portada 34×44.
- Petición externa a s.w.org por la lupa de la galería. Sin meta description salvo en la portada. Pie h1 → h3. Migas "Breadcrumb" en inglés. Carrito lateral sin nombre.
- Portada: modelos a x=24 y el resto a 48; franja papel bajo el cierre. Relacionados con botón rojo y "S/ 54.90" sin "+ impresión". La pista del editor tapa el dobladillo. Lienzo de escritorio de 424 px.
- Imágenes de portada hasta ×5,5 de lo pintado. 52–54 prefetch de WooCommerce (6,7 MB locales).
- Salió bien: 0 desbordes (11 páginas × 4 anchos), 0 errores de consola, contraste mínimo 4,71:1 fuera de los pasos, foco visible en 9 páginas, 0 animaciones con movimiento reducido.

#### Questions to Consider

- Si casi todos llegan con una foto o una frase, ¿por qué se empieza por el modelo y los estampados?
- ¿Tres tamaños con precio en vez de un deslizador en centímetros?
- ¿Cómo sería un pago con tu prenda en grande y "llega antes del viernes"?
