# Estado de la web: hecho y pendiente

Resumen de dónde está la tienda Camisama (tema `camisama` + plugin `camisama-customizer`) y qué
falta para abrirla al público. Actualizado el 28 de octubre de 2026.

Contexto de producto y decisiones visuales: [PRODUCT.md](../PRODUCT.md) y [DESIGN.md](../DESIGN.md).
Registro de lo que se va haciendo: [CHANGELOG.md](../CHANGELOG.md).

---

## Hecho

### Base del proyecto (fases 0 a 4)

| Fase | Qué quedó funcionando |
|---|---|
| 0. Estructura | Repositorio con tema de bloques y plugin, Composer y npm, CI en GitHub Actions (PHPCS, PHPStan, PHPUnit, lint y pruebas JS, compilación). |
| 1. Tema | Tema completo con la identidad "el sello" (papel, tinta y rojo sello; Bricolage Grotesque e Inter), plantillas de tienda, ficha, carrito y checkout, y tienda configurada para Perú en soles. |
| 2. Datos | Configuración por producto (vistas, zonas en cm, colores y maquetas) y tabla de precios editable en el administrador. |
| 3. Personalizador | Editor con Fabric.js: estampados del catálogo, texto con 8 fuentes, imágenes propias con aviso de calidad, deshacer y rehacer, y precio en vivo calculado igual en el navegador y en el servidor. Subida de imágenes segura (recodificación, sin EXIF ni GPS, límite de frecuencia). |
| 4. Pedido y producción | El diseño viaja al carrito y al pedido con vistas previas; el servidor revalida y recalcula el precio. El taller descarga PNG a 300 DPI por zona y una hoja de producción desde cada pedido. |

También: scripts para encender y apagar la tienda local (`Iniciar tienda.cmd` y `Detener
tienda.cmd`) y la skill de diseño Impeccable instalada en el proyecto, sin hooks.

### Diseño de la ficha de producto (Impeccable)

1. **Contexto de diseño.** `PRODUCT.md` (usuarios, propósito, principios) y `DESIGN.md`
   (sistema visual extraído del tema y del editor, con estrella polar "El sello", controles
   táctiles y seguros, y sin aspecto de tienda genérica ni sobrecargado).
2. **Crítica de la ficha con el personalizador: 23/40.** Tres problemas graves (P1) y dos
   moderados (P2). Informe en `.impeccable/critique/`.
3. **Compra unificada (P1).** Un solo precio: el desglose y el total viven en el formulario de
   compra, pegados a "Añadir al carrito". Se ocultan el rango junto al título y el precio de la
   variante. En escritorio la columna de compra acompaña al editor.
4. **Editor en el móvil (P1).** Lienzo fijo arriba mientras se usan las herramientas, el lienzo
   ya no atrapa el desplazamiento, barra inferior con total, talla y "Añadir al carrito", y
   controles de 44 px en pantallas táctiles. El lienzo nunca supera el alto de la pantalla.
5. **Texto visible sobre la prenda (P1).** El texto nuevo toma el color que más contrasta con
   la prenda; si un texto casi no se ve, aviso junto al lienzo con el arreglo en un toque.
6. **Repaso final.** Campos de compra con el aspecto del editor, etiquetas de zona sin
   solaparse, miniaturas parejas, un solo botón rojo, iconos de deshacer y rehacer, mensaje
   "Preparando tu diseño…", y detalles del navegador con la paleta.

Todo probado en escritorio, tableta y móvil (emulados), con compras de prueba en el carrito
local, 45 pruebas de JS y lint limpio.

---

## Pendiente

### Ficha de producto (siguiente en la lista)

- [x] **Repetir la crítica de Impeccable**: 25/40 (antes 23/40). Nuevos P1: el diseño se perdía,
      lienzo y herramientas no conviven, texto sin vista previa.
- [x] **No perder el diseño**: borrador en el dispositivo (7 días, por producto) que se recupera
      al volver, y añadir al carrito sin recargar, con el minicarrito abierto con la miniatura y
      "Tu diseño está en el carrito" junto al botón.
- [x] **Editar el diseño desde el carrito**: "Editar diseño" en cada línea (carrito, minicarrito
      y pago; también el nombre y la miniatura) abre el editor con ese diseño, su talla y su
      cantidad; "Guardar cambios" sustituye la línea.
