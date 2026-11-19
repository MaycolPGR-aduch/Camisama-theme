---
target: tienda, carrito y pago (sin portada)
total_score: 24
max_score: 40
na_heuristics: 
p0_count: 0
p1_count: 3
target_identity: "file:C:\\Camisama\\themes\\camisama\\templates"
timestamp: 2026-11-19T20-50-41Z
slug: themes-camisama-templates
---
Method: dual-agent (A: revisión de diseño · B: detector + navegador)

Alcance: tienda, categoría, carrito (con prenda, vacío y lateral), pago (envío y recojo) y "Sigue tu pedido", a 320, 390 y 1280 px. Portada fuera: otra sesión la está cambiando. Sin pedidos.

## Design Health Score

| # | Heurística | Nota | Problema clave |
|---|---|---|---|
| 1 | Visibilidad del estado | 2 | El total suma S/ 10 de envío sin distrito; tras cambiar a Lima sigue el error viejo "No hay opciones de envío…" |
| 2 | Lenguaje del mundo real | 3 | Voz de tú ("Cómo pagas", "Ver cómo va"), pero salen "(tarifa provisional)", "Municipalidad Metropolitana de Lima" y "Dirección de facturación" al recoger |
| 3 | Control y libertad | 3 | Deshacer, "Editar diseño" y "Volver al carrito". (A puso 2 porque Deshacer le falló con prendas diseñadas; no se reproduce: se verificó que recupera la prenda con su diseño) |
| 4 | Consistencia | 2 | Campos de 4 px y borde gris, rojo de error de WooCommerce, total en Inter 16 px/500, textos de 10,7 y 12,25 px |
| 5 | Prevención de errores | 2 | 25 departamentos para 2 zonas; teléfono "abc" y correo "maria@gmail" sin aviso al salir del campo |
| 6 | Reconocer antes que recordar | 3 | Miniatura del diseño y desglose a la vista; la tarifa del distrito solo se conoce al elegirlo |
| 7 | Flexibilidad y eficiencia | 2 | Tres desplegables nativos (25/10/43 opciones) sin buscador ni autocomplete |
| 8 | Estética y minimalismo | 3 | Limpio; "S/ 53.90" tres veces por línea y metadatos apretados |
| 9 | Recuperación de errores | 2 | Error de WooCommerce que culpa a la persona; "escríbenos" sin enlace en el seguimiento |
| 10 | Ayuda y documentación | 2 | "¿Dudas? Escríbenos" en el pago, pero sin plazos y con "Dirección/Horario por confirmar" en el recojo |
| **Total** | | **24/40** | **Aceptable** |

## Design Specificity Verdict

