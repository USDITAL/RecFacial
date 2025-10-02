<?php
header('Content-Type: application/json');
//Eliminar en producción <<<<<<<<<<< PRODUCCION  >>>>>>>>>>>>>>>>>
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Incluir las clases de Composer
require '../../vendor/autoload.php';
//Clase DatosBiometricos
use Clases\DatosBiometricos;

// Crear una instancia de la clase DatosBio
$bio = new DatosBiometricos();


// Lista de direcciones IP permitidas
$ip_permitidas = ['127.0.0.1', '::1']; // Localhost

// Verificar si la IP del cliente está en la lista de permitidas
/*if (!in_array($_SERVER['REMOTE_ADDR'], $ip_permitidas)) {
    http_response_code(403); // Código de estado 403: Prohibido
    echo json_encode(['message' => 'Acceso no autorizado.']);
    exit;
}*/


// Ruta al archivo que contiene la clave de encriptación
$so = PHP_OS;
if (stripos($so, 'WIN') !== false) {
    $ruta_clave = 'c:/xampp/clave.txt';
} else {
    $ruta_clave = '/var/www/clave.txt';
}

// Leer la clave de encriptación desde el archivo
if (!file_exists($ruta_clave)) {
    //Mensaje de error
    echo json_encode(['message' => 'Error: No se encontró el archivo de la clave de encriptación.']);
    exit;
} else {
    $clave = trim(file_get_contents($ruta_clave)); // Leer y eliminar espacios en blanco
}

// Verificar si se recibieron los datos necesarios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Se carga la entrada POST y se sacan los datos
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Comprobar si falta alguno de los campos
    if (!isset($data['nombre']) || !isset($data['descriptor'])) {
        // Mensaje de error si falta el nombre o el descriptor
        $error = [];
        //añadimos errores si los hay
        if (!isset($data['nombre'])) {
            $error[] = 'Falta el campo "nombre".';
        }
        if (!isset($data['descriptor'])) {
            $error[] = 'Falta el campo "descriptor".';
        }
        //Devolvemos los errores
        echo json_encode(['message' => 'Error: ' . implode(' ', $error)]);
        exit;
    }

    // Obtener el nombre y el descriptor de los datos obtenidos y el empleado y usuario
    $nombre = $data['nombre'];
    $usuarioData = $data['usuario'];
    $empleado=$data['empleado'];
    $descriptor = $data['descriptor'];

    // Verificar que el descriptor tenga 128 valores
    if (count($descriptor) !== 128) {
        echo json_encode(['message' => 'Error: El descriptor debe tener 128 valores.']);
        exit;
    }
    // Convertir el array a JSON solo del nombre y el descriptor
    $datos_descriptor = [
        'nombre'=>$data['nombre'],
        'descriptor'=>$data['descriptor']
    ];
    $json_data = json_encode($datos_descriptor);

    // Encriptar los datos
    $metodo = 'AES-256-CBC'; // Método de encriptación
    $iv_length = openssl_cipher_iv_length($metodo);
    $iv = openssl_random_pseudo_bytes($iv_length); // Vector de inicialización
    $datos_encriptados = openssl_encrypt($json_data, $metodo, $clave, 0, $iv);

    // Guardar el vector de inicialización junto con los datos encriptados
    $datos_guardar = base64_encode($iv . $datos_encriptados);

    // Insertar los datos en la base de datos
    try {
        
        // Asignar valores a los parámetros
        $cod_tipo_bio = 1; // COD_TIPO_BIO siempre será 1, ya que este método es para rostros
        $dato_bio = $datos_guardar; // Datos encriptados
        $cod_empleado = $empleado; //Empleado
        $fecha=new DateTime(); //Fecha actual
        $usuario=$usuarioData; //Usuario de Alta
        //Grabamos los parámetros del objeto $bio
        $bio->setCodBio(0);//Indicamos con 0 que es nuevo. Debería de cargar igualmente 0 en el constructor
        $bio->setCodTipo($cod_tipo_bio);
        $bio->setDatoBio($dato_bio);
        $bio->setCodEmpleado($cod_empleado);
        $bio->setFecAlta($fecha);
        $bio->setNomUsuarioAlta($usuario);
        //Grabamos el objeto bio
        $bio->grabar();
        
        // Mensaje de éxito
        echo json_encode(['message' => 'Descriptor guardado correctamente en la base de datos.']);
    } catch (Exception $e) {
        // Manejar errores de la base de datos
        echo json_encode(['message' => 'Error al guardar el descriptor en la base de datos: ' . $e->getMessage()]);
    }
}
?>
