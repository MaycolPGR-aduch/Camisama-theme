# Portada de Camisama

La portada conserva la identidad «el sello» y sigue este recorrido: presentación → ejemplos de catálogo, texto y foto → modelos → pasos → zonas y precio → preguntas frecuentes → llamada final.

Los patrones se componen en `themes/camisama/templates/front-page.html`. El CSS de portada está separado y se carga solo en el inicio, además de estar disponible en el editor. Las tarjetas usan bloques nativos de WooCommerce: catálogo, descripciones y precios siguen siendo dinámicos.

El bloque `camisama/home-price` se registra con metadatos y vista previa de servidor en el editor. En cada render busca una variante publicada, comprable y en stock, valida que el producto tenga personalizador y zona frontal, y calcula una impresión de texto de hasta 10 cm mediante `DesignPricer`. El desglose utiliza las mismas tarifas del plugin y el formato de precios de WooCommerce. Las medidas desplegables proceden de la configuración del modelo de ese ejemplo. Si no hay un producto válido, se muestra una explicación sin importes inventados.

## Verificación

Ejecutar con WordPress y WooCommerce locales disponibles:

```powershell
php vendor/wp-cli/wp-cli/php/boot-fs.php eval-file bin/check-home.php --use-include
```

Comprueba variante comprable, suma y precio renderizado, reacción a tarifas distintas, catálogo vacío y registro del bloque. Los filtros temporales no escriben productos ni opciones en la base de datos.

Revisión manual: anclas, FAQ con ratón y teclado, acceso al personalizador, cuatro columnas en escritorio y dos en móvil, ausencia de desbordamiento en 320, 390, 768 y 1280 px. Los recursos de muestra y el prompt están documentados en `themes/camisama/assets/images/EXAMPLES.md`.

Las fotografías reales del taller y las condiciones comerciales definitivas siguen dependiendo del negocio. No se añaden testimonios, fechas, direcciones ni promesas de pago sin respaldo.
