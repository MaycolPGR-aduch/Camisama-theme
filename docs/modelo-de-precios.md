# Modelo de precios

Propuesta inicial. Todos los importes se configurarán desde el administrador. La tienda vende en
**soles (PEN)** con el formato `S/ 49.90`, y los precios que ve el cliente **incluyen el IGV**.
Los importes de ejemplo son ilustrativos: hay que calibrarlos con el coste real de producción
(film por metro, tinta, polvo, prenda lisa y tiempo de planchado).

## Fórmula

```
precio unitario = precio de la prenda (variante)
                + Σ por cada zona impresa: recargo según el tamaño del estampado
                + Σ recargo de los estampados premium del catálogo usados
total           = precio unitario × cantidad − descuento por volumen
```

### 1. Precio de la prenda

El precio de la **variante** de WooCommerce (modelo + color + talla). Así una talla XXL o un algodón
pesado pueden costar más sin lógica adicional. Incluye la prenda lisa y la mano de obra base.
Los productos de demostración usan S/ 32.90–54.90 y S/ 5 más por la XXL.

### 2. Recargo por zona según tamaño

El tamaño se mide con el **rectángulo que ocupa todo lo que hay en la zona** (estampados, texto e
imágenes juntos), en cm, porque eso es lo que se imprime en el film DTF.

| Tamaño del estampado | Lado mayor | Ejemplo |
|---|---|---|
| Pequeño | hasta 10 cm | + S/ 8 |
| Mediano | hasta 20 cm | + S/ 14 |
| Grande | hasta 30 cm | + S/ 20 |
| Máximo | hasta 40 cm | + S/ 25 |

Las mangas y la nuca solo admiten "Pequeño". Una zona vacía no suma nada.

Se usan escalones y no un precio por cm² porque el cliente los entiende mejor
("Frente grande + S/ 20"), y el precio no cambia con cada pequeño ajuste del diseño.

### 3. Estampados premium (opcional)

Cada estampado del catálogo puede tener un recargo propio (por ejemplo diseños de artistas
invitados, para pagarles una comisión). Por defecto es 0. El texto y las imágenes propias no tienen
recargo adicional: ya pagan por tamaño.

### 4. Descuento por volumen (fase posterior)

| Unidades del mismo diseño | Descuento |
|---|---|
| 1–4 | — |
| 5–11 | 10 % |
| 12+ | 15 % |

## Reglas técnicas

- El navegador muestra el precio en vivo, pero **el servidor lo recalcula siempre** al añadir al
  carrito y al pagar, a partir del diseño guardado. Nunca se usa el precio que manda el navegador.
- El carrito, el correo y el pedido muestran el desglose: prenda + cada zona + premium.
- La lógica de precios se implementa como clases PHP puras con pruebas unitarias.

## Pendiente para salir a producción

- Configurar el IGV (18 %) en WooCommerce con precios que lo incluyen.
- Pasarela de pago peruana (por ejemplo Izipay, Niubiz, Culqi o Mercado Pago; Yape suele llegar a
  través de ellas) y comprobantes electrónicos de SUNAT (boleta/factura) mediante un proveedor.
