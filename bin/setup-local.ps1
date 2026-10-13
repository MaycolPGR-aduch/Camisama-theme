#Requires -Version 7.0
<#
.SYNOPSIS
	Crea o completa el sitio local de Camisama en Laragon.

.DESCRIPTION
	Se puede ejecutar todas las veces que haga falta: cada paso comprueba si ya está hecho.

	Requisitos previos:
	  - Laragon iniciado (MySQL en marcha).
	  - "composer install" y "npm install" ejecutados en la raíz del repositorio.

	Pasos:
	  1. Descarga WordPress en <Laragon>\www\<Site> y genera wp-config.php para desarrollo.
	  2. Crea la base de datos.
	  3. Instala WordPress (las credenciales del admin quedan en .env.local, ignorado por git).
	  4. Enlaza el tema y el plugin del repositorio dentro de wp-content (junctions).
	  5. Instala WooCommerce y activa tema y plugin.

.EXAMPLE
	pwsh bin/setup-local.ps1
#>
[CmdletBinding()]
param(
	[string] $LaragonRoot = 'C:\laragon',
	[string] $Site = 'camisama',
	# es_PE no tiene traducción completa de WordPress ni de WooCommerce; es_MX sí.
	[string] $Locale = 'es_MX',
	[string] $DbName = 'camisama',
	[string] $DbUser = 'root',
	[string] $DbPassword = '',
	# No crear los productos de demostración (bin/seed-demo.php).
	[switch] $SkipDemo
)

$ErrorActionPreference = 'Stop'
$PSNativeCommandUseErrorActionPreference = $true

$repo     = Split-Path -Parent $PSScriptRoot
$sitePath = Join-Path $LaragonRoot "www\$Site"
$siteUrl  = "http://$Site.test"
$wpBoot   = Join-Path $repo 'vendor\wp-cli\wp-cli\php\boot-fs.php'

function Write-Step( [string] $Message ) {
	Write-Host "`n==> $Message" -ForegroundColor Cyan
}

# WP-CLI se llama directamente con PHP (vendor/bin/wp.bat necesita "sh" en el PATH).
function Invoke-Wp {
	& php $wpBoot @args
}

# Igual que Invoke-Wp, pero devuelve $true/$false según el código de salida en lugar de fallar.
function Test-Wp {
	$PSNativeCommandUseErrorActionPreference = $false
	& php $wpBoot @args *> $null
	return $LASTEXITCODE -eq 0
}

function New-Password( [int] $Length = 24 ) {
	$chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789'
	-join (1..$Length | ForEach-Object { $chars[[Security.Cryptography.RandomNumberGenerator]::GetInt32($chars.Length)] })
}

function New-Junction( [string] $Link, [string] $Target ) {
	$item = Get-Item -LiteralPath $Link -ErrorAction SilentlyContinue
	if ($item -and $item.LinkType -eq 'Junction') {
		Write-Host "    ya enlazado: $Link"
		return
	}
	if ($item) {
		throw "$Link ya existe y no es un enlace al repositorio. Revísalo y bórralo a mano si sobra."
	}
	New-Item -ItemType Junction -Path $Link -Target $Target | Out-Null
	Write-Host "    $Link -> $Target"
}

Set-Location $repo

if (-not (Test-Path $wpBoot)) {
	throw 'No encuentro WP-CLI. Ejecuta "composer install" en la raíz del repositorio.'
}

# --- 1. WordPress (no necesita MySQL) --------------------------------------------
Write-Step "WordPress en $sitePath"
@"
# Generado por bin/setup-local.ps1 (no se versiona).
_:
  inherit: wp-cli.yml
path: $($sitePath -replace '\\', '/')
url: $siteUrl
"@ | Set-Content -Path (Join-Path $repo 'wp-cli.local.yml') -Encoding utf8NoBOM

