<?php
session_start();
//Cargamos librerías de Composer
require '../vendor/autoload.php';

// Si ya hay una sesión activa, redirigir al dashboard
if (isset($_SESSION['COD_USUARIO'])) {
   //Si es empleado redirije a empleado.php
    if (in_array('Empleado',$_SESSION['ROLES'])){
        header('Location: empleado.php');
        exit();
    }
    //Si es conserje redirije a wellcome.php
    if (in_array('Conserje',$_SESSION['ROLES'])){
        header('Location: wellcome.php');
        exit();
    }
    
}

//Clases a usar
use Clases\Usuario;
use Clases\Rol;
use Clases\Transaccion;
//Inicializar datos necesarios
$error="";

// Si se recibe el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Volcamos el usuario y la contraseña enviadas
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    // Crear un objeto de la clase Usuario e iniciamos sesión
    $usuarioObj = new Usuario();
    if ($usuarioObj->iniciarSesion($usuario, $contrasena)) {
        header('Location: login.php'); // Redirigir a login.php si el login es correcto para leer $_SESSION
        exit();
    } else {
        $error = 'Usuario o contraseña incorrectos'; // Mostrar error si las credenciales son incorrectas
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
  <link rel="stylesheet" href="../css/login.css">

  <div id="welcome-message">
                <div class="logo-container">
                    <img src="../recursos/logo.png" alt="Logo" class="logo">
                </div>
            </div>
</head>
<body>
    <h1>Iniciar Sesión</h1>
    <form method="POST" action="">
        <label for="usuario">Usuario:</label>
        <input type="text" id="usuario" name="usuario" required><br><br>

        <label for="contrasena">Contraseña:</label>
        <input type="password" id="contrasena" name="contrasena" required><br><br>

        <!-- Espacio reservado para mostrar errores -->
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <button type="submit">Entrar</button>
    </form>
</body>
</html>


