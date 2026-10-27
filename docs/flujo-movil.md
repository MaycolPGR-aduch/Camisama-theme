# Flujo de personalización en el móvil

Plan aprobado el 27 de octubre de 2026 (`/impeccable shape`). Solo para pantallas de menos de
782 px, donde las columnas de la ficha se apilan; el escritorio sigue con dos columnas (editor y
compra fija). Sistema visual: [DESIGN.md](../DESIGN.md). Contexto: [PRODUCT.md](../PRODUCT.md).

## Quién llega y para qué

Una persona sin experiencia en diseño, para sí o para regalar, desde el móvil, a menudo con una
mano e interrumpida. Es una tarea (modo Operate): claridad y gestos conocidos antes que efecto.
Hoy el editor vive dentro de una ficha de WooCommerce: sin invitación a empezar, lienzo pequeño,
herramientas lejos de la prenda y talla preseleccionada.

**Resultado:** pasar de "tengo una idea" a "diseño en el carrito" en tres pantallas claras, viendo
siempre la prenda mientras se diseña y eligiendo la talla a conciencia. En cada paso hay una sola
acción principal y nunca se pierde lo hecho.

## Los tres pasos

| Paso | Qué se ve | Acción principal |
|---|---|---|
| **1. Ficha** | La prenda grande en el color actual, nombre, "desde S/ 39.90" con "+ impresión según el tamaño", y debajo descripción, garantías y zonas | **Personalizar** (rojo). Secundaria: "Comprar sin diseño", que lleva al paso 3 |
| **2. Diseño a pantalla completa** | Arriba: cerrar, Frente/Espalda y deshacer. En medio: el lienzo grande. Abajo: una hoja con "Añadir en: zona", el color de la prenda y las pestañas Estampados / Texto / Tu imagen. Al pie: el total | **Siguiente: talla** |
| **3. Talla y pago** | Miniatura del diseño (frente y espalda), desglose del precio, talla en botones sin ninguna marcada, cantidad | **Añadir al carrito**, con la confirmación actual (cajón con la miniatura y "Tu diseño está en el carrito") |

**Momento clave:** al tocar un estampado o seleccionar un elemento, el lienzo se acerca a esa zona
y la hoja cambia a los ajustes del elemento (tamaño, giro, acciones y "Listo"). Se ajusta viendo de
cerca.

## Alcance

- **Entra:** la ficha en el móvil, el modo diseño a pantalla completa y el paso de talla y pago.
- **No se toca:** el escritorio; las reglas de precio y validación; el borrador, añadir sin
  recargar y editar desde el carrito; "Añadir en: zona" como forma de elegir dónde va cada cosa.
- **Va aparte, aunque encaja aquí:** texto que aparece mientras se escribe (dentro de la hoja de
  Texto) y escalones de precio explicados.
- **No se busca:** un asistente que obligue a seguir pasos, animaciones vistosas ni esconder el
  precio hasta el final.

## Estados

- **Primera vez (editor vacío):** invitación sobre la prenda, "Elige un estampado, escribe tu
  frase o sube tu foto", señalando la hoja.
- **Con borrador:** el botón de la ficha dice "Seguir diseñando"; el editor muestra "Recuperamos…".
- **Editar desde el carrito:** se abre directamente el paso 2 con ese diseño; "Siguiente" lleva a
  "Guardar cambios".
- **Cargando el editor** (Fabric, unos 300 KB): "Personalizar" muestra que se está preparando y no
  abre un editor vacío.
- **Errores** (contraste, calidad de imagen, WooCommerce): junto al lienzo o al botón, como ahora.
- **Paso 3 sin talla:** el botón pide elegirla con un mensaje en la página, sin `alert()`.
- **Sin JavaScript:** la ficha de WooCommerce de siempre, para comprar la prenda lisa.

## Interacción

- El modo diseño es una pantalla propia: el "atrás" del teléfono y "Cerrar" vuelven a la ficha sin
  perder nada (el borrador se guarda), en lugar de salir de la página.
- El lienzo ocupa la parte de arriba; el dedo desplaza la hoja, nunca la página de debajo. Lo
  seleccionado se arrastra; un toque fuera lo suelta (se mantiene `allowTouchScrolling`).
- La hoja de herramientas se abre a media altura y se sube a casi toda la pantalla para ver el
  catálogo; bajada del todo deja ver la prenda entera. Controles de 44 px.
- El paso 3 es una pantalla propia con "Editar diseño" para volver. Sus botones de talla manejan el
  formulario de WooCommerce, que sigue enviando el pedido (wc-bridge.js).
- Accesibilidad: el modo diseño se anuncia como diálogo y mantiene el foco dentro; Esc lo cierra y
  el foco vuelve a "Personalizar".

## Decisiones

- **Color de la prenda:** en el paso 2, junto al lienzo.
- **Guía "¿Qué talla?":** necesita las medidas reales por talla y modelo (ancho y largo en cm), que
  pasará el usuario. No se inventan; hasta entonces el enlace no se muestra.

## Construcción por tandas

1. **Hecha.** Ficha del paso 1 en el móvil: la prenda tal como va, "Desde S/ … + impresión según
   el tamaño", "Personalizar" / "Seguir diseñando", "Comprar sin diseño" / "Ir a talla y pago" y
   "Preparando el editor…" mientras carga. Incluye lo mínimo del modo diseño (pantalla completa
   con ✕, "atrás" del móvil y Esc, pie con el total y "Siguiente: talla"). Mientras no esté el
   paso 3, "Siguiente: talla" muestra el formulario de WooCommerce bajo la ficha.
2. **Hecha.** Modo diseño completo: diálogo con `aria-modal` y el tabulador encerrado; hoja de
   herramientas con asa y tres alturas (media al abrir, casi completa para el catálogo, mínima
   para ver la prenda entera), por toque, deslizamiento o flechas; el lienzo usa el hueco que
   deja la hoja. Invitación "Elige un estampado, escribe tu frase o sube tu foto" con el diseño
   vacío (también en escritorio). Pendiente para la tanda 3: al seleccionar desde el catálogo, los
   ajustes quedan más arriba en la hoja, fuera de la vista.
3. **Hecha.** Al seleccionar, el lienzo se acerca a la zona del elemento (zoom de Fabric, hasta ×4)
   y la hoja muestra solo sus ajustes con "Listo"; si es un texto, también el panel de texto.
4. **Hecha.** Paso 3 en pantalla propia (BuyStep): miniaturas del diseño (o la prenda lisa),
   "Editar diseño", desglose, talla en botones sin preselección (la M por defecto de WooCommerce
   se quita al entrar), guía "¿Qué talla?" con medidas de referencia (Products/SizeGuide.php,
   editables por producto con el meta _camisama_size_guide o el filtro camisama_size_guide),
   cantidad y "Añadir al carrito" / "Guardar cambios"; sin talla, mensaje en la página. El cajón
   del minicarrito se abre encima al añadir. Del diseño a la talla (y al revés) se sustituye la
   entrada del historial: "atrás" vuelve a la ficha.
5. Repaso (`/impeccable polish`) y crítica nueva para comparar con el 25/40.
