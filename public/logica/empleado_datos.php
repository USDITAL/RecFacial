<?php
session_start();
date_default_timezone_set('Europe/Madrid');

// Carga librerías de Composer
require($_SERVER['DOCUMENT_ROOT'] . '/Proyecto-DAW/vendor/autoload.php');

// Clases a usar
use Clases\Incidencia;
use Clases\Empleado;
use Clases\Marcaje;


// Inicializa variables
$nombreCompleto = '';
$fotoEmpleado = '';
$ultimosMarcajes = [];
$horasTrabajadas = 0;
$labels = [];
$valores = [];
$admin = false;

// Verifica si hay una sesión activa
if (isset($_SESSION['COD_USUARIO'])) {
    if (in_array('Empleado', $_SESSION['ROLES'])) {
        // Obtiene el código del empleado desde la sesión
        $codEmpleado = $_SESSION['COD_EMPLEADO'];

        // Crea una instancia de la clase Empleado
        $empleado = new Empleado();
        if ($empleado->cargarDatosEmpleado($codEmpleado)) {
            // Crea una instancia de la clase Marcaje
            $marcaje = new Marcaje();
            
            //<<<<<<<<<<<<<<<<<<      Datos de empleado    >>>>>>>>>>>>>>>>>>

            // Obtiene los datos del empleado
            $nombreCompleto = $empleado->getNombre() . ' ' . $empleado->getApellido1() . ' ' . $empleado->getApellido2();
            $fotoEmpleado = $empleado->getFoto();

            //<<<<<<<<<<<<<<<<<<<         Horas          >>>>>>>>>>>>>>>>>>>>>>>>>

            //Obtiene horas máximas del empleado
            $maxHoras = $empleado->getMaxHorasDia();
            $horario = $empleado->getHorario();
            
            // Obtiene las horas trabajadas hoy
            $fechaHoy = new DateTime('now', new DateTimeZone('UTC'));
            $fechaHoy->setTimezone(new DateTimeZone('Europe/Madrid'));
            $horasTrabajadas = $marcaje->calcularHorasTrabajadas($codEmpleado, $fechaHoy,0,89);
            $horasSemanales = $marcaje->calcularHorasSemana($codEmpleado,$fechaHoy,0,89);
            $progresoHorario=($horasTrabajadas*100)/$empleado->getMaxHorasDia();
            $horasMes = $marcaje->calcularHorasMensual($codEmpleado,$fechaHoy);
            $mesPasado = $fechaHoy->modify('first day of previous month');
            $horasMesPasado = $marcaje->calcularHorasMensual($codEmpleado,$mesPasado);
            

            //Calcula la bolsa de horas del empleado a partir de los marcajes del mes indicado
            $marcaje->calcularBolsaMensual($codEmpleado,$fechaHoy);
            $bolsa = $empleado->getBolsa();
            
            //<<<<<<<<<<<<<<<<<<<<<<<       Incidencias     >>>>>>>>>>>>>>>>>>>>>>>>
            $incidencias = new Incidencia();
            $incidenciasPendientes = $incidencias->cargarPendientes($codEmpleado);
            $incidenciasResueltas = $incidencias->cargarResueltas($codEmpleado);
            //<<<<<<<<<<<<<<<<<<<<<<<       Marcajes        >>>>>>>>>>>>>>>>>>>>>>>>>
            //Datos descriptivos de los tipos de marcaje
            $tiposAcceso= $marcaje->listaTiposAcceso();

            // Obtiene los últimos 5 marcajes
            $ultimosMarcajes = array_filter(
                $marcaje->obtenerUltimosMarcajes($codEmpleado, 5),
                function ($registro) {
                    return $registro['COD_TIPO_ACCESO'] < 90;
                }
            );
            // Determina las fechas según el filtro seleccionado para registros y gráfica
            //Lee el filtro enviado por POST
            $filtro = $_POST['filter-mode'] ?? 'week';
            //Inicia datos
            $fechaInicio = null;
            $fechaFin = new DateTime('now', new DateTimeZone('Europe/Madrid'));
            //Switch Case para los posibles valores del combo
            switch ($filtro) {
                case 'week':
                    $fechaInicio = (clone $fechaFin)->modify('this week monday');
                    break;
                case 'lastweek':
                    $fechaInicio = (clone $fechaFin)->modify('last week monday');
                    $fechaFin = $fechaFin->modify('last week sunday');
                    break;
                case 'month':
                    $fechaInicio = (clone $fechaFin)->modify('first day of this month');
                    break;
                case 'lastmonth':
                        $fechaInicio = (clone $fechaFin)->modify('first day of last month');
                        $fechaFin = $fechaFin->modify('last day of last month');
                        break;
                case 'year':
                    $fechaInicio = (clone $fechaFin)->modify('first day of January');
                    break;
                case 'lastyear':
                    $fechaInicio = (clone $fechaFin)->modify('first day of January last year');
                    $fechaFin = $fechaFin->modify('last day of December last year');
                    break;
                case 'range':
                    $fechaInicio = isset($_POST['start-date']) ? new DateTime($_POST['start-date'], new DateTimeZone('Europe/Madrid')) : null;
                    $fechaFin = isset($_POST['end-date']) ? new DateTime($_POST['end-date'], new DateTimeZone('Europe/Madrid')) : null;
                break;
            }
            //Control de errores
            if (!$fechaInicio || !$fechaFin) {
                die('Fechas no válidas.');
            }
            
            // Carga los marcajes entre las fechas seleccionadas
            $datosMarcajes = array_filter(
                $marcaje->cargarMarcajesEntreFechas($codEmpleado, $codEmpleado, $fechaInicio, $fechaFin),
                function ($registro){
                    return $registro['COD_TIPO_ACCESO']<100;
                }
            );

            // Procesa los datos para la gráfica
            $horasPorDia = [];
            //Crea un periodo de fechas para recorrerlas
            $periodo = new DatePeriod($fechaInicio, new DateInterval('P1D'), $fechaFin->modify('+1 day'));
            //Bucle sobre el periodo
            foreach ($periodo as $fecha) {
                $horasTrabajadasGrafica = $marcaje->calcularHorasTrabajadas($codEmpleado, $fecha,0,89);
                $horasAusenciasGrafica = $marcaje->calcularHorasTrabajadas($codEmpleado, $fecha,90,99);
                $fechaFormateada = $fecha->format('Y-m-d');
                $horasPorDia[$fechaFormateada] = $horasTrabajadasGrafica;
                $horasPorDiaAusencia[$fechaFormateada] = $horasAusenciasGrafica;
            }
            //Ordena los datos
            ksort($horasPorDia);
            //Divide los datos en 2 arrays(para etiquetas y para valores)
            $labels = array_keys($horasPorDia);
            $valores = array_values($horasPorDia);
            $ausencias = array_values($horasPorDiaAusencia);

            //Agrupa los registros detallados
            $registrosAgrupados = [];
            foreach ($datosMarcajes as $registro) { 
                $fecha = (new DateTime($registro['FEC_MARCAJE']))->format('Y-m-d');
             $registrosAgrupados[$fecha][] = $registro;
            }

            // Procesa los registros agrupados para emparejar entradas y salidas
            $registrosDetallados = [];
            foreach ($registrosAgrupados as $fecha => $registros) {
                $entradas = array_filter($registros, fn($r) => $r['COD_TIPO_MARCAJE'] == 1); // Entradas
                $salidas = array_filter($registros, fn($r) => $r['COD_TIPO_MARCAJE'] == 2); // Salidas

                // Empareja entradas y salidas
                $pares = [];
                while ($entrada = array_shift($entradas)) {
                    $salida = array_shift($salidas); // Toma la primera salida disponible
                    //Formato y parejas
                    $pares[] = [
                        'fecha' => $fecha,
                        'tipoAccesoEntrada' => $tiposAcceso[$entrada['COD_TIPO_ACCESO']] ?? 'Desconocido',
                        'horaEntrada' => (new DateTime($entrada['FEC_MARCAJE']))->format('H:i:s'),
                        'tipoAccesoSalida' => $salida ? ($tiposAcceso[$salida['COD_TIPO_ACCESO']] ?? 'Desconocido') : '',
                        'horaSalida' => $salida ? (new DateTime($salida['FEC_MARCAJE']))->format('H:i:s') : '',
                        'incidencia' => $entrada['DES_OBSERVACIONES'] ?? '',
                        'estado' => $entrada['IND_PENDIENTE'] == 1 ? 'Pendiente' : ''
                    ];
                }

                // Agrega los pares procesados al resultado final
                $registrosDetallados = array_merge($registrosDetallados, $pares);

                
            }
        }
    } else {
        header('Location: login.php');
        exit();
    }

    if (in_array('Admin', $_SESSION['ROLES'])) {
        $admin = true;
    }

    if (in_array('Conserje', $_SESSION['ROLES'])) {
        header('Location: wellcome.php');
        exit();
    }
} else {
    header('Location: login.php');
    exit();
}

function solicitarRevision(int $cempleado, string $fechaSol, string $obs, int $prioridad){
    $incidencia = new Incidencia();
    $incidencia->setEmpleado($cempleado);
    $incidencia->setComentario($obs);
    $incidencia->setPrioridad($prioridad);
    $incidencia->setFecha_inc(new DateTime($fechaSol));
    $incidencia->setFecha_rev(new DateTime());
    $incidencia->grabar();
};

if (isset($_POST['registro_id'])) {
    solicitarRevision($codEmpleado,$_POST['comentario-fecha'],$_POST['comentario'],intval($_POST['prioridad']));
    unset($_POST);
    $incidenciasPendientes = $incidencias->cargarPendientes($codEmpleado);
    $incidenciasResueltas = $incidencias->cargarResueltas($codEmpleado);
    header('Location: '.$_SERVER['PHP_SELF']);
    exit();
}
