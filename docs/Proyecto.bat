@echo off
:: Verifica si el script se está ejecutando como administrador
net session >nul 2>&1
if %errorLevel% == 0 (
    echo Ejecutando XAMPP como administrador...
    start c:\xampp\xampp-control.exe
    echo Espera a que cargue XAMPP
    pause
    start node c:\xampp\htdocs\Proyecto-DAW\js\server.js
) else (
    echo Solicitud de privilegios de administrador...
    :: Vuelve a ejecutar el script como administrador
    powershell -Command "Start-Process '%~f0' -Verb runAs"
    exit /b
)