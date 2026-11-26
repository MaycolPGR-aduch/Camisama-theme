# Registro de cambios

Cambios y avances de la tienda Camisama, del más reciente al más antiguo. Lo pendiente está en
[docs/estado-de-la-web.md](docs/estado-de-la-web.md); el detalle de cada cambio, en el historial
de git (`git log`).

Cada entrada separa lo **añadido**, lo **cambiado**, lo **corregido** y las **decisiones** que
conviene recordar.

## 2026-11-26

### Añadido
- **Plan de subida a Hostinger** ([docs/subida-hostinger.md](docs/subida-hostinger.md)): Cloud
  Startup, instalación limpia, staging, lo que es del negocio y cómo publicar sin perder pedidos.
- **Paquetes para el servidor** (`bin/paquete.py` y la acción *Paquetes* de GitHub): plugin compilado,
  tema y carga (scripts, estampados y fotos).
- **Instalación en el servidor** con un solo script (`bin/produccion/configurar.sh`, instrucciones en
  [bin/produccion/LEEME.md](bin/produccion/LEEME.md)): deja la tienda configurada y en
  "Próximamente". Probado en un WordPress nuevo fuera de `.test`, dos veces seguidas.

### Corregido
- **Editor:** un texto que se alargaba y se encogía para caber ya no se queda pequeño al acortarlo:
  vuelve a la letra elegida (la de inicio, la del control de tamaño o la de "Partir en dos líneas"),
  o a la mayor que quepa. Vale también para los diseños que se editan desde el carrito.
- **Carrito, carrito lateral y resumen del pago, más claros:**
  - la prenda en grande: 112 px en el móvil (antes 80) y en el resumen del pago (antes 88);
  - color y talla en un renglón justo bajo el nombre, para distinguir dos prendas del mismo modelo;
  - el precio de la línea una sola vez (el precio por unidad solo con más de una, con "c/u"), y el
    desglose en un renglón: "prenda S/ 39.90 + impresión S/ 14.00";
  - "Editar diseño" como enlace propio, al final, sin la etiqueta "Diseño:".
- **Editor en el móvil:** "Listo" está fijo a la derecha del asa de la hoja; antes se perdía de vista
  al escribir un texto largo.

### Cambiado
- La ficha que edita un diseño del carrito (`?camisama_edit=`) pide no guardarse en ninguna caché.
- Sin el cargador de emojis de WordPress en la tienda: rompía la lista del editor y pedía imágenes a
  otro servidor.
- `bin/import-fotos-modelos.php` funciona fuera de local si se añade "confirmar".

## 2026-11-24

### Añadido
- **Crítica independiente de toda la web** (dos evaluadores con navegador propio): **25/40**. Informe en
  `.impeccable/critique/`. Resueltos sus cinco problemas importantes:
  - **Aviso de escalón al añadir**, junto a los demás avisos del editor y siempre a la vista: cuenta desde
    lo que costaba la zona antes de añadir, y "Seguir en Mediano" encoge el conjunto. Las fotos nacen con
    aire bajo el límite de su escalón (16 cm en vez de 20).
  - **Un solo plazo en todas partes** (`PaymentTimes::promise()`, provisional): "Llega en 3 a 5 días
    hábiles desde que confirmamos tu pago; si la recoges, está lista en 2 a 3", en la ficha, el carrito,
    el pago y las preguntas de la portada.
- **Plantillas fotográficas en los 4 modelos** ([docs/plantillas-fotograficas.md](docs/plantillas-fotograficas.md)):
  26 maquetas recortadas (13 colores, frente y espalda) en el editor, la tienda, la ficha, el carrito y la
  confirmación. `bin/plantillas/preparar.py` las prepara y `bin/import-fotos-modelos.php` las pone en la
  tienda local, con copia de lo anterior y opción de volver ("restaurar"). **Son imágenes generadas con
  IA**, no fotos de las prendas del taller.
- **La portada, con las mismas fotos**: hero, ejemplos con sus cuatro colores, pasos, cierre e imagen
  para compartir. La zona discontinua y los estampados salen de las medidas del editor. El pie de los
  ejemplos dice que las prendas son imágenes generadas con IA. Las tarjetas de modelos muestran la
  primera frase entera de su descripción, como en la tienda.
- **Correo y teléfono revisados al salir del campo**, en el pago (`client/checkout/contact.js`):
  "maria@gmail" o un celular de 8 dígitos se avisan en el momento y dicen cómo se corrigen. Se
  aceptan celulares y fijos peruanos, con o sin +51, y teléfonos de otros países con "+".
- **En qué se diferencia cada modelo**, en las tarjetas de la tienda: la primera frase de su
  descripción corta (corte, tela, tallas), con el precio y el botón alineados entre tarjetas.

### Cambiado
- En "Sigue tu pedido", "escríbenos" lleva a la página de contacto, los campos con error quedan
  marcados y ligados al aviso, un correo mal escrito ya no se borra al enviar y el ejemplo del
  número no coincide con un pedido real.
- Los avisos de WooCommerce, con el radio y el rojo de la tienda.

### Corregido
- **El recojo venía elegido solo** ("Recojo GRATIS" en el carrito y el pago abierto en recojo): regresión
  del 19 de noviembre, al quitar el envío sin distrito. Ahora el pago abre en envío y el carrito dice
  "Entrega desde S/ 10 en Lima y Callao, o recojo gratis en el taller: la eliges al pagar".
