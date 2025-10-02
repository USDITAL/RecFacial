<?php
session_start();

// Verificar si la sesión es válida y tiene el rol requerido
if (empty($_SESSION['COD_USUARIO']) && in_array('Admin', $_SESSION['ROLES'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'error' => 'Acceso no autorizado']);
    exit;
}
header('Content-Type: application/json');
require '../../vendor/autoload.php';
// Clases a usar
use Clases\Incidencia;
use Clases\Empleado;
use Clases\Usuario;
use Clases\Transaccion;
use Clases\Ajuste;
use Clases\Marcaje;
use Clases\DatosBiometricos;
use Clases\Privilegio;
use Clases\Rol;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$nombrePrivilegios=[
    "empCrear"=>"Alta empleados",
    "empModificar"=>"Modificar empleados",
    "empBaja"=>"Baja empleados",
    "usrCrear"=>"Alta usuarios",
    "usrModificar"=>"Modificar usuarios",
    "usrBaja"=>"Baja usuarios",
    "usrGenerarPass"=>"Generar password de usuario",
    "marCrearPropio"=>"Marcaje biométrico propio",
    "marConsultarPropio"=>"Consultar marcajes propios",
    "marCrear"=>"Crear marcajes de otros empleados",
    "marModificar"=>"Modificar marcajes",
    "marEliminar"=>"Eliminar marcajes",
    "marConsultar"=>"Consultar marcajes",
    "marAuth"=>"Autorizar marcajes",
    "bioCrear"=>"Crear datos biométricos",
    "bioEliminar"=>"Eliminar datos biométricos",
    "rolCrear"=>"Crear roles",
    "rolModificar"=>"Modificar roles",
    "rolEliminar"=>"Eliminar roles",
    "ajustesModificar"=>"Modificar ajustes"
];

