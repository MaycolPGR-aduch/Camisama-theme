# Plantillas del piloto clásico v1

Solo para el piloto local oculto `CAM-CLASICA-PILOTO-V1`. No sustituye las maquetas del catálogo actual.

- Cuatro WebP RGBA de 1254 × 1254; conversión con FFmpeg/libwebp, calidad 90, compresión 6. Lienzo, escala y alfa conservados; no se recortaron ni alinearon píxeles automáticamente.
- Total: 270 464 bytes frente a 4 394 381 bytes de los PNG seleccionados (aproximadamente 94 % menos).
- `manifest.json` registra archivos, origen, SHA-256 y geometría común a ambos colores.
- Cinco zonas ajustadas visualmente, con proporciones y centímetros anteriores. Todos sus píxeles quedan sobre alfa ≥250 en las cuatro combinaciones pertinentes. Esto no confirma medidas físicas, costuras ni imprimibilidad real.
- **Pendiente:** recorte fino del blanco y confirmación del taller. Los intentos nuevos con ImageGen siguen en propuestas/variantes; no mejoraron suficientemente el borde.
- `verificacion/` conserva capturas y el resultado de la comprobación de exportación.

Importar/reparar el piloto desde la raíz:

```powershell
php vendor/wp-cli/wp-cli/php/boot-fs.php eval-file bin/import-customizer-pilot.php --use-include --user=admin
```

El script exige un sitio `.test`, reutiliza adjuntos por clave/huella y variantes por SKU. Guarda una copia inicial de la configuración de la clásica en la opción local `_camisama_pilot_v1_source_backup`; no escribe en ese producto. Repetir la importación restablece la configuración del piloto al manifiesto.

Para retirar el piloto, pasarlo a borrador en WooCommerce. Conservar los medios mientras existan carritos o pedidos de prueba que los referencien.
