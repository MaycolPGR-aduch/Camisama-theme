#Requires -Version 7.0
<#
.SYNOPSIS
	Apaga todo lo que usa la tienda en local.

.DESCRIPTION
	Detiene, en este orden: la recompilación automática (npm start) de este proyecto, Apache,
	MySQL (de forma ordenada, para no dañar la base de datos), Mailpit y la ventana de Laragon.
	Solo toca procesos que estén dentro de la carpeta de Laragon o que sean "npm start" de este
	repositorio.

.PARAMETER DejarLaragonAbierto
	No cierra la ventana de Laragon (solo sus servicios).

.EXAMPLE
	pwsh bin/detener-tienda.ps1
#>
[CmdletBinding()]
param(
	[string] $LaragonRoot = 'C:\laragon',
	[switch] $DejarLaragonAbierto
)

$ErrorActionPreference = 'Stop'
$repo = Split-Path -Parent $PSScriptRoot

function Write-Step( [string] $Message ) { Write-Host "==> $Message" -ForegroundColor Cyan }

# Procesos con ese nombre cuyo ejecutable está dentro de Laragon.
function Get-LaragonProcess( [string] $Name ) {
	$root = (Resolve-Path $LaragonRoot).Path
	Get-Process -Name $Name -ErrorAction SilentlyContinue |
		Where-Object { $_.Path -and $_.Path.StartsWith($root, [StringComparison]::OrdinalIgnoreCase) }
}

function Stop-Gently( $Processes, [string] $Label ) {
	if (-not $Processes) {
		Write-Host "    $Label no estaba en marcha"
		return
	}
	$Processes | Stop-Process -Force -ErrorAction SilentlyContinue
	Write-Host "    $Label detenido"
}

# --- npm start de este repositorio -----------------------------------------------
Write-Step 'Recompilación automática (npm start)'
$watchers = Get-CimInstance Win32_Process -Filter "Name='node.exe'" -ErrorAction SilentlyContinue |
	Where-Object { $_.CommandLine -match 'wp-scripts' -and $_.CommandLine -match ' start' -and $_.CommandLine -like "*$repo*" }
if ($watchers) {
	$watchers | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }
	Write-Host '    detenida'
} else {
	Write-Host '    no estaba en marcha'
}

# --- Apache ----------------------------------------------------------------------
Write-Step 'Apache'
Stop-Gently (Get-LaragonProcess 'httpd') 'Apache'

# --- MySQL: apagado ordenado -----------------------------------------------------
Write-Step 'MySQL'
$mysqld = Get-LaragonProcess 'mysqld'
if ($mysqld) {
	$mysqlAdmin = Get-ChildItem "$LaragonRoot\bin\mysql\*\bin\mysqladmin.exe" | Select-Object -First 1
	if ($mysqlAdmin) {
		& $mysqlAdmin.FullName -u root -h 127.0.0.1 shutdown 2>$null
	}
	for ($i = 0; $i -lt 40 -and (Get-LaragonProcess 'mysqld'); $i++) {
		Start-Sleep -Milliseconds 500
	}
	if (Get-LaragonProcess 'mysqld') {
		Write-Host '    no se apagó de forma ordenada en 20 s; se fuerza el cierre' -ForegroundColor Yellow
		Get-LaragonProcess 'mysqld' | Stop-Process -Force
	}
	Write-Host '    detenido'
} else {
	Write-Host '    MySQL no estaba en marcha'
}

# --- Mailpit ---------------------------------------------------------------------
Write-Step 'Mailpit'
Stop-Gently (Get-LaragonProcess 'mailpit') 'Mailpit'

# --- Ventana de Laragon ----------------------------------------------------------
if (-not $DejarLaragonAbierto) {
	Write-Step 'Laragon'
	$laragon = Get-LaragonProcess 'laragon'
	if ($laragon) {
		$laragon | ForEach-Object { [void] $_.CloseMainWindow() }
		Start-Sleep -Seconds 2
		Get-LaragonProcess 'laragon' | Stop-Process -Force -ErrorAction SilentlyContinue
		Write-Host '    cerrado'
	} else {
		Write-Host '    no estaba abierto'
	}
}

Write-Host ''
Write-Host 'Todo apagado.' -ForegroundColor Green
