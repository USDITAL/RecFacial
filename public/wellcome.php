<?php
session_start();
//Carga librerías de Composer
require '../vendor/autoload.php';

// Si ya hay una sesión activa, redirige según ROL
if (isset($_SESSION['COD_USUARIO'])) {
    if (in_array('Empleado',$_SESSION['ROLES'])){
        header('Location: empleado.php');
        exit();
    }
    if (in_array('Conserje',$_SESSION['ROLES'])){
        //Espacio para iniciar si no se descarta el rol
    }else {
        //Si no tiene el rol Conserje vuelve a login para que le redirija
        header('Location: login.php');
            exit();
    }
    
} else {
    //Si no hay sesión manda a login
    header('Location: login.php');
        exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control horario facial</title>
    <link rel="stylesheet" href="../css/registro.css">
    <script defer src="../js/face-api.min.js"></script>
    <script defer src="../js/registro.js"></script>
</head>
<body>
    <div class="container">
        <!--Logo de la aplicación-->
        <div id="logo-container">
            <img src="../recursos/logo.png" alt="Logo de la Empresa" id="logo">
        </div>
        <h1>Bienvenido</h1>
        <!--Reloj-->
        <p id="clock">Cargando hora...</p> <!-- Aquí se mostrará la hora -->
        <p>Pulse en iniciar reconocimiento y mire a la cámara para registrar su entrada o salida</p>
        <div class="camera-container">
            <!-- Aquí se mostraría la vista de la cámara -->
            <video id="video" width="320" height="240" autoplay muted></video>
        </div>
        <!--Botón para iniciar reconocimiento-->
        <div id="botonera">
            <button class="btnAzul" id="startRecognition">Iniciar Reconocimiento Facial</button>
        </div>
        <!--Necesario para PHP-->
        <input type="file" id="fileInput" style="display: none;"/>
        <!--Contenedor para el estado de la página-->
        <div id="status"></div>
    </div>
    <script>// Espera a que todos los scripts con defer estén listos
    document.addEventListener('DOMContentLoaded', function() {
        // Verifica si la función existe
        if (typeof cargar === 'function') {
            cargar();
        } else {
            console.error('La función cargar no está definida');
        }
    });</script>
</body>
</html>