<?php

//Rutas por S.O
$so = PHP_OS;
if (stripos($so, 'WIN') !== false) {
    $rutaBase = 'c:/xampp/fotos/';
} else {
    $rutaBase = '/var/www/fotos/';
}

// Verifica si el parámetro "perfil" está presente y tiene el valor "perfil"
if (isset($_GET['perfil']) && $_GET['perfil'] === 'perfil') {
    //Esta es la ruta a añadir para acceder a las fotos de los empleados
    $rutaBase .= 'BBDD/';
}
//Obtenemos los datos del archivo mediante GET
$archivo = isset($_GET['archivo']) ? basename($_GET['archivo']) : '';
$base64 = isset($_GET['base64']) ? true : false;
//Completamos la ruta
$rutaCompleta = $rutaBase . $archivo;

//Si existe devolvemos la imagen 
if (file_exists($rutaCompleta)) {
    if ($base64){
        $tipoImagen = mime_content_type($rutaCompleta);
        // Leer la imagen y convertirla en base64
        $datosImagen = base64_encode(file_get_contents($rutaCompleta));
        echo "data:$tipoImagen;base64,$datosImagen";
    }else{
        header('Content-Type: image/jpeg');
        readfile($rutaCompleta);
    }
} else {
    //Si no existe devolvemos un 404
    http_response_code(404);
    echo 'Imagen no encontrada.';
}
?>