- **Segundo formulario al recargar el pago** tras pasar por recojo (de 8 a 15 campos): la facturación
  guardaba la región de la tienda y el envío no. La región sale solo del distrito.
- **Recojo con menos datos:** correo, nombre, apellidos y teléfono, sin dirección de facturación. La calle
  es obligatoria solo con envío (en el formulario y en el servidor). El aviso "elige tu distrito" ya no
  se queda al pasar a recojo.
- **La ficha ya no salta al cargar:** CLS de 1,3 a 0,00 en el móvil y de 0,25 a 0,06 como mucho en
  escritorio (límite 0,1). El servidor pinta la prenda y la entrada al editor en su sitio definitivo; la
  galería de WooCommerce queda de respaldo si el editor no carga.
- **Pasos de la portada legibles:** los que no están en curso iban al 35 % (1,66:1).
- Los campos vacíos del pago dicen "Falta este dato: …"; el aviso interno de las páginas legales
  ("Borrador para revisar…") solo lo ve quien gestiona la tienda.
- Filo blanco a los lados del lienzo del editor, y franjas blancas junto a las vistas previas de la
  confirmación y de "Revisa antes de añadir": llevan el mismo papel que la imagen.
- Mangas a la misma escala que el resto de la prenda (en el piloto, un tercio más pequeñas).
- Los campos de "Sigue tu pedido" seguían con el aspecto de WooCommerce (su regla ganaba a la del
  tema), aunque el pulido del 19 los daba por cambiados.
- Esquinas blancas cuadradas detrás de los desplegables del pago; icono del error encogido en los
  mensajes de dos líneas; etiqueta del campo con error, en el rojo de la marca.

### Decisiones
- Niños en rojo y mostaza se tiñen desde la blanca, la única foto que hay.
- Las zonas conservan identificador y centímetros: no cambian precios, impresión ni diseños guardados.
- El formato del teléfono solo se exige en el formulario: el servidor sigue con la comprobación de
  WooCommerce (dígitos y signos).

## 2026-11-19

### Añadido
- **Crítica independiente de tienda, carrito y pago** (dos evaluadores, sin portada): **24/40**,
  desde 18/40. Informe en `.impeccable/critique/`.

- **Plazos de la transferencia** (`Checkout\PaymentTimes`, **provisionales**: 48 horas para pagar,
  preparación en 2 a 3 días hábiles y envío en 1 a 2 días hábiles más). Salen en "Cómo pagas", en
  las instrucciones de la confirmación y del correo, y en "Sigue tu pedido", que además calcula la
  fecha límite ("Esperamos tu transferencia hasta el 06/11/2026 a las 17:18") y muestra los datos de
  la cuenta cuando estén configurados.

### Cambiado
- **Pulido del pago, el carrito y "Sigue tu pedido"** (`/impeccable polish`, tema):
  - Campos con el aspecto de la tienda: blancos, radio de 12 px, borde de 1,5 px y 56 px de alto,
    con la etiqueta flotante a 14 px una vez escrito el dato (WooCommerce la encogía a 13 px). El
    borde es un grafito aclarado (`--camisama-field-border`, 3,3:1 sobre el papel): con el lino de
    las tarjetas los campos casi no se veían. También lo usan la ficha y "Sigue tu pedido".
  - Errores en el rojo de la marca; la cantidad del carrito y la nota del pedido, del mismo juego
    (la nota a 16 px para que el iPhone no amplíe la página).
  - Títulos de cada paso en Bricolage 700 y el total del pedido en Bricolage a tamaño grande; el
    resumen del pedido, una tarjeta blanca de 20 px.
  - Casillas de 24 px con borde de tinta que se rellenan al marcarlas, con toda la fila pulsable;
    "+ Agregar referencia" como enlace con su objetivo de 44 px.
  - Ningún texto por debajo de 14 px (detalles de la prenda y distrito del envío).
  - El aviso "Por ahora solo hacemos envíos a Lima…" y el de las páginas legales en borrador ya no
    llevan franja lateral: aviso redondeado, ámbar cuando advierte.
- **Recojo en el taller sin datos de más:** con el recojo ya no se piden departamento, provincia ni
  distrito (el campo solo es obligatorio con envío) y el lugar de recojo dice "Taller Camisama · Lima"
  con "Te escribimos con la dirección y el horario cuando tu pedido esté listo", en vez de "Dirección
  por confirmar… Municipalidad Metropolitana de Lima". La dirección y el horario reales siguen
  pendientes (`bin/seed-delivery.php`, marcado como provisional).
- **Primero dónde, luego quién:** en el pago, departamento, provincia y distrito van al principio de
  la dirección, antes de escribir la calle. En la dirección de entrega, "Con envío a domicilio"
  (Lima y Callao, Lima primero) va aparte de "Solo recojo en el taller", y cada distrito muestra su
  tarifa ("Pachacámac · envío S/ 20.00").
- **El envío no se suma hasta saber el distrito:** antes se sumaba la tarifa más baja (S/ 10) y el
  total subía al elegir un distrito lejano. Ahora el carrito dice "El envío se calculará al
  finalizar la compra" y el pago, "Elige tu distrito para ver el costo del envío".
