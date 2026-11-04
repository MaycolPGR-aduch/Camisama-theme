# Registro de cambios

Cambios y avances de la tienda Camisama, del más reciente al más antiguo. Lo pendiente está en
[docs/estado-de-la-web.md](docs/estado-de-la-web.md); el detalle de cada cambio, en el historial
de git (`git log`).

Cada entrada separa lo **añadido**, lo **cambiado**, lo **corregido** y las **decisiones** que
conviene recordar.

## 2026-11-04

### Añadido
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
