# Portada de Camisama

La portada conserva la identidad «el sello» y sigue este recorrido: presentación → estampados del catálogo → ejemplos de catálogo, texto y foto → modelos → pasos → zonas y precio → preguntas frecuentes → llamada final.

Los patrones se componen en `themes/camisama/templates/front-page.html`. El CSS de portada está separado y se carga solo en el inicio, además de estar disponible en el editor. Las tarjetas usan bloques nativos de WooCommerce: catálogo, descripciones y precios siguen siendo dinámicos.

El bloque `camisama/home-price` se registra con metadatos y vista previa de servidor en el editor. En cada render busca una variante publicada, comprable y en stock, valida que el producto tenga personalizador y zona frontal, y calcula una impresión de texto de hasta 10 cm mediante `DesignPricer`. El desglose utiliza las mismas tarifas del plugin y el formato de precios de WooCommerce. Las medidas desplegables proceden de la configuración del modelo de ese ejemplo. Si no hay un producto válido, se muestra una explicación sin importes inventados.

## Movimiento (tanda 1 del rediseño dinámico)

El movimiento de la portada enseña el producto; no es adorno. Todo es mejora progresiva con
`themes/camisama/assets/js/home.js` (sin librerías, se carga solo en el inicio con `defer`).

- **Hero vivo** (`patterns/hero.php`): tres ejemplos digitales que se turnan sobre la prenda
  (frase en negra, estampado pequeño en blanca, foto grande en negra), con la zona de impresión
  como línea discontinua y una píldora con el escalón y su recargo real. Avanza cuando termina la
  barra de la pestaña activa (4,5 s); se pausa con el botón, al elegir un ejemplo, con el cursor o
  el foco encima y fuera de pantalla. Sin JavaScript queda el primer ejemplo, sin precio.
- **Sello** junto a la prenda: "Desde 1 unidad ✦ Estampado en Perú" girando despacio. Solo datos
  verificados.
- **Simulador de zonas y precio** (`template-parts/home-pricing.php`): fichas por zona del modelo
  de ejemplo y tamaño en las grandes; el mapa se rellena y el desglose se recalcula. Las tarifas
  van en el JSON `#camisama-home-prices` (`camisama_home_price_data()`), ya en precio mostrado y en
  céntimos, con el mismo cálculo que el desglose del servidor. Sin JavaScript queda el ejemplo fijo
  de una frase en el frente. En el móvil el total va justo después de las fichas.
- Con `prefers-reduced-motion` no hay avance automático, giro ni rebotes: los ejemplos se eligen a
  mano y los cambios son instantáneos.

## Movimiento (tanda 2)

- **Franja de estampados** (bloque dinámico `camisama/home-designs`, `template-parts/home-designs.php`):
  bajo el hero, los estampados publicados del personalizador (`DesignRepository`) se desplazan en
  bucle, con su categoría o su recargo premium. Sigue al catálogo aunque se guarde la plantilla.
  Se pausa con el cursor, con su botón y fuera de pantalla; con movimiento reducido queda quieta y
  se desliza a mano. Sin estampados no se muestra.
- **Colores en los ejemplos** (`patterns/personalization-examples.php`): cada ejemplo cambia entre
  blanco, negro, rojo y azul marino, las maquetas reales de la camiseta clásica (copiadas en
  `assets/images`). El texto impreso pasa a tinta sobre la prenda blanca.
- **Cómo funciona con el scroll** (`patterns/how-it-works.php`): la prenda se queda fija mientras
  pasan los pasos y se arma por etapas (color, diseño, talla y "Lista para pedir"). El paso que
  cruza el centro de la pantalla es el actual. En el móvil la prenda queda arriba, pequeña. Sin JS
  se ve terminada.
- **Modelos en carrusel en el móvil**: desliza con imán y asoma el siguiente modelo.

## Movimiento (tanda 3)

- **Entradas al hacer scroll**: encabezados, ejemplos, tarjetas de modelos, mapa de zonas, preguntas
  y el cierre aparecen subiendo un poco, escalonados dentro de cada grupo. Solo los oculta
  `home.js` (clase `camisama-reveal`), así que sin JS o con movimiento reducido se ven siempre. El
  hero no entra con animación, para no retrasar la primera imagen.
- **Botones que estampan**: los botones rojos y el de papel se hunden al pulsarlos y dejan un aro
  que se abre.
- **Cierre con sello** (`patterns/call-to-action.php`): el sello de goma "Tu diseño, tu ley." cae
  sobre la camiseta la primera vez que se ve casi entera, y la prenda acusa el golpe. Sin JS o con
  movimiento reducido el sello ya está puesto.

## Vista previa al compartir

`camisama_home_social_meta()` (en `inc/home.php`) añade en la portada la descripción para
buscadores y las etiquetas Open Graph y de X, con `og-home.png` (1200 × 630). No hay plugin de SEO:
si se instala uno, quitar esa función para no duplicar etiquetas. WhatsApp y Facebook guardan la
vista previa en caché; tras cambiar la imagen, renombrarla o usar el depurador de Facebook. La
imagen solo se ve al compartir cuando la tienda esté publicada (desde `camisama.test` no la pueden
descargar).

## Verificación

Ejecutar con WordPress y WooCommerce locales disponibles:

```powershell
php vendor/wp-cli/wp-cli/php/boot-fs.php eval-file bin/check-home.php --use-include
```

Comprueba variante comprable, suma y precio renderizado, reacción a tarifas distintas, catálogo vacío y registro del bloque. Los filtros temporales no escriben productos ni opciones en la base de datos.

Revisión manual: anclas, FAQ con ratón y teclado, pestañas y pausa del hero, fichas del simulador con teclado, muestras de color, pausa de la franja, pasos al bajar, carrusel de modelos en el móvil, acceso al personalizador, cuatro columnas en escritorio y dos en móvil, ausencia de desbordamiento en 320, 390, 768 y 1280 px. Los recursos de muestra y el prompt están documentados en `themes/camisama/assets/images/EXAMPLES.md`.

Las fotografías reales del taller y las condiciones comerciales definitivas siguen dependiendo del negocio. No se añaden testimonios, fechas, direcciones ni promesas de pago sin respaldo.
