<?php
//Iniciamos la sesión para poder comprobar si hay datos
session_start();

// Verifica si existen datos de sesión (Debería de estar los datos de usuario y rol)
if (!isset($_SESSION['COD_USUARIO'])) {
    // Redirigir a la página de login si no hay datos de sesión
    header('Location: public/login.php');
    exit();
}

// Verifica si existe la variable 'rol' en la sesión
if (!isset($_SESSION['ROLES']) || !is_array($_SESSION['ROLES'])) {
    // Redirige a la página de login si no existe la variable 'rol' o no es un array
    header('Location: public/login.php');
    exit();
}

// Verifica si la variable 'rol' contiene el texto 'conserje' o 'empleado'
if (in_array('Conserje', $_SESSION['ROLES'])) {
    // Redirige a la página de bienvenida si el rol es 'conserje'
    header('Location: public/wellcome.php');
    exit();
} elseif (in_array('Empleado', $_SESSION['ROLES'])) {
    // Redirige a la página de empleado si el rol es 'empleado'
    header('Location: public/empleado.php');
    exit();
} else {
    // Redirige a la página de login si el rol no es 'conserje' ni 'empleado'
    header('Location: public/login.php');
    exit();
}

// Si se llega a este punto, algo salió mal, redirige a la página de login
header('Location: public/login.php');
exit();
?>