- La etiqueta del envío ya no dice "(tarifa provisional)": las tarifas siguen siendo provisionales,
  pero eso no es asunto del cliente (anotado en el estado de la web).

### Corregido
- "Envío / Recojo" no mostraba el foco del teclado (1,27:1) ni respondía a las flechas: ahora
  lleva el anillo índigo, las flechas cambian de opción y Tab entra solo en la elegida.
- En "Sigue tu pedido", el detalle del pedido se salía 33 px de la pantalla a 320 px y partía
  "Personalización" a media palabra.
- El aviso "Revisa este dato: distrito" salía bajo "Referencia", lejos de los combos: ahora sale
  justo debajo, con su icono, y el combo que falta se marca en rojo y queda ligado al aviso para los
  lectores de pantalla.
- Al pasar de envío a recojo y volver, ya no quedan combos huérfanos de la dirección anterior.
- El aviso "No hay opciones de envío… Verifica que la dirección sea correcta" culpaba a la persona y
  se quedaba al cambiar de departamento; ahora orienta: elegir el distrito o el recojo en el taller.

## 2026-11-18

### Añadido
- **Portada más dinámica, tanda 2** ([docs/home.md](docs/home.md)):
  - Franja de estampados del catálogo bajo el hero, en movimiento y con pausa. Sale del catálogo
    real del personalizador, con categoría o recargo premium (bloque `camisama/home-designs`).
  - Muestras de color en los ejemplos: blanco, negro, rojo y azul marino de la camiseta clásica.
  - "Cómo funciona" avanza con el scroll: la prenda se queda fija y se arma paso a paso.
  - En el móvil, los modelos se deslizan en carrusel.
- **Portada más dinámica, tanda 3**:
  - Las secciones entran suavemente al hacer scroll, escalonadas.
  - Los botones principales se hunden al pulsarlos y dejan un aro de "sello".
  - En el cierre rojo, el sello "Tu diseño, tu ley." cae sobre una camiseta al llegar.
- **Vista previa al compartir la portada** por WhatsApp o redes: imagen de 1200 × 630 con la marca
  (`og-home.png`, generada desde `bin/og-image/`), etiquetas Open Graph y descripción para
  buscadores.

## 2026-11-17

### Añadido
- **Sigue tu pedido** (`/sigue-tu-pedido/`, `Checkout\OrderTracking`): quien compró sin cuenta ve
  cómo va su pedido con el número y su correo. Cada estado dice qué significa ("Esperamos tu
  transferencia…"). Se enlaza desde el pie, la confirmación del pedido (con el número, si es su
  pedido) y los correos al cliente. La página la crea `bin/seed-tracking.php`.
- **Portada más dinámica, tanda 1** ([docs/home.md](docs/home.md)):
  - El hero turna tres ejemplos sobre la prenda (frase, estampado y foto, en negra y blanca), con
    la zona de impresión y su recargo real. Lleva pestañas y botón de pausa.
  - Junto a la prenda, un sello de marca que gira ("Desde 1 unidad ✦ Estampado en Perú").
  - "Zonas y precio" pasa a ser un simulador: tocas zonas y tamaños, el mapa se rellena y el total
    se suma en vivo con las tarifas reales. En el móvil, el total sigue a las fichas.
  - Sin JavaScript se ve como antes, y con movimiento reducido no hay avance automático ni giros.

### Cambiado
- Sin pestaña "Valoraciones (0)" en las fichas: las reseñas quedan desactivadas mientras no haya
  ninguna (`woocommerce_enable_reviews`, en `bin/setup-local.ps1`).
- **Pago más corto en el móvil** (de 2259 a 2126 px): con una sola opción, el costo del envío, el taller
  de recojo y la forma de pago se muestran como un dato, sin botón de radio ni recuadro. Nombre y
  apellidos van en una fila.
- Títulos del pago en la voz de la tienda: "Costo del envío", "Dónde lo recoges" y "Cómo pagas", en
  lugar de "Opciones de envío", "Ubicaciones de recolección" y "Opciones de pago".

### Corregido
- Al pasar por "Recojo" y volver a "Envío", "Usar la misma dirección para facturación" quedaba
  desmarcada y aparecía un segundo formulario entero. Ahora vuelve a como estaba.

### Decisiones
- DESIGN.md cambia "movimiento mínimo" por **movimiento que enseña**. Se puede mover lo que
  demuestra el producto, con pausa y alternativa reducida. Siguen fuera los degradados y las
  sombras de adorno.

## 2026-11-05

### Añadido
- **Piloto de plantillas fotográficas** de la camiseta clásica, oculto y solo local
  ([docs/plantillas-personalizador-piloto.md](docs/plantillas-personalizador-piloto.md)). La clásica a
  la venta no cambia.
- El editor distingue si la prenda está cargando, falta o falló la carga (con "Reintentar"), y nunca
  muestra otro color en su lugar.
- **Tarjetas con estampado** (`Products\CardPrint`), en la tienda, la portada y los relacionados: un
  estampado de muestra en la zona de impresión de cada prenda, no una camiseta lisa. Es solo para la
  tarjeta (la foto del producto no cambia) y lo asigna `bin/seed-demo.php`. La tienda avisa de que son
  ejemplos.
