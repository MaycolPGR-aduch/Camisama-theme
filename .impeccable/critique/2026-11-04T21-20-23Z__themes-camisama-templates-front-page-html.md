---
target: "Home de Camisama: crítica por secciones y propuestas de secciones nuevas"
total_score: 25
max_score: 32
na_heuristics: 7,9
p0_count: 0
p1_count: 2
target_identity: "file:C:\\Camisama\\themes\\camisama\\templates\\front-page.html"
target_fingerprint: "sha256:8cfd86fdfecc0b50700e2cd8a4b582b284311ee6a53e3ff5312515e184eda10e"
target_path: "C:\\Camisama\\themes\\camisama\\templates\\front-page.html"
timestamp: 2026-11-04T21-20-23Z
slug: themes-camisama-templates-front-page-html
closed: true
---
Method: dual-agent (A: /root/home_design_review · B: /root/home_detector_evidence)

**El home tiene una identidad visual sólida, pero todavía demuestra poco del producto que vende.** Explica cómo personalizar, aunque cuesta imaginar una camiseta terminada y entender cuánto costará. Recomiendo conservar “el sello” y mejorar contenido, recorrido y demostración visual.

Revisión del home vivo en escritorio y móvil de 390 × 844. No se cambió código.

**Qué conservar.** Papel cálido, tinta, rojo y tipografías coherentes; una acción principal reconocible; cuatro modelos fáciles de comparar. El menú móvil abre y cierra correctamente. Lima y recojo ya aparecen: falta concretar las condiciones.

**Valoración heurística: 25/32 — buena base, con mejoras importantes.** Es un juicio de diseño del home; no certifica que la tienda esté lista para vender.

| Heurística | Nota | Observación |
|---|---:|---|
| Visibilidad del estado | 3/4 | El menú comunica apertura y cierre. |
| Lenguaje del usuario | 3/4 | DTF y medidas necesitan apoyo visual. |
| Control y libertad | 3/4 | Navegación y salida del menú claras. |
| Consistencia | 3/4 | Los CTA de diseñar llevan primero a la tienda. |
| Prevención de errores | 2/4 | Precio y máximos de zonas admiten interpretaciones. |
| Reconocer antes que recordar | 3/4 | Falta relacionar zonas con la prenda. |
| Flexibilidad y eficiencia | N/A | Atajos de edición fuera del alcance del home. |
| Estética y simplicidad | 3/4 | Limpio, pero largo y repetitivo en móvil. |
| Recuperación de errores | N/A | No hay formularios ni estados de error propios que evaluar. |
| Ayuda | 2/4 | Faltan respuestas antes de comprar. |

**Cinco problemas prioritarios.**

1. **P1 — La confianza termina en enlaces sin respuesta.** “Cambios y devoluciones” abre un 404 y “Privacidad” regresa al inicio. “Pago seguro” es una afirmación poco concreta mientras la integración de pagos está pendiente. Corregir destinos y mostrar métodos y condiciones verificados. Comando: `$impeccable harden`.
2. **P1 — No queda suficientemente claro qué incluye el precio.** La clásica muestra S/ 39.90–44.90, pero el visitante debe reunir información de distintas secciones para entender prenda, impresión y posibles estampados premium. Etiquetar el precio base y mostrar un desglose real obtenido del sistema. Comando: `$impeccable clarify`.
3. **P2 — Faltan resultados personalizados que despierten ideas.** La ilustración del hero representa el editor y el catálogo muestra prendas vacías. Añadir ejemplos de catálogo, texto y foto sobre camisetas; identificarlos como ejemplos digitales. Comando: `$impeccable shape`.
4. **P2 — El móvil explica demasiado antes de comparar modelos.** El CTA inicial sí está visible, pero después se apilan ilustración y tres tarjetas antes del catálogo. Acortar el hero, adelantar modelos y compactar el tutorial. Comando: `$impeccable adapt`.
5. **P2 — Las zonas parecen una ficha técnica.** Cinco filas de centímetros ayudan poco a imaginar posiciones. Se anuncia “Pecho (logo)”, que no aparece como zona separada en la configuración estándar, y no se distinguen tamaños infantiles. Sustituir la lista dominante por un mapa de frente y espalda, con medidas verificadas bajo demanda. Comando: `$impeccable clarify`.