// Verifica si hay una sesión activa
        try {
            $datos = json_decode(file_get_contents('php://input'), true);
            
            // Validar datos recibidos
            if (empty($datos['accion'])) {
                throw new Exception('Acción no válida');
            }else{
                if ($datos['accion']=='actualizar_marcaje_incidencia'){
                    $marcaje= new Marcaje();
                    $marcaje->cargar($datos['cod_marcaje']);
                    $marcaje->setCodEmpleado($datos['cod_empleado']);
                    $marcaje->setFecMarcaje(new DateTime($datos['fec_marcaje']));
                    $marcaje->grabar();

                    $incidencia = new Incidencia();
                    if (!$incidencia->cargar($datos['cod_incidencia'])) {
                        throw new Exception('Incidencia no encontrada'.$datos['cod_incidencia']);
                    }
                    $incidencia->setResuelta(true);
                    $incidencia->setUsuario($datos['cod_usuario']);
                    $incidencia->grabar();
           
                    echo json_encode(['success' => true]);
                }
            
            if ($datos['accion']=='actualizar_incidencia'){
                $incidencia = new Incidencia();
                if (!$incidencia->cargar($datos['cod_incidencia'])) {
                    throw new Exception('Incidencia no encontrada'.$datos['cod_incidencia']);
                }
                $incidencia->setResuelta(true);
                $incidencia->setUsuario($datos['cod_usuario']);
                $incidencia->grabar();
            echo json_encode(['success' => true]);
            }
            if ($datos['accion']=='cerrarSesion'){
                session_unset(); // Elimina todas las variables de sesión
                session_destroy(); // Destruye la sesión
                echo json_encode(['success' => true, 'message' => 'Sesión cerrada correctamente.']);
            }
            if ($datos['accion']=='asigna_rol'){
                $usuario = new Usuario();
                $cod_usuario = $datos['cod_usuario'];
                $cod_rol = $datos['cod_rol'];
                $usuario->cargarUsuario((int)$cod_usuario);
                $usuario->setRol((int)$cod_rol);
            echo json_encode(['success' => true]);
            }

            if ($datos['accion']=='quita_rol'){
                $usuario = new Usuario();
                $cod_usuario = $datos['cod_usuario'];
                $cod_rol = $datos['cod_rol'];
                $usuario->cargarUsuario((int)$cod_usuario);
                $usuario->unsetRol((int)$cod_rol);
            echo json_encode(['success' => true]);
            }

            if ($datos['accion']=='guarda_ajuste'){
                $ajuste = new Ajuste();
                $cod_ajuste = $datos['cod_ajuste'];
                $valor = $datos['valor'];
                $ajuste->cargar($cod_ajuste);
                $ajuste->setValor($valor);
                $ajuste->grabar();
            echo json_encode(['success' => true]);
            }

            if ($datos['accion']=='guarda_rol'){
                $rol = new Rol();
                $codusuario = $_SESSION['COD_USUARIO'];
                $user = new Usuario();
                $user->cargarUsuario($codusuario);
                $usuario = $user->getNomLogin();
                $privilegio = new Privilegio();
                $privilegio->setPrivilegios($datos['privilegios']);
                $cod_rol = $datos['cod_rol'];
                $nom_rol = $datos['nom_rol'];
                $des_rol = $datos['des_rol'];
                if ($cod_rol>0){ $rol->cargarRol($cod_rol);} else {
                    $rol->setUsuarioAlta($usuario);
                    $rol->setFechaAlta(new DateTime());
                }
                $rol->setNombreRol($nom_rol);
                $rol->setDescripcion($des_rol);
                $rol->setPermisos($privilegio);
                $rol->grabar();

            echo json_encode(['success' => true]);
            }

            if ($datos['accion']=='baja_rol'){
                $rol = new Rol();
                $cod_rol = $datos['cod_rol'];
                $fecha = new DateTime();
                $usuario = new Usuario();
                $usuario->cargarUsuario($_SESSION['COD_USUARIO']);
                $nom_usuario = $usuario->getNomLogin();
                $rol->cargarRol($cod_rol);
                $rol->setFechaBaja($fecha);
                $rol->setUsuarioBaja($nom_usuario);
                $rol->grabar();
                echo json_encode(['success' => true]);
            }

            if ($datos['accion'] == 'baja_empleado') {
                try {
                    $codusuario = $_SESSION['COD_USUARIO'];
                    $user = new Usuario();
                    $user->cargarUsuario($codusuario);
                    $usuario = $user->getNomLogin();
                    $empleado = new Empleado();
                    $empleado->cargarDatosEmpleado($datos['cod_empleado']);
                    $fechaBaja = new DateTime();
                    $empleado->setFecBaja($fechaBaja);
                    $empleado->setNomUsuarioBaja($usuario);
                    $empleado->grabar();
                    
                    echo json_encode(['success' => true]);
            
                } catch (Exception $e) {
                    // Registro de error y respuesta
                    error_log('Error en baja_empleado: ' . $e->getMessage());
                    echo json_encode([
                        'success' => false,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if ($datos['accion']=='bolsa_empleado'){
                $empleado = new Empleado();
                $empleado->cargarDatosEmpleado($datos['cod_empleado']);
                $empleado->procesarHorasExtrasMensuales($empleado->getMaxHorasDia());
                $empleado->grabar();
                echo json_encode(['success' => true]);
            }
            
            if ($datos['accion'] == 'graba_empleado') {
                try {
                    // Validaciones básicas
                    if (!isset($datos['cod_empleado'], $datos['apellido1'], $datos['nombre'], 
                              $datos['horario'], $datos['horas'], $datos['usuario'])) {
                        throw new Exception('Faltan datos obligatorios');
                    }
            
                    $empleado = new Empleado();
                    $codusuario = $_SESSION['COD_USUARIO'] ?? null;
                    
                    if (!$codusuario) {
                        throw new Exception('Usuario no autenticado');
                    }
            
                    $user = new Usuario();
                    $user->cargarUsuario($codusuario);
                    $usuario = $user->getNomLogin();
            
                    // Cargar o crear nuevo empleado
                    if ($datos['cod_empleado'] > 0) {
                        $empleado->cargarDatosEmpleado((int)$datos['cod_empleado']);
                    } else {
                        $empleado->setNomUsuarioAlta($usuario);
                        $empleado->setFecAlta(new DateTime('now', new DateTimeZone('Europe/Madrid')));
                    }
            
                    // Asignar datos con sanitización básica
                    $empleado->setApellido1(htmlspecialchars($datos['apellido1']));
                    $empleado->setApellido2(htmlspecialchars($datos['apellido2'] ?? ''));
                    $empleado->setNombre(htmlspecialchars($datos['nombre']));
                    $empleado->setHorario(htmlspecialchars($datos['horario']));
                    $empleado->setMaxHorasDia((float)$datos['horas']);
                    $empleado->setFoto($datos['foto'] ?? null);
                    $empleado->setCodUsuario((int)$datos['usuario']);
                    $empleado->setContacto($datos['contacto'] ?? null);
            
                    // Procesar y grabar
                    $empleado->procesarHorasExtrasMensuales($empleado->getMaxHorasDia());
                    
                    if ($empleado->grabar()) {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Empleado guardado correctamente'
                        ]);
                    } else {
                        throw new Exception('Error al grabar el empleado');
                    }
            
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if ($datos['accion'] == 'graba_usuario') {
                try{$usuario = new Usuario();
                if ($datos['cod_usuario'] > 0) {
                    $usuario->cargarUsuario($datos['cod_usuario']);
                } else {
                    $usuario->setFecAlta(new DateTime());
                    $usuario_temp = new Usuario();
                    $usuario_temp->cargarUsuario($_SESSION['COD_USUARIO']);
                    $usuario->setNomUsuarioAlta($usuario_temp->getNomLogin());
                }
                $usuario->setNomLogin($datos['login']);
                $usuario->setDesCorreo($datos['email']);
                $usuario->grabar();
                echo json_encode(['success' => true]);
                }catch(Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if ($datos['accion'] == 'baja_usuario') {
                try{
                    $usuario = new Usuario();
                    $usuario->cargarUsuario($datos['cod_usuario']);
                    $fechaBaja = new DateTime();
                    $usuario->setFecBaja($fechaBaja);
                    $usuarioBaja=$_SESSION['COD_USUARIO'];
                    $usuario_temp = new Usuario();
                    $usuario_temp->cargarUsuario($usuarioBaja);
                    $usuario->setNomUsuarioBaja($usuario_temp->getNomLogin());
                    $usuario->grabar();
                    echo json_encode(['success' => true]);
                }catch(Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if ($datos['accion'] == 'pass_usuario') {
                try{
                    $usuario = new Usuario();
                    $usuario->cargarUsuario($datos['cod_usuario']);
                    $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_-=+;:,.?';
                    $longitud = 8;
                    $contrasena = '';
                    for ($i = 0; $i < $longitud; $i++) {
                        $indice = rand(0, strlen($caracteres) - 1);
                        $contrasena .= $caracteres[$indice];
                    }
                    $usuario->setDesContrasena($contrasena);
                    $usuario->grabar();
                    enviarCorreoBasico($usuario->getDesCorreo(), 'Nuevo Password Recfacial', 'Su nuevo password es: '.$contrasena);
                    echo json_encode(['success' => true]);
                }catch(Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if ($datos['accion'] == 'graba_bio') {
            }

            if ($datos['accion'] == 'baja_bio') {
                try{
                    $bio = new DatosBiometricos();
                    $bio->cargar($datos['cod_bio']);
                    $bio->eliminar();
                    echo json_encode(['success' => true]);
                }catch(Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if ($datos['accion'] == 'muestra_bio_empleado') {
                $empleado = $datos['cod_empleado'];
                $bio = new DatosBiometricos();
                $bios=$bio->biosPorEmpleado($empleado);
                $html='
                    <ul class="marcoListados">
                        <li class="cabecera_bio">
                            <span class="">Código</span>
                            <span class="">Tipo</span>
                            <span class="">Fecha</span>
                            <span class="">Alta</span>
                        </li>';
                        foreach($bios as $registro){
                            $html=$html.'<li class="linea_bio" data-id="'.$registro['COD_BIO'].'">
                                <span><b>'.$registro['COD_BIO'].'</b></span>
                                <span>'.$registro['COD_TIPO_BIO'].'</span>
                                <span>'.$registro['FEC_ALTA'].'</span>
                                <span>'.$registro['NOM_USUARIO_ALTA'].'</span>
                            </li>';
                        }
                $html=$html.'</ul>';
                header('Content-Type: text/html');
                echo $html;
                exit;
            }
            
            if ($datos['accion']=='exportar_empleado'){
                $empleado = new Empleado();
                $empleado->cargarDatosEmpleado($datos['cod_empleado']);
                $fecha = $empleado->getFecAlta();
                $fechaAlta = $fecha ? $fecha->format('Y-m-d') : '';
                $fecha = $empleado->getFecBaja();
                $fechaBaja = $fecha ? $fecha->format('Y-m-d') : '';
                $usuario=new Usuario();
                $usuarios=$usuario->obtenerUsuarios();
                $html= '<div class="formulario-grid">
                            <div class="fila-grid">
                                <div style="grid-column: span 2;">
                                    <img src="./logica/mostrar_imagen.php?perfil=perfil&archivo='.$empleado->getFoto().'" width="100" id="fotoEmpleado" data-foto="'.$empleado->getFoto().'" height="100" class="rounded-circle me-2">
                                </div>
                                <div style="grid-column: span 5;">
                                    <label for="apellido1Empleado">1er apellido</label>
                                    <input type="text" id="apellido1Empleado" value="'.$empleado->getApellido1().'">
                                </div>
                                <div style="grid-column: span 5;">
                                    <label for="apellido2Empleado">2º apellido</label>
                                    <input type="text" id="apellido2Empleado" value="'.$empleado->getApellido2().'">
                                </div>
                            </div>
                            <div class="fila-grid">
                                <div style="grid-column: span 6;">
                                    <label for="nombreEmpleado">Nombre</label>
                                    <input type="text" id="nombreEmpleado" value="'.$empleado->getNombre().'">
                                    <input type="text" id="codEmpleado" value="'.$empleado->getCodEmpleado().'" hidden>
                                </div>
                                <div style="grid-column: span 3;">
                                    <label for="fechaAltaEmpleado">Fecha Alta</label>
                                    <input type="date" id="fechaAltaEmpleado" value="'.$fechaAlta.'" readonly>
                                </div> 
                                <div style="grid-column: span 3;">
                                    <label for="fechaBajaEmpleado">Fecha Baja</label>
                                    <input type="date" id="fechaBajaEmpleado" value="'.$fechaBaja.'" readonly>
                                </div>
                            </div>
                            <div class="fila-grid fila-completa">
                                <div style="grid-column: span 8;">
                                    <label for="contactoEmpleado">Contacto</label>
                                    <input type="text" id="contactoEmpleado" value="'.$empleado->getContacto().'">
                                </div>
                                <div style="grid-column: span 4;">
                                    <label for="usuarioEmpleado">Usuario</label>
                                    <select name="usuarioEmpleado" id="usuarioEmpleado" class="form-select" required>
                                    ';
                                    if (!empty($usuarios)){
                                        foreach ($usuarios as $usuario){
                                        if ($empleado->getCodUsuario() == $usuario['COD_USUARIO']){
                                            $selected='selected';
                                        } else {
                                            $selected='';
                                        }
                                        $codigo = htmlspecialchars($usuario['COD_USUARIO'] ?? 0);
                                        $nombreUsuario = $usuario['NOM_LOGIN'];
                                        $html=$html.'    
                                        <option value="'.$codigo.'" '.$selected.'>
                                            '.$nombreUsuario.'
                                        </option>';
                                        }
                                        }else{
                                    $html=$html.'<option value="" disabled>No hay usuarios disponibles</option>';
                                }
                            
                                $html=$html.'
                                </select>
                                </div>
                            </div>
                            <div class="fila-grid">
                                <div style="grid-column: span 4;">
                                    <label for="horarioEmpleado">Horario</label>
                                    <input type="text" id="horarioEmpleado" value="'.$empleado->getHorario().'">
                                </div>
                                <div style="grid-column: span 4;">
                                    <label for="horasEmpleado">Máx.horas</label>
                                    <input type="number" id="horasEmpleado" value="'.$empleado->getMaxHorasDia().'">
                                </div>
                                <div style="grid-column: span 4;">
                                    <label for="bolsaEmpleado">Bolsa de horas</label>
                                    <input type="text" id="bolsaEmpleado" value="'.$empleado->getBolsa().'">
                                </div>
                            </div>
                        </div>';
                    header('Content-Type: text/html');
                    echo $html;
                    exit;
            }

            if ($datos['accion']=='recalcular_bolsa'){
                $empleado = new Empleado();
                $marcaje = new Marcaje();
                $empleado->cargarDatosEmpleado($datos['cod_empleado']);
                $empleado->setMaxHorasDia($datos['horas']);
                $empleado->grabar();
                $marcaje->calcularBolsaMensual($empleado->getCodEmpleado(),new DateTime('now'));
                $empleado->cargarDatosEmpleado($datos['cod_empleado']);
                $bolsa=$empleado->getBolsa();
                echo $bolsa;
            }
            if ($datos['accion']=='mostrar_empleado'){
                $empleado = new Empleado();
                $empleado->cargarDatosEmpleado($datos['cod_empleado']);
                $fecha = $empleado->getFecAlta();
                $fechaAlta = $fecha ? $fecha->format('Y-m-d') : '';
                $fecha = $empleado->getFecBaja();
                $fechaBaja = $fecha ? $fecha->format('Y-m-d') : '';
                $usuario=new Usuario();
                $usuarios=$usuario->obtenerUsuarios();
                $html= '<div class="formulario-grid">
                            <div class="fila-grid">
                                <div style="grid-column: span 2;">
                                    <img src="./logica/mostrar_imagen.php?perfil=perfil&archivo='.$empleado->getFoto().'" width="100" id="fotoEmpleado" data-foto="'.$empleado->getFoto().'" height="100" class="rounded-circle me-2">
                                </div>
                                <div style="grid-column: span 5;">
                                    <label for="apellido1Empleado">1er apellido</label>
                                    <input type="text" id="apellido1Empleado" value="'.$empleado->getApellido1().'">
                                </div>
                                <div style="grid-column: span 5;">
                                    <label for="apellido2Empleado">2º apellido</label>
                                    <input type="text" id="apellido2Empleado" value="'.$empleado->getApellido2().'">
                                </div>
                            </div>
                            <div class="fila-grid">
                                <div style="grid-column: span 6;">
                                    <label for="nombreEmpleado">Nombre</label>
                                    <input type="text" id="nombreEmpleado" value="'.$empleado->getNombre().'">
                                    <input type="text" id="codEmpleado" value="'.$empleado->getCodEmpleado().'" hidden>
                                </div>
                                <div style="grid-column: span 3;">
                                    <label for="fechaAltaEmpleado">Fecha Alta</label>
                                    <input type="date" id="fechaAltaEmpleado" value="'.$fechaAlta.'" readonly>
                                </div> 
                                <div style="grid-column: span 3;">
                                    <label for="fechaBajaEmpleado">Fecha Baja</label>
                                    <input type="date" id="fechaBajaEmpleado" value="'.$fechaBaja.'" readonly>
                                </div>
                            </div>
                            <div class="fila-grid fila-completa">
                                <div style="grid-column: span 8;">
                                    <label for="contactoEmpleado">Contacto</label>
                                    <input type="text" id="contactoEmpleado" value="'.$empleado->getContacto().'">
                                </div>
                                <div style="grid-column: span 4;">
                                    <label for="usuarioEmpleado">Usuario</label>
                                    <select name="usuarioEmpleado" id="usuarioEmpleado" class="form-select" required>
                                    ';
                                    if (!empty($usuarios)){
                                        foreach ($usuarios as $usuario){
                                        if ($empleado->getCodUsuario() == $usuario['COD_USUARIO']){
                                            $selected='selected';
                                        } else {
                                            $selected='';
                                        }
                                        $codigo = htmlspecialchars($usuario['COD_USUARIO'] ?? 0);
                                        $nombreUsuario = $usuario['NOM_LOGIN'];
                                        $html=$html.'    
                                        <option value="'.$codigo.'" '.$selected.'>
                                            '.$nombreUsuario.'
                                        </option>';
                                        }
                                        }else{
                                    $html=$html.'<option value="" disabled>No hay usuarios disponibles</option>';
                                }
                            
                                $html=$html.'
                                </select>
                                </div>
                            </div>
                            <div class="fila-grid">
                                <div style="grid-column: span 4;">
                                    <label for="horarioEmpleado">Horario</label>
                                    <input type="text" id="horarioEmpleado" value="'.$empleado->getHorario().'">
                                </div>
                                <div style="grid-column: span 4;">
                                    <label for="horasEmpleado">Máx.horas</label>
                                    <input type="number" id="horasEmpleado" value="'.$empleado->getMaxHorasDia().'">
                                </div>
                                <div style="grid-column: span 4;">
                                    <label for="bolsaEmpleado">Bolsa de horas</label>
                                    <input type="text" id="bolsaEmpleado" value="'.$empleado->getBolsa().'">
                                </div>
                            </div>
                            <div id="botoneraEmpleado" class="fila-botones">
                                <button class="btn btn-primary" id="guardarEmpleado">Guardar cambios</button>
                                <button class="btn btn-secondary" id="recalcularBolsa">Recalcular Bolsa</button>
                                <button class="btn btn-success" id="bioEmpleado">Datos Biométricos</button>
                                <button class="btn btn-danger" id="bajaEmpleado">Dar de baja</button>
                            </div>
                        </div>';
                    header('Content-Type: text/html');
                    echo $html;
                    exit;
            }

            if ($datos['accion']=='mostrar_nuevo_empleado'){
                $usuario=new Usuario();
                $usuarios=$usuario->obtenerUsuarios();
                $html= '<div class="formulario-grid">
                            <div class="fila-grid">
                                <div style="grid-column: span 2;">
                                    <img src="./logica/mostrar_imagen.php?perfil=perfil&archivo=emp_base.jpg" width="100" id="fotoEmpleado" data-foto="emp_base.jpg" height="100" class="rounded-circle me-2">
                                </div>
                                <div style="grid-column: span 5;">
                                    <label for="apellido1Empleado">1er apellido</label>
                                    <input type="text" id="apellido1Empleado" value="">
                                </div>
                                <div style="grid-column: span 5;">
                                    <label for="apellido2Empleado">2º apellido</label>
                                    <input type="text" id="apellido2Empleado" value="">
                                </div>
                            </div>
                            <div class="fila-grid">
                                <div style="grid-column: span 6;">
                                    <label for="nombreEmpleado">Nombre</label>
                                    <input type="text" id="nombreEmpleado" value="">
                                    <input type="text" id="codEmpleado" value="0" hidden>
                                </div>
                                <div style="grid-column: span 3;">
                                    <label for="fechaAltaEmpleado">Fecha Alta</label>
                                    <input type="date" id="fechaAltaEmpleado" value="" readonly>
                                </div> 
                                <div style="grid-column: span 3;">
                                    <label for="fechaBajaEmpleado">Fecha Baja</label>
                                    <input type="date" id="fechaBajaEmpleado" value="" readonly>
                                </div>
                            </div>
                            <div class="fila-grid fila-completa">
                                <div style="grid-column: span 8;">
                                    <label for="contactoEmpleado">Contacto</label>
                                    <input type="text" id="contactoEmpleado" value="">
                                </div>
                                <div style="grid-column: span 4;">
                                    <label for="usuarioEmpleado">Usuario</label>
                                    <select name="usuarioEmpleado" id="usuarioEmpleado" class="form-select" required>
                                    ';
                                    if (!empty($usuarios)){
                                        foreach ($usuarios as $usuario){
                                        $codigo = htmlspecialchars($usuario['COD_USUARIO'] ?? 0);
                                        $nombreUsuario = $usuario['NOM_LOGIN'];
                                        $html=$html.'    
                                        <option value="'.$codigo.'">
                                            '.$nombreUsuario.'
                                        </option>';
                                        }
                                        }else{
                                    $html=$html.'<option value="" disabled>No hay usuarios disponibles</option>';
                                }
                            
                                $html=$html.'
                                </select>
                            </div>
                            </div>
                            <div class="fila-grid">
                                <div style="grid-column: span 4;">
                                    <label for="horarioEmpleado">Horario</label>
                                    <input type="text" id="horarioEmpleado" value="">
                                </div>
                                <div style="grid-column: span 4;">
                                    <label for="horasEmpleado">Máx.horas</label>
                                    <input type="number" id="horasEmpleado" value="">
                                </div>
                                <div style="grid-column: span 4;">
                                    <label for="bolsaEmpleado">Bolsa de horas</label>
                                    <input type="text" id="bolsaEmpleado" value="">
                                </div>
                            </div>
                            <div class="fila-botones">
                                <button class="btn btn-primary" id="guardarEmpleado">Guardar cambios</button>
                            </div>
                        </div>';
                    header('Content-Type: text/html');
                    echo $html;
                    exit;
            }

            if ($datos['accion']=='mostrar_nuevo_usuario'){
                $html= '<div >
                            <div >
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Código Usuario</label>
                                        <input type="text" id="codigoUsuarioUsuario" class="form-control" value="0" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Login</label>
                                        <input type="text" id="loginUsuario" class="form-control" value="">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Correo</label>
                                        <input type="email" id="emailUsuario" class="form-control" value="">
                                    </div>
                                </div>
                                <div id "botoneraUsuario">
                                    <button class="btn btn-primary" id="guardarUsuario">Guardar cambios</button>
                                </div>
                            </div>
                        </div>';
                    header('Content-Type: text/html');
                    echo $html;
                    exit;
            }

            if ($datos['accion']=='mostrar_usuario'){
                $usuario = new Usuario();
                $usuario->cargarUsuario($datos['cod_usuario']);
                $html= '<div >
                            <div >
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Código Usuario</label>
                                        <input type="text" id="codigoUsuarioUsuario" class="form-control" value="'.$usuario->getCodUsuario().'" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Login</label>
                                        <input type="text" id="loginUsuario" class="form-control" value="'.$usuario->getNomLogin().'">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Correo</label>
                                        <input type="email" id="emailUsuario" class="form-control" value="'.$usuario->getDesCorreo().'">
                                    </div>
                                </div>
                                <div id="botoneraUsuario">
                                    <button class="btn btn-primary" id="guardarUsuario">Guardar cambios</button>
                                    <button class="btn btn-danger" id="bajaUsuario">Dar de baja</button>
                                    <button class="btn btn-secondary" id="passUsuario">Generar Password</button>
                                </div>
                            </div>
                        </div>';
                    header('Content-Type: text/html');
                    echo $html;
                    exit;
            }
            if ($datos['accion']=='exportar_usuario'){
                $usuario = new Usuario();
                $usuario->cargarUsuario($datos['cod_usuario']);
                $html= '<div >
                            <div >
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Código Usuario</label>
                                        <input type="text" id="codigoUsuarioUsuario" class="form-control" value="'.$usuario->getCodUsuario().'" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Login</label>
                                        <input type="text" id="loginUsuario" class="form-control" value="'.$usuario->getNomLogin().'">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Correo</label>
                                        <input type="email" id="emailUsuario" class="form-control" value="'.$usuario->getDesCorreo().'">
                                    </div>
                                </div>
                            </div>
                        </div>';
                    header('Content-Type: text/html');
                    echo $html;
                    exit;
            }

            if ($datos['accion']=='exportar_transacciones'){
                $transaccion = new Transaccion();
                $transacciones = $transaccion->obtenerTransaccionesFiltradas(new DateTime($datos['desdeFecha']),new DateTime($datos['hastaFecha']),intval($datos['desdeUsuario']),intval($datos['hastaUsuario']),$datos['desdeActividad'],$datos['hastaActividad']);
                $html= '
                <ul class="marcoListados">
                    <li class="cabecera_trans">
                        <span class="">Fecha</span>
                        <span class="">Usuario</span>
                        <span class="">IP</span>
                        <span class="">Tipo</span>
                        <span class="">Descripción</span>
                        <span class="">Nombre Objeto</span>
                    </li>';
                foreach ($transacciones as $registro){
                $html=$html.'
                    <li class="linea_trans" data-id="'.$registro['COD_TRANSACCION'].'">
                        <span><b>'.$registro['FEC_SIS'].'</b></span>
                        <span>'.$registro['COD_USUARIO'].'</span>
                        <span>'.$registro['IP_USUARIO'].'</span>
                        <span>'.$registro['TIP_TRANS'].'</span>
                        <span>'.$registro['DESC_TRANS'].'</span>
                        <span>'.$registro['NOM_OBJ'].'</span>
                    </li>';
                };
                $html=$html.'</ul>';
                header('Content-Type: text/html');
                echo $html;
                exit;
            }

            if ($datos['accion']=='mostrar_transacciones'){
                $transaccion = new Transaccion();
                $transacciones = $transaccion->obtenerTransaccionesFiltradas(new DateTime($datos['desdeFecha']),new DateTime($datos['hastaFecha']),intval($datos['desdeUsuario']),intval($datos['hastaUsuario']),$datos['desdeActividad'],$datos['hastaActividad']);
                $html= '
                <ul class="marcoListados">
                    <li class="cabecera_trans">
                        <span class="">Fecha</span>
                        <span class="">Usuario</span>
                        <span class="">IP</span>
                        <span class="">Tipo</span>
                        <span class="">Descripción</span>
                        <span class="">Nombre Objeto</span>
                    </li>';
                foreach ($transacciones as $registro){
                $html=$html.'
                    <li class="linea_trans" data-id="'.$registro['COD_TRANSACCION'].'">
                        <span><b>'.$registro['FEC_SIS'].'</b></span>
                        <span>'.$registro['COD_USUARIO'].'</span>
                        <span>'.$registro['IP_USUARIO'].'</span>
                        <span>'.$registro['TIP_TRANS'].'</span>
                        <span>'.$registro['DESC_TRANS'].'</span>
                        <span>'.$registro['NOM_OBJ'].'</span>
                    </li>';
                };
                $html=$html.'</ul>';
                header('Content-Type: text/html');
                echo $html;
                exit;
            }

            if ($datos['accion']=='exportar_marcajes'){
                $marcaje = new Marcaje();
                $marcajes = $marcaje->cargarMarcajesFiltro(intval($datos['desdeEmpleado']),intval($datos['hastaEmpleado']),new DateTime($datos['desdeFecha']),new DateTime($datos['hastaFecha']),$datos['desdeTipo'],$datos['hastaTipo']);
                $html= '
                <ul class="marcoListados">
                    <li class="cabecera_trans">
                        <span class="">Fecha</span>
                        <span class="">Empleado</span>
                        <span class="">Foto</span>
                        <span class="">Tipo</span>
                        <span class="">Observaciones</span>
                        <span class="">Pendiente</span>
                    </li>';
                foreach ($marcajes as $registro){
                $html=$html.'
                    <li class="linea_trans" data-id="'.$registro['COD_MARCAJE'].'">
                        <span><b>'.$registro['FEC_MARCAJE'].'</b></span>
                        <span>'.$registro['COD_EMPLEADO'].'</span>
                        <span><img src="./logica/mostrar_imagen.php?archivo='.$registro['DES_FOTO'].'" alt="Foto acceso" class="foto-acceso"></span>
                        <span>'.$registro['COD_TIPO_MARCAJE'].'</span>
                        <span>'.$registro['DES_OBSERVACIONES'].'</span>
                        <span>'.$registro['IND_PENDIENTE'].'</span>
                    </li>';
                };
                $html=$html.'</ul>';
                header('Content-Type: text/html');
                echo $html;
                exit;
            }

            if ($datos['accion']=='mostrar_marcajes'){
                $marcaje = new Marcaje();
                $marcajes = $marcaje->cargarMarcajesFiltro(intval($datos['desdeEmpleado']),intval($datos['hastaEmpleado']),new DateTime($datos['desdeFecha']),new DateTime($datos['hastaFecha']),$datos['desdeTipo'],$datos['hastaTipo']);
                $html= '
                <ul class="marcoListados">
                    <li class="cabecera_trans">
                        <span class="">Fecha</span>
                        <span class="">Empleado</span>
                        <span class="">Foto</span>
                        <span class="">Tipo</span>
                        <span class="">Observaciones</span>
                        <span class="">Pendiente</span>
                    </li>';
                foreach ($marcajes as $registro){
                //$nombreArchivo = $registro['DES_FOTO']; 
                //$base64Imagen = file_get_contents("https://".$_SERVER['HTTP_HOST']."/Proyecto-DAW/public/logica/mostrar_imagen.php?base64=true&archivo=$nombreArchivo");
                $html=$html.'
                    <li class="linea_trans" data-id="'.$registro['COD_MARCAJE'].'">
                        <span><b>'.$registro['FEC_MARCAJE'].'</b></span>
                        <span>'.$registro['COD_EMPLEADO'].'</span>
                        <span><img src="./logica/mostrar_imagen.php?archivo='.$registro['DES_FOTO'].'" alt="Foto acceso" class="foto-acceso"></span>
                        <span>'.$registro['COD_TIPO_MARCAJE'].'</span>
                        <span>'.$registro['DES_OBSERVACIONES'].'</span>
                        <span>'.$registro['IND_PENDIENTE'].'</span>
                    </li>';
                    //<span><img src="./logica/mostrar_imagen.php?archivo='.$registro['DES_FOTO'].'" alt="Foto acceso" class="foto-acceso"></span>
                    //<span><img src="'.$base64Imagen.'" alt="Foto acceso" class="foto-acceso"></span>
                        
                };
                $html=$html.'</ul>';
                header('Content-Type: text/html');
                echo $html;
                exit;
            }

            if ($datos['accion']=='mostrar_rol'){
                $rol=new Rol();
                $rol->cargarRol($datos['cod_rol']);
                $usuarios=$rol->cargarUsuariosPorRol($datos['cod_rol']);
                $usuario=new Usuario();
                $html='<div class="contenido">
                            <h4>Mantenimiento de roles</h4>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Código</label>
                                        <input type="number" id="campoCodRol" class="form-control" value="'.$rol->getCodigoRol().'" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Nombre</label>
                                        <input type="text" id="campoNomRol" class="form-control" value="'.$rol->getNombreRol().'">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Descripción</label>
                                        <textarea id="campoDesRol" class="form-control">'.$rol->getDescripcion().'</textarea>
                                    </div>
                                </div>
                                <div class="marcoListados listaPrivilegios">
                                ';
                                $permiso = new Privilegio;
                                $permiso = $rol->getPermisos();
                                $privilegios = $permiso->getPrivilegios();
                                foreach($privilegios as $clave=>$privi){
                                    $checked = $privi ? 'checked' : '';
                                    $html=$html.'
                                    <div class="linea_roles">
                                        <label class="form-label">'.$nombrePrivilegios[$clave].'</label>
                                        <input type="checkbox" class="rolCheckbox" data-id="'.$clave.'" '.$checked.'>
                                    </div>';
                                }
                                $html=$html.'
                                </div>
                                <div id="botoneraRol" class="enLinea botonesRol">
                                    <button class="btn btn-primary" id="guardarRol">Guardar cambios</button>
                                    <button class="btn btn-danger" id="bajaRol">Dar de baja</button>
                                </div>
                            </div>
                        </div>
                        <div class="contenido">
                            <h4>Usuarios con este rol asignado</h4>
                            <ul class="marcoListados">';
                foreach($usuarios as $user){
                    $usuario->cargarUsuario($user['COD_USUARIO']);
                    $nombreUsuario=$usuario->getNomLogin();
                    $html=$html.'<li class="linea_trans">'.$nombreUsuario.'</li>';
                }
                $html=$html.'   
                            </ul>
                        </div>
                        ';
                header('Content-Type: text/html');
                echo $html;
                exit;
            }

            if ($datos['accion']=='mostrar_nuevo_rol'){
                $rol=new Rol();
                $html='<div class="contenido">
                            <h4>Mantenimiento de roles</h4>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Código</label>
                                        <input type="number" id="campoCodRol" class="form-control" value="" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Nombre</label>
                                        <input type="text" id="campoNomRol" class="form-control" value="">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Descripción</label>
                                        <textarea id="campoDesRol" class="form-control"></textarea>
                                    </div>
                                </div>
                                <div class="marcoListados listaPrivilegios">
                                ';
                                $permiso = new Privilegio;
                                $privilegios = $permiso->getPrivilegios();
                                foreach($privilegios as $clave=>$privi){
                                    $checked = $privi ? 'checked' : '';
                                    $html=$html.'
                                    <div class="linea_roles">
                                        <label class="form-label">'.$nombrePrivilegios[$clave].'</label>
                                        <input type="checkbox" class="rolCheckbox" data-id="'.$clave.'" '.$checked.'>
                                    </div>';
                                }
                                $html=$html.'
                                </div>
                                <div class="enLinea botonesRol">
                                    <button class="btn btn-primary" id="guardarRol">Guardar cambios</button>
                                </div>
                            </div>
                        </div>
                        <div class="contenido">
                            <h4>Usuarios con este rol asignado</h4>
                            <ul class="marcoListados">   
                            </ul>
                        </div>
                        ';
                header('Content-Type: text/html');
                echo $html;
                exit;
            }

            if ($datos['accion']=='mostrar_usuariorol'){
                $rol=new Rol();
                $usuario=new Usuario();
                $usuario->cargarUsuario($datos['cod_usuario']);
                $usuario->cargarRol();
                $rolesUsuario = $usuario->getRoles();
                $rolesPosibles=$rol->cargarRoles();

                $html='<div class="contenido">
                            <div class="contenedor-flex">
                                <div class="columna-flex">
                                    <h6>Roles asignados</h6>
                                    <div class="marcoListados" id="rolesAsignados">
                                    ';

                                    foreach($rolesUsuario as $clave=>$rolUsuario){
                                        try{
                                            if ($rolUsuario>0){
                                            $rol->cargarRol($rolUsuario);
                                            $nombreRol = $rol->getNombreRol();
                                            }else{
                                                $nombreRol = "";
                                            }
                                        }catch(Exception $e){
                                            
                                        }
                                        $html=$html.'
                                        <div class="linea_roles rolesAsignados"  data-id="'.$rolUsuario.'" data-usuario="'.$datos['cod_usuario'].'">
                                            <label class="form-label">'.$nombreRol.'</label>
                                        </div>';
                                    }
                                    $html=$html.'
                                    </div>
                                </div>
                                
                                <div class="columna-flex">
                                    <h6>Roles disponibles</h6>
                                    <div class="marcoListados">
                                    ';
                                    foreach($rolesPosibles as $rolPosible){
                                        if(!in_array($rolPosible['COD_ROL'],$rolesUsuario)){
                                            $html=$html.'
                                            <div class="linea_roles rolesPosibles" data-id="'.$rolPosible['COD_ROL'].'" data-usuario="'.$datos['cod_usuario'].'">
                                                <label class="form-label">'.$rolPosible['NOM_ROL'].'</label>
                                            </div>';
                                        }
                                    }
                                    $html=$html.'
                                    </div>
                                </div>
                            </div>
                        </div>
                        ';
                header('Content-Type: text/html');
                echo $html;
                exit;
            }

            if ($datos['accion']=='mostrar_ajustes'){
                $ajuste = new Ajuste();
                $ajustes = $ajuste->obtenerAjustes();
                $html='<div class="contenido">
                            <h4>Mantenimiento de ajustes</h4>
                                <div class="marcoListados" id="listaAjustes">
                                ';
                                foreach($ajustes as $clave=>$parametro){
                                    $html=$html.'
                                    <div class="linea_Ajustes">
                                        <label class="form-label">'.$parametro['NOM_AJUSTE'].'</label>
                                        <label class="form-label">'.$parametro['DESC_AJUSTE'].'</label>';
                                        switch($parametro['TIPO_AJUSTE']){
                                            case 'int':
                                                $html=$html.'<input class="elementoAjuste" type="number" data-id="'.$parametro['ID_AJUSTE'].'" value="'.$parametro['VALOR_AJUSTE'].'">';
                                                break;
                                            case 'string':
                                                $html=$html.'<input class="elementoAjuste" type="text" data-id="'.$parametro['ID_AJUSTE'].'" value="'.$parametro['VALOR_AJUSTE'].'">';
                                                break;
                                            case 'bool':
                                                $html=$html.'<input class="elementoAjuste" type="checkbox" data-id="'.$parametro['ID_AJUSTE'].'" value="'.$parametro['VALOR_AJUSTE'].'">';
                                                break;
                                            case 'date':
                                                $html=$html.'<input class="elementoAjuste" type="date" data-id="'.$parametro['ID_AJUSTE'].'" value="'.date($parametro['VALOR_AJUSTE']).'">';
                                                break;
                                            default:
                                                $html=$html.'<input class="elementoAjuste" type="text" data-id="'.$parametro['ID_AJUSTE'].'" value="'.$parametro['VALOR_AJUSTE'].'">';
                                        }
                                }
                                $html=$html.'</div>
                            </div>
                        </div>
                        ';
                header('Content-Type: text/html');
                echo $html;
                exit;
            }
            
            if ($datos['accion']=='mostrar_incidencia'){
                $empleado=new Empleado();
                $empleado->cargarDatosEmpleado($datos['cod_empleado']);
                $marcaje = new Marcaje();
                $marcajes = $marcaje->cargarMarcajesFiltro($datos['cod_empleado'],$datos['cod_empleado'],new DateTime($datos['fecha']),new DateTime($datos['fecha']),0,99);
                $html='
                <div class="contenedor-empleado">
                    <button class="cerrar" aria-label="Cerrar ventana">&times;</button>
                    <div class="cabecera-empleado">
                        <img id="fotoIncidenciaEmpleado" src="./logica/mostrar_imagen.php?perfil=perfil&archivo='.$empleado->getFoto().'" class="foto-empleado">
                        <div class="info-empleado">
                            <p class="nombre-empleado" id="nombreEmpleadoIncidencia">'.$datos['nombre'].'</p>
                            <p class="fecha-empleado" id="fechaIncidencia">Sobre la fecha:'. $datos['fecha'].'</p>
                            <p id="incidenciaActiva" data-incidencia="'.$datos['id'].'">'.$datos['id'].'</p>
                        </div>
                    </div>

                    <!-- Queja del empleado -->
                    <div class="queja-empleado">
                        <p id="quejaEmpleado"> '.$datos['comentario'].'</p>
                    </div>
                    <!-- Lista de eventos -->
                    <h3>Registro de accesos:</h3>
                    <div class="lista-eventos">
                    ';
                    foreach($marcajes as $indice=>$marca){
                        $tipoTexto = $marca['COD_TIPO_MARCAJE'] == 1 ? "Entrada" : "Salida";
                        
                    $html=$html.'<div class="fila-evento marcajeIncidenciaP" data-id="'.$marca['COD_MARCAJE'].'">
                            <span class="tipo-evento">'.$tipoTexto.'</span>
                            <span class="fecha-evento">'.$marca['FEC_MARCAJE'].'</span>
                            <img class="foto_peque" src="./logica/mostrar_imagen.php?archivo='.$marca['DES_FOTO'].'">
                        </div>';
                    };
                    $html=$html.'</div>
                </div>';
                header('Content-Type: text/html');
                echo $html;
                exit;
            }

        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
    ]);
}

function enviarCorreoBasico($destinatario, $asunto, $mensaje) {
    $so = PHP_OS;
        if (stripos($so, 'WIN') !== false) {
            $ruta_mail = 'c:/xampp/mail.txt';
        } else {
            $ruta_mail = '/var/www/mail.txt';
        }

        //Compruebo si existe el archivo de conexión
        if (!file_exists($ruta_mail)) {
            die("Error: No se encontró el archivo de la conexión a la base de datos.");
        } else {
            //Leo los datos de conexión desde el archivo
            $datos = file($ruta_mail);
            //Vuelco los datos eliminando espacios en blanco, saltos de línea, etc...
            $localMail = trim($datos[0]);
            $localPass = trim($datos[1]);
            $smtp = trim($datos[2]);
            $mail = new PHPMailer(true);

            try {
                // Configuración del servidor SMTP de Gmail
                $mail->isSMTP();
                $mail->Mailer ="smtp";
                $mail->SMTPSecure = 'tls';
                $mail->Host = $smtp;
                $mail->SMTPAuth = true;
                $mail->Username = $localMail;
                $mail->Password = $localPass;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS
                $mail->Port = 587; // Puerto para TLS

                // Remitente y destinatario
                $mail->setFrom($localMail, 'Administración'); // El nombre es opcional
                $mail->addAddress($destinatario, ''); // Puedes añadir múltiples destinatarios
    
                // Contenido del correo
                $mail->isHTML(true); // Establecer el formato del email a HTML
                $mail->Subject = $asunto;
                $mail->Body    = $mensaje;
                $mail->AltBody = $mensaje;

                $mail->send();
            } catch (Exception $e) {
                echo("No se pudo enviar el mensaje. Error: {$mail->ErrorInfo}");
            }
        }
}