- **Volver al carrito** en el pago, que no tenía ninguna salida hacia atrás.
- **Deshacer al quitar una prenda**, en el carrito y en el carrito lateral (`Cart\CartUndo`): la prenda
  vuelve con su diseño y su cantidad. El aviso dura 10 segundos, o más si lo estás usando.
- **Carrito vacío con voz propia** (`Cart\EmptyCart`): "Aquí falta tu diseño" y una sola salida, "Elegir
  modelo", en lugar del mensaje y la lista de productos de WooCommerce.

### Cambiado
- La portada dice "Desde S/ 39.90" como la tienda, en lugar de "S/ 39.90 – S/ 44.90", un rango que solo
  respondía a la talla XXL.
- **Móvil, 44 px para el dedo:** menú y su cierre, enlaces del menú, cuenta, carrito, cantidad, papelera,
  "Editar diseño", la marca y los enlaces del pie. El menú va junto a la cuenta, no en mitad de la cabecera.
- **Foco del teclado que se ve en todas partes:** claro sobre el pie y las bandas oscuras y rojas (el
  índigo no contrastaba), y también en los botones, campos y cantidad de WooCommerce.

### Corregido
- **El estampado Rayo cobraba de más:** crecía solo hasta 40 cm (S/ 25) y dejaba el editor en un bucle
  de renderizado. La escala no contaba el borde del dibujo y cada medida daba un 6 % más. Ahora empieza
  en 10,1 × 20 cm (S/ 14).
- Los estampados nuevos respetan el tramo de precio también con su medida real: la Llama pasa de
  "Grande" a "Mediano". Si el cliente los agranda, se cobra lo que elige.
- Comprar una prenda lisa después de una con diseño ya no arrastra la impresión anterior.
- **Si a un color le falta una vista**, el móvil ya no deja los botones sin respuesta: abre el diseño con
  el aviso junto al selector de color. El administrador avisa de que ese color no se podrá comprar.
- Un error de compra ya no se queda tapando el total al cambiar de color o de diseño, ni al reintentar.
- **En móvil no se podía pulsar la papelera del carrito:** el bloque del total la tapaba.
- El carrito lateral cerrado ya no recibe el foco del tabulador, y su botón de cerrar se anuncia en español.
- A 320 px la cabecera ya no aplasta el logo.

## 2026-11-04

### Añadido
- **Portada nueva** ([docs/home.md](docs/home.md)): ejemplos de catálogo, texto y foto, modelos, pasos,
  zonas con precio de ejemplo calculado con las tarifas reales (bloque `camisama/home-price`), preguntas
  frecuentes y llamada final. `bin/check-home.php` comprueba el precio. Su crítica: 25/32.
- **Crítica del resto de la web** (portada, tienda, carrito y pago): **18/40**. El problema más grave:
  el pago no ofrecía ninguna entrega. Informe en `.impeccable/critique/`.
- **Páginas de confianza** (P1 de esa crítica): Política de privacidad, Cambios y devoluciones,
  Términos y condiciones, y Contacto. Son **borradores para revisar**: llevan un aviso y huecos
  "[Por completar: …]" donde falta un dato del negocio (`bin/seed-legal.php`, que no toca lo ya
  publicado). El pie y el pago las enlazan; "Cambios y devoluciones" daba 404 y "Privacidad" llevaba
  a la portada.
- **Entrega en el pago** (P0 de esa crítica): "¿Cómo lo recibes?" con "Envío en Lima" y "Recojo en el
  taller". `bin/seed-delivery.php` la configura y `setup-local` lo ejecuta. **Tarifa, dirección y
  horario son provisionales** y lo dicen en pantalla.
- **Los extremos del recorrido** (P2 de la cuarta crítica):
  - En la ficha del móvil, "O empieza con uno:" ofrece tres estampados sin recargo: un toque abre el
    editor con ese estampado puesto. "Comprar sin diseño" pasa a enlace discreto.
  - Al añadir al carrito (móvil y escritorio), una confirmación propia: el diseño en grande, talla,
    color, cantidad y total, "Ir al carrito", "Seguir con este diseño" y "Empezar otro diseño". Sin
    plazos ni promesas de entrega: aún no hay datos.

### Cambiado
- El minicarrito ya no se abre solo al añadir: lo sustituye la confirmación. Sigue en la cabecera,
  con su contador al día.
- **Dirección adaptada a Perú:** "Distrito" y "Referencia" en lugar de "Localidad" y "colonia", sin
  región (se reparte solo en Lima; el pedido guarda Lima como región de entrega) ni código postal,
  y teléfono obligatorio para coordinar la entrega. La dirección de envío va primero y se puede
  facturar a otra (regalos).
- **Formulario del pago** (P1 de la crítica del resto de la web):
  - Errores que se entienden: "Revisa este dato: dirección" en lugar de "introduce un dirección".
    Se corrigen filtrando la traducción de WooCommerce (`Checkout\Texts`), sin tocar el plugin.
  - "Política de privacidad" con tilde, "Ir al pago" en lugar de "Proceder al pago" y "Total" en
    lugar de "Total estimado".
  - El pago tiene título ("Finalizar compra"), un h1 que faltaba; el país, que era una sola opción,
    no se muestra.
  - "Realizar el pedido" mide 48 px, y al saltar a un error el campo queda a la vista, con su
    etiqueta.
  - La prenda del resumen se ve a 88 px (antes, unos 40).