# Se descarga el zip oficial en inglés y se extrae con el tar de Windows: "wp core download"
# falla en Windows con las rutas largas de WordPress 7.x. El idioma se instala después como
# paquete de traducción (los paquetes ya traducidos de una versión nueva tardan en publicarse).
if (-not (Test-Path (Join-Path $sitePath 'wp-load.php'))) {
	$version = (Invoke-RestMethod 'https://api.wordpress.org/core/version-check/1.7/').offers[0].version
	$zipUrl  = "https://downloads.wordpress.org/release/wordpress-$version.zip"
	$zip     = Join-Path ([IO.Path]::GetTempPath()) "wordpress-$version.zip"

	Write-Host "    descargando WordPress $version"
	Invoke-WebRequest $zipUrl -OutFile $zip
	$expected = (Invoke-RestMethod "$zipUrl.sha1").Trim()
	if ((Get-FileHash $zip -Algorithm SHA1).Hash -ne $expected) {
		Remove-Item $zip
		throw "El SHA-1 de $zipUrl no coincide; descarga descartada."
	}

	New-Item -ItemType Directory -Force -Path $sitePath | Out-Null
	& "$env:SystemRoot\System32\tar.exe" -xf $zip --strip-components=1 -C $sitePath
	Remove-Item $zip
	Invoke-Wp core verify-checksums --quiet
} else {
	Write-Host '    ya descargado'
}

if (-not (Test-Path (Join-Path $sitePath 'wp-config.php'))) {
	Invoke-Wp config create --dbname=$DbName --dbuser=$DbUser --dbpass=$DbPassword --dbhost=127.0.0.1 --skip-check
}
Invoke-Wp config set WP_ENVIRONMENT_TYPE local --quiet
# Sin esto WordPress guarda en caché la lista de patrones del tema y no ve los archivos nuevos.
Invoke-Wp config set WP_DEVELOPMENT_MODE all --quiet
foreach ($constant in 'WP_DEBUG', 'WP_DEBUG_LOG', 'SCRIPT_DEBUG') {
	Invoke-Wp config set $constant true --raw --quiet
}
Invoke-Wp config set WP_DEBUG_DISPLAY false --raw --quiet

# --- 2. Base de datos ------------------------------------------------------------
Write-Step "Base de datos '$DbName'"
$mysql = Get-ChildItem "$LaragonRoot\bin\mysql\*\bin\mysql.exe" | Select-Object -First 1
if (-not $mysql) {
	throw "No encuentro mysql.exe dentro de $LaragonRoot\bin\mysql."
}
$mysqlArgs = @('-u', $DbUser, '-h', '127.0.0.1')
if ($DbPassword) { $mysqlArgs += "--password=$DbPassword" }

$PSNativeCommandUseErrorActionPreference = $false
& $mysql.FullName @mysqlArgs -e 'SELECT 1' *> $null
$mysqlUp = $LASTEXITCODE -eq 0
$PSNativeCommandUseErrorActionPreference = $true
if (-not $mysqlUp) {
	throw 'MySQL no responde. Abre Laragon, pulsa "Iniciar todo" (Start All) y vuelve a ejecutar este script.'
}
& $mysql.FullName @mysqlArgs -e "CREATE DATABASE IF NOT EXISTS ``$DbName`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# --- 3. Instalación --------------------------------------------------------------
Write-Step 'Instalación'
if (Test-Wp core is-installed) {
	Write-Host '    ya instalado'
} else {
	$password = New-Password
	Invoke-Wp core install --url=$siteUrl --title=Camisama --admin_user=admin --admin_password=$password --admin_email="admin@$Site.test" --skip-email
	# Contenido de ejemplo de WordPress ("Hello world!", "Sample Page").
	Invoke-Wp post delete 1 2 --force --quiet
	# WordPress crea la página de privacidad (ID 3) antes de que esté instalado el idioma.
	Invoke-Wp post update 3 --post_title='Política de privacidad' --post_name=politica-de-privacidad --quiet
	@"
# Credenciales del sitio local (generadas por bin/setup-local.ps1, no se versionan).
WP_URL=$siteUrl
WP_ADMIN_URL=$siteUrl/wp-admin/
WP_ADMIN_USER=admin
WP_ADMIN_PASSWORD=$password
"@ | Set-Content -Path (Join-Path $repo '.env.local') -Encoding utf8NoBOM
	Write-Host '    credenciales del admin guardadas en .env.local'
}
if (-not (Test-Wp language core is-installed $Locale)) {
	Invoke-Wp language core install $Locale
}
Invoke-Wp site switch-language $Locale --quiet
Invoke-Wp option update blogdescription 'Tu diseño, tu ley.' --quiet
Invoke-Wp option update timezone_string America/Lima --quiet
Invoke-Wp option update date_format 'd/m/Y' --quiet

