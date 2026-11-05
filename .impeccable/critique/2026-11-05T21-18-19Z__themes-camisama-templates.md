---
target: "resto de la web tras el plan: portada, tienda, carrito y pago"
total_score: 28
max_score: 40
na_heuristics: 
p0_count: 0
p1_count: 2
target_identity: "file:C:\\Camisama\\themes\\camisama\\templates"
timestamp: 2026-11-05T21-18-19Z
slug: themes-camisama-templates
---
⚠️ DEGRADED: single-context (sin subagentes porque no se pidieron; el autor de los cambios se puntúa a sí mismo)

Alcance: portada, tienda, categoría, carrito, carrito lateral, pago, mi cuenta, contacto y 404, a 320, 375, 390 y 1280 px. Sin pedidos nuevos en este repaso. La crítica anterior (18/40) fue de doble agente: la comparación es orientativa.

## Design Health Score

| # | Heurística | Antes | Ahora | Problema clave |
|---|---|---:|---:|---|
| 1 | Visibilidad del estado | 2 | 3 | Insignia, confirmación al añadir y aviso al quitar. El pago sigue sin indicar pasos |
| 2 | Correspondencia con el mundo real | 1 | 3 | Departamento, provincia, distrito y referencia. Quedan "Opciones de envío" y "Nombre de usuario" |
| 3 | Control y libertad | 2 | 3 | Deshacer, editar diseño y vuelta al carrito. Tras pedir no hay forma de cancelar |
| 4 | Consistencia | 2 | 3 | 44 px y "Desde" en tienda y portada. Los relacionados de la ficha aún dicen "S/ 54.90" a secas; "Tienda" en el pie frente a "Modelos" en el menú |
| 5 | Prevención de errores | 1 | 3 | Combos, tarifa por distrito y teléfono obligatorio. La transferencia promete datos que no existen |
| 6 | Reconocer antes que recordar | 2 | 3 | Tarjetas con estampado y prenda a la vista en carrito y pago. Las tarjetas no muestran colores |
| 7 | Flexibilidad y eficiencia | 2 | 2 | Invitado y autocompletado bien; /mi-cuenta sin registro y sin seguimiento de pedido para invitados |
| 8 | Estética y minimalismo | 3 | 3 | Limpio. El pago mide ~2200 px en móvil y "¿Cómo lo recibes?" y "Opciones de envío" repiten decisión |
| 9 | Recuperación de errores | 2 | 3 | Errores ligados al campo y en español, foco al primero. Solo se reescribieron los textos comunes |
| 10 | Ayuda y documentación | 1 | 2 | Preguntas, legales y contacto existen, pero Contacto muestra "[Por completar]": hoy no se puede contactar |
| **Total** | | **18/40** | **28/40** | **Bueno, en el límite inferior** |

## Design Specificity Verdict

LLM: la portada ya es propia; la tienda ganó con las prendas estampadas y "Aquí falta tu diseño" da voz a un estado que era de WooCommerce. El carrito y el pago siguen siendo WooCommerce bien vestido, algo razonable en una superficie de tarea. Las prendas dibujadas son lo que más delata que es una maqueta.

Detector: templates, patterns y parts, salida 0, con 3 avisos de color rgb(0,0,0) en 404, tienda e index (falso positivo: lee el color antes de resolver las variables del tema). En el navegador sin desbordes a 320, 375, 390 y 1280 px, controles a 44 px, sin peticiones fallidas y foco con contraste de 4,7:1 a 16,4:1.

Overlay: no disponible; el script de detect.js no carga desde localhost:8400 (mismo bloqueo que en las críticas anteriores). El servidor quedó detenido.

## Overall Impression

El recorrido ya funciona de principio a fin y el cobro es correcto. Lo que falta para abrir ya casi no es diseño: son datos del negocio que hoy se ven como huecos en pantalla.

## What's Working

- Combos de ubigeo con tarifa por distrito: lo más específico de Perú en la web, y evita un error caro.
- Deshacer: protege un diseño hecho a mano sin confirmaciones que estorben.
- Tarjetas con estampado y precio "Desde + impresión": se entiende qué se vende y que el precio no es final.

## Priority Issues

1. [P1] Huecos visibles al cliente: Contacto con "[Por completar]", legales con aviso de borrador, "tarifa provisional" y "dirección provisional" en el pago. Fix: datos reales y quitar avisos. Depende del negocio. Comando: /impeccable harden.
2. [P1] La transferencia promete datos que no hay: "Te enviaremos los datos de la cuenta" sin cuenta configurada ni envío automático, y es el único medio de pago. Fix: cuenta real en la confirmación y el correo, o pasarela. Comando: /impeccable harden.
3. [P2] Prendas dibujadas frente a la promesa de calidad. Fix: plantillas reales, ya en marcha en docs/plan-plantillas-personalizador.md; CardPrint usa las zonas de cada producto, así que las tarjetas se adaptan al calibrarlas. Comando: /impeccable polish.
4. [P2] El pago pide dos veces lo mismo: "¿Cómo lo recibes?" y "Opciones de envío", con una sola opción tras elegir distrito; además mide ~2200 px en móvil. Fix: ocultar la segunda cuando hay una sola opción. Comando: /impeccable distill.
5. [P2] Tras pedir como invitado no hay cómo seguir el pedido, y /mi-cuenta no permite registrarse. Fix: decidir registro o seguimiento por correo y número. Comando: /impeccable onboard.

## Persona Red Flags

- Casey: pago largo con la acción final al fondo; el resumen plegado oculta el total del envío hasta abrirlo o mirar la cabecera del resumen.
- Riley: Contacto sin datos; pedir por transferencia y no recibir instrucciones.
- Jordan: "Opciones de envío" tras haber elegido entrega; "(tarifa provisional)" genera duda real.
- Sam: flujo completo por teclado y anuncios de estado correctos en lo probado; sin probar con un lector de pantalla real.

## Minor Observations

- Relacionados de la ficha con precio sin "Desde".
- "Tienda" en el pie y "Modelos" en el menú.
- Nombres de producto del carrito a 39 px y "Inicio" en las migas: por debajo de 44, aceptado.
- Pedidos de prueba en la base local.
- Rendimiento con carrito lleno sin confirmar en producción.
- Menú móvil sin acceso directo a "Diseña la tuya".

## Questions to Consider

- ¿Tiene sentido abrir solo con transferencia, o la pasarela debe ir antes de la apertura?
- ¿Hace falta registro, o basta con seguir el pedido por número y correo?
- Con las plantillas nuevas, ¿las tarjetas deben seguir usando estampado de muestra o fotos propias del taller?
