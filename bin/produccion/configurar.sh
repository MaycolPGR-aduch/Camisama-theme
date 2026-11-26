#!/usr/bin/env bash
# Deja lista una instalación nueva de WordPress en el servidor (staging o producción).
#
# Uso, por SSH, desde la carpeta descomprimida de camisama-carga-<versión>.zip:
#   bash bin/produccion/configurar.sh /ruta/a/public_html ruta/al/camisama-customizer.zip ruta/al/camisama.zip
#
# Instala WooCommerce y el idioma, sube el plugin y el tema, configura la tienda para Perú, crea la
# entrega, las páginas legales (borradores) y la de seguimiento, los 4 modelos con sus estampados y
# sus fotos. La tienda queda en "Próximamente" (solo la ve quien inicia sesión) hasta abrirla.
# Se puede repetir: no duplica nada y no pisa páginas ya publicadas. Detalle en LEEME.md.
set -euo pipefail

SITE="${1:?Falta la carpeta de WordPress (por ejemplo ~/domains/camisama.pe/public_html)}"
PLUGIN_ZIP="${2:?Falta el zip del plugin}"
THEME_ZIP="${3:?Falta el zip del tema}"
HERE="$(cd "$(dirname "$0")/../.." && pwd)"
WP="wp --path=$SITE"

step() { printf '\n==> %s\n' "$1"; }

step "Comprobaciones"
$WP core is-installed || { echo "WordPress no está instalado en $SITE (instálalo desde hPanel)."; exit 1; }
ENVIRONMENT="$($WP eval 'echo wp_get_environment_type();')"
echo "    entorno: $ENVIRONMENT ($($WP option get home))"
if [ "$ENVIRONMENT" = "local" ]; then
	echo "    Esto es para el servidor. En local usa bin/setup-local.ps1."; exit 1
fi

step "Idioma"
$WP language core install es_MX --activate
$WP option update timezone_string America/Lima
$WP option update date_format 'd/m/Y'
$WP option update blogdescription 'Tu diseño, tu ley.'

step "WooCommerce, tema y plugin"
$WP plugin is-installed woocommerce || $WP plugin install woocommerce
$WP language plugin install woocommerce es_MX || true
$WP plugin activate woocommerce
$WP plugin install "$PLUGIN_ZIP" --force --activate
$WP theme install "$THEME_ZIP" --force --activate
$WP plugin delete hello akismet 2>/dev/null || true

ADMIN="$($WP user list --role=administrator --field=user_login --number=1)"

step "Tienda"
$WP eval-file "$HERE/bin/produccion/configurar.php" --use-include --user="$ADMIN"

step "Entrega, páginas y catálogo"
for script in seed-delivery seed-legal seed-tracking seed-demo; do
	$WP eval-file "$HERE/bin/$script.php" --use-include --user="$ADMIN"
done
$WP eval-file "$HERE/bin/import-fotos-modelos.php" confirmar --use-include --user="$ADMIN"

step "Enlaces"
$WP rewrite structure '/%postname%/' --hard

step "Listo"
echo "    La tienda está en «Próximamente». Sigue con la lista de LEEME.md (SMTP, cron, LiteSpeed, pasarela)."
