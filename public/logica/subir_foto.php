<?php
require '../../vendor/autoload.php';

use Clases\Empleado;
//Rutas por S.O
$so = PHP_OS;
if (stripos($so, 'WIN') !== false) {
    $rutaBase = 'c:/xampp/fotos/BBDD/';
} else {
    $rutaBase = '/var/www/fotos/BBDD/';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['imagen'])) {
        $empleado = new Empleado();
        $empleado->cargarDatosEmpleado($_POST['cod_empleado']);
        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $cod_empleado= str_pad($empleado->getCodEmpleado(), 4, '0', STR_PAD_LEFT);
        $n= substr($empleado->getNombre(), 0, 2);
        $a1= substr($empleado->getApellido1(), 0, 2);
        $a2= substr($empleado->getApellido2(), 0, 2);
        $nuevoNombre = "emp_".$cod_empleado."_".$n."_".$a1."_".$a2."." . $extension;
        $targetFile = $rutaBase . $nuevoNombre;
        // Mover el archivo subido al directorio destino
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $targetFile)) {
            $empleado->setFoto($nuevoNombre);
            $empleado->grabar();
        } else {
            error_log("Lo siento, hubo un error subiendo tu archivo.");
        }
    } else {
        error_log("No se recibió ninguna imagen.");
    }
} else {
    error_log("Método no permitido.");
}
?>