- **Combos de departamento, provincia y distrito en el pago**, de todo el Perú (25 departamentos, 196
  provincias y 1892 distritos, en `data/ubigeo.json`): cada uno carga según el anterior. De ellos salen
  la ciudad y la región, y el pedido guarda el ubigeo; el servidor rechaza un código que no exista.
  También sirven para facturar desde cualquier región.
- **Tarifas de envío por distrito** (`Checkout\DeliveryRates`): cuatro franjas con **precios
  provisionales** (S/ 10, 14, 20 y 25). Sin distrito elegido se muestra la más baja, avisando de que
  depende del distrito.
- **Aviso de envíos:** "Por ahora solo hacemos envíos a Lima Metropolitana y el Callao". Si se elige otra
  zona, el aviso lo dice con claridad y propone el recojo en el taller; ahí no se ofrece envío.
- **La entrega incluye el Callao:** "Envío en Lima y Callao", y los Términos lo dicen.
- **Tienda y precio** (P1 de la crítica del resto de la web):
  - La tienda se titula "Elige tu modelo", explica que al precio de la prenda se suma la impresión y
    muestra los 4 modelos en una fila; en la miga de pan se llama "Modelos", como en el menú.
  - Las tarjetas de la tienda y de las categorías dicen "Desde S/ 39.90 + impresión" (el rango
    "S/ 39.90 – S/ 44.90" respondía a la talla XXL). La portada conserva su propio texto.
  - "Personalizar" en lugar de "Seleccionar opciones" en todas las tarjetas, también en la portada.
  - Sin selector de orden ni "Mostrando todos los 4 resultados": sobran con 4 modelos.
  - En el carrito y el pago, la línea desglosa "Prenda" e "Impresión y estampados", para que la
    impresión no parezca un recargo aparte.
  - Sin "Añadir cupones" mientras no haya cupones.
- "Pago seguro" desaparece de la cabecera del pago, el pie y la ficha mientras solo haya una
  transferencia de prueba: "¿Dudas? Escríbenos" y "Precios en soles, con IGV". Las instrucciones de la
  transferencia dicen que los datos de la cuenta llegan por correo.
- La portada ya no dice solo "te lo enviamos": "te lo llevamos en Lima o lo recoges en el taller".
- **Repaso final de la cuarta crítica** (`polish`):
  - "Talla y pago" pasa a "Talla y cantidad" (ahí no se paga) e "Ir a talla y pago", a "Elegir
    talla".
  - Resumen: "2 unidades × S/ 53.90" en lugar de "Por unidad: 2 × S/ 53.90", y "desde" solo en el
    total.
  - Carrito, minicarrito y resumen del pago: cada dato de la línea en su renglón, sin barras. En el
    carrito, miniatura de 144 px en escritorio y 88 px en el móvil, y total dentro de los márgenes.
  - Acercar una zona llega a 2,5× como máximo (a 4× la manga se veía pixelada y sin contexto).
  - Hoja de herramientas: en altura mínima solo queda el asa y la prenda se centra en el hueco; en
    altura completa el lienzo conserva unos 140 px.
  - "Añadir al carrito" sin talla ya no se ve apagado: se puede pulsar y dice qué falta.

### Corregido
- Cada error del pago queda ligado a su campo (`aria-describedby`), para que lo lea un lector de
  pantalla.
- **El pedido cobraba la tarifa de envío más baja**, aunque el pago mostrara la del distrito: al
  confirmar, WooCommerce recalculaba el envío sin ciudad ni región. Ahora se fijan también en ese paso.
- En el carrito y el pago ya no salen peticiones fallidas a `undefinedwc/store/v1/cart`: es un fallo de
  WooCommerce 11.2.1, que allí no le da a su código la URL de la API.

### Decisiones
- La zona de envío cubre todo Perú aunque se reparta solo en Lima: el pago no pide región, y una
  zona por región dejaría sin envío a quien escribe su dirección. El límite real es que no se puede
  elegir otra región.

### Corregido
- El recuadro de la zona medía unos 4 px más que el área de impresión.
- "Frente" ya no se parte en la lista de capas cuando aparece "Revisar".
- "100 %" ya no se parte en dos líneas en el extracto del producto.
- El aviso de contraste del panel de texto ahora se anuncia a los lectores de pantalla.
- Una prenda sin diseño ya no dice "Tu diseño está en el carrito".

## 2026-11-03

### Añadido
- **Cuarta crítica de la ficha** con Impeccable: **27/40** (antes 23, 25 y 24). Nuevos P1: el texto
  largo se rompe en silencio y el móvil pierde su sitio (el diálogo se desplaza, el foco se pierde
  y la talla queda bajo el pliegue). Informe en `.impeccable/critique/`.
- **Texto largo sin sorpresas** (primer P1 de la cuarta crítica):
  - Contador de caracteres bajo el campo ("48/60") y aviso al llegar al máximo.
  - Si la letra se queda pequeña en una sola línea (menos de 1,5 cm) o el texto ocupa todo el
    ancho, "Partir en dos líneas y agrandar" lo parte por el espacio más cercano a la mitad y
    agranda la letra (el lienzo la vuelve a encajar en la zona).
  - Al pasar a un escalón más caro mientras se ajusta o se escribe: "Frente pasó a Grande:
    + S/ 6.00 más."

