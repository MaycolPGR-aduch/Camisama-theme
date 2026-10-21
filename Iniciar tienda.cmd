@echo off
rem Doble clic: arranca MySQL, Apache y Mailpit de Laragon y abre la tienda en el navegador.
rem Opciones: -Desarrollo (recompila el JS/CSS al guardar), -SinNavegador
pwsh -NoProfile -ExecutionPolicy Bypass -File "%~dp0bin\iniciar-tienda.ps1" %*
if errorlevel 1 (
	echo.
	echo No se pudo iniciar la tienda. Lee el mensaje de arriba.
	pause
	exit /b 1
)
pwsh -NoProfile -Command "Start-Sleep -Seconds 8"
