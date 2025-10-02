<?php
header("Cache-Control: no-cache");
date_default_timezone_set('Europe/Madrid');
//Carga la lógica de la página
require './logica/empleado_datos.php';



//Defino la fecha de hoy
$fechaDiaHoy = (new DateTime('now', new DateTimeZone('Europe/Madrid')))->format('Y-m-d');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empleado</title>
    <!-- Favicon (Logo de la pestaña del navegador) -->
    <link rel="icon" href="../recursos/logo.png" type="image/png">
    <!--Hoja de estilos-->
    <link rel="stylesheet" href="../css/empleado.css">
    <!--Scripts para la gráfica-->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation"></script>
    <!-- Contiene método para la gráfica y para cambiar los filtros-->
    <script src="../js/empleado.js"></script> 
</head>
<body>

<div id="main" class="section">
    <!--Sección fija con logo y reloj-->
        <div class="logo-container">
            <img src="../recursos/logo.png" alt="Logo" class="logo">
            <div id="current-time"></div>
    </div>
    

<div class="user-panel-container">
        <div class="navbar">
            <button class="nav-btn" id="btnPerfil" onclick="updateContent('perfil')">Mi Perfil</button>
            <button class="nav-btn" id="btnActividades" onclick="updateContent('actividades')">Mis Actividades</button>
            <button class="nav-btn" id="btnUltimos" onclick="updateContent('ultimos')">Ultimos Accesos</button>
            <button class="nav-btn" id="btnFiltrar" onclick="updateContent('filtrar')">Filtrar Registros</button>
            <button class="nav-btn" id="btnCambiarPassword" onclick="updateContent('cambiarPassword')">Cambiar contraseña</button>
            <?php if (isset($admin) && $admin): ?>
                <button class="nav-btn" id="btnAdmin" onclick="window.location.href='administracion.php'">
                    Administración
                </button>
            <?php endif; ?>
            <button class="nav-btn" id="btnLogout" onclick="logout()">Cerrar sesión</button>
        </div>
        


           
            <div id="dynamicContent" class="section">
                <div id="principal" style="display:block">
                    <h1>Bienvenido a tu portal de empleado</h1>
                </div>
                <div id="perfil" style="display:none;">
        
                    <h1 id="employee-name" data-id="<?php echo $empleado->getCodEmpleado();?>"><?php echo htmlspecialchars($nombreCompleto); ?></h1> 
                    <img id="foto_empleado" class="foto" src="./logica/mostrar_imagen.php?perfil=perfil&archivo=<?php echo htmlspecialchars($fotoEmpleado); ?>" alt="Foto del empleado">
                    <input type='file' id='fileinput' accept='image/jpeg' style='display:none;'>
                    <h4><?php echo $empleado->getContacto(); ?></h4>
                    <h4><?php echo "Horario: ".$empleado->getHorario(); ?></h4>
                    <div class="incidencias-container">
                        <div class="tabs">
                            <button class="tab-button active" onclick="openTab(event, 'pendientes')">Pendientes</button>
                            <button class="tab-button" onclick="openTab(event, 'resueltas')">Resueltas</button>
                        </div>
        

                        <div id="pendientes" class="tab-content" style="display:block;">
                            <h4>Incidencias Pendientes</h4>
                            <ul class="incidencias-list">
                                <?php foreach ($incidenciasPendientes as $incidencia): 
                                    switch($incidencia['PRIORIDAD']){
                                        case 1:
                                            $prioridad = "Baja";
                                            break;
                                        case 2:
                                            $prioridad = "Media";
                                            break;
                                        case 3:
                                            $prioridad = "Alta";
                                            break;
                                        default:
                                            $prioridad = "Baja";
                                            break;
                                    }
                                    ?>
                                    <li class="incidencia-item prioridad-<?= strtolower($prioridad) ?>">
                                        <span class="fecha"><?= htmlspecialchars($incidencia['FECHA_INC']) ?></span>
                                        <span class="descripcion"><?= htmlspecialchars($incidencia['COMENTARIO']) ?></span>
                                        <span class="prioridad"><?= htmlspecialchars($incidencia['PRIORIDAD']) ?></span>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (empty($incidenciasPendientes)): ?>
                                    <li>No hay incidencias pendientes</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        
                        <div id="resueltas" class="tab-content">
                            <h4>Incidencias Resueltas</h4>
                            <ul class="incidencias-list">
                                <?php foreach ($incidenciasResueltas as $incidencia): ?>
                                    <li class="incidencia-item resuelta">
                                        <span class="fecha"><?= htmlspecialchars($incidencia['FECHA_INC']) ?></span>
                                        <span class="descripcion"><?= htmlspecialchars($incidencia['COMENTARIO']) ?></span>
                                        <span class="prioridad"><?= htmlspecialchars($incidencia['PRIORIDAD']) ?></span>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (empty($incidenciasResueltas)): ?>
                                    <li>No hay incidencias resueltas</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <div id="cambiarPassword" style="display:none;">
                    <h2>Cambiar contraseña</h2>
                    <form id="formCambiarPassword">
                        <label for="oldPassword">Contraseña actual:</label><br>
                        <input type="password" id="oldPassword" name="oldPassword" required><br><br>
                        <label for="nuevaPassword">Nueva contraseña:</label><br>
                        <input type="password" id="nuevaPassword" name="nuevaPassword" required><br><br>
                        <label for="nuevaPassword2">Repite nueva contraseña:</label><br>
                        <input type="password" id="nuevaPassword2" name="nuevaPassword2" required><br><br>
                        <button id="botonPassword" >Actualizar contraseña</button>
                    </form>
                    <p id="mensajePassword" style="color:green;"></p>
                </div>

                <div id="actividades" style="display:none;">
                
                        <div class="columna-progreso">
                            <h3>Progreso de la jornada</h3>
                            <div class="barra-progreso" style="margin-left:25%; margin-right: 25%;margin-bottom: 25px;">
                                <div class="progreso" style="width:<?php echo htmlspecialchars($progresoHorario); ?>%;"></div>
                            </div>
                            <div class="dashboard">
                                <div class="horas">
                                    <h3>Horas trabajadas hoy</h3>
                                    <?php 
                                        $horas = floor($horasTrabajadas); // Parte entera de las horas
                                        $minutos = round(($horasTrabajadas - $horas) * 60); // Calcula los minutos
                                    ?>
                                    <p><?php echo $horas . ' horas y ' . $minutos . ' minutos'; ?></p>
                                </div>
                                <div class="bolsa">
                                    <h3>Horas trabajadas esta semana</h3>
                                    <?php 
                                        $horas = intval($horasSemanales); // Parte entera de las horas
                                        $minutos = abs(round(($horasSemanales - $horas) * 60)); // Calcula los minutos
                                        //ajustamos si son negativos
                                        if ($horasSemanales < 0 && $minutos > 0) {
                                            $horas = $horas === 0 ? -1 : $horas; // Asegura que las horas sean negativas si es necesario
                                            $minutos = -$minutos;
                                        }
                                    ?>
                                    <p><?php echo $horas . ' horas y ' . $minutos . ' minutos '; ?></p>
                                    <h3>Horas trabajadas este mes</h3>
                                    <?php 
                                        $horas = intval($horasMes['Normales']); // Parte entera de las horas
                                        $minutos = abs(round(($horasMes['Normales'] - $horas) * 60)); // Calcula los minutos
                                        //ajustamos si son negativos
                                        if ($horasMes['Normales'] < 0 && $minutos > 0) {
                                            $horas = $horas === 0 ? -1 : $horas; // Asegura que las horas sean negativas si es necesario
                                            $minutos = -$minutos;
                                        }
                                    ?>
                                    <p><?php echo $horas . ' horas y ' . $minutos . ' minutos '; ?></p>
                                    <h3>Horas extras trabajadas este mes</h3>
                                    <?php 
                                        $horas = intval($horasMes['Extras']); // Parte entera de las horas
                                        $minutos = abs(round(($horasMes['Extras'] - $horas) * 60)); // Calcula los minutos
                                        //ajustamos si son negativos
                                        if ($horasMes['Extras'] < 0 && $minutos > 0) {
                                            $horas = $horas === 0 ? -1 : $horas; // Asegura que las horas sean negativas si es necesario
                                            $minutos = -$minutos;
                                        }
                                    ?>
                                    <p><?php echo $horas . ' horas y ' . $minutos . ' minutos '; ?></p>
                                    <h3>Bolsa acumulada</h3>
                                    <?php 
                                        $horas = intval($bolsa); // Parte entera de las horas
                                        $minutos = abs(round(($bolsa - $horas) * 60)); // Calcula los minutos
                                        //ajustamos si son negativos
                                        if ($bolsa < 0 && $minutos > 0) {
                                            $horas = $horas === 0 ? -1 : $horas; // Asegura que las horas sean negativas si es necesario
                                            $minutos = -$minutos;
                                        }
                                    ?>
                                    <p><?php echo $horas . ' horas y ' . $minutos . ' minutos '; ?></p>
                                </div>
                                <div class="horas">
                                    <h3>Horas trabajadas el mes pasado</h3>
                                    <?php 
                                        $horas = intval($horasMesPasado['Normales']); // Parte entera de las horas
                                        $minutos = abs(round(($horasMesPasado['Normales'] - $horas) * 60)); // Calcula los minutos
                                        //ajustamos si son negativos
                                        if ($horasMesPasado['Normales'] < 0 && $minutos > 0) {
                                            $horas = $horas === 0 ? -1 : $horas; // Asegura que las horas sean negativas si es necesario
                                            $minutos = -$minutos;
                                        }
                                    ?>
                                    <p><?php echo $horas . ' horas y ' . $minutos . ' minutos '; ?></p>
                                    <h3>Horas extras trabajadas el mes pasado</h3>
                                    <?php 
                                        $horas = intval($horasMesPasado['Extras']); // Parte entera de las horas
                                        $minutos = abs(round(($horasMesPasado['Extras'] - $horas) * 60)); // Calcula los minutos
                                        //ajustamos si son negativos
                                        if ($horasMesPasado['Extras'] < 0 && $minutos > 0) {
                                            $horas = $horas === 0 ? -1 : $horas; // Asegura que las horas sean negativas si es necesario
                                            $minutos = -$minutos;
                                        }
                                    ?>
                                    <p><?php echo $horas . ' horas y ' . $minutos . ' minutos '; ?></p>
                                </div>
                            </div>
                            
                        </div>
                
                </div>
                <div id="ultimos" style="display:none;">
                    <div class="section" id="recent-accesses">
                        <h3>Últimos 5 accesos</h3>
                        <ul>
                            <?php foreach ($ultimosMarcajes as $marcaje): ?>
                            <?php 
                                // Obtiene solo la fecha del marcaje (sin la hora)
                                $fechaMarcaje = (new DateTime($marcaje['FEC_MARCAJE']))->format('Y-m-d');
                                $tipoClase = $marcaje['COD_TIPO_MARCAJE'] ==1 ? "tipoA":"tipoB";
                                // Verifica si la fecha del marcaje coincide con la fecha de hoy
                                $esHoy = ($fechaMarcaje === $fechaDiaHoy);
                                ?>
                                <li class="acceso-item" style="color: <?php echo !$esHoy ? 'darkgrey' : 'inherit'; ?>;">
                                    <span class="<?php echo $tipoClase;?>"><?php echo $marcaje['COD_TIPO_MARCAJE'] == 1 ? 'Entrada' : 'Salida'; ?></span>
                                    <span class="fecha"><?php echo (new DateTime($marcaje['FEC_MARCAJE']))->format('Y-m-d H:i:s'); ?></span>
                                    <span class="imagen">
                                        <img class="foto_peque" src="./logica/mostrar_imagen.php?archivo=<?php echo htmlspecialchars($marcaje['DES_FOTO']); ?>" alt="Foto de fichaje">
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div id="filtrar" style="display:none;">
                    <div class="contenedor-grafica-reg">
                        <div class="chart-container">
                            <!-- Contenedor para el filtro -->
                            <div class="filter-container">
                                <h3>Filtrar registros</h3>
                                <div>
                                    <!--Opciones del combo-->
                                    <select id="filter-mode" name="filter-mode">
                                        <option value="week" <?php echo ($filtro === 'week') ? 'selected' : ''; ?>>Semana actual</option>
                                        <option value="lastweek" <?php echo ($filtro === 'lastweek') ? 'selected' : ''; ?>>Semana pasada</option>
                                        <option value="month" <?php echo ($filtro === 'month') ? 'selected' : ''; ?>>Mes actual</option>
                                        <option value="lastmonth" <?php echo ($filtro === 'lastmonth') ? 'selected' : ''; ?>>Mes anterior</option>
                                        <option value="year" <?php echo ($filtro === 'year') ? 'selected' : ''; ?>>Año actual</option>
                                        <option value="lastyear" <?php echo ($filtro === 'lastyear') ? 'selected' : ''; ?>>Año anterior</option>
                                        <option value="range" <?php echo ($filtro === 'range') ? 'selected' : ''; ?>>Entre fechas</option>
                                    </select>
                                        <!--Inputs para las fechas-->
                                    <input type="date" id="start-date" name="start-date" disabled>
                                    <input type="date" id="end-date" name="end-date" disabled>
                                        <!--Botón-->
                                    <button id="filtroDatos">Filtrar</button>
                                </div>
                            </div>
                                
                            <!-- Contenedor para la gráfica -->
                        
                            
                        </div>
                    </div>
                    <div>
                        <div>
                            <canvas id="hours-chart"></canvas>
                            <script>
        
                            // Datos generados desde PHP
                            toggleDateInputs();
                            const labels = <?php echo json_encode($labels); ?>; // Fechas
                            const data = <?php echo json_encode($valores); ?>; // Horas trabajadas
                            const average = <?php echo array_sum($valores) / count($valores); ?>; // a usar en js
                            const ausencias = <?php echo json_encode($ausencias); ?>;
                            let registros = <?php echo json_encode($registrosDetallados); ?>;
                            var todosMarcajes = <?php echo json_encode($datosMarcajes); ?>;
                            renderChart(labels, data, ausencias, average,<?php echo $maxHoras;?>);
                                
                        </script>
                        </div>
                         
                        <div class="cabeceraRegistros">
                            <h3>Registros detallados</h3>
                            <div>
                                <button type="button" id="exp-reg-csv">CSV</button>
                                <button type="button" id="exp-reg-xls">Excel</button>
                                <button type="button" id="exp-reg-pdf">PDF</button>
                            </div>
                        </div>
                        <div id="registrosExportables" class="registro">
                            <ul>
                                <li class="registro-header">
                                    <span class="col-fecha">Fecha</span>
                                    <span class="col-tipo">Tipo</span>
                                    <span class="col-fecha">Entrada</span>
                                    <span class="col-tipo">Tipo</span>
                                    <span class="col-fecha">Salida</span>
                                    <span class="col-incidencia">Incidencia</span>
                                    <span class="col-estado">Estado</span>
                                </li>
                                <?php foreach ($registrosDetallados as $index=>$registro): ?>
                                <li data-id="<?php echo $index;?>" 
                                    data-fecha="<?php echo $registro['fecha'];?>">
                                    <span class="fecha"><?php echo $registro['fecha']; ?></span>
                                    <span class="metodo"><?php echo $registro['tipoAccesoEntrada']; ?></span>
                                    <span class="hora"><?php echo $registro['horaEntrada']; ?></span>
                                    <span class="metodo"><?php echo $registro['tipoAccesoSalida']; ?></span>
                                    <span class="hora"><?php echo $registro['horaSalida']; ?></span>
                                    <span class="incidencia"><?php echo $registro['incidencia']; ?></span>
                                    <span class="estado"><?php echo $registro['estado']; ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <!--             Bloques ocultos             -->
                <!--Bloque menú contextual -->
                <div id="context-menu" class="context-menu">
                    <ul>
                        <li id="solicitar-revision">Solicitar revisión</li>
                        <li id="mostrar-datos">Mostrar datos</li>
                    </ul>
                </div>

                <!-- Bloque de Revisión -->
                <div id="bloque-revision" style="display: none;">
                    <h3>Solicitar Revisión</h3>
                    <form id="form-revision" method="post" action="empleado.php">
                        <input type="hidden" id="registro-id-revision" name="registro_id">
                        <div>
                            <label for="comentario-fecha">Fecha:</label>
                            <input id="comentario-fecha" name="comentario-fecha" readonly>
                        </div>
                        <div>
                            <label for="comentario-revision">Comentario:</label>
                            <textarea id="comentario-revision" name="comentario" required></textarea>
                        </div>
                        <div>
                            <label for="prioridad">Prioridad:</label>
                            <select id="prioridad" name="prioridad">
                                <option value="1">Baja</option>
                                <option value="2">Media</option>
                                <option value="3">Alta</option>
                            </select>
                        </div>
                        <button type="submit" name="SolRevision">Enviar Revisión</button>
                        <button type="button" id="cerrar-revision">Cancelar</button>
                    </form>
                </div>

                <!-- Bloque de Mostrar Datos -->
                <div id="bloque-mostrardatos" style="display: none;">
                    <h3>Detalles del Registro</h3>
                    <div id="registro-id-datos"></div>
                    <div class="detalles-registro" id="detalles-registro" data-fecha="<?= $datosMarcajes ?>">
                        <!-- Los datos se generarán dentro de este DIV-->
                    </div>
                    <button type="button" id="cerrar-mostrardatos">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</body> 
</html>