### Cambiado
- **Escritorio en dos columnas** (P2 de la cuarta crítica): desde 1100 px, el lienzo va a la
  izquierda y acompaña al bajar, con las herramientas a su lado, de modo que nada queda debajo de
  él. El editor ocupa dos tercios de la ficha y la compra, uno. Por debajo de 1100 px (tabletas)
  sigue apilado con el lienzo fijo.
- **Jerarquía de herramientas** (P2 de la cuarta crítica):
  - El color de la prenda va primero, en móvil y en escritorio: es lo que más cambia el resultado
    (en el móvil estaba al final de la hoja).
  - Las pestañas Estampados, Texto y Tu imagen son una sola pieza con tres partes, y responden a
    las flechas, Inicio y Fin (solo la activa está en el tabulador).
  - Las zonas llevan el mismo borde discontinuo que tienen sobre la prenda, con la activa rellena.
  - Las categorías de estampados son más discretas: no compiten con pestañas ni zonas.
- Todos los controles del editor miden 44 px en cualquier pantalla (antes solo en las táctiles):
  píldoras, muestras de color y deslizadores.

### Corregido
- Un texto nunca toca el borde de la zona (0,3 cm de aire): pegado al borde se le cortaban los
  remates de las letras.
- El texto que crece mientras se escribe ya no queda encima de otro elemento: pasa al hueco libre
  más cercano sin encogerse (si no hay, se queda donde está).
- Una frase pegada de una vez ya no nace con la letra diminuta: se encaja en la zona y va donde
  menos pise, sin encogerse para ahorrar escalón (eso queda para el hueco que se reserva al
  empezar a escribir).
- Las medidas del lienzo que llegan tarde (de un dibujo anterior) se descartan: aplicadas a un
  texto que ya había cambiado lo encogían de más.
- **El móvil ya no pierde su sitio** (segundo P1 de la cuarta crítica):
  - El modo diseño no se desplaza entero al llevar a la vista un control de la hoja: la etiqueta
    oculta "Otro color" alargaba el diálogo (la hoja no estaba posicionada) y el diálogo admitía
    desplazamiento (`overflow: clip` en lugar de `hidden`).
  - Al añadir un elemento con el teclado o pulsar "Listo", el foco ya no cae al cuerpo de la
    página: va al elemento seleccionado o a la pestaña activa.
  - Con el modo diseño o "Talla y pago" abiertos, el resto de la página queda inerte: ni el
    tabulador ni un lector de pantalla salen del diálogo.
  - "Elige una talla." va encima de los botones, ligado a ellos, y su grupo se lleva a la vista
    (antes quedaba bajo el pliegue con "Revisa antes de añadir" visible).

## 2026-10-28

### Añadido
- **Texto en vivo** (P1 de la segunda crítica): el texto aparece en la prenda con la primera letra
  y cada cambio se ve al momento (ya no hay "Añadir texto"); borrarlo todo lo quita de la prenda.
  Con un texto seleccionado, "Nuevo texto" empieza otro con el mismo estilo (antes, en táctil,
  había que tocar un hueco del lienzo). Mientras se escribe, el campo no pierde el foco ni se sale
  de la vista, y la hoja del móvil no salta arriba al crearse el texto.
- **Contraste también para estampados y fotos:** si la mayor parte de una imagen casi no se
  distingue de la prenda (azul marino sobre negro, un corazón rojo sobre prenda roja), aviso en el
  lienzo (en el móvil, en la franja bajo el asa) con "Prenda en …", el color donde todo el diseño
  se ve; también la marca "Revisar" en la capa y el riesgo en "Revisa antes de añadir".
- **Precio sin sorpresas** (P2 de la tercera crítica):
  - El total multiplica la cantidad, con la fila "Por unidad: 2 × S/ 53.90"; en escritorio sigue
    la cantidad del formulario. El pie del modo diseño y la ficha usan el mismo total.
  - Junto a "Tamaño", el escalón de la zona y dónde salta: "Frente: Mediano (+ S/ 14.00) hasta
    20 cm de lado; ahora 18.0 cm. Más grande: Grande (+ S/ 20.00)." En el resumen, "Frente ·
    Mediano (hasta 20 cm)".
  - Los botones de talla muestran el recargo de la que cuesta más (XXL + S/ 5.00).
- **Tercera crítica de la ficha** con Impeccable, ya con el flujo móvil completo: **24/40**
  (antes 23 y 25). Nuevos P1: los avisos del lienzo no se ven en el móvil, los riesgos de calidad
  no llegan al pago y la hoja de herramientas entierra el contenido. Resueltos en esta misma fecha.
- **"Revisa antes de añadir"** en el paso de talla y pago: lista los riesgos del diseño (foto que
  se verá pixelada, con el ancho máximo para que se vea bien; texto que casi no se ve; letra de
  menos de 0,6 cm) con "Corregir", que vuelve al diseño con ese elemento seleccionado y acercado.
  Las capas con algo que revisar llevan la marca "Revisar" (también en escritorio).
