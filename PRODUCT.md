# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Personas que quieren una prenda única **para sí mismas o para regalar** (cumpleaños, aniversarios,
frases propias, una foto especial). Compran una o pocas unidades, normalmente desde el móvil, y
muchas veces sin experiencia en diseño: llegan con una idea, una imagen o un texto y necesitan verla
sobre la prenda antes de pagar.

Otras audiencias (grupos y eventos, emprendedores, empresas) no son el foco actual; no están
descartadas, pero no deben guiar las decisiones de producto por ahora.

## Product Purpose

Camisama es una tienda online de camisetas personalizables. El cliente elige modelo, color y talla,
diseña cada zona de la prenda (frente, espalda, mangas, nuca) con estampados del catálogo, texto o
sus propias imágenes, ve el precio en vivo y compra. El taller propio imprime en DTF y prepara el
pedido.

Éxito: que alguien sin experiencia termine un diseño que le guste, entienda lo que paga y reciba
exactamente lo que vio en pantalla.

## Positioning

- **Desde una unidad y con precio claro:** sin pedidos mínimos; el precio depende del tamaño de lo
  que se imprime en cada zona y se ve mientras se diseña (prenda + recargo por zona + estampados
  premium).
- **Calidad DTF en cualquier prenda:** colores vivos, fotos y degradados, también en prendas
  oscuras, impresos en taller propio.
- **Diseños con identidad peruana:** catálogo propio de estampados con temas peruanos, con espacio
  para artistas invitados (recargo premium que les paga una comisión).

## Operating Context

- Tienda WordPress + WooCommerce (tema `camisama` y plugin `camisama-customizer`), en español, para
  **Perú**, con precios en **soles (S/)** que incluyen IGV.
- Entrega **en Lima Metropolitana** y **recojo en el taller**. La configuración actual de la tienda
  todavía permite envíos a todo el Perú: hay que ajustarla.
- Producción: el taller descarga desde cada pedido los PNG a 300 DPI por zona y la hoja de
  producción, imprime los transfers DTF y los plancha (docs/produccion-e-impresion.md).
- El editor del cliente es el centro del producto: vistas, zonas con medidas reales, estampados,
  texto con 8 fuentes, imágenes propias con aviso de calidad, deshacer y precio en vivo.

## Capabilities and Constraints

- Zonas estándar adulto: frente y espalda hasta 30 × 40 cm, mangas 9 × 9 cm, nuca 8 × 5 cm; niños
  más pequeñas. Precio por zona en escalones según el lado mayor de lo impreso
  (docs/modelo-de-precios.md).
- Imágenes del cliente: PNG, JPG o WEBP hasta 15 MB; sin SVG de clientes. Se borran a los 30 días
  si no acaban en un pedido.
- Terminología: "estampado" (diseño del catálogo), "zona" (área imprimible), "prenda", "personalizar".
- **Pendiente:** pasarela de pago peruana (hoy solo transferencia de prueba), IGV y comprobantes
  SUNAT, tarifas y plazos de entrega en Lima, dirección y horario de recojo en el taller.

## Brand Commitments

- Nombre **Camisama** y lema **"Tu diseño, tu ley."**
- Voz cercana, de **tú**, frases cortas, español de Perú.
- Identidad visual propuesta y aceptada: concepto "el sello" (docs/identidad-visual.md). El logotipo
  actual es provisional; el definitivo está por hacer.

## Evidence on Hand

- 4 modelos de demostración con colores y tallas, y maquetas **generadas por código** (no hay fotos
  reales de producto ni de personas usando las prendas).
- 6 estampados SVG de demostración (Sol andino, Llama con manta, Cordillera, Ola, Rayo, Corazón).
- **No existen** testimonios, reseñas, cifras de clientes, prensa ni plazos de entrega comprometidos:
  no deben inventarse en la tienda.

## Product Principles

1. **Lo que ves es lo que recibes:** cada decisión del editor debe acercar la vista previa a la
   prenda real (medidas en cm, aviso de calidad, colores reales).
2. **Precio sin sorpresas:** el cliente siempre sabe qué paga y por qué, antes de llegar al carrito.
3. **Sin saber diseñar también se puede:** el camino de "tengo una idea" a "diseño listo" debe ser
   corto y guiado, sobre todo en el móvil.
4. **Hecho aquí:** la identidad peruana y el taller propio son parte del valor, no decoración.
