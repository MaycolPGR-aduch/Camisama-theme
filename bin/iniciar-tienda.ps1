#Requires -Version 7.0
<#
.SYNOPSIS
	Arranca todo lo necesario para ver la tienda en local y la abre en el navegador.

.DESCRIPTION
	Inicia MySQL, Apache y Mailpit de Laragon con los mismos comandos que usa Laragon (sin necesidad
	de abrir su ventana). Lo que ya esté en marcha se deja como está. Espera a que la tienda responda
	y la abre en el navegador predeterminado.

	Para apagarlo todo: bin/detener-tienda.ps1 (o "Detener tienda.cmd").

.PARAMETER Desarrollo
	Además abre una ventana con "npm start", que recompila el JS/CSS del plugin al guardar cambios.

.PARAMETER SinNavegador
	No abre el navegador.

.EXAMPLE
	pwsh bin/iniciar-tienda.ps1
	pwsh bin/iniciar-tienda.ps1 -Desarrollo
#>
[CmdletBinding()]
param(
	[string] $LaragonRoot = 'C:\laragon',
	[string] $Site = 'camisama',
	[switch] $Desarrollo,
	[switch] $SinNavegador
)

$ErrorActionPreference = 'Stop'
$repo    = Split-Path -Parent $PSScriptRoot
$siteUrl = "http://$Site.test"

function Write-Step( [string] $Message ) { Write-Host "==> $Message" -ForegroundColor Cyan }

function Test-Port( [int] $Port ) {
	$client = [Net.Sockets.TcpClient]::new()
	try { return $client.ConnectAsync('127.0.0.1', $Port).Wait(500) -and $client.Connected }
	catch { return $false }
	finally { $client.Dispose() }
}

function Wait-Port( [int] $Port, [string] $Name, [int] $Seconds = 60 ) {
	for ($i = 0; $i -lt $Seconds * 2; $i++) {
		if (Test-Port $Port) { return }
		Start-Sleep -Milliseconds 500
	}
	throw "$Name no respondió en $Seconds s. Revisa el registro de errores de Laragon."
}

function Find-Latest( [string] $Pattern ) {
	$item = Get-ChildItem $Pattern -ErrorAction SilentlyContinue | Sort-Object Name -Descending | Select-Object -First 1
	if (-not $item) { throw "No encuentro $Pattern. ¿Está instalado Laragon en $LaragonRoot?" }
	return $item.FullName
}

# --- MySQL -----------------------------------------------------------------------
Write-Step 'MySQL'
if (Test-Port 3306) {
	Write-Host '    ya estaba en marcha'
} else {
	$mysqlHome = Find-Latest "$LaragonRoot\bin\mysql\mysql-*"
	$dataDir   = (Select-String -Path "$mysqlHome\my.ini" -Pattern '^\s*datadir\s*=\s*"?([^"]+)"?' | Select-Object -First 1).Matches[0].Groups[1].Value
	Start-Process -FilePath "$mysqlHome\bin\mysqld.exe" -ArgumentList "--log-error=$dataDir\mysqld.log" -WindowStyle Hidden
	Wait-Port 3306 'MySQL'
	Write-Host '    iniciado'
}

# --- Apache ----------------------------------------------------------------------
Write-Step 'Apache'
if (Test-Port 80) {
	$owner = Get-NetTCPConnection -LocalPort 80 -State Listen -ErrorAction SilentlyContinue | Select-Object -First 1
	$name  = if ($owner) { (Get-Process -Id $owner.OwningProcess -ErrorAction SilentlyContinue).ProcessName } else { '?' }
	if ($name -ne 'httpd') {
		throw "El puerto 80 lo está usando otro programa ($name). Ciérralo y vuelve a intentarlo."
	}
	Write-Host '    ya estaba en marcha'
} else {
	$apacheHome = Find-Latest "$LaragonRoot\bin\apache\httpd-*"
	Start-Process -FilePath "$apacheHome\bin\httpd.exe" -WorkingDirectory "$apacheHome\bin" -WindowStyle Hidden
	Wait-Port 80 'Apache'
	Write-Host '    iniciado'
}

# --- Mailpit (correos de prueba) -------------------------------------------------
Write-Step 'Mailpit (correos de prueba)'
if (Test-Port 8025) {
	Write-Host '    ya estaba en marcha'
} else {
	$mailpitHome = Find-Latest "$LaragonRoot\bin\mailpit\*"
	Start-Process -FilePath "$mailpitHome\mailpit.exe" -ArgumentList '--smtp', '127.0.0.1:1025', '--log-file', "$mailpitHome\mailpit.log", '--quiet' -WindowStyle Hidden
	Wait-Port 8025 'Mailpit' 20
	Write-Host '    iniciado'
}

# --- Comprobación de la tienda ---------------------------------------------------
Write-Step "Tienda $siteUrl"
$hosts = Get-Content "$env:SystemRoot\System32\drivers\etc\hosts" -ErrorAction SilentlyContinue
if (-not ($hosts | Select-String -SimpleMatch "$Site.test")) {
	throw "Windows no conoce $Site.test. Abre Laragon una vez y pulsa 'Recargar' para que lo registre."
}
$ok = $false
for ($i = 0; $i -lt 30 -and -not $ok; $i++) {
	try {
		$ok = (Invoke-WebRequest $siteUrl -UseBasicParsing -TimeoutSec 10).StatusCode -eq 200
	} catch {
		Start-Sleep -Seconds 1
	}
}
if (-not $ok) { throw "La tienda no responde en $siteUrl. Revisa wp-content/debug.log." }
Write-Host '    responde correctamente'

# --- Modo desarrollo -------------------------------------------------------------
if ($Desarrollo) {
	Write-Step 'Recompilación automática (npm start)'
	Start-Process -FilePath 'pwsh' -ArgumentList '-NoExit', '-Command', "Set-Location '$repo'; npm start" -WorkingDirectory $repo
	Write-Host '    abierta en otra ventana; ciérrala o usa detener-tienda.ps1 para pararla'
}

Write-Host ''
Write-Host "Tienda:          $siteUrl" -ForegroundColor Green
Write-Host "Administración:  $siteUrl/wp-admin/  (usuario y contraseña en .env.local)" -ForegroundColor Green
Write-Host 'Correos:         http://localhost:8025' -ForegroundColor Green
Write-Host 'Para apagarlo todo: "Detener tienda.cmd" o pwsh bin/detener-tienda.ps1'

if (-not $SinNavegador) {
	Start-Process $siteUrl
}
