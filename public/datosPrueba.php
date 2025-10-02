<?php
session_start();
date_default_timezone_set('Europe/Madrid');

require '../vendor/autoload.php';

use Clases\Conexion;
use Clases\Empleado;
use Clases\Marcaje;
use Clases\DatosBiometricos;



// Función para generar una hora aleatoria dentro de un rango
function generarHoraAleatoria($horaMin, $minMin, $horaMax, $minMax) {
    $minutosTotalesMin = $horaMin * 60 + $minMin;
    $minutosTotalesMax = $horaMax * 60 + $minMax;
    $minutosAleatorios = rand($minutosTotalesMin, $minutosTotalesMax);
    
    $hora = floor($minutosAleatorios / 60);
    $minutos = $minutosAleatorios % 60;
    
    return sprintf("%02d:%02d:00", $hora, $minutos);
}

// Función para generar marcajes para un empleado en un día específico
function generarMarcajesEmpleado($empleado, $fecha) {
    $fotosEmpleado=['empleado_1_1742819095.jpg',
'empleado_1_1742819161.jpg',
'empleado_1_1742829918.jpg',
'empleado_1_1742830016.jpg',
'empleado_1_1742832595.jpg',
'empleado_1_1742837544.jpg',
'empleado_1_1742848138.jpg',
'empleado_1_1742848306.jpg',
'empleado_1_1742848317.jpg',
'empleado_1_1742889590.jpg',
'empleado_1_1742891899.jpg',
'empleado_1_1742900460.jpg',
'empleado_1_1742916423.jpg',
'empleado_1_1742919496.jpg',
'empleado_1_1742981114.jpg',
'empleado_1_1742981762.jpg',
'empleado_1_1742982244.jpg',
'empleado_1_1742997438.jpg',
'empleado_1_1742997614.jpg',
'empleado_1_1742997741.jpg',
'empleado_1_1742998169.jpg',
'empleado_1_1743011885.jpg',
'empleado_1_1743060817.jpg',
'empleado_1_1743080252.jpg',
'empleado_1_1743146363.jpg',
'empleado_1_1743148134.jpg',
'empleado_1_1743148139.jpg',
'empleado_1_1743273358.jpg',
'empleado_1_1743273473.jpg',
'empleado_1_1743274535.jpg',
'empleado_1_1743402990.jpg',
'empleado_1_1743431511.jpg',
'empleado_1_1743488616.jpg',
'empleado_1_1743577305.jpg',
'empleado_1_1743577374.jpg',
'empleado_1_1743660474.jpg',
'empleado_1_1743680355.jpg',
'empleado_1_1743680780.jpg',
'empleado_1_1746994071.jpg',
'empleado_1_1746994082.jpg',
'empleado_1_1747047401.jpg',
'empleado_1_1747071089.jpg'];
$fotosEmpleado9=['empleado_9_1747223453.jpg',
'empleado_9_1747223460.jpg'];

    // Obtener los códigos biométricos del empleado
    $datosBio = new DatosBiometricos();
    $biometricos = $datosBio->biosPorEmpleado($empleado->getCodEmpleado());
    
    if (empty($biometricos)) {
        echo "Empleado {$empleado->getCodEmpleado()} no tiene códigos biométricos asignados.\n";
        return;
    }
    
    // Seleccionar un código biométrico aleatorio
    $codBio = $biometricos[array_rand($biometricos)]['COD_BIO'];
    
    // Generar hora de entrada (07:55 - 08:10)
    $horaEntrada = generarHoraAleatoria(7, 55, 8, 10);
    $fechaEntrada = clone $fecha;
    list($h, $m, $s) = explode(':', $horaEntrada);
    $fechaEntrada->setTime($h, $m, $s);
    
    // Generar hora de salida (15:52 - 16:15)
    $horaSalida = generarHoraAleatoria(15, 52, 16, 15);
    $fechaSalida = clone $fecha;
    list($h, $m, $s) = explode(':', $horaSalida);
    $fechaSalida->setTime($h, $m, $s);
    if ($empleado->getCodEmpleado()==1) {
        $fotos = $fotosEmpleado[array_rand($fotosEmpleado)];
    } else if ($empleado->getCodEmpleado()==9) {
        $fotos = $fotosEmpleado9[array_rand($fotosEmpleado9)];
    } else{
        $fotos='empleado_'.$empleado->getCodEmpleado().'_foto.jpg';
    };
    // Crear marcaje de entrada
    $marcajeEntrada = new Marcaje();
    $marcajeEntrada->setCodTipoMarcaje(1); // Tipo 1 = Entrada
    $marcajeEntrada->setCodEmpleado($empleado->getCodEmpleado());
    $marcajeEntrada->setCodBio($codBio);
    $marcajeEntrada->setFecMarcaje($fechaEntrada);
    $marcajeEntrada->setFecGrabacion(new DateTime());
    $marcajeEntrada->setIncidencia(false);
    $marcajeEntrada->setPendiente(false);
    $marcajeEntrada->setFoto($fotos);
    $marcajeEntrada->setTipoAcceso(1);
    $marcajeEntrada->setObs('');
    $marcajeEntrada->grabar();
    
    // Crear marcaje de salida
    $marcajeSalida = new Marcaje();
    $marcajeSalida->setCodTipoMarcaje(2); // Tipo 2 = Salida
    $marcajeSalida->setCodEmpleado($empleado->getCodEmpleado());
    $marcajeSalida->setCodBio($codBio);
    $marcajeSalida->setFecMarcaje($fechaSalida);
    $marcajeSalida->setFecGrabacion(new DateTime());
    $marcajeSalida->setIncidencia(false);
    $marcajeSalida->setPendiente(false);
    $marcajeSalida->setFoto($fotos);
    $marcajeSalida->setTipoAcceso(1);
    $marcajeSalida->setObs('');
    $marcajeSalida->grabar();
    
    echo "Generados marcajes para {$empleado->getNombre()} {$empleado->getApellido1()} el {$fecha->format('Y-m-d')}: Entrada {$horaEntrada}, Salida {$horaSalida}\n";
}

// Obtener todos los empleados
$empleado = new Empleado();
$empleados = $empleado->listarEmpleados();

// Fechas de inicio y fin
$fechaInicio = new DateTime('2025-02-14');
$fechaFin = new DateTime('2025-05-14');

// Generar marcajes para cada día laboral (lunes a viernes)
$intervalo = new DateInterval('P1D');
$periodo = new DatePeriod($fechaInicio, $intervalo, $fechaFin->modify('+1 day'));

foreach ($periodo as $fecha) {
    $diaSemana = $fecha->format('N'); // 1 (lunes) - 7 (domingo)
    
    if ($diaSemana >= 1 && $diaSemana <= 5) { // Solo días laborales (lunes a viernes)
        echo "Procesando día: {$fecha->format('Y-m-d')}...\n";
        
        foreach ($empleados as $emp) {
            $empleadoActual = new Empleado();
            $empleadoActual->cargarDatosEmpleado($emp['COD_EMPLEADO']);
            generarMarcajesEmpleado($empleadoActual, $fecha);
        }
    }
}

echo "Proceso completado.\n";
?>