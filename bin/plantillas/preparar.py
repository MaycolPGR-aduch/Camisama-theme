"""Prepara las plantillas fotográficas del personalizador a partir de assets/propuestas.

    python bin/plantillas/preparar.py

Necesita Pillow, NumPy y SciPy. Para cada modelo y vista saca un único contorno (de la foto con mejor
contraste sobre el fondo) y lo usa en todos los colores: al cambiar de color la prenda no se mueve y
las zonas valen para todos. Escribe en assets/customizer/modelos/v1:

- <modelo>/<color>-<vista>.webp        maqueta del editor, con transparencia;
- <modelo>/<color>-<vista>-foto.webp   foto del producto, sobre el fondo de las tarjetas;
- manifest.json                        archivos, huellas y zonas (zonas.json).

Los frentes de la clásica se copian también al tema (example-tee-*.webp), para la portada.
"""

import hashlib
import json
import shutil
from pathlib import Path

import numpy as np
from PIL import Image
from scipy import ndimage

ROOT = Path(__file__).resolve().parents[2]
SOURCE = ROOT / "assets" / "propuestas"
TARGET = ROOT / "assets" / "customizer" / "modelos" / "v1"
SIZE = 1024
THEME = ROOT / "themes" / "camisama" / "assets" / "images"
THEME_TEES = {"blanco": "white", "negro": "black", "rojo": "red", "azul-marino": "navy"}
VIEWS = {"front": "frente", "back": "espalda"}
CARD = np.array([0xF1, 0xED, 0xE6], float)

# Lo que se ve lejos del borde es prenda seguro; el borde se rehace con el color de dentro.
EDGE = 4

# Por modelo: carpeta, nombre de archivo, foto del contorno y colores. "tone" iguala el tono medio de
# la prenda (el mismo por delante y por detrás); "from" tiñe desde otro color.
MODELS = {
    "clasica": {
        "sku": "CAM-CLASICA",
        "dir": "plantillas-clasica",
        "outline": "roja",
        "colors": {
            "blanco": {"file": "blanca", "tone": (238, 238, 237)},
            "negro": {"file": "negra"},
            "rojo": {"file": "roja", "tone": (190, 42, 36)},
            "azul-marino": {"file": "azul-marino", "tone": (31, 42, 74)},
        },
    },
    "oversize": {
        "sku": "CAM-OVERSIZE",
        "dir": "oversize",
        "outline": "negra",
        "colors": {
            "negro": {"file": "negra"},
            "blanco": {"file": "blanca", "tone": (234, 233, 229)},
            "arena": {"file": "arena"},
        },
    },
    "manga-larga": {
        "sku": "CAM-MANGA-LARGA",
        "dir": "manga-larga",
        "outline": "negra",
        "colors": {
            "blanco": {"file": "blanca"},
            "negro": {"file": "negra"},
            "gris": {"file": "gris", "tone": (154, 154, 158)},
        },
    },
    "ninos": {
        "sku": "CAM-NINOS",
        "dir": "ninos",
        "outline": "blanca",
        "colors": {
            "blanco": {"file": "blanca"},
            "rojo": {"file": "blanca", "dye": (196, 44, 37)},
            "mostaza": {"file": "blanca", "dye": (224, 168, 26)},
        },
    },
}


def load(model, name, view):
    path = SOURCE / model["dir"] / f"{model['dir'].replace('plantillas-', '')}-{name}-{VIEWS[view]}.webp"
    return Image.open(path).convert("RGBA").resize((SIZE, SIZE), Image.LANCZOS)


def largest(mask):
    """La prenda: la mancha mayor."""
    labels, count = ndimage.label(mask)
    sizes = ndimage.sum(mask, labels, range(1, count + 1))
    return labels == 1 + int(np.argmax(sizes))


def inner(inside):
    """Píxeles seguros de la prenda, y para cada píxel el seguro más cercano."""
    safe = ndimage.binary_erosion(inside, iterations=EDGE)
    return ndimage.distance_transform_edt(~safe, return_distances=False, return_indices=True)


