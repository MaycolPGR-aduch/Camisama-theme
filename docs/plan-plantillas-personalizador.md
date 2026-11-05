# Plan de integración de las nuevas plantillas

Revisión del 5 de noviembre de 2026. Piloto local implementado y oculto del catálogo; medidas físicas sin confirmar por decisión del usuario. La clásica original conserva su configuración. Resultados y pendientes en [el informe de implementación](plantillas-personalizador-piloto.md). El análisis siguiente conserva el diagnóstico previo y el plan de una futura activación.

## Decisión

Las cuatro imágenes son compatibles como bases visuales, pero todavía no están listas para activarlas directamente. Conviene realizar un piloto de camiseta clásica en blanco y negro, corregir recortes y encuadre, calibrar las cinco zonas y comprobar las vistas previas. No hace falta sustituir Fabric.js ni rediseñar el modelo de datos para ese piloto.

Los diseños del cliente deben seguir almacenándose en centímetros. Las fotografías solo son la representación de la prenda; no definen por sí mismas medidas físicas ni la calidad del producto fabricado.

## Archivos seleccionados

Carpeta: `assets/propuestas/plantillas-clasica/`.

| Archivo | Vista | Color | Tamaño del PNG |
|---|---|---|---|
| clasica-blanca-frente.png | front | blanco | 1254 × 1254, RGBA |
| clasica-negra-frente.png | front | negro | 1254 × 1254, RGBA |
| clasica-blanca-espalda.png | back | blanco | 1254 × 1254, RGBA |
| clasica-negra-espalda.png | back | negro | 1254 × 1254, RGBA |

Las cuatro tienen canal alfa real con valores entre 0 y 255 y pesan 4,19 MiB en conjunto. Se verificaron nuevamente las dimensiones y los archivos del proyecto, además de inspeccionar visualmente las cuatro vistas. No se han editado sus píxeles en esta revisión.

Las propuestas de `assets/propuestas/home/` llevan diseños impresos o personas: son material de promoción y no deben usarse como fondo del editor.

## Hallazgos que condicionan la integración

1. **Recorte pendiente en blanco.** Se ven flecos y manchas claras alrededor de mangas, cuello y bajo, especialmente sobre un fondo oscuro. Tener transparencia no equivale a tener un recorte limpio.
2. **Alineación cercana, no idéntica.** Al comparar máscaras de alfa con umbral 128, blanco y negro difieren aproximadamente un 0,73 % del área conjunta de sus siluetas, tanto delante como detrás. Esto no certifica coincidencia de cuello, costuras o zonas: hay que revisar esos puntos de referencia.
3. **Las zonas actuales no sirven sin ajuste.** En las imágenes nuevas el frente/espalda actuales abarcan aproximadamente x=476–777, y=417–819 px. Aunque caen sobre tela, eso no demuestra que estén bien situados o que representen 30 × 40 cm. La nuca queda demasiado baja en relación con el cuello nuevo. Las mangas actuales solo tienen entre 98,04 % y 98,79 % de cobertura sobre píxeles opacos; parte de su rectángulo cae fuera de la prenda. Esta métrica es una comprobación geométrica, no una prueba de imprimibilidad física.
4. **Las zonas se comparten entre colores.** `View.mockups` cambia la imagen según color; `Zone.rect` pertenece a la vista/producto y no tiene una geometría distinta por color. Hay que normalizar las imágenes para usar una calibración común.
5. **Faltan colores del producto actual.** El producto `CAM-CLASICA` tiene blanco, negro, rojo y azul marino en ambas vistas. Mezclar imágenes nuevas de dos colores con maquetas antiguas de otros dos produciría encuadres incompatibles con las mismas zonas. Además, `App.js` usa la primera maqueta disponible cuando falta la del color solicitado; no debe dependerse de ese fallback para mostrar una prenda de otro color.
6. **La vista previa es plana.** Fabric.js sitúa objetos sobre una imagen de fondo y los recorta a rectángulos. No adapta automáticamente la impresión a pliegues, volumen o perspectiva. Conviene mantener el área central suave y tratar el resultado como vista previa de composición.
7. **La miniatura JPEG requiere atención.** `renderer.js` exporta `renderPreview()` como JPEG sin definir un fondo opaco explícito en el lienzo. Al introducir maquetas transparentes hay que comprobar el resultado y fijar un fondo coherente con el editor para evitar fondos negros o diferencias visuales. Este riesgo se identificó por el código; todavía no se hizo una prueba de exportación en navegador con estas nuevas imágenes.

