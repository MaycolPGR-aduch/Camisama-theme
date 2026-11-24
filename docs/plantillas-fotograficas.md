# Plantillas fotográficas

Desde el 24 de noviembre de 2026 los 4 modelos de la tienda local usan fotos en lugar de las maquetas
dibujadas: en el editor, la tienda, la ficha, el carrito y la confirmación.

**Son imágenes generadas con IA**, no fotos de las prendas del taller. Antes de abrir al público hay que
sustituirlas por fotos reales o comprobar, prenda en mano, que corte, color y textura coinciden.

## De dónde sale cada cosa

| Qué | Dónde |
|---|---|
| Fotos de partida | `assets/propuestas/<modelo>/` (fondo gris, sin transparencia) |
| Recorte, color y fotos de producto | `python bin/plantillas/preparar.py` (Pillow, NumPy, SciPy) |
| Zonas de impresión por modelo | `bin/plantillas/zonas.json` |
| Resultado, con huellas | `assets/customizer/modelos/v1/` y su `manifest.json` |
| Puesta en la tienda local | `bin/import-fotos-modelos.php` (también desde `bin/setup-local.ps1`) |

Volver a las maquetas y zonas anteriores:

```powershell
php vendor/wp-cli/wp-cli/php/boot-fs.php --path=C:\laragon\www\camisama eval-file bin/import-fotos-modelos.php restaurar --use-include --user=admin
```

## Decisiones

- **Un solo contorno por modelo y vista**, común a todos los colores (las siluetas coinciden en más del
  99 %): al cambiar de color la prenda no se mueve y las zonas valen para todos. Sale de la foto con mejor
  contraste con el fondo gris: roja en la clásica, negra en oversize y manga larga, blanca en niños. Así se
  recorta también la gris, que se confunde con el fondo.
- **Sin halo:** los 4 píxeles del borde se rehacen con el color de la prenda.
- **Tono igualado** entre frente y espalda en rojo, azul marino y gris jaspeado; blanco algo más claro en
  la clásica y la oversize.
- **Niños en rojo y mostaza, teñidos** desde la blanca (solo había blanca). Si se generan sus fotos, se
  cambia `dye` por `file` en `preparar.py`.
- **Zonas a escala:** las fotos son de prenda "puesta", más estrecha de lo que mide extendida. La escala de
  cada modelo es la media geométrica entre la que da el ancho y la que da el largo de la talla de
  referencia (M; 8 en niños). Es la misma en toda la prenda: en el piloto las mangas iban un tercio más
  pequeñas. Los 20 rectángulos caen enteros sobre tela, con 0,6 cm o más de margen.
- **Los centímetros y los identificadores de las zonas no cambian**, así que tampoco los precios, los PNG
  de impresión, los pedidos ni los diseños guardados.

## Pendiente

1. Confirmar las zonas con el taller y la prenda física (costuras, posición, diferencias por talla).
2. Fotos reales, o niños en rojo y mostaza generadas como las demás.
3. La vista previa sigue siendo plana: la impresión no sigue los pliegues.
4. La portada usa todavía sus prendas dibujadas (`themes/camisama/assets/images/example-tee-*.png`).
5. El producto del piloto (`CAM-CLASICA-PILOTO-V1`, oculto) ya no hace falta; sigue publicado.
