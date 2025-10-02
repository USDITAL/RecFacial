<?php
// listar_descriptores.php

header('Content-Type: application/json');

// Incluir las clases necesarias
require '../../vendor/autoload.php';

//use Clases\Conexion;
use Clases\DatosBiometricos;

// Lista de direcciones IP permitidas
$ip_permitidas = ['127.0.0.1', '::1']; // Localhost

// Verificar si la IP del cliente está en la lista de permitidas
if (!in_array($_SERVER['REMOTE_ADDR'], $ip_permitidas)) {
    http_response_code(403); // Código de estado 403: Prohibido
    echo json_encode(['message' => 'Acceso no autorizado.']);
    exit;
}

// Ruta al archivo de la clave
$so = PHP_OS;
$ruta_clave = stripos($so, 'WIN') !== false ? 'c:/xampp/clave.txt' : '/var/www/clave.txt';

// Obtener los registros de la base de datos usando la clase DatosBiometricos
$descriptores = [];
$listaDatos = DatosBiometricos::listarPorTipo(1); // Filtrar por COD_TIPO_BIO = 1

foreach ($listaDatos as $datos) {
    // Desencriptar los datos
    $datos_desencriptados = desencriptarDatos($datos->getDatoBio(), $ruta_clave);

    // Control de errores
    if ($datos_desencriptados !== false) {
        $data = json_decode($datos_desencriptados, true);
    } else {
        error_log("Error al desencriptar los datos para COD_BIO: " . $datos->getCodBio());
        continue;
    }

    // Verificar que los datos tengan el formato correcto
    if (isset($data['nombre']) && isset($data['descriptor'])) {
        $descriptores[] = [
            'cod_tipo' => $datos->getCodTipo(),
            'cod_bio' => $datos->getCodBio(),
            'cod_empleado' => $datos->getCodEmpleado(),
            'nombre' => $data['nombre'],
            'descriptor' => $data['descriptor']
        ];
    }
}

// Devolver la lista de descriptores
echo json_encode($descriptores);

// Función para desencriptar los datos
function desencriptarDatos($datos_encriptados, $ruta_clave) {
    if (!file_exists($ruta_clave)) {
        return false; // No se encontró el archivo de la clave
    }

    $clave = trim(file_get_contents($ruta_clave)); // Leer la clave
    $datos_encriptados = base64_decode($datos_encriptados);
    $metodo = 'AES-256-CBC';
    $iv_length = openssl_cipher_iv_length($metodo);
    $iv = substr($datos_encriptados, 0, $iv_length);
    $datos_encriptados = substr($datos_encriptados, $iv_length);
    return openssl_decrypt($datos_encriptados, $metodo, $clave, 0, $iv);
}