- [x] **Plan del flujo de tres pasos en el móvil**: aprobado en [flujo-movil.md](flujo-movil.md)
      (ficha → diseño a pantalla completa → talla y pago).
- [x] **Construir el flujo móvil** por tandas (ver flujo-movil.md): las 5 tandas hechas (ficha,
      modo diseño con hoja, acercar la zona al seleccionar, paso de talla y pago, y repaso), y
      tercera crítica: 24/40.
- [x] **P1 de la tercera crítica (24/40):** avisos visibles en el móvil, "Revisa antes de añadir"
      en el paso de talla y pago y hoja de herramientas reordenada.
- [x] **Igualar el escritorio al móvil:** talla en botones sin preselección y con la guía, aviso en
      la página en lugar del `alert()`, sin los desplegables duplicados de WooCommerce y lienzo
      fijo arriba mientras se usan las herramientas.
- [x] **Precio sin sorpresas (P2 de la tercera crítica):** total por la cantidad ("2 × S/ 53.90"),
      el escalón y su límite junto a "Tamaño" y en el resumen, lo nuevo empieza sin pasar por poco
      al escalón caro, "desde" con la misma regla en todos los pasos (solo si la talla cambia el
      precio), el recargo de la XXL en su botón y "Impresión y estampados" en el carrito.
- [x] **Elementos nuevos apilados en el centro** y contraste también para estampados oscuros sobre
      prenda oscura: lo nuevo va al hueco libre que deja la zona en el escalón más barato, y los
      estampados y fotos que casi no se ven avisan con "Prenda en …".
- [x] **Texto en vivo** (P1): aparece en la prenda con la primera letra y se edita al momento,
      borrarlo todo lo quita, "Nuevo texto" siempre a mano y lo nuevo no cae encima de lo
      anterior.
- [x] **Talla sin preselección y guía de tallas en el móvil** (P2): botones de talla sin ninguna
      marcada, "¿Qué talla?" con medidas de referencia y mensaje en la página en lugar del
      `alert()`, en el paso de talla y pago.
- [ ] **Jerga del editor**: "Traer adelante / Enviar atrás" y "Añadir en:" (los escalones ya dicen
      hasta cuántos cm llegan).
- [ ] **Probar el gesto táctil en un móvil real**: deslizar sobre el lienzo debe desplazar la
      página y arrastrar el elemento seleccionado debe moverlo (la emulación no lo cubre).

### Contenido (decisiones del negocio)

- [ ] **Pestaña "Valoraciones (0)"**: es prueba social vacía; desactivar las reseñas mientras no
      haya.
- [ ] **Zona "Pecho (logo) 10 × 10 cm"**: la página de zonas de impresión la anuncia pero el
      editor no la ofrece. Quitarla del texto o añadir la zona.
- [ ] **Fotos reales** de las prendas en lugar de las maquetas generadas; con ellas, encuadrar
      mejor la prenda en el lienzo (en el móvil ocupa la mitad).
- [ ] **Logotipo definitivo** (el actual es provisional).
- [ ] **Medidas reales de las tallas** por modelo (ancho y largo en cm). Mientras tanto, la guía
      "¿Qué talla?" usará unas de referencia por defecto.

### Resto de la web

- [ ] Crítica y repaso de la **portada**, la **tienda**, el **carrito** y el **checkout** (solo
      se ha trabajado la ficha de producto).
- [ ] Ajustar la **entrega**: hoy WooCommerce permite envíos a todo el Perú; debe ser solo Lima
      Metropolitana y recojo en el taller, con tarifas, plazos, dirección y horario.

### Fase 5: funciones del personalizador

- [ ] Guardar y compartir diseños.
- [x] Editar el diseño desde el carrito (hecho antes, ver la ficha de producto).
- [ ] Zoom en el lienzo del móvil.
- [ ] Quitar el fondo de las fotos de los clientes.
- [ ] Descargar en un ZIP los archivos de impresión de un pedido.
- [ ] Pruebas de extremo a extremo (e2e) del flujo de compra.

### Producción (antes de abrir al público)

- [ ] **Pasarela de pago peruana** (hoy solo transferencia de prueba).
- [ ] **IGV y comprobantes SUNAT** (boletas y facturas electrónicas).
- [ ] **Servidor y dominio**, copias de seguridad y correo transaccional real.
- [ ] Textos legales: términos, privacidad, cambios y devoluciones.
