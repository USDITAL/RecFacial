<?php
session_start();
date_default_timezone_set('Europe/Madrid');

require '../vendor/autoload.php';

use Clases\Conexion;
use Clases\Ajuste;
use Clases\DatosBiometricos;
use Clases\Empleado;
use Clases\Marcaje;
use Clases\Privilejio;
use Clases\Rol;
use Clases\TipoDatoBiometrico;
use Clases\Transaccion;
use Clases\Usuario;
use Clases\Privilegio;
use Clases\Incidencia;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


//$privilejio = new Privilejio();




$fecha = new DateTime('now', new DateTimeZone('UTC'));
$fecha->setTimezone(new DateTimeZone('Europe/Madrid'));

function pruebaAjuste(bool $crear, bool $modificar, bool $mostrar, ?int $id){
    $ajuste = new Ajuste();
    if ($crear){
        $ajuste->crear('MaxLoginRq','3');
    }
    if ($modificar){
        //$ajuste->actualizarAjuste(1, 'umbral', '0.8');
        $ajuste->cargar($id);
        $ajuste->setValor('4');
        $ajuste->grabar();
    }
    if ($mostrar){
        $ajustes = $ajuste->obtenerAjustes($id);
        var_dump($ajustes);
    }
}
function pruebaDatosBio(bool $crear, bool $modificar, bool $mostrar, ?int $id){
    global $fecha;
    $datosBiometricos = new DatosBiometricos();
    if ($crear){
        $datosBiometricos->setCodEmpleado(1);
        $datosBiometricos->setCodTipo(1);
        $datosBiometricos->setDatoBio('dadsdsdsdsds');
        $datosBiometricos->setFecAlta($fecha);
        $datosBiometricos->setNomUsuarioAlta('Admon');
        $datosBiometricos->grabar();
        //$datosBiometricos->eliminar();
    }
    if ($modificar){
        $datosBiometricos = $datosBiometricos->cargar($id);
        $datosBiometricos->setDatoBio('dadsdsdsdsds-Modificado');
        $datosBiometricos->grabar();
    }
    if ($mostrar){
        $datosBiometricos = $datosBiometricos->cargar($id);
        var_dump($datosBiometricos);
    }

}
function pruebaEmpleado(bool $crear, bool $modificar, bool $mostrar, ?int $id){
    global $fecha;
    $empleado = new Empleado();
    if ($crear){
        $empleado->setCodUsuario(1);
        $empleado->setNombre('Juan');
        $empleado->setApellido1('Perez');
        $empleado->setApellido2('Gomez');
        $empleado->setContacto('juanpg@local.com');
        $empleado->setFecAlta($fecha);
        $empleado->setNomUsuarioAlta('admon');
        $empleado->grabar();
        $empleado->darBaja('admon',$fecha);
    }
    if ($modificar){
        $empleado->cargarDatosEmpleado($id);
        $empleado->setApellido1('Velázquez');
        $empleado->grabar();
    }
    if ($mostrar){
        $empleado->cargarDatosEmpleado($id);
        var_dump($empleado);
        $empleados =$empleado->listarEmpleados();
        var_dump($empleados);
    }
}
function pruebaMarcaje(bool $crear, bool $modificar, bool $mostrar, ?int $id){
    global $fecha;
    $marcaje = new Marcaje();
    if ($crear){
        $marcaje->setCodTipoMarcaje(1);
        $marcaje->setCodEmpleado($id);
        $marcaje->setCodBio(14);
        $marcaje->setFecMarcaje($fecha);
        $marcaje->setFecGrabacion($fecha);
        $marcaje->setIncidencia(false);
        $marcaje->setPendiente(false);
        $marcaje->setFoto('foto');
        $marcaje->setTipoAcceso(1);
        $marcaje->setObs('observaciones');
        $marcaje->grabar();
    }
    if ($modificar){
        $marcaje = $marcaje->cargar($id);
        $marcaje->setObs('observaciones nuevas');
        $marcaje->grabar();
    }
    if ($mostrar){
        $fecha2= new DateTime('2025-3-10 00:00');
        $marcajes = $marcaje->cargarMarcajesEntreFechas(1,1,$fecha2, $fecha);
        $marcajes2=$marcaje->marcajesHoy(1,$fecha);
        var_dump($marcajes2);
        echo($marcaje->calcularHorasTrabajadas(1,$fecha));
       
    }
}
function pruebaRol(bool $crear, bool $modificar, bool $mostrar, ?int $id){
    global $fecha;
    $rol = new Rol();
    $privis = new Privilegio();
    $privis->setRolModificar(true);
    if ($crear){
        $rol->setNombreRol('MdCrear');
        $rol->setDescripcion('Crear MD');
        $rol->setUsuarioAlta('Admon');
        $rol->setFechaAlta($fecha);
        $rol->setPermisos($privis);
        $rol->grabar();
    }
    if ($modificar){
        $rol->cargarRol($id);
        $rol->setPermisos($privis);
        $rol->grabar();
    }
    if ($mostrar){
        $rol->cargarRol(1);
        $privis=$rol->getPermisos();
        //var_dump($privis);
        $privilegios = $privis->getPrivilegios();
        //var_dump($privilegios);
        $privilegios['empCrear'] = true;
        $privis->setPrivilegios($privilegios);
        var_dump($privis->getPrivilegios());
        $rol->setPermisos($privis);
        //$rol->grabar();
    }
}
function pruebaTipoBio(bool $crear, bool $modificar, bool $mostrar, ?int $id){
    global $fecha;
    $tipoDatoBiometrico = new TipoDatoBiometrico();
    if ($crear){
        $tipoDatoBiometrico->setDesTipoBio('Keypad');
        $tipoDatoBiometrico->setNomUsuarioAlta('Admon');
        $tipoDatoBiometrico->setFecAlta($fecha);
        $tipoDatoBiometrico->grabar();
    }
    if ($modificar){
        $tipoDatoBiometrico->cargar($id);
        $tipoDatoBiometrico->setDesTipoBio('Teclado');
        $tipoDatoBiometrico->grabar();
    }
    if ($mostrar){
        $tipoDatoBiometrico->cargar($id);
        var_dump($tipoDatoBiometrico);
    }
}
function pruebaTransaccion(bool $crear, bool $modificar, bool $mostrar, ?int $id){
    global $fecha;
    $transaccion = new Transaccion();
    if ($crear){
        $transaccion->setTipoTrans('mod_usuario');
        $transaccion->setDesTrans('Modificación del usuario Admon');
        $transaccion->setCodObj(1);
        $transaccion->setNomObj('tUsuario');
        $transaccion->setCodUsuario(1);
        $transaccion->setFecSis($fecha);
        $transaccion->setIpUsuario('127.0.0.1');
        $transaccion->nueva();
    }
    if ($modificar){
        echo "No se pueden modificar";
    }
    if ($mostrar){
        
        $fecha1 = new DateTime('2025-3-17 10:10');
        $fecha2 = new DateTime('2025-3-25 10:10');
        $transacciones=$transaccion->obtenerTransaccionesFiltradas($fecha1,$fecha2,1,6,'','zzzzzzzz');
        var_dump($transacciones);
    }
}
function pruebaUsuario(bool $crear, bool $modificar, bool $mostrar, ?int $id){
    global $fecha;
    $usuario = new Usuario();
    if ($crear){
        $usuario->setNomLogin('Admon');
        $usuario->setDesContrasena('Prueba');
        $usuario->setDesCorreo('benito@sefue.com');
        $usuario->setFecAlta($fecha);
        $usuario->setNomUsuarioAlta('Admon');
        $usuario->grabar();
    }
    if ($modificar){
        $usuario->cargarUsuario(cod_usuario: $id);
        /*$usuario->setDesContrasena('Prueba');
        $usuario->grabar();*/
        //$usuario->setRol(2);
        //var_dump($usuario->getRoles());
        //$usuario->unsetRol(2);
        //var_dump($usuario->getRoles());
    }
    if ($mostrar){
        $usuario->cargarUsuario($id);
        //$resultado=$usuario->compararContrasena('Prueba');
        //if ($resultado){
            var_dump($usuario);
        //} else {
        //    echo "Error de login";
        //}
        //$usuario->iniciarSesion('Admon','Prueba');
        //var_dump($_SESSION);*/
    }
}

