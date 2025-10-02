<?php
// Incluir librerías de Composer
require '../../vendor/autoload.php';
//Uso de la clase Marcaje
use Clases\Marcaje;

// Lista de direcciones IP permitidas
$ip_permitidas = ['127.0.0.1', '::1']; // Localhost

// Verificar si la IP del cliente está en la lista de permitidas
if (!in_array($_SERVER['REMOTE_ADDR'], $ip_permitidas)) {
    http_response_code(403); // Código de estado 403: Prohibido
    echo json_encode(['message' => 'Acceso no autorizado.']);
    exit;
}
//Rutas por S.O
$so = PHP_OS;
if (stripos($so, 'WIN') !== false) {
    $ruta_foto = 'c:/xampp/fotos/';
} else {
    $ruta_foto = '/var/www/fotos/';
}

//Obtengo los datos del POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Obtiene datos y decodifica de json al array $data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    // Validar que los datos se hayan recibido correctamente
    if (isset($data['empleado']) && isset($data['bio'])) {
        //Si es así vuelca los datos a las variables
        $cod_Empleado = intval($data['empleado']);
        $cod_Bio = intval($data['bio']);
        $foto = $data['foto'];
        $tipo_acceso = intval($data['tipo_acceso']);
        $fec_Marcaje = $data['fec_marcaje'];
        $incidencia = intval($data['incidencia']);
        $pendiente = intval($data['pendiente']);
        $obs = $data['obs'];

        // Convertir la fecha de marcaje (fec_marcaje) de UTC a GMT+1
        $fec_MarcajeUTC = new DateTime($fec_Marcaje, new DateTimeZone('UTC')); // Fecha en UTC
        $fec_MarcajeLocal = $fec_MarcajeUTC->setTimezone(new DateTimeZone('Europe/Madrid')); // Convertir a GMT+1
        $fec_Marcaje = $fec_MarcajeLocal->format('Y-m-d H:i:s'); // Formatear para la base de datos


         // Decodificar la imagen Base64
         $fotoData = explode(',', $foto);
         $fotoBinaria = base64_decode(end($fotoData));

         // Crear un nombre único para la imagen
        $nombreArchivo = 'empleado_' . $cod_Empleado . '_' . time() . '.jpg';

        // Guardar la imagen en la carpeta fotos
       if (!file_exists($ruta_foto)) {
            mkdir($ruta_foto, 0777, true); // Crear la carpeta si no existe
        }
        //define la ruta completa y crea el fichero con la foto
        $rutaArchivo = $ruta_foto . $nombreArchivo;
        file_put_contents($rutaArchivo, $fotoBinaria);
        //Responde con mensaje de confirmación        
        //echo json_encode(['message' => 'Datos recibidos correctamente.']);
    } else {
        //Responde con mensaje de error
        echo json_encode(['message' => 'Faltan datos en la solicitud.']);
    }
}else{
    //Mensajes de error
    http_response_code(403); // Código de estado 403: Prohibido
    echo json_encode(['message' => 'Empleado no indicado.']);
    exit;
}

// Crear una instancia de la clase Marcaje
$marca = new Marcaje();
//Cargamos el último marcaje realizado
//añadir aquí más controles horarios <<<<<<<<<<<Controles horarios>>>>>>>>>>>>>>
$tipo_marca = $marca->ultimoMarcaje($cod_Empleado);
//Si el último marcaje era de Entrada, ahora graba salida y al revés.
if ($tipo_marca==1){
    $tipo_marca=2;
} else {
    $tipo_marca=1;
}
//Marco la fecha actual como fecha de grabación.
$fecha_Grab=new DateTime();
//Realizo el marcaje
$marca->marcar($tipo_marca,$cod_Empleado,$cod_Bio,$fec_Marcaje,$fecha_Grab->format('Y-m-d H:i:s'),$incidencia,$pendiente,$nombreArchivo,$tipo_acceso,$obs);
echo json_encode($tipo_marca);