- Miniaturas del paso de talla y pago que se ven en grande al tocarlas.
- Guía de tallas de referencia propia de la oversize (S 54/71, M 57/73, L 60/75, XL 63/77), como
  dato del producto (`_camisama_size_guide`), también en los datos de demostración.

### Cambiado
- **Jerga del editor:** "Traer adelante / Enviar atrás" pasan a "Poner encima / Poner debajo" y
  llevan el elemento por encima o por debajo de todo lo de su zona; se desactivan si ya está
  arriba o abajo del todo y no aparecen si está solo en la zona. "El diseño es más grande que el
  tamaño máximo a la venta" pasa a "El diseño es más grande de lo que podemos imprimir: hazlo un
  poco más pequeño".
- **Avisos visibles en el móvil:** contraste, "Estás editando…" y "Recuperamos…" van en una franja
  fija bajo el asa de la hoja (antes quedaban tapados bajo la hoja).
- **Hoja de herramientas reordenada:** "Añadir en:" en una fila que se desplaza de lado, las
  pestañas y el catálogo justo debajo (ya se ve el primer estampado a media altura) y el color de
  la prenda al final. Al seleccionar un elemento la hoja vuelve siempre a media altura.
- Con la zona acercada no se muestra su etiqueta (quedaba cortada bajo la hoja); el asa ya no se
  anuncia a la vez como "ampliar" y como desplegada.
- **Escritorio igualado al móvil:** la talla va en botones en la columna de compra, sin ninguna
  marcada (WooCommerce marcaba la M), con la guía "¿Qué talla?"; al editar desde el carrito se
  conserva la de la línea. Sin talla, "Añadir al carrito" muestra "Elige una talla." en la página
  y lleva el foco a la primera, en lugar del `alert()` del navegador. Se ocultan los desplegables
  de WooCommerce (el color repetía la paleta del editor) y su "Limpiar".
- **Lienzo fijo en escritorio** (pantallas de 600 px de alto o más): se queda arriba, a 55 % del
  alto, mientras se bajan las herramientas, como en el móvil. Los controles del editor guardan
  margen para no quedar debajo al llegar a ellos con el tabulador.

- El texto que se empieza a escribir reserva hueco para unas 12 letras, y su letra inicial se
  ajusta para que ese texto previsto no pase por poco a un escalón más caro ("Feliz cumple" solo
  en el frente: 2,8 cm de letra, Mediano, en lugar de 4 cm y Grande).
- **Lo nuevo ya no se apila en el centro:** va al hueco libre más cercano de la zona (también al
  duplicar). Entre los huecos gana el que deja la zona en el escalón de precio más barato; si
  entero solo cabe pasando a un escalón más caro, entra al 75 % o a la mitad. Si no hay hueco, va
  a tamaño completo donde menos pise.
- Lo nuevo (estampado o foto) empieza al 60 % de la zona, salvo que eso pase por poco el límite
  de un escalón: entonces se queda en el límite (un estampado alto en el frente, 20 cm en lugar
  de 24, Mediano en lugar de Grande).
- **"desde" con una sola regla**: solo cuando falta la talla y la talla cambia el precio
  (`price_varies`); igual en la ficha, el pie del modo diseño, el resumen y talla y pago. La talla
  que WooCommerce marca por defecto se quita también en el móvil.
- En el carrito y el pedido, "Recargo de estampado" pasa a "Impresión y estampados" (los pedidos
  anteriores conservan su etiqueta).
- Las reglas de los botones de talla y de la guía valen también en escritorio (estaban dentro de
  la consulta del móvil y la tabla de "¿Qué talla?" salía sin estilo).

### Corregido
- Cambiar el orden de un elemento lo movía un puesto en la lista de todo el diseño: a veces se
  cruzaba con algo de otra zona y en la prenda no cambiaba nada.

### Decisiones
- Una imagen "no se ve" cuando más de la mitad de sus píxeles visibles contrasta menos de 1,5 con
  la prenda (muestra de 48 × 48 px, una vez por imagen). Con el catálogo actual avisan la
  cordillera y la ola sobre azul marino y el corazón sobre rojo; la cordillera sobre negro (42 %)
  no, porque se distinguen la nieve y el sol.
- Los botones de talla son un solo componente (`SizeOptions`) para el paso de talla y pago del
  móvil y la columna de compra del escritorio; el formulario de WooCommerce sigue enviando el
  pedido, oculto.

## 2026-10-27

### Cambiado
- **Flujo móvil, tanda 5 (repaso):** en la hoja de herramientas los bloques van sin marco de
  tarjeta (no hay tarjetas dentro de la hoja) y las pestañas y categorías caben en una fila; en el
  modo diseño del móvil solo se nombra la zona activa; "o arrástrala aquí" no aparece en pantallas
  táctiles; "Elegir imagen" muestra el foco con teclado y centra su texto.

### Corregido
- La etiqueta "Nuca" caía dentro de la zona Espalda.

### Añadido
- **Flujo móvil, tanda 4: talla y pago** en una pantalla propia: miniaturas del diseño (o de la
  prenda lisa), "Editar diseño", desglose del precio, **talla en botones sin ninguna marcada**,
  guía "¿Qué talla?" con medidas de referencia, cantidad y "Añadir al carrito" (o "Guardar
  cambios" al editar desde el carrito). Sin talla, "Elige una talla." en la página, sin el
  `alert()` del navegador.
- **Medidas de talla de referencia** (ancho de pecho y largo): adulto S 47/69, M 50/72, L 53/74,
  XL 56/76, XXL 59/78; niños 4 32/45, 6 35/49, 8 38/53, 10 41/57, 12 44/61. Cada producto puede
  tener las suyas (`_camisama_size_guide`) y la tienda puede cambiarlas con el filtro
  `camisama_size_guide`.
- **Flujo móvil, tanda 3** ([docs/flujo-movil.md](docs/flujo-movil.md)): al seleccionar un
  elemento en el modo diseño, el lienzo se acerca a su zona (hasta ×4 en mangas y nuca, nítido y
  con el arrastre en cm correctos) y la hoja muestra solo sus ajustes con "Listo"; si es un texto,
  también el panel para cambiarlo. Al soltarlo, vuelve la prenda entera y las herramientas.
- **Flujo móvil, tanda 2:** hoja de herramientas con asa y tres alturas (media, casi completa,
  mínima), diálogo con el tabulador encerrado e invitación "Elige un estampado, escribe tu frase o
  sube tu foto" con el diseño vacío (también en escritorio).
- **Flujo móvil, tanda 1:** la ficha en el móvil es el paso 1, con "Personalizar" / "Seguir
  diseñando" y "Comprar sin diseño" / "Ir a talla y pago"; el editor se abre a pantalla completa
  (✕, "atrás" del móvil y Esc lo cierran) con "Siguiente: talla" al pie.
- **Plan del flujo móvil** de tres pasos (ficha → diseño a pantalla completa → talla y pago),
  aprobado con `/impeccable shape`.
- **Editar el diseño desde el carrito:** "Editar diseño" en cada línea (carrito, minicarrito y
  pago) abre el editor con ese diseño, su talla y su cantidad; "Guardar cambios" sustituye la línea.
- **No perder el diseño:** borrador en el dispositivo (7 días, por producto) y añadir al carrito sin
  recargar, con el minicarrito abierto y la miniatura del diseño.
- `CHANGELOG.md` (este registro).
- `.prettierignore`: el formateador no toca `.claude/`, `vendor/` ni `build/`.

### Corregido
- Con avisos en el modo diseño, el lienzo encogía y las líneas de zona se desalineaban del dibujo.
- Con la hoja bajada, la ✕ y el lienzo se estiraban.
- Al cerrar el modo diseño con "atrás", el foco no volvía al botón de entrada.

### Decisiones
- Se trabaja directamente sobre `main`, sin ramas ni pull requests.
- Medidas de talla: se usarán unas de referencia por defecto en la guía "¿Qué talla?" (tanda 4),
  para cambiarlas después por las reales.
- Al editar desde el carrito no se guarda borrador: salir sin guardar es descartar los cambios.

## 2026-10-22

### Añadido
- `PRODUCT.md` (contexto de producto) y `DESIGN.md` con su `.impeccable/design.json` (sistema
  visual "El sello": papel, tinta y rojo sello; Bricolage Grotesque e Inter).
- Primera crítica de la ficha con Impeccable: **23/40**. Segunda, tras los arreglos: **25/40**.
- Aviso de contraste: el texto nuevo toma el color más legible sobre la prenda y, si un texto casi
  no se ve, se ofrece el arreglo en un toque.
- `docs/estado-de-la-web.md` (hecho y pendiente) y guía de instalación más completa en el README.

### Cambiado
- **Compra unificada:** un solo precio, el desglose y el total junto a "Añadir al carrito"; la
  columna de compra acompaña al editor en escritorio.
- **Editor en el móvil:** lienzo fijo arriba, el lienzo ya no atrapa el desplazamiento, barra de
  compra y controles táctiles de 44 px.
- **Repaso final:** campos de compra con el aspecto del editor, etiquetas de zona sin solaparse,
  miniaturas parejas, un solo botón rojo e iconos de deshacer y rehacer.

### Corregido
- `style.css` se servía viejo desde la caché del navegador tras cada cambio.
- WooCommerce cortaba el texto de los desplegables de color y talla, y dejaba a 14 px el selector
  de fuentes (Safari en iPhone amplía la página con menos de 16 px).

## 2026-10-21

### Añadido
- **Pedido con diseño:** validación en el servidor, precio recalculado, carrito, pedido y vistas
  previas.
- **Producción:** hoja de producción y archivos de impresión PNG a 300 DPI por zona.
- Scripts para encender y apagar la tienda local (`Iniciar tienda.cmd` y `Detener tienda.cmd`).
- Skill de diseño Impeccable en el proyecto, sin hooks.

## 2026-10-14

### Añadido
- Cálculo del precio del diseño en PHP y JS con los mismos casos de prueba.
- Personalizador en la tienda: vistas, colores y precio en vivo.
- Subida segura de imágenes de los clientes (recodificación, sin EXIF ni GPS, límite de frecuencia).
- Editor con Fabric.js: estampados del catálogo, texto e imágenes propias.

## 2026-10-13

### Añadido
- **Fase 0:** estructura del repositorio, tema de bloques y plugin base, con CI.
- **Fase 1:** tema completo y tienda configurada para Perú (soles).
- **Fase 2:** datos y administración del personalizador (zonas, colores, maquetas y precios).