pruebaAjuste(false,false,false,1);
pruebaDatosBio(false,false,false,1);
pruebaEmpleado(false,false,false,5);
pruebaMarcaje(false,false,false,1);
pruebaRol(false,false,false,2);
pruebaTipoBio(false,false,false,1);
pruebaTransaccion(false,false,false,6); //Sin pasar
pruebaUsuario(false,false,false,2);


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

//enviarCorreoBasico("usdital@gmail.com","Prueba de correo","Esto es una prueba de correo");
$clave = '&5Gd87Fdded3456dF$%&"Ws-?32s';
$datos_encriptados = "LNFSRGId/U1nwRGv6RYECnRsdnBKNXY3YWJFVFZuME16UE9CQUFLT0l3OWdTSjhDV0dqZTFHUGRaUWNIUjh2T214dlVFbnF2azdzMTN5Qjl6WSsvVDIrYkN3OE1OVDBxb3ZSSEVjNmRRQlhUa0hmcktyNGp2aEF4M2oxbGhiZERsM0FMWDM1Uy9kWjNwVmFJT1hwbkNkWGhLc0VTbHFqQW5DajRoOVZIaFQzTUNuWXJScExTaXI5cUtVazB6Yml1eEorODdkNm9nUVNlQyszMG1qU2cxUkxsbXh0L2liMFY4VlUyLzhpclhBK2Z1MmIyaHVmUDV4azVpS2pNZ0kzb0ZHSzIvYlRXdHFSTjBxU0dSbThsWVA3bXU1ano5K09uSGJxWU5uVmtiUzBoeGpqMm1kUDlTdnYwSTA0NjdYT1VDRUpJTUN1UkJFZGtwbEF5QTh0dWxjTnJBM3VpaVM0NElodnJhc1BBdTlkdkU4d3MrK3pYV2QxMUFlVWRnYXo1VDkzclEzbWJwU1FSaStHUGFVYXczdy80L3NhMXowVWwxaVlUVFk4WURlWmtsZmNwR0dLZTArWURjY3dCakJPcC9lajN4dmpNWFpTMlBOYWk3MHBHU2g0R1FNbW9ZVW9wdW1WbVdhd29QZDlIOXhPSHQ5Vmt4QU94dHF3ZDh3S0lnd0VEZnlaYnhGd2N0cEVnZUxEOGRxcEtFV2ZpTHpUd3VDTnNxS05SQTZXZnhDdDR1UDJ4NkVTanA5NVk2eHNpVGZHVTErVGE5V1NOS25CK21hZTlQOXBPaXI0ckJLbGtuT3A4V09tK1RZcWlYV3ZkcVA5RkQxT1NteXhIV0NXSEE0Nk1mdlJmdUoxMmpvSVJ2Z1NNQVZVSXZKSEZuNDB4OWd4dm9nd1JmWS9RMGFybHFlOVBqY3BCZktiQTZwb3MxdEJ6TDlhMkttaTNydVo2Zk1YaW5nem54UHFtZWk3R2tjWVE1ajJQTXoxRjVaUW1wVmVqQXlLUVZwc1l6OHlRTWRuTGd0U3AvMitHSXNyWkJJa2VhSEV6OGJYaVRFZGJOS1dRcW9uYnllZnRRcGhjNmtXNGJwNDFzUExqVUN4bDQ5aUpHTTlLdG9qYWhyci9YQ0pVZDRnL2htL1Z2ckFGTjNOMGFib3h1ZHRJTnJKalEwZ0NBSU9tUmFUWTFXVW5MbXpxd0ptWERsek5GQkVYdnNTS1VhTm81QjhZb3NxbXNuL25zOVNtbXpaNzJXTXpkZzNQaVh0elBxbk9tY29GMVgvWnNpUEFBMU9XWU9LRkFUOHJ6RlFvRHRueDBxSDJZUWRDNXZWMjhVZGhzUFpNYUx5WlNUZHpsMHU0QWFVckVtUlVETEp3Qk5rRnZNZGJZMG9LWDlDUU92MUNKTmdsMW5xZXpxcXFZanlTekppSjRIdUpIZ21wcW5vaHQxb0FsRXhvUXZoRlU3SjlOV3pmNzg0WklrTlp4WE5KcTFubi9nYWt5M3BZT01UeWJkS2NqNGRYSytQVitJQVBiSk9lNHVobU4rMDQ2d1pRMFFtRXQzQVFiN3Z1aHRHQy9wZ1BERnB0QU1RSmJ3QTRVc2NnN2lMZFhkUEZsam5DNlFId0V0anovKzBORW1PbzJjWHdyUkR1ZGdJQXlNRlVENlhYb0F2WmREakFkM1ZjdUwrRHBpK1c0dVFVbnFOOHh1ZkZISTl0RkFKQ1UvK2x1NmE0M2FTL3Z0YXliRlhZTVpuSFB6bEkwMlZIb3VvcVNrdTAzMzlMWldkTC90MlEyR3BoRytQbmZ6T3M1YkROb25tRytRWFJSN2xNN1N0dnFtK0VTelFLbVkxaEFuektLVXVKQ2QrQUdnRzRDKzViWjR4bjFPeXpjWkJjR1RodmJQamc1UmpBWXRsaUhlVjV4OE1VQi9tT3daWTFCTC8zajVObUw2bEE0RGV5Vm54R2p4K2xxWFh6VVRxc2hlN0tWUUZOb3VzUmFoMzlpaDJ4MGsyVFZYWTZoN1JpQmhTQUNsdGFhSDlWT09YWStzTHlWU29VNWtuNmRyZk5ZbmRmLzRVTUpITnJwZ2xnM0NuazFKc3htcG1YdWVlNjRVMkl6MXdvbFJGWVJBNHVQRjdza2xrL3VEa2txZ0R1WE1tblJGeE5KWWFNdGh1VEJpOWg0bzhlTW1KcFlWNkJlYmtsVGNxakVpRlRhWUJIRW84MXFJNWRvN3JpZmZZWWQ3ejNnQkNnbTdaYlI1V0puT25QYVhuKzYvSFVpamltcU9WOEtzY2JDL29ERWZyNFpJRi8ydWorVy9PNGRLYWlUbFU0VnRJeFVpVVNXdFZqU0oyRlZ4WjZYdUhHcDJKd3pDanQ5MmE0czNKZk1uN2ZRbzQ3S0daWjl2MTZCZm42S2c0Wk5uZ0oxNmtKN0Y3cGhXcjNUdFJ5eFQ1WU5vZW4xa0ljKzRPNVlYZWZBQWpZOHpYWEtpSkdLRUlhOHA4U3BZQzF2VnNHV1JPeU55bnhzL2Z2V3lWNU5ydzduWjVxbnVUT2RWTXNUNXlCS2xPTEpqNjJUS2ZFTkREeEl1bzA0RHQ3ZkJVMWxaM01TRjhWa3RMVTVtZnN6Sy9rNlFLR3FkajJROWRpVTZSemE3ZWtwVFBtbE84N1NSU2lUWGxvVlZjbDhDbFB5K3VZVWxvTHBOeFJhMmJMSlYwcEFzY3hPWkVjUmFpZW5aMnlyKzRrcVhSQmRiTFJjRXdnOElKc0RFalY1R1NOdmo2K09hbGJKMHFTa01oYVZ2bFQ4bmRQajhNYk9qK2RmaFR4L1FLREJFa0VDblRaOFlwWVZrMG1ZR0duYXFrKy82aDN6ZWxOemowZTlsZTd3VGFGTjQ4cDF1YVc0M3hVWVFqQVJ6TlF0TGdVbXBGbENQZnJTZmovUXlWc2dkaUtrZUI5bDFiem9nNXpPcEJGcjFFTjhxbFIvOThQdTNpMTZ2U2RlSExvTWxjUmlWUmJIaFg4VWRjdzZUR2ZVSVF0K2JVNlZoMmxZRDU0Y25FZE5zUWlJRXk2OWNSbjdVYm1zbmZoZS9mbTIyT2lPVUcrVTMyblRINVBadU9RcHVoQWlKM1REdXF6SXUyWlAyakhnU1dQdS9ieGw0V29YcHB4dWttU0V3aGl0ZjJsbEUwbFZZckhJeWpYQlBtRTd1S1l1WGNJRzVsaGtOeDUrTEdZOHBMQ2hVV1BrU3RGYUJRRHNzWXVESklXNW9IYnE3NEVlSCsrUERia2I0b2t3djVqdzJjQmx3VTFoN2hpY21tZEk0VHc2cXRNOWloREpDWFYwSkpSUWpkcFNVSmp0Y3k4cEc0SEl4L2lYSGNhbmMvNHY1VjZwclVwa1pDUUh3WXMyQ2N0V0JXMFJzZWU4RUpyTEN0OU5mZ0gycjhlZmlYY2JReXNMNFhNVGhwUTVKRVdQbjJCNm9BcGxCU0VjRkpSVHA2TmE4QW5uRlJvNDlLb2lTZlV3UG10N3JCcXlIOGk0UWNEeGlWSXBFb1FkUGNlbVNhUUFZZkN6QWlvamRZcHF2RVA2ZFZHWUQ0WGFhU2I0RzFQc3dLQ29TbUdaR0JBNTNWUHM4YWx5WDdFZ2VUNVdMRHV5UkZCc2Q0emZJZWNVcXlUYWc3Vmt4cEl5TWJ1clRaMVcySnA5OFpLM2EwU091MVJDK0F0SHU0Y0dqZkErTUZ5RWpTY01kQXlkdHEzTERMUm1lWWdhL1BlbUlRdDllUkEvTXRCRG5yT0Noc3VOa3RrTlZKOW8vRFNjYUFCejQ3dmhDUUE5SlBHQlVEREx6L1c0YzRpYldyUUpoa2FVL0Fkc3diL25iczFYVzV5SEFHekErdGgxWkVkdEFKZDlNVHdGcHl1bWJwZW5lazRnTFdXZ2tiaG5yYUd2Q2N2TWxZZ2ZSWDhoNDhuOUZNc2NZUE80M2JuSjdmUHFKN1NjY3BkaFhXN2lzN2wrR2RDYlQ3bFJwRkQ4YXBIbXJJVnhET2F6OXBudnBBaWhwNW1LUXdNdEZ1dmt1MGJuWWtSNDhzOURsRHJjd0JZREdZZ3BOM2xiOVBpTVNZd2pnUjNBUHBVZUYyaWlxaG13cGRDZUVBdmNhQnp5aDB3Y2pWUDdTbUhWM1huallEMnlpTnhVNVZ1RkhUY05Qc1BoQmVKSFNDSk9wbFc3VDFFRnllamNKeEZ0T1hlVkJHdVA0TkpEQklTVFNyeUZaTW55YzljWjkwTnM0RStyWVgvb1gwNjhBQUxQVEkvTUxuYkdEZUNHK3pFdXdZd3RmSjV1V1VOZkUwVitoZUNaS2dybHZnbC9MQlI4cmdKMU80dHBOdkwwTklBMXJlejJRRS9RZjlreUU4L21QM3lsRmVSRktKNGc4YnBsdmdWK0R6V21qZndYTW9WcW9FZ2xqN1NBcFZ1bnVsQS9wc2VMRTlyQThVelF5THYwL2FPalZKTzB0MDJmc21HUnBqMHhQcm9iN2FUb09JTk9JQWZFSEUrTGp4UjFnZWpDeEpIUmprTDRqQ09wd2R1SVR0dXdJRmRrRlI4VGRzWVExZTdpYmZLNUVNM3RKMWV6UVRVTlpDMlNzL2RLMnBsYWkvWnlmSDJyTWNCVzZBbmZmSHJ1aCtCd0ZSTWNXMEU3QTNIVGhhUkxDcG5uVFdON2o1NTY5RXR1RldWSEZPOEdLV2QvVk1PemhGOWY2Sk5jYXB6MyszNlkxdFZCU0djdGN0SDNmVnZwZit4Rm9mT0pKYlRITEF4c2VnYzhkbkJTRzArYlkzODRVY2l5Nm1Yc1JMZnVZem95Nk14";
    $datos_encriptados = base64_decode($datos_encriptados);
    $metodo = 'AES-256-CBC';
    $iv_length = openssl_cipher_iv_length($metodo);
    $iv = substr($datos_encriptados, 0, $iv_length);
    $datos_encriptados = substr($datos_encriptados, $iv_length);
    echo openssl_decrypt($datos_encriptados, $metodo, $clave, 0, $iv);