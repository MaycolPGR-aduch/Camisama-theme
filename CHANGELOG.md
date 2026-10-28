# Registro de cambios

Cambios y avances de la tienda Camisama, del más reciente al más antiguo. Lo pendiente está en
[docs/estado-de-la-web.md](docs/estado-de-la-web.md); el detalle de cada cambio, en el historial
de git (`git log`).

Cada entrada separa lo **añadido**, lo **cambiado**, lo **corregido** y las **decisiones** que
conviene recordar.

## 2026-10-28

### Añadido
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

### Decisiones
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
