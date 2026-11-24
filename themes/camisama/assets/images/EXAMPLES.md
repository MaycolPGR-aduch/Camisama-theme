# Recursos de ejemplo de la portada

- `example-tee-white.webp`, `-black`, `-red` y `-navy`: frentes de la camiseta clásica, las mismas plantillas del editor (recortadas y con transparencia), que `bin/plantillas/preparar.py` copia aquí para que el tema no dependa de adjuntos de una instalación local. **Imágenes generadas con IA** (docs/plantillas-fotograficas.md); la portada lo dice junto a los ejemplos.
- `example-sun.svg`: estampado Sol andino de `bin/demo-designs/sol-andino.svg`.
- `print-zones.svg`: diagrama vectorial de ubicaciones de referencia. Las medidas mostradas junto al diagrama se leen de la configuración del modelo real.
- `example-photo.webp`: muestra ilustrativa generada mediante la herramienta integrada ImageGen, reducida a 520 × 520 y optimizada a WEBP con FFmpeg. No representa una foto aportada por un cliente ni un pedido realizado.

Prompt final utilizado:

> Use case: photorealistic-natural. Asset: a small square sample photograph to demonstrate a user's photo printed on a custom t-shirt, not a real customer image or proof of merchandise quality. Create one square natural travel photograph of a tranquil turquoise Andean mountain lake, brown-green mountain ridges and distant snow peaks, soft clear morning light, tactile photographic detail. Clean striking composition readable at thumbnail size, natural restrained colors. Image only, no shirt, no people, no words, no logos, no border, no collage. Save the resulting generated image locally.

Los ejemplos se identifican como digitales en la portada. La composición sobre las prendas se realiza en HTML/CSS; los enlaces permiten elegir modelo, sin prometer cargar automáticamente el diseño de ejemplo.

- `og-home.png` (1200 × 630): vista previa al compartir la portada. Se genera desde `bin/og-image/og-home.html` (fuentes del tema y la prenda negra de arriba) con `pwsh bin/og-image/render.ps1`, que la fotografía con Chrome sin ventana. Para cambiarla, editar el HTML y volver a ejecutar el script.