LLM: textos hechos para Camisama ("¿Cómo lo recibes?", combos de ubigeo, desglose prenda + impresión con la miniatura real, "Aquí falta tu diseño"); capa visual del carrito y del pago todavía de WooCommerce (tabla PRODUCTO/TOTAL, "TOTAL DEL CARRITO", campos de 4 px, avisos rojos #CC1818, "estás comprando como invitado"). El sello no aparece en el pago. Las cuatro tarjetas de la tienda no dicen en qué se diferencian los modelos.

Detector: salida 0 con 1 aviso `design-system-color` en archive-product.html (texto rgb(0,0,0)): falso positivo, lee `has-muted-color` sin resolver la variable; el color real es #5F5E66 (5,83:1). Navegador: sin errores de consola ni peticiones ≥400, contraste de texto mínimo 5,15:1, anillo de foco general 10,08:1 (16,44:1 en el pie). Fallos medidos: foco invisible en las tarjetas "Envío en Lima y Callao / Recojo en el taller" (1,27:1, sin contorno) y sin flechas; casillas con contorno de 1 px; tabla de "Sigue tu pedido" desborda 33 px a 320; textos de 10,72 px (vía del envío) y 12,25 px (metadatos); "+ Agregar referencia" y casillas de 20 px; el resumen del pedido recibe el foco después del botón final; botón final a ~3 pantallas en el móvil.

Overlay: no disponible (ERR_BLOCKED_BY_CLIENT en localhost:8400); servidor parado.

## Priority Issues

1. [P1] El envío se cobra sin distrito y se presenta como "provisional". Carrito y pago suman S/ 10 (la tarifa más baja) sin distrito; en la periferia el total sube en el último paso. "(tarifa provisional)" sale tal cual al cliente (carrito, pago y seguimiento) a 10,72 px; el carrito lateral dice "El envío se calcula en el momento del pago" y se contradice. Fix: sin distrito, "Envío: desde S/ 10, según tu distrito" fuera del total; quitar "provisional" de lo que ve el cliente; tarifa junto a cada distrito. Comando: /impeccable clarify.
2. [P1] Zona de entrega después de la dirección y con errores de WooCommerce. Se escribe la calle antes de saber si se llega; 25 departamentos para 2 zonas, "Lima" 8.ª de 10 provincias; el error genérico ("Verifica que la dirección sea correcta…") culpa a la persona y sigue tras elegir Lima; sin autocomplete; errata "La Uniòn". Fix: primero el distrito (Lima Metropolitana y Callao arriba o buscador, "Estoy fuera de Lima" lleva al recojo), mensaje propio y limpiar el error al cambiar. Comando: /impeccable clarify.
3. [P1] El pago por transferencia no dice cuándo. Ni cuándo llegan los datos, ni el plazo para pagar, ni cuándo se prepara o llega; "Sigue tu pedido" dice "Esperamos tu transferencia" sin recordar a qué cuenta. Para quien regala, "¿llega a tiempo?" es la primera pregunta. Fix: plazos y datos de cuenta en el pago, la confirmación, el correo y el seguimiento (datos del negocio). Comando: /impeccable harden.
4. [P2] El recojo pide una dirección de facturación completa y muestra "Taller Camisama (dirección provisional) · Dirección por confirmar, Lima, Municipalidad Metropolitana de Lima · Horario por confirmar". Fix: para el recojo, solo nombre, apellidos y teléfono; "Te escribimos con la dirección y el horario cuando esté listo". Comando: /impeccable distill.
5. [P2] Aspecto y foco de WooCommerce en el pago: campos de 4 px y borde gris, error #CC1818, títulos de paso a 16 px, total en Inter 16 px, textos de 10,7/12,25 px; tarjetas de entrega sin foco visible (1,27:1) ni flechas; casillas con contorno de 1 px. Fix: campos de la marca (12 px, lino 1,5 px), total en Bricolage, mínimo 14 px, foco de 2 px en las tarjetas y navegación con flechas. Comando: /impeccable polish.

## Persona Red Flags

- Casey: botón final en y≈2046 de 2186 px a 390; tres desplegables nativos seguidos; a 320, "Envío / en Lima / y Callao" en tres líneas.
- Riley: teléfono "abc" y correo "maria@gmail" sin aviso al salir; error viejo tras cambiar de departamento; categoría Niños con una sola tarjeta pegada a la izquierda y miga que se salta "Modelos"; Deshacer falla si se carga otra página entre quitar y deshacer (sesión compartida entre pestañas).
- Jordan: "Desde S/ 39.90 + impresión" sin decir cuánto cuesta la impresión; "estás comprando como invitado" no aclara si hace falta cuenta; "(tarifa provisional)".
- Lucía (regalo desde el móvil): ningún plazo de entrega en todo el recorrido; la nota del pedido va plegada y sin pista; "Usar la misma dirección para facturación" marcado sin explicar qué pasa al desmarcarlo.

## Minor Observations

- Carrito lateral vacío sin la frase explicativa del carrito; h1 "Carrito" a la izquierda y el vacío centrado.
- "Envío: S/ 20.00 vía Envío a Pachacámac" repite "Envío"; "Por ejemplo: 176" coincide con un pedido real.
- El aviso "Quitaste Camiseta clásica." no dice diseño, color ni talla.
- El pie enlaza "Camisetas" y "Niños" sin filtros en la tienda; botón "Cerrar" del carrito lateral sobresale 4 px a 320.
- En "Sigue tu pedido", el campo con error no lleva aria-invalid y "escríbenos" no es enlace.

## Questions to Consider

- Si solo se entrega en Lima y Callao, ¿por qué recorrer 25 departamentos en vez de preguntar "¿En qué distrito estás?" con la tarifa al lado?
- Quien paga por transferencia un viernes por la noche: ¿cuándo sabe que su pago llegó y cuándo tiene su prenda?
- ¿Dónde vive en el recorrido la promesa de "cuándo llega", y dónde se estampa el sello al confirmar?
