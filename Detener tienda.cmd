@echo off
rem Doble clic: apaga Apache, MySQL (de forma ordenada), Mailpit, npm start y la ventana de Laragon.
rem Opcion: -DejarLaragonAbierto
pwsh -NoProfile -ExecutionPolicy Bypass -File "%~dp0bin\detener-tienda.ps1" %*
if errorlevel 1 (
	echo.
	echo Hubo un problema al detener la tienda. Lee el mensaje de arriba.
	pause
	exit /b 1
)
pwsh -NoProfile -Command "Start-Sleep -Seconds 5"