def outline(image, fill):
    """Transparencia del contorno de una foto sobre fondo gris liso.

    Con fill se tapan los agujeros: en una prenda blanca, una sombra puede parecerse al fondo. En las
    de color o negras no pasa, y un agujero es fondo de verdad (el hueco de la axila).
    """
    rgb = np.asarray(image.convert("RGB"), float)
    border = np.concatenate([rgb[:12].reshape(-1, 3), rgb[-12:].reshape(-1, 3), rgb[:, :12].reshape(-1, 3), rgb[:, -12:].reshape(-1, 3)])
    back = np.median(border, axis=0)
    inside = np.abs(rgb - back).max(axis=2) > 18
    inside = largest(ndimage.binary_fill_holes(inside) if fill else inside)
    near = inner(inside)
    front = rgb[near[0], near[1]]
    # Cada píxel del borde es mezcla de prenda y fondo: cuánto hay de prenda.
    span = front - back
    alpha = ((rgb - back) * span).sum(axis=2) / np.maximum((span**2).sum(axis=2), 1)
    band = ndimage.binary_dilation(inside, iterations=2) & ~ndimage.binary_erosion(inside, iterations=EDGE)
    alpha = np.where(band, np.clip(alpha, 0, 1), ndimage.binary_erosion(inside, iterations=EDGE))
    # Dentro, aunque una sombra se parezca al fondo, es prenda.
    alpha = np.where(ndimage.binary_erosion(inside, iterations=2), 1, alpha)
    return ndimage.gaussian_filter(alpha.astype(float), 0.6)


def fabric(image, alpha):
    """Color de la prenda en todo el contorno común, sin rastro del fondo en el borde."""
    rgba = np.asarray(image, float)
    inside = alpha > 0.5
    if rgba[..., 3].min() < 250:
        inside &= ndimage.binary_fill_holes(rgba[..., 3] > 250)
    near = inner(largest(inside))
    return rgba[..., :3][near[0], near[1]]


def tone(rgb, alpha, target):
    """Lleva el tono medio de la prenda al pedido, conservando luces y pliegues."""
    body = alpha > 0.99
    mean = np.median(rgb[body], axis=0)
    return np.clip(rgb * (np.array(target, float) / mean), 0, 255)


def dye(rgb, alpha, target):
    """Tiñe una prenda blanca: su sombreado, con algo más de contraste, sobre el color."""
    light = rgb.mean(axis=2)
    shade = light / np.median(light[alpha > 0.99])
    shade = np.clip(1 + (shade - 1) * 1.6, 0.25, 1.14)
    return np.clip(np.array(target, float) * shade[..., None], 0, 255)


def save(image, path, quality):
    path.parent.mkdir(parents=True, exist_ok=True)
    image.save(path, "WEBP", quality=quality, method=6, exact=False)
    data = path.read_bytes()
    return {"file": path.relative_to(TARGET).as_posix(), "bytes": len(data), "sha256": hashlib.sha256(data).hexdigest()}


def main():
    zones = json.loads((Path(__file__).parent / "zonas.json").read_text(encoding="utf-8"))
    manifest = {"version": 1, "physical_measurements_confirmed": False, "models": {}}
    for slug, model in MODELS.items():
        alphas = {view: outline(load(model, model["outline"], view), model["outline"] == "blanca") for view in VIEWS}
        images = []
        for color, spec in model["colors"].items():
            for view, alpha in alphas.items():
                rgb = fabric(load(model, spec["file"], view), alpha)
                if "tone" in spec:
                    rgb = tone(rgb, alpha, spec["tone"])
                if "dye" in spec:
                    rgb = dye(rgb, alpha, spec["dye"])
                layer = np.dstack([rgb, alpha * 255]).round().astype(np.uint8)
                photo = (rgb * alpha[..., None] + CARD * (1 - alpha[..., None])).round().astype(np.uint8)
                base = TARGET / slug / f"{color}-{view}"
                images.append(
                    {
                        "color": color,
                        "view": view,
                        "mockup": save(Image.fromarray(layer, "RGBA"), base.with_suffix(".webp"), 86),
                        "photo": save(Image.fromarray(photo, "RGB"), base.with_name(base.name + "-foto.webp"), 84),
                    }
                )
        manifest["models"][slug] = {"sku": model["sku"], "zones": zones[slug], "images": images}
    text = json.dumps(manifest, ensure_ascii=False, indent="\t")
    (TARGET / "manifest.json").write_text(text + "\n", encoding="utf-8", newline="\n")
    for color, name in THEME_TEES.items():
        shutil.copyfile(TARGET / "clasica" / f"{color}-front.webp", THEME / f"example-tee-{name}.webp")
    files = sorted(TARGET.rglob("*.webp"))
    print(f"{len(files)} imágenes, {sum(f.stat().st_size for f in files) // 1024} KB")


if __name__ == "__main__":
    main()
