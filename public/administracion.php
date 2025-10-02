<?php
date_default_timezone_set('Europe/Madrid');
//Carga la lógica de la página
require './logica/administracion_datos.php';

//Defino la fecha de hoy
$fechaDiaHoy = (new DateTime('now', new DateTimeZone('Europe/Madrid')))->format('Y-m-d');

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Admin</title>
    
    <!-- Favicon (Logo de la pestaña del navegador) -->
    <link rel="icon" href="../recursos/logo.png" type="image/png">

    <!-- Estilos -->
    <link rel="stylesheet" href="../css/admin_panel.css">

    <!-- Librería Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!--Librerías JS propias-->
    <script src="../js/admin_panel.js"></script>
    <script defer src="../js/face-api.min.js"></script>
    <script defer src="../js/registro.js"></script>

    <!--Librerías de bootstrap-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
</head>
<body>
    <script>
        //Variables generadas por administracion_datos
        const usuarioSesion = <?php echo $codUsuarioSesion;?>;
        const incidenciasP = <?php echo json_encode($incidenciasPendientes); ?>;
        
        const empleados = <?php echo json_encode($empleados); ?>; 
        const permisos = <?php echo json_encode($privilegios);?>;
        const ajustes = <?php echo json_encode($ajustes);?>;
        
    </script>




 <!-- Barra de navegación -->
<div class="navbar">
    <!-- Botón Principal -->
    <button class="nav-btn" id="menuPrincipal">Principal</button>

    <!-- Menú Admin -->
    <div class="nav-btn-container">
        <button class="nav-btn" id="menuAdmin">Admin</button>
        <div class="dropdown-content">
            <a href="#" id="menuEmpleados"><button>Empleados</button></a>
            <a href="#" id="menuUsuarios"><button>Usuarios</button></a>
            <a href="#" id="menuMarcajes"><button>Marcajes</button></a>
            <a href="#" id="menuTransacciones"><button>Transacciones</button></a>
        </div>
    </div>

    <!-- Menú Configuración -->
    <div class="nav-btn-container">
        <button class="nav-btn" id="menuConfiguracion">Configuración</button>
        <div class="dropdown-content">
            <a href="#" id="menuRoles"><button>Roles</button></a>
            <a href="#" id="menuUsuariosRoles"><button>Usuarios y Roles</button></a>
            <a href="#" id="menuAjustes"><button>Ajustes</button></a>
        </div>
    </div>
    <!-- Cerrar sesión -->
    <button class="nav-btn" id="menuPortalEmpleado">Portal empleado</button>
    <!-- Cerrar sesión -->
    <button class="nav-btn" id="menuCerrar">Cerrar sesión</button>
</div>