## Implementación propuesta por etapas

### 1. Preparar derivados y conservar originales

- Mantener intacta la carpeta de propuestas.
- Crear una versión preparada para el personalizador, por ejemplo `assets/customizer/clasica/v1/`, solo durante la implementación.
- Limpiar los recortes blancos y comprobarlos sobre blanco, papel y negro.
- Conservar el lienzo cuadrado y el encuadre compartido. No recortar cada archivo automáticamente al borde de su propio alfa: eso alteraría la posición relativa al cambiar de color.
- Alinear hombros, costuras laterales, eje central, mangas y bajo entre blanco y negro de cada vista. Conservar la diferencia anatómica del cuello entre frente y espalda.
- Optimizar tamaño manteniendo alfa y detalle. Evaluar PNG optimizado o WEBP con transparencia, comparando calidad y compatibilidad antes de elegir. No hace falta aumentar resolución ni crear maquetas a 300 DPI.

**Salida revisable:** cuatro derivados con nombres versionados, registro de dimensiones/peso y revisión de recortes y referencias anatómicas.

### 2. Crear un piloto aislado e importar de forma reversible

- Usar un producto de prueba oculto en el entorno local, con variantes solo blanco/negro. No cambiar ni desactivar variantes del producto que ya se vende.
- Importar los cuatro derivados a la biblioteca de medios de WordPress. El administrador valida adjuntos de imagen; copiar archivos al repositorio no los convierte en maquetas seleccionables.
- Registrar la relación archivo → ID de adjunto y usar identificadores estables o huellas para evitar duplicados si se repite la importación.
- Asignar las imágenes en Productos → Personalizador: Frente/Blanco, Frente/Negro, Espalda/Blanco y Espalda/Negro.
- Guardar copia del JSON de configuración y del mapa de adjuntos antes de una futura activación. Identificar el modelo por SKU, no por el ID local 18.

**Salida revisable:** piloto que carga las cuatro imágenes por la API actual, con posibilidad de volver a las maquetas anteriores.

### 3. Calibrar zonas con referencias físicas

- Conservar los identificadores `front`, `back`, `left-sleeve`, `right-sleeve` y `nape` para proteger diseños guardados.
- Mantener inicialmente los tamaños comerciales existentes: frente/espalda 30 × 40 cm, mangas 9 × 9 cm y nuca 8 × 5 cm. Confirmarlos con la prenda y el taller antes de publicar.
- Ajustar posiciones y tamaños relativos (`rect`) sobre las imágenes nuevas desde el editor de producto.
- Preservar la proporción física. En una imagen cuadrada, un rectángulo de 30 × 40 cm debe tener alto/ancho 4/3. El editor ya calcula esta relación con `heightFor()`; cambiarla visualmente sin respetar los centímetros produciría distorsión.
- Situar nuca debajo del cuello y comprobar que no invade la espalda. Mantener márgenes de seguridad respecto de cuello, costuras y bajo.
- Comprobar ambas mangas sobre ambos colores. Las zonas actuales no tienen rotación ni deformación de superficie. Si un área físicamente útil no cabe en un rectángulo seguro con esta vista, preparar una vista adicional de manga antes de ampliar el motor geométrico.
- No fijar coordenadas definitivas por intuición: las imágenes generadas no incluyen una regla física ni equivalen a las medidas reales de todas las tallas.

**Salida revisable:** una configuración válida con cinco zonas utilizables y el mismo diseño en blanco/negro sin desplazamientos apreciables.

### 4. Ajustar vistas previas sin alterar producción