# --- 4. Enlaces al repositorio ---------------------------------------------------
Write-Step 'Enlazando tema y plugin'
New-Junction (Join-Path $sitePath 'wp-content\themes\camisama') (Join-Path $repo 'themes\camisama')
New-Junction (Join-Path $sitePath 'wp-content\plugins\camisama-customizer') (Join-Path $repo 'plugins\camisama-customizer')

if (-not (Test-Path (Join-Path $repo 'plugins\camisama-customizer\build\blocks\customizer\block.json'))) {
	Write-Host '    compilando los assets del plugin (npm run build)'
	npm run build --silent
}

# --- 5. WooCommerce, tema y plugin -----------------------------------------------
Write-Step 'WooCommerce, tema y plugin'
if (-not (Test-Wp plugin is-installed woocommerce)) {
	Invoke-Wp plugin install woocommerce
}
# La traducción va antes de activar: WooCommerce crea sus páginas (Tienda, Carrito…) al activarse.
if (-not (Test-Wp language plugin install woocommerce $Locale)) {
	Write-Host "    (no hay traducción de WooCommerce para $Locale)"
}
Invoke-Wp plugin activate woocommerce camisama-customizer
Invoke-Wp theme activate camisama

# Tienda visible sin iniciar sesión y sin el asistente de configuración inicial.
Invoke-Wp option update woocommerce_coming_soon no --quiet
Invoke-Wp option update woocommerce_onboarding_profile '{"skipped":true}' --format=json --quiet

# Perú: soles con el formato "S/ 1,234.50", tienda en Lima.
$storeOptions = [ordered]@{
	woocommerce_default_country      = 'PE:LMA'
	woocommerce_currency             = 'PEN'
	woocommerce_currency_pos         = 'left_space'
	woocommerce_price_thousand_sep   = ','
	woocommerce_price_decimal_sep    = '.'
	woocommerce_price_num_decimals   = '2'
	woocommerce_allowed_countries    = 'specific'
	woocommerce_ship_to_countries    = 'specific'
	woocommerce_weight_unit          = 'kg'
	woocommerce_dimension_unit       = 'cm'
}
foreach ($option in $storeOptions.GetEnumerator()) {
	Invoke-Wp option update $option.Key $option.Value --quiet
}
Invoke-Wp option update woocommerce_specific_allowed_countries '["PE"]' --format=json --quiet
Invoke-Wp option update woocommerce_specific_ship_to_countries '["PE"]' --format=json --quiet
Invoke-Wp option update woocommerce_permalinks '{"product_base":"/producto","category_base":"categoria-producto","tag_base":"etiqueta-producto","attribute_base":"","use_verbose_page_rules":false}' --format=json --quiet

if (-not $SkipDemo) {
	Write-Step 'Productos de demostración'
	Invoke-Wp eval-file (Join-Path $repo 'bin\seed-demo.php') --use-include --user=admin
}

Invoke-Wp rewrite structure '/%postname%/' --hard --quiet

# --- Resumen ---------------------------------------------------------------------
$vhost = Join-Path $LaragonRoot "etc\apache2\sites-enabled\auto.$Site.test.conf"
Write-Host "`nListo: $siteUrl  (admin: $siteUrl/wp-admin/, credenciales en .env.local)" -ForegroundColor Green
if (-not (Test-Path $vhost)) {
	Write-Host "Laragon aún no creó el host virtual $Site.test: en Laragon pulsa 'Recargar' (Reload)." -ForegroundColor Yellow
}
