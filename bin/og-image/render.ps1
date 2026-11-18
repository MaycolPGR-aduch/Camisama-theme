# Fotografía og-home.html a 1200 × 630 con Chrome sin ventana y deja el PNG en el tema.
# Uso: pwsh bin/og-image/render.ps1
$ErrorActionPreference = 'Stop'
$here   = $PSScriptRoot
$source = Join-Path $here 'og-home.html'
$target = Join-Path $here '..\..\themes\camisama\assets\images\og-home.png' | Resolve-Path -ErrorAction SilentlyContinue
if (-not $target) { $target = Join-Path (Resolve-Path (Join-Path $here '..\..\themes\camisama\assets\images')) 'og-home.png' }
$chrome = @(
	"$env:ProgramFiles\Google\Chrome\Application\chrome.exe",
	"${env:ProgramFiles(x86)}\Microsoft\Edge\Application\msedge.exe"
) | Where-Object { Test-Path $_ } | Select-Object -First 1
if (-not $chrome) { throw 'No se encontró Chrome ni Edge.' }
$url = ([System.Uri] (Resolve-Path $source).Path).AbsoluteUri
& $chrome --headless=new --disable-gpu --hide-scrollbars --force-device-scale-factor=1 `
	--allow-file-access-from-files --virtual-time-budget=3000 `
	--window-size=1200,630 "--screenshot=$target" $url 2>$null | Out-Null
Write-Host "Imagen: $target"
