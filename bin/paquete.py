"""Paquetes para instalar la tienda en un servidor (bin/produccion/LEEME.md).

    python bin/paquete.py [versión]

Antes: `npm run build` y `composer install --no-dev` en plugins/camisama-customizer (lo compilado y
las dependencias no están en el repositorio). Deja en dist/:

- camisama-customizer-<versión>.zip  el plugin, listo para subirlo desde el admin de WordPress;
- camisama-<versión>.zip             el tema;
- camisama-carga-<versión>.zip       scripts, estampados y fotos para configurar una instalación
                                     nueva (bin/produccion/configurar.sh). No va en la web.
"""

import sys
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
DIST = ROOT / "dist"
PLUGIN = ROOT / "plugins" / "camisama-customizer"

# Del plugin no se publican las fuentes del editor, las pruebas ni la configuración de Composer.
PLUGIN_SKIP = {"client", "tests", "composer.json", "composer.lock", ".phpunit.cache"}

LOAD = [
    "bin/produccion",
    "bin/seed-delivery.php",
    "bin/seed-legal.php",
    "bin/seed-tracking.php",
    "bin/seed-demo.php",
    "bin/demo-designs",
    "bin/import-fotos-modelos.php",
    "assets/customizer/modelos",
]


def files(base):
    return sorted(path for path in base.rglob("*") if path.is_file())


def pack(target, entries):
    count = 0
    with zipfile.ZipFile(target, "w", zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
        for path, name in entries:
            archive.write(path, name)
            count += 1
    print(f"{target.relative_to(ROOT)}: {count} archivos, {target.stat().st_size // 1024} KB")


def main():
    version = sys.argv[1] if len(sys.argv) > 1 else "dev"
    for needed in (PLUGIN / "build" / "blocks" / "customizer" / "block.json", PLUGIN / "vendor" / "autoload.php"):
        if not needed.is_file():
            sys.exit(f"Falta {needed.relative_to(ROOT)}: compila (npm run build) e instala (composer install --no-dev) antes.")
    DIST.mkdir(exist_ok=True)

    plugin = [
        (path, "camisama-customizer/" + path.relative_to(PLUGIN).as_posix())
        for path in files(PLUGIN)
        if path.relative_to(PLUGIN).parts[0] not in PLUGIN_SKIP
    ]
    pack(DIST / f"camisama-customizer-{version}.zip", plugin)

    theme = ROOT / "themes" / "camisama"
    pack(DIST / f"camisama-{version}.zip", [(path, "camisama/" + path.relative_to(theme).as_posix()) for path in files(theme)])

    load = []
    for item in LOAD:
        source = ROOT / item
        paths = files(source) if source.is_dir() else [source]
        load += [(path, "camisama-carga/" + path.relative_to(ROOT).as_posix()) for path in paths]
    pack(DIST / f"camisama-carga-{version}.zip", load)


if __name__ == "__main__":
    main()
