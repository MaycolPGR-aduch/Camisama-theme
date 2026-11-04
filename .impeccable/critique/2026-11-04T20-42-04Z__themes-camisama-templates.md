---
target: "resto de la web: portada, tienda, carrito y pago"
total_score: 18
max_score: 40
na_heuristics: 
p0_count: 1
p1_count: 3
target_identity: "file:C:\\Camisama\\themes\\camisama\\templates"
timestamp: 2026-11-04T20-42-04Z
slug: themes-camisama-templates
---
Method: dual-agent (A: revisión de diseño · B: detector + navegador)

Alcance: recorrido fuera de la ficha (portada, tienda, carrito y pago), en escritorio y móvil. Sin pedidos.

## Design Health Score

| # | Heurística | Nota | Problema clave |
|---|---|---|---|
| 1 | Visibilidad del estado | 2 | Insignia del carrito bien; sin icono en /carrito, sin aviso visto al eliminar, pago sin pasos |
| 2 | Correspondencia con el mundo real | 1 | "Colonia", "Localidad", "Región / Estado", "Dirección de facturación" |
| 3 | Control y libertad | 2 | "Editar diseño" bien; papelera sin deshacer y pago sin vuelta al carrito |
| 4 | Consistencia | 2 | Orden de 20 px frente a campos de 44 px; "Realizar el pedido" 42 px y "Proceder al pago" 51 px |
| 5 | Prevención de errores | 1 | Sin elección de entrega, 26 departamentos, teléfono opcional |
| 6 | Reconocer antes que recordar | 2 | Tarjetas sin colores ni prendas estampadas; miniatura del pago de ~40 px |
| 7 | Flexibilidad y eficiencia | 2 | Invitado y autocompletado bien; /mi-cuenta sin registro |
| 8 | Estética y minimalismo | 3 | Limpio en portada y pago; se rompe en carrito vacío y selector de orden |
| 9 | Recuperación de errores | 2 | Foco al primer error; mensajes sin ligar al campo y con fallos ("un apellidos") |
| 10 | Ayuda y documentación | 1 | Devoluciones 404, Privacidad a la portada, sin contacto |
| **Total** | | **18/40** | **Deficiente** |

## Design Specificity Verdict

LLM: portada propia en la piel (lema, hero con tiradores, zonas en cm) y de plantilla en el esqueleto; su texto no menciona Perú ni taller y los estampados peruanos solo aparecen en el editor. Tienda, carrito y pago son WooCommerce con los colores del tema.

Detector: templates, patterns y parts salida 0, con 2 avisos de color (404.html, falso positivo; index.html, probable falso positivo sin comprobar). URL: overused-font y cream-palette (falsos positivos: DESIGN.md) y hero-eyebrow-chip en la portada (por revisar). Medidas: ningún texto <12 px, ningún fallo de contraste AA en textos, sin desborde a 1280 y 375 px. Defectos medidos: `/carrito` con producto se desborda a 320 px (364); anillo de foco 1,64:1 sobre oscuro y 2,14:1 sobre rojo; carrito lateral cerrado en el orden de tabulación; pago sin h1, errores sin asociar al campo, resumen plegable sin teclado, "Realizar el pedido" 42 px.

Overlay: no disponible (ERR_BLOCKED_BY_CLIENT en localhost:8400).

## Priority Issues

1. [P0] El pago no ofrece ninguna entrega. Causa comprobada: 0 zonas de envío, por lo que WooCommerce da el carrito por no enviable (needs_shipping false) aunque las prendas lo son. Lo prometen la portada ("te lo enviamos"), el minicarrito y la confirmación ("al pagar, eliges cómo recibirlo"). Fix: zona Lima Metropolitana y recojo en el taller, bloque "¿Cómo lo recibes?", destinos limitados a Lima y dirección distinta para regalos. Faltan tarifa, dirección y horario (datos del negocio). Comando: /impeccable harden.
2. [P1] Salidas rotas: "Cambios y devoluciones" da 404 y "Privacidad" lleva a la portada (ambas páginas en borrador); términos y privacidad del pago sin enlace; sin contacto; "Pago seguro" con solo una transferencia de prueba. Fix: publicar con texto real, enlazar, contacto visible, revisar "Pago seguro". Comando: /impeccable harden.
3. [P1] Formulario y errores no hablan peruano: "colonia", "Localidad", código postal obligatorio, teléfono opcional, sin distrito ni referencia; errores con fallos; sin h1; errores sin asociar; resumen móvil sin teclado; "Realizar el pedido" de 42 px a ~1737 px. Fix: separar datos y entrega, Distrito y Referencia, reescribir errores, corregir estructura y teclado. Comando: /impeccable clarify + harden.
4. [P1] La tienda no continúa la promesa y el precio confunde: "Diseña la tuya" lleva a "Tienda" con prendas lisas; "Camisetas" repite los 4 resultados; tarjetas sin "desde"; rango que responde a la XXL; carrito con S/ 53.90 e "Impresión y estampados: S/ 14.00" sin base. Fix: "Elige tu modelo", "Desde S/ 39.90 + impresión", verbo en tarjetas, desglose en carrito, quitar órdenes sin sentido. Comando: /impeccable clarify.
5. [P2] Móvil y estados por defecto: menú y cierre 24×24, enlaces del menú 22 px, cantidad 30×27, papelera 24×24, pie 17 px; desborde a 320 px; foco sin contraste; carrito lateral en el tabulador; carrito vacío de WooCommerce. Fix: 44 px, deshacer al eliminar, foco que contraste, vacío con voz propia. Comando: /impeccable adapt + polish.

## Persona Red Flags

- Casey: menú en mitad de la cabecera; papelera sin deshacer; menú sin carrito ni "Diseña la tuya".
- Riley: entrega en Tumbes; camiseta sin diseño sin aviso; "Pecho (logo)" anunciado y ausente del editor.
- Jordan: busca el envío prometido; duda si S/ 14.00 se suma; "colonia"; "Añadir cupones".
- Sam: resumen sin teclado; errores sin asociar; controles fuera de pantalla en el tabulador; foco sin contraste en pie y banda roja.
- Quien regala con foto: sin dirección distinta de facturación; sin saber cuándo llega; política de cambios 404; sin revisión final en grande.

## Minor Observations

- Tres listas de zonas distintas en la portada; "Elige tu modelo" repetido.
- Tienda en 3 columnas con tarjeta huérfana; "Mostrando todos los 4 resultados"; "Pedido de la tienda".
- Franja de papel de ~16 px entre la banda roja y el pie; papel bajo el pie en páginas cortas.
- Menú móvil en blanco y negro puros.
- Ilustración del hero cortada en la primera pantalla móvil.
- "Proceder al pago" e "Ir al pago"; "Total estimado" y "Total"; "Close" en inglés.
- Rendimiento: con producto en el carrito, los scripts de la portada pasan de 18 a 72 (pila React del carrito lateral); entorno local con versiones de desarrollo: confirmar en producción.

## Questions to Consider

- Si borras el logo del pago, ¿qué queda de "Tu diseño, tu ley"?
- ¿Por qué la identidad peruana se descubre recién dentro del editor?
- ¿Hace falta una "Tienda" con 4 productos, o "Diseña la tuya" debería ser un selector de modelo?
- ¿Puedes decir "Pago seguro" con una transferencia de prueba y sin políticas publicadas?