<!-- Contenedor principal debajo de la barra de navegación -->
<div class="user-panel-container">

    <!-- Contenido principal -->
    <div id="main" class="section">
        <!-- Sección fija con logo y reloj -->
        <div class="logo-container">
            <img src="../recursos/logo.png" alt="Logo" class="logo">
            <div id="current-time"></div>
            </div>
    

                <!--Panel de Bienvenida-->
                <div id="panelBienvenida" class="contenido" style="display:block;">
                    <h1>Bienvenido al Panel de Administración</h1>
                </div>

                <!--Panel de datos del empleado administrador-->
                <div id="panelDatosAdmin" class="contenido" style="display: none;">
                    
                    <div class="dashboard">
                        <!-- Columna 1: Progreso y horas -->
                        <div class="columna-progreso">
                            <div class="barra-progreso">
                                <div class="progreso" style="width:<?php echo htmlspecialchars($progresoHorario); ?>%;"></div>
                            </div>
                            <div class="horas">
                                <h3>Horas trabajadas hoy</h3>
                                <?php 
                                    $horas = floor($horasTrabajadas); // Parte entera de las horas
                                    $minutos = round(($horasTrabajadas - $horas) * 60); // Calcula los minutos
                                ?>
                                <p><?php echo $horas . ' horas y ' . $minutos . ' minutos'; ?></p>
                            </div>
                            <div class="bolsa">
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
                        </div>
                    
                        <!-- Columna 2: Foto -->
                        <div class="columna-foto">
                            <img src="./logica/mostrar_imagen.php?perfil=perfil&archivo=<?php echo htmlspecialchars($empleado->getFoto()); ?>" alt="Foto empleado" class="foto-empleado">
                        </div>
                        <!-- Columna 3: Nombre y horario -->
                        <div class="columna-info">
                            <div class="nombre-empleado"><?php echo htmlspecialchars($empleado->getNombre()." ".$empleado->getApellido1()); ?></div>
                            <div class="horario">
                                <p><strong>Horario:</strong></p>
                                <p><?php echo htmlspecialchars($empleado->getHorario()); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div></br></div>
                <!--Paneles de incidencias-->
                <div id="panelIncidencias" style="display: none;">
                    <div class="tabs">
                        <!-- Header de pestañas -->
                        <div class="tabs-header">
                            <div class="tab active" onclick="openTab('pendientes')">Pendientes</div>
                            <div class="tab" onclick="openTab('completados')">Completados</div>
                        </div>

                        <!-- Contenido de pestañas -->
                        <div id="pendientes" class="tab-content active">
                            <div class="lista-tareas">
                                <!-- Bucle incidencias pendientes -->
                                <?php foreach ($incidenciasPendientes as $incidenciaP): ?>
                                    <?php $fotoIncidencia=pideFoto($incidenciaP['COD_EMPLEADO']);
                                        $empleadoIncidencia = pideNombre($incidenciaP['COD_EMPLEADO']);
                                        $marcajes=$marcaje->marcajesHoy($incidenciaP['COD_EMPLEADO'],new DateTime($incidenciaP['FECHA_INC']));
                                        $marcajesPorIncidencia[$incidenciaP['ID']] = $marcajes;
                                    ?>
                                <div class="fila-tarea incidenciaP" data-id="<?php echo $incidenciaP['ID'];?>" data-foto="<?php echo htmlspecialchars($fotoIncidencia); ?>" 
                                    data-nombre="<?php echo htmlspecialchars($empleadoIncidencia);?>" data-empleado="<?php echo $incidenciaP['COD_EMPLEADO'] ?>" 
                                    data-fecha="<?php echo $incidenciaP['FECHA_INC']?>">
                                    
                                    <img src="./logica/mostrar_imagen.php?perfil=perfil&archivo=<?php echo htmlspecialchars($fotoIncidencia); ?>" class="foto-empleado-peque">
                                    <div><?php echo htmlspecialchars($empleadoIncidencia);?></div>
                                    <div><?php echo htmlspecialchars($incidenciaP['FECHA_INC']);?></div>
                                    <div class="prioridad prioridad-1"><?php echo htmlspecialchars($incidenciaP['PRIORIDAD']);?></div>
                                </div>
                                <?php endforeach; ?>  
                                <script>const marcajesPorIncidencia = <?php echo json_encode($marcajesPorIncidencia); ?>;</script>                          
                            </div>
                        </div>

                        <div id="completados" class="tab-content">
                            <div class="lista-tareas">
                                <!-- Bucle incidencias Resueltas -->
                                <?php foreach ($incidenciasResueltas as $incidenciaR): ?>
                                <div class="fila-tarea" data-id="<?php echo $incidenciaR['ID'];?>">
                                <?php $fotoIncidencia=pideFoto($incidenciaR['COD_EMPLEADO']);
                                        $empleadoIncidencia = pideNombre($incidenciaR['COD_EMPLEADO']);
                                    ?>
                                    <img src="./logica/mostrar_imagen.php?perfil=perfil&archivo=<?php echo htmlspecialchars($fotoIncidencia); ?>" class="foto-empleado-peque">
                                    <div><?php echo htmlspecialchars($empleadoIncidencia);?></div>
                                    <div><?php echo htmlspecialchars($incidenciaR['FECHA_INC']);?></div>
                                    <div class="prioridad prioridad-3">3</div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>    
                    <!--Subpanel ficha incidencia-->
                <div id="panelFichaIncidencia" class="ventana" style="display: none;"></div>
                    <!--Subpanel Formulario de resolución-->
                <div id="panelFormularioResolucion" class = "ventana" style="display: none;">     
                    <button class="cerrar" aria-label="Cerrar ventana">&times;</button>
                    <form>
                        <div class="campo-formulario">
                            <label for="cod_marcaje">COD_MARCAJE:</label>
                            <input type="text" id="resolucionCod" name="cod_marcaje" readonly>
                        </div>
                        <div class="campo-formulario">
                            <select name="empleado" id="resolucionEmpleado" class="form-select" required>
                            <?php if (!empty($empleados)): ?>
                            <?php foreach ($empleados as $empleado): ?>
                                <?php 
                                $codigo = htmlspecialchars($empleado['COD_EMPLEADO'] ?? '');
                                $nombreCompleto = htmlspecialchars(
                                    trim(($empleado['NOM_EMPLEADO'] ?? '') . ' ' . 
                                ($empleado['APE1_EMPLEADO'] ?? ''))
                                );
                                ?>
                                <option value="<?php echo $codigo; ?>">
                                    <?php echo $nombreCompleto; ?>
                                </option>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <option value="" disabled>No hay empleados disponibles</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="campo-formulario">
                            <label for="fec_marcaje">FEC_MARCAJE:</label>
                            <input id="resolucionFecha" name="fec_marcaje">
                        </div>
        
                        <button id="resolucionG" aria-label="Guardar modificación">Guardar</button>
                    </form>
                </div>
                <!--Panel de Entradas y salidas-->
                <div id="panelEntrasSalidas" style="display: none;">
                    <div class="dashboard-columnas">
                        <div class="columna-estado sin-acceso marcoListados">
                            <div class="cabecera-columna">Sin acceso</div>
                            <div class="lista-empleados">
                                <?php foreach($empleadosAusentes as $asistente):?>
                                <div class="fila_foto">
                                    <img src="./logica/mostrar_imagen.php?perfil=perfil&archivo=<?php echo htmlspecialchars($asistente['FOTO']); ?>" alt="Foto empleado" class="foto-empleado-peque">
                                    <div class="info-empleado">
                                        <p class="nombre-empleado"><?php echo $asistente['NOM_EMPLEADO'];?></p>
                                        <p class="apellido-empleado"><?php echo $asistente['APE1_EMPLEADO']." ".$asistente['APE2_EMPLEADO'];?></p>
                                    </div>
                                </div>
                                <?php endforeach;?>
                            </div>
                        </div>
                        <div class="columna-estado trabajando marcoListados">
                            <div class="cabecera-columna">Trabajando</div>
                            <div class="lista-empleados">
                            <?php foreach($empleadosDentro as $asistente):?>
                                <div class="fila_foto">
                                    <img src="./logica/mostrar_imagen.php?perfil=perfil&archivo=<?php echo htmlspecialchars($asistente['FOTO']); ?>" alt="Foto empleado" class="foto-empleado-peque">
                                    <div class="info-empleado">
                                        <p class="nombre-empleado"><?php echo $asistente['NOM_EMPLEADO'];?></p>
                                        <p class="apellido-empleado"><?php echo $asistente['APE1_EMPLEADO']." ".$asistente['APE2_EMPLEADO'];?></p>
                                    </div>
                                </div>
                                <?php endforeach;?>
                            </div>
                        </div>
                        <div class="columna-estado fuera marcoListados">
                            <div class="cabecera-columna">Fuera</div>
                            <div class="lista-empleados">
                            <?php foreach($empleadosFuera as $asistente):?>
                                <div class="fila_foto">
                                    <img src="./logica/mostrar_imagen.php?perfil=perfil&archivo=<?php echo htmlspecialchars($asistente['FOTO']); ?>" alt="Foto empleado" class="foto-empleado-peque">
                                    <div class="info-empleado">
                                        <p class="nombre-empleado"><?php echo $asistente['NOM_EMPLEADO'];?></p>
                                        <p class="apellido-empleado"><?php echo $asistente['APE1_EMPLEADO']." ".$asistente['APE2_EMPLEADO'];?></p>
                                    </div>
                                </div>
                                <?php endforeach;?>
                            </div>
                        </div>
                    </div>
                </div>    
                    <!--Subpanel con datos del empleado-->
                <div id="panelDatosEmpleado" style="display: none;"></div>    
                <!--Paneles de la página Admin-->
                <!--Panel de Mantenimiento de empleados-->
                <div id="panelEmpleados" style="display: none;">
                    <div class="contenido">
                        <div>
                            <div style="display: flex; gap:10px;height:50px;">
                                <select name="empleado" id="seleccionPanelEmpleado" style="width:250px;" class="form-select" required>
                                <?php if (!empty($empleados)): ?>
                                    <?php foreach ($empleados as $empleado): ?>
                                        <?php 
                                            $codigo = htmlspecialchars($empleado['COD_EMPLEADO'] ?? '');
                                            $nombreCompleto = htmlspecialchars(
                                    trim(($empleado['NOM_EMPLEADO'] ?? '') . ' ' . 
                                            ($empleado['APE1_EMPLEADO'] ?? '').' '.
                                            ($empleado['APE2_EMPLEADO'] ?? ''))
                                            );
                                        ?>
                                            <option value="<?php echo $codigo; ?>">
                                                <?php echo $nombreCompleto; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>No hay empleados disponibles</option>
                                    <?php endif; ?>
                                </select>
                                <button id="nuevoEmpleado" name="nuevoEmpleado" class="botonVerde">Nuevo</button>
                            </div>
                        </div>
                        <div class="contenido" id="formularioEmpleado">
                            
                        </div>
                        <div class="enLinea">
                            <button id="exportarEmpleados" class="botonVerde">Exportar</button>
                        </div>
                    </div>
                    <div id="exportarEmpleado" style="display: none;">
                        
                    </div>    
                </div>
                <!--Subpanel Mantenimiento Descriptores-->
                <div id="panelDescriptores" class="ventana" style="display:none;">
                    <button class="cerrar" aria-label="Cerrar ventana">&times;</button>    
                    <div class="contenido">
                        <div id="listaDescriptores">

                        </div>
                        <div class="botonesSN">
                            <button class ="btn btn-primary" id="nuevoDescriptor" name="nuevoDescriptor">Nuevo rostro</button>
                            <button class = "btn btn-danger" id="eliminarDescriptor" name="eliminarDescriptor">Eliminar</button>
                        </div>
                    </div>
                </div>
                <!--Subpanel Alta Descriptor-->
                <div id="panelCamara" class="ventana" style="display:none;">
                    <button class="cerrar" aria-label="Cerrar ventana">&times;</button>    
                    <div class="contenido" style="text-align: center;">
                        <div class="camera-container" style="display: inline-block; margin: 0 auto;">
                            <!-- Aquí se mostraría la vista de la cámara -->
                            <video id="video" width="100%" height="100%" autoplay muted style="padding:15px 0px 15px 0px; display: inline-block;"></video>
                        </div>
                        <p id="status"></p>
                        <button id="guardarDescriptor" class="btn btn-primary">Guardar Rostro</button>
                    </div>
                </div>
                <!--Subpanel Exportar Empleados-->
                <div id="panelExportarEmpleados" class="ventana" style="display: none;"></div>
                <!--Panel de Mantenimiento de usuarios-->
                <div id="panelUsuarios" style="display: none;">
                    <div class="contenido">
                        <div>
                            <div style="display: flex; gap:10px;height:50px;">
                                <select name="usuario" id="seleccionPanelUsuario" style="width:250px;" class="form-select" required>
                                <?php if (!empty($usuarios)): ?>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <?php 
                                            $codigo = htmlspecialchars($usuario['COD_USUARIO'] ?? '');
                                            $nombreUsuario = $usuario['NOM_LOGIN'];
                                        ?>
                                            <option value="<?php echo $codigo; ?>">
                                                <?php echo $nombreUsuario; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>No hay usuarios disponibles</option>
                                    <?php endif; ?>
                                </select>
                                <button id="nuevoUsuario" name="nuevoUsuario" class="botonVerde">Nuevo</button>
                            </div>
                        </div>
                        <div class="contenido" id="formularioUsuario">
                            
                        </div>
                        <div class="enLinea">
                            <button id="exportarUsuarios" class="botonVerde">Exportar</button>    
                        </div>
                    </div>
                    <div id="exportarUsuario" style="display: block;">
                        
                    </div>

                
                </div>
                <!--Subpanel Exportar usuarios-->
                <div id="panelExportarUsuarios" class="ventana" style="display: none;"></div>
                <!--Panel Listado Transacciones-->
                <div id="panelListadoTransacciones" class ="contenido" style="display: none;">
                    <div class="contenedor container py-5">
                        <h1 class="mb-4">Transacciones</h1>
                        <div class="row g-4">
                            <!-- Filtros de Fecha -->
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="fechaInicioTrans" class="form-label">Desde Fecha</label>
                                    <input type="date" id="fechaInicioTrans" name="fechaInicioTrans" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label for="usuarioInicioTrans" class="form-label">Desde Usuario</label>
                                    <input type="numeric" id="usuarioInicioTrans" name="usuarioInicioTrans" class="form-control" value="0">
                                </div>
                                <div class="col-md-4">
                                    <label for="actividadInicioTrans" class="form-label">Desde Actividad</label>
                                    <input type="text" id="actividadInicioTrans" name="actividadInicioTrans" class="form-control" value="">
                                </div>
                                <div class="col-md-1">
                                    
                                </div>
                            </div>    
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="fechaFinTrans" class="form-label">Hasta Fecha</label>
                                    <input type="date" id="fechaFinTrans" name="fechaFinTrans" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label for="usuarioFinTrans" class="form-label">Hasta Usuario</label>
                                    <input type="numeric" id="usuarioFinTrans" name="usuarioFinTrans" class="form-control" value="9999">
                                </div>
                                <div class="col-md-4">
                                    <label for="actividadFinTrans" class="form-label">Hasta Actividad</label>
                                    <input type="text" id="actividadFinTrans" name="actividadFinTrans" class="form-control" value="ZZZZZZZZZZZZZ">
                                </div>
                                <div class="col-md-1">
                                    <button id="filtrarTransacciones" class="btn btn-primary">Filtrar</button>
                                </div>
                            </div>
                                
                        </div>
                    </div>
                    <div class="contenedor" id="listaTransacciones"></div>
                    <div class="enLinea">
                        <button id="exportarTransacciones" class="botonVerde">Exportar</button>    
                    </div>
                    <div id="exportarTransaccion" style="display:none;"></div>                    
                </div>
                <!--Panel Listado Marcajes-->
                <div id="panelListadoMarcajes" class="contenido" style="display: none;">
                    <div class="contenedor container py-5">
                        <h1 class="mb-4">Marcajes</h1>
                        <div class="row g-4">
                            <!-- Filtros de Fecha -->
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="fechaInicioMarcaje" class="form-label">Desde Fecha</label>
                                    <input type="date" id="fechaInicioMarcaje" name="fechaInicioMarcaje" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label for="empleadoInicioMarcaje" class="form-label">Desde Empleado</label>
                                    <input type="numeric" id="empleadoInicioMarcaje" name="empleadoInicioMarcaje" class="form-control" value="0">
                                </div>
                                <div class="col-md-4">
                                    <label for="tipoInicioMarcaje" class="form-label">Desde Tipo</label>
                                    <input type="text" id="tipoInicioMarcaje" name="tipoInicioMarcaje" class="form-control" value="0">
                                </div>
                                <div class="col-md-1">
                                    
                                </div>
                            </div>    
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="fechaFinMarcaje" class="form-label">Hasta Fecha</label>
                                    <input type="date" id="fechaFinMarcaje" name="fechaFinMarcaje" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label for="empleadoFinMarcaje" class="form-label">Hasta Empleado</label>
                                    <input type="numeric" id="empleadoFinMarcaje" name="empleadoFinMarcaje" class="form-control" value="999">
                                </div>
                                <div class="col-md-4">
                                    <label for="tipoFinMarcaje" class="form-label">Hasta Tipo</label>
                                    <input type="numeric" id="tipoFinMarcaje" name="tipoFinMarcaje" class="form-control" value="99">
                                </div>
                                <div class="col-md-1">
                                    <button id="filtrarMarcajes" class="btn btn-primary">Filtrar</button>
                                </div>
                            </div>
                                
                        </div>
                        
                    </div>
                    <div class="contenedor" id="listaMarcajes"></div>
                    <div class="enLinea">
                        <button id="exportarMarcajes" class="botonVerde">Exportar</button>    
                    </div>
                    <div id="exportarMarcaje" style="display:none;"></div>                   
                </div>
                <!--Paneles de la página Configuración-->
                <!--Panel Mantenimiento de permisos por Rol-->
                <div id="panelRoles" style="display: none;">
                    <div class="contenido">
                        <div>
                            <div style="display: flex; gap:10px;height:50px;">
                                <select name="seleccionRol" id="seleccionRol" style="width:250px;" class="form-select" required>
                                <?php if (!empty($roles)): ?>
                                    <?php foreach ($roles as $rol): ?>
                                        <?php 
                                            // Verificar si el rol NO está dado de baja (FEC_BAJA es NULL)
                                            if ($rol['FEC_BAJA'] === null) {
                                                $codigo = $rol['COD_ROL'];
                                                $nombreRol = $rol['NOM_ROL'];
                                        ?>
                                        <option value="<?php echo $codigo; ?>">
                                            <?php echo $nombreRol; ?>
                                        </option>
                                        <?php } ?>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>No hay roles disponibles</option>
                                    <?php endif; ?>
                                </select>
                                <button id="nuevoRol" name="nuevoRol" class="botonVerde">Nuevo</button>
                            </div>
                        </div>
                        <div id="datosRol">
                            
                        </div>
                    </div> 
                </div>
                <!--Panel de Mantenimiento de usuarios por rol-->
                <div id="panelAsignarRoles" style="display: none;">
                    <div class="contenido">
                        <div style="display: flex; gap:10px;height:50px;">
                            <select name="seleccionUsuarioRol" id="seleccionUsuarioRol" style="width:250px;" class="form-select" required>
                                <?php if (!empty($usuarios)): ?>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <?php 
                                            $codigo = $usuario['COD_USUARIO'] ?? 0;
                                            $nombreLogin = $usuario['NOM_LOGIN']??'';
                                        ?>
                                            <option value="<?php echo $codigo; ?>">
                                                <?php echo $nombreLogin; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>No hay usuarios disponibles</option>
                                    <?php endif; ?>
                            </select>
                        </div>
                        <div id="datosUsuarioRol">

                        </div>
                    </div> 
                </div>
                <!--Panel de ajustes-->
                <div id="panelAjustes" style="display: none;"></div>
                <!--Ventana de confirmación-->
                <div id="ventana_emergente" style="display: none;">
                    <div class="emergente">
                        <p id="mensaje_confirmacion"></p>
                        <div class="botonesSN">
                            <button id="botonSI">Sí</button>
                            <button id="botonNO">No</button>
                        </div>
                    </div>
                </div>
                <!--Ventana de mensajes-->
                <div id="ventana_emergente_mensaje" style="display: none;">
                    <div class="emergente">
                        <p id="mensaje_info"></p>
                        <div class="botonesSN">
                            <button id="botonACEPTAR">Aceptar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--Bootstrap para el dropdown con fotos de empleados-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>