**Mejoras por sección actual.**

| Sección | Propuesta concreta |
|---|---|
| Cabecera | Conservar la marca; simplificar el solapamiento entre Tienda, Camisetas y Niños. Propuesta: Modelos, Cómo funciona y Ayuda. |
| Hero | Mantener “Tu diseño, tu ley.”; reducir el párrafo a una frase clara y mostrar una personalización protagonista. CTA: “Elegir mi camiseta”, coherente con su destino. |
| Modelos | Adelantarlo. Añadir una diferencia útil por corte/modelo, precio base bien identificado y acceso claro a personalizar. |
| Así de fácil | Tres pasos breves que reflejen el flujo real: elegir modelo/color, diseñar viendo el precio y elegir talla/cantidad para comprar. |
| Zonas | Conservar el contraste oscuro; incorporar una prenda anotada y relegar medidas al detalle. |
| Cierre | Resolver primero las dudas de compra y repetir una acción coherente con el hero. |
| Pie | Reparar enlaces y añadir contacto y datos operativos reales, cuando estén definidos. |

**Secciones nuevas que sí aportan.**

- **“Esto podrías crear” — prioridad alta.** Tres ejemplos: estampado del catálogo, frase propia y foto. Mostrar la idea aplicada a una prenda. Si se ofrece “Usar este diseño”, habrá que implementar la carga de ese ejemplo en el editor; hoy no debe darse por existente.
- **“Tu precio, explicado” — prioridad alta.** Un ejemplo comprobado: prenda + impresión por zona/tamaño + premium, cuando corresponda. Diferenciar el precio de una camiseta lisa del de una personalizada.
- **“Antes de pedir” — prioridad alta.** FAQ breve sobre una unidad, formatos de imagen, resolución, tallas, vista previa y entrega/recojo. Costos, plazos, dirección y horario solo cuando estén confirmados.
- **“Hecho en nuestro taller” — segunda etapa.** Fotografías propias del estampado, tejido y acabado para aportar evidencia. Mientras no existan, no presentaría imágenes generadas como prueba de calidad ni inventaría testimonios.

**Orden recomendado:** hero compacto → ejemplos personalizados → modelos → pasos breves → zonas y precio explicado → FAQ con entrega/recojo → cierre → pie. El bloque de taller puede incorporarse junto a las respuestas de calidad cuando haya material real.

No convertiría cada idea en una banda larga: zonas y precio pueden compartir un bloque, y las FAQ pueden desplegarse. La estructura debe permitir comparar pronto y consultar detalles después.

**Carga y recorrido.** La carga cognitiva es moderada: el precio está disperso y los detalles técnicos llegan antes de resolver dudas básicas. El recorrido comienza con personalidad, se vuelve abstracto entre prendas vacías y medidas, y termina pidiendo actuar sin despejar todas las dudas. Los ejemplos aportarían el momento de “eso podría hacerlo yo”.

**Personas.** Jordan, que compra por primera vez, necesita ver un resultado y entender el precio; Casey, desde el móvil, necesita llegar antes a modelos; Riley, que contrasta condiciones, encuentra enlaces que no resuelven sus preguntas.

**Observaciones menores.** Conservar la estructura semántica, el enlace para saltar al contenido y los nombres accesibles de cuenta/carrito. Las imágenes de producto ya incluyen texto alternativo, dimensiones y carga diferida. No hace falta sustituir toda la base visual.

**Validación de una futura implementación.** Comparar escritorio y móvil, probar enlaces y teclado, comprobar ausencia de desbordamientos y verificar que cada precio y ejemplo coincide con el editor. Una maqueta digital no sustituye comprobar el resultado impreso.

**Dos decisiones para concretar la siguiente etapa:**

1. ¿Qué alcance prefieres: **A) hero + ejemplos + modelos primero — recomendado**, o **B) reorganizar todo el home, incluyendo precio y FAQ**?
2. ¿Qué ejemplos deben dominar: **A) mezcla de catálogo, texto y foto — recomendado**, **B) estampados con identidad peruana**, o **C) regalos con frases y fotos**?
