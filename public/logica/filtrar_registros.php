<?php
session_start();

// Verificar si la sesión es válida y tiene el rol requerido
/*if (empty($_SESSION['COD_USUARIO']) && in_array('Empleado', $_SESSION['ROLES'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'error' => 'Acceso no autorizado']);
    exit;
}*/
header('Content-Type: application/json');
require '../../vendor/autoload.php';

use Clases\Marcaje;
use Clases\Empleado;
use Clases\Usuario;
//require './empleado_datos.php';
try {
    $datos = json_decode(file_get_contents('php://input'), true);
    
    // Validar datos recibidos
    if (empty($datos['accion'])) {
        throw new Exception('Acción no válida');
    }else{
        if ($datos['accion']=='cargar_grafica' || $datos['accion']=='filtrar_datos'){
        $codEmpleado = $datos['empleado'];
        $empleado = new Empleado();
        if ($empleado->cargarDatosEmpleado($codEmpleado)) {
            $marcaje = new Marcaje();
            $filtro = $datos['filtro']??'week';
            $fechaInicio = null;
            $fechaFin = new DateTime('now', new DateTimeZone('Europe/Madrid'));
            $tiposAcceso= $marcaje->listaTiposAcceso();    
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
                    $fechaInicio = isset($datos['desdeFecha']) ? new DateTime($datos['desdeFecha'], new DateTimeZone('Europe/Madrid')) : null;
                    $fechaFin = isset($datos['hastaFecha']) ? (new DateTime($datos['hastaFecha'], new DateTimeZone('Europe/Madrid')))->setTime(23, 59, 59) : null;
                    break;
            }
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
            if ($datos['accion']=='cargar_grafica'){
            
                $respuesta = [
                    'success' => true,
                    
                        'labels' => $labels,
                        'valores' => $valores,
                        'ausencias' => $ausencias,
                        'average' => array_sum($valores) / count($valores),
                        'registrosDetallados' => $registrosDetallados,
                        'maxHoras' => $empleado->getMaxHorasDia(),
                        'datosMarcajes' => $datosMarcajes
                    
                ];
                
                // Establece las cabeceras y devuelve JSON
                header('Content-Type: application/json');
                echo json_encode($respuesta);
                exit;
            }
            if ($datos['accion']=='filtrar_datos'){
            
                $html='    <ul>
                                    <li class="registro-header">
                                        <span class="col-fecha">Fecha</span>
                                        <span class="col-tipo">Tipo</span>
                                        <span class="col-fecha">Entrada</span>
                                        <span class="col-tipo">Tipo</span>
                                        <span class="col-fecha">Salida</span>
                                        <span class="col-incidencia">Incidencia</span>
                                        <span class="col-estado">Estado</span>
                                    </li>';

                                    foreach ($registrosDetallados as $index=>$registro){
                                    $html=$html.'<li class="linea_trans" data-id="'.$index.'" 
                                        data-fecha="'.$registro['fecha'].'">
                                        <span class="fecha">'.$registro['fecha'].'</span>
                                        <span class="metodo">'.$registro['tipoAccesoEntrada'].'</span>
                                        <span class="hora">'.$registro['horaEntrada'].'</span>
                                        <span class="metodo">'.$registro['tipoAccesoSalida'].'</span>
                                        <span class="hora">'.$registro['horaSalida'].'</span>
                                        <span class="incidencia">'.$registro['incidencia'].'</span>
                                        <span class="estado">'.$registro['estado'].'</span>
                                    </li>';
                                    }
                                $html=$html.'</ul>';
                $response = [
                    'html' => $html,
                    'registros' => $registrosDetallados
                ];

                header('Content-Type: application/json');
                echo json_encode($response);
                exit;   
            }
        

        }

        if ($datos['accion']=='cambiarPass'){
            $pass=$datos['valor'];
            $oldPass = $datos['valorViejo'];
            $usuario = $_SESSION['COD_USUARIO'];
            try{
                $usu = new Usuario();
                $usu->cargarUsuario($usuario);
                if ($usu->compararContrasena($oldPass,$usu->getDesContrasena())){
                    $usu->setDesContrasena($pass);
                    $usu->grabar();
                    echo json_encode(['success' => true,
                    'mensaje'=>'Contraseña cambiada correctamente.']);
                    
                }else {
                    echo json_encode(['success' => false,
                'error'=>'Datos incorrectos']);
                }
            }catch(Exception $e){
                echo json_encode(['success' => false,
            'error'=>'Error con la BBDD']);
            }

        }

        if ($datos['accion']=='cerrarSesion'){
            session_unset(); // Elimina todas las variables de sesión
            session_destroy(); // Destruye la sesión
            echo json_encode(['success' => true, 'message' => 'Sesión cerrada correctamente.']);
        }

        
    }
} catch (Exception $e) {
}