- Revisar `FabricStage.js` y `renderer.js` como un mismo recorrido visual.
- Dar a la composición JPEG un fondo opaco explícito y coherente con el editor. No aplicar ese fondo a los archivos de impresión.
- Verificar que cada color anunciado tenga una maqueta en cada vista; impedir o avisar una ausencia en lugar de mostrar silenciosamente otro color.
- Mantener la impresión a 300 DPI en `renderZone()` con fondo transparente y solo los elementos del cliente. La prenda, sus sombras y el fondo nunca deben aparecer en el PNG del taller.
- Mantener posiciones, tamaños y cálculos de precio en centímetros. El cambio de resolución de la maqueta no debe cambiar ni el importe ni los píxeles de impresión del mismo diseño.
- Dejar el sombreado/deformación de la impresión sobre tela para una etapa posterior. Si se incorpora, debe ser exclusivamente visual y compartido por editor/miniaturas; no debe deformar el archivo de producción.

**Salida revisable:** mismo diseño y total en editor, carrito y pedido; impresión limpia e independiente de la maqueta.

### 5. Completar colores y activar

- Si se mantienen las cuatro variantes de color actuales, preparar rojo y azul marino de frente/espalda usando las mismas referencias de alineación: harían falta cuatro derivados adicionales.
- No reutilizar estas clásicas como plantilla de oversize, manga larga o niños. Cada corte necesita sus propias imágenes y calibración.
- Antes de cambiar `CAM-CLASICA`, probar borradores locales, editar una línea antigua desde el carrito y revisar un pedido previo. El pedido guarda su documento y zonas; no se deben reescribir pedidos existentes.
- Activar únicamente tras pasar la matriz de pruebas y conservar los adjuntos/configuración anteriores. No borrar los originales que puedan seguir enlazados en registros o vistas previas.

## Matriz de pruebas y criterios de aceptación

| Prueba | Resultado exigido |
|---|---|
| Blanco/negro × frente/espalda | Cuatro combinaciones correctas; ninguna muestra otro color o cara. |
| Recorte en fondos claro/oscuro | Sin manchas exteriores ni halos visibles al tamaño de uso. |
| Texto, estampado y foto | Tamaños y centros se conservan al cambiar color y al volver de otra vista. |
| Cinco zonas | Rectángulos íntegramente sobre tela, con márgenes, y sin invasión del cuello/costuras. |
| Móvil y escritorio | Revisar al menos 320, 390, 768 y 1280 px, además de zoom de mangas/nuca. |
| Precio | Mismo diseño, centímetros y variante: mismo precio antes/después del cambio de maqueta. |
| Carrito y pedido | Miniatura con fondo correcto; permite editar el diseño y conserva los datos. |
| Exportación a 300 DPI | Solo diseño, alfa real y dimensiones esperadas por zona, sin camiseta ni fondo. |
| Borradores y pedidos anteriores | No se pierden diseños ni se alteran los documentos ya guardados. |
| Imagen ausente o carga fallida | Estado claro; no compra basada en una prenda de color equivocado. |
| Rendimiento | Carga solo las imágenes necesarias, sin saltos de aspecto ni archivos innecesariamente pesados. |
| Reversión | Restaurar configuración anterior recupera la representación previa sin borrar pedidos. |

Durante la implementación: ejecutar PHPUnit, pruebas JS de geometría/precios y lint/análisis; añadir una prueba de exportación JPEG con maqueta transparente y una prueba de selección sin maqueta del color. La matriz describe la aceptación completa para publicar; el informe del piloto distingue las comprobaciones ejecutadas de las pendientes.

## Puntos del código revisados

- `src/Config/View.php` y `src/Config/Zone.php`: maquetas por color y geometría común por zona.
- `src/Products/CustomizerData.php`: convierte IDs de medios en URL y dimensiones.
- `src/Admin/ProductConfigMetaBox.php`: selector de medios y validación de adjuntos.
- `client/admin/product-config/ZoneCanvas.js` y `utils.js`: calibración proporcional.
- `client/customizer/FabricStage.js` y `geometry.js`: dibujo del fondo y conversión cm/píxeles.
- `client/customizer/App.js`: selección y fallback de maqueta.
- `client/customizer/renderer.js`: vistas previas JPEG y PNG de impresión separados.
- `src/Design/DesignValidator.php` y `src/Cart/ProductionPage.php`: zonas guardadas en el documento y reutilizadas para producción.

Las rutas de código de esta lista son relativas a `plugins/camisama-customizer/`.
