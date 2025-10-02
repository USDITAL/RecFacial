<?php

namespace Clases;
//Clases
use PDO;
use PDOEception;
use DateTime;

class Usuario{
    // Atributos
    private int $cod_usuario;
    private string $nom_login;
    private string $des_contrasena;
    private string $des_correo;
    private DateTime $fec_alta;
    private string $nom_usuario_alta;
    private ?DateTime $fec_baja;
    private ?string $nom_usuario_baja;
    private array $roles;  

    //Método constructor
    public function __construct() {
        $this->nom_usuario_alta = '';
        $this->fec_baja = null;
        $this->des_contrasena="";
        $this->nom_usuario_baja = null;
        $this->cod_usuario=0;
        $this->roles[]=null;
    }

    // Método para cargar datos de la bbdd
    public function cargarUsuario(int $cod_usuario): void {
        try {
            $conexion = new Conexion();
            // Preparo la consulta SELECT
            $consulta = "SELECT * FROM tusuario WHERE COD_USUARIO = :cod_Usuario";
            $stmt = $conexion->conexion->prepare($consulta);
            $stmt->bindParam(':cod_Usuario', $cod_usuario, PDO::PARAM_INT);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            //Si ha volcado datos
            if ($usuario) {
                //Vuelco en los atributos del objeto
                $this->cod_usuario = $usuario['COD_USUARIO'];
                $this->nom_login = $usuario['NOM_LOGIN'];
                $this->des_contrasena = $usuario['DES_CONTRASENA'];
                $this->des_correo = $usuario['DES_CORREO'];
                $this->fec_alta = new DateTime($usuario['FEC_ALTA']);
                $this->nom_usuario_alta = $usuario['NOM_USUARIO_ALTA'];
                $this->fec_baja = $usuario['FEC_BAJA'] ? new DateTime($usuario['FEC_BAJA']) : null;
                $this->nom_usuario_baja = $usuario['NOM_USUARIO_BAJA'] ? $usuario['NOM_USUARIO_BAJA'] : null;
                $this->cargarRol();
            }
        } catch (PDOException $e) {
            //muestro error
            echo "Error al cargar el usuario: " . $e->getMessage();
        }
    }

    //Método que compara contraseñas y devuelve bool
    public function compararContrasena(string $contrasena, string $hash): bool {
        return password_verify($contrasena, $hash);
    }

    //Método para obtender roles
    public function cargarRol(){
        try {
            //Prepara consulta SELECT
            $conexion = new Conexion();
            $rol = new Rol(); //objeto rol para los roles
            $consulta = "SELECT * FROM tusuariorol WHERE COD_USUARIO = :cod_usuario";
            $stmt = $conexion->conexion->prepare($consulta);
            $stmt->bindValue('cod_usuario',$this->cod_usuario, PDO::PARAM_INT);
            $stmt->execute();
            //Vuelca el resultado
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($resultado){
                //por cada resultado carga el rol obtenido en objeto rol
                foreach ($resultado as $r){
                    $codRol = $r['COD_ROL'];
                    $rol->cargarRol($codRol);
                    //Añade el rol obtenido en un array
                    $arrayRoles[]=$rol->getCodigoRol();
                    
                }
                //añade el array de roles al usuario
                $this->roles = $arrayRoles;
            } return;
        }catch(PDOException $e){
            //Muestra mensaje de error
            echo "Error al cargar los roles: ".$e;
            return;
        }
    }

    //Método para iniciar sesión
    public function iniciarSesion(string $nom_login, string $contrasena): bool {
        try {
            $conexion = new Conexion();
            $rol = new Rol();
            // Preparo la consulta SELECT
            $consulta = "SELECT * FROM tusuario WHERE NOM_LOGIN = :nom_login";
            $stmt = $conexion->conexion->prepare($consulta);
            $stmt->bindParam(':nom_login', $nom_login, PDO::PARAM_STR);
            $stmt->execute();
            //Obtiene el usuario
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($usuario && $this->compararContrasena($contrasena, $usuario['DES_CONTRASENA'])) {
                // Iniciar sesión si hay usuario y coincide contraseña
                //asigna el usuario
                $this->cod_usuario = $usuario['COD_USUARIO'];
                //carga roles
                $this->cargarRol();
                //Cargamos el empleado asignado
                $empleado = new Empleado();
                $roles=[];
                $empleado->cargarDatosPorUsuario($usuario['COD_USUARIO']);
                foreach($this->roles as $rl){
                    $rol->cargarRol($rl);
                    $roles[]=$rol->getNombreRol();
                }
                //Define datos de sesión
                $_SESSION['COD_USUARIO'] = $usuario['COD_USUARIO'];
                $_SESSION['NOM_USUARIO'] = $usuario['NOM_LOGIN'];
                $_SESSION['ROLES'] = $roles;
                $_SESSION['COD_EMPLEADO'] = $empleado->getCodEmpleado();
                //devuelve true
                return true;
            } else {
                //si no es correcto el login devuelve false
                return false;
            }
        } catch (PDOException $e) {
            //muestra error
            echo "Error al iniciar sesión: " . $e->getMessage();
            return false;
        }
    }

    //Método para crear usuario
    public function grabar() {
        try {
            $conexion = new Conexion();
            //si no hay cod_usuario prepara un INSERT
            if ($this->cod_usuario==0 || is_null($this->cod_usuario)){
                $consulta = "INSERT INTO tusuario (NOM_LOGIN, DES_CONTRASENA, DES_CORREO, FEC_ALTA, NOM_USUARIO_ALTA, FEC_BAJA, NOM_USUARIO_BAJA) VALUES (:nom_Login, :des_Contrasena, :des_Correo, :fec_Alta, :nom_Usuario_Alta, :fec_Baja, :nom_Usuario_Baja)";
                $stmt = $conexion->conexion->prepare($consulta);
            //Si lo hay prepara un updste
            }else{
                $consulta = "UPDATE tusuario SET NOM_LOGIN = :nom_Login, DES_CONTRASENA = :des_Contrasena, DES_CORREO = :des_Correo,
                 FEC_ALTA = :fec_Alta, NOM_USUARIO_ALTA = :nom_Usuario_Alta, FEC_BAJA = :fec_Baja, NOM_USUARIO_BAJA = :nom_Usuario_Baja WHERE COD_USUARIO = :cod_Usuario";
                 $stmt = $conexion->conexion->prepare($consulta);
                 $stmt->bindValue('cod_Usuario', $this->cod_usuario, PDO::PARAM_INT);
            }
            //Parametriza y ejecuta
            $stmt->bindValue('nom_Login', $this->nom_login, PDO::PARAM_STR);
            $stmt->bindValue('des_Contrasena', $this->des_contrasena, PDO::PARAM_STR); // Guardar la contraseña hasheada
            $stmt->bindValue('des_Correo', $this->des_correo, PDO::PARAM_STR);
            $stmt->bindValue('fec_Alta', $this->fec_alta->format('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue('nom_Usuario_Alta', $this->nom_usuario_alta, PDO::PARAM_STR);
            $stmt->bindValue('fec_Baja', $this->fec_baja ? $this->fec_baja->format('Y-m-d H:i:s'):null, PDO::PARAM_STR);
            $stmt->bindValue('nom_Usuario_Baja', $this->nom_usuario_baja ? $this->nom_usuario_baja:null, PDO::PARAM_STR);
            
            $stmt->execute();
            $this->setCodUsuario($conexion->conexion->lastInsertId()); // Asigna el ID del último usuario insertado
            return;
        } catch (PDOException $e) {
            //Muestra error
            echo "Error al crear el usuario: " . $e->getMessage();
            return;
        }
    }

    //Método para modificar usuario con parámetros nombre y password
    public function modificarUsuario(string $nombre, string $password): bool {
        try {
            //encripta el password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $conexion = new Conexion();
            //Prepara un update
            $consulta = "UPDATE tusuario SET NOM_LOGIN = :NOM_LOGIN, DES_CONTRASENA = :DES_CONTRASENA WHERE COD_USUARIO = :COD_USUARIO";
            $stmt = $conexion->conexion->prepare($consulta);
            //parametriza yejecuta
            $stmt->bindParam(':NOM_LOGIN', $nombre, PDO::PARAM_STR);
            $stmt->bindParam(':DES_CONTRASENA', $hashedPassword, PDO::PARAM_STR);
            $stmt->bindParam(':COD_USUARIO', $this->cod_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            //muestra error
            echo "Error al modificar el usuario: " . $e->getMessage();
            return false;
        }
    }

    //Método para dar de baja a un usuario
    public function darBajaUsuario($nom_usuario_baja): bool {
        try {
            $conexion = new Conexion();
            //prepara un update
            $consulta = "UPDATE tusuario SET FEC_BAJA = :fec_Baja, NOM_USUARIO_BAJA = :nom_Usuario_Baja WHERE COD_USUARIO = :cod_Usuario";
            $stmt = $conexion->conexion->prepare($consulta);
            $fec_Baja = new DateTime(); //define fecha baja a hoy
            //Parametriza y ejecuta
            $formattedFecBaja = $fec_Baja->format('Y-m-d H:i:s') ?? null;
            $stmt->bindParam(':fec_Baja', $formattedFecBaja, PDO::PARAM_STR);
            $nomUsuarioBaja = $nom_usuario_baja ?? '';
            $stmt->bindParam(':nom_Usuario_Baja', $nomUsuarioBaja, PDO::PARAM_STR);
            $stmt->bindParam(':cod_Usuario', $this->cod_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            //Muestra error
            echo "Error al dar de baja el usuario: " . $e->getMessage();
            return false;
        }
    }

    public function obtenerUsuarios():array{
        try{
            $conexion=new Conexion();
            $sql="SELECT COD_USUARIO, NOM_LOGIN, DES_CORREO FROM tusuario";
            $stmt = $conexion->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch (PDOException $e){
            echo "Error al cargar usuarios: " . $e->getMessage();
            return[];
        }

    }
//<<<<<<<<<<<<<<<<<<<<<<<<<< GETTERS Y SETTERS >>>>>>>>>>>>>>>>>>>>>>>>>>
// Getters
public function getCodUsuario(): int {
    return $this->cod_usuario;
}

public function getNomLogin(): string {
    return $this->nom_login;
}

public function getDesContrasena(): string {
    return $this->des_contrasena;
}

public function getDesCorreo(): string {
    return $this->des_correo;
}

public function getFecAlta(): DateTime {
    return $this->fec_alta;
}

public function getNomUsuarioAlta(): string {
    return $this->nom_usuario_alta;
}

public function getFecBaja(): ?DateTime {
    return $this->fec_baja;
}

public function getNomUsuarioBaja(): ?string {
    return $this->nom_usuario_baja;
}

public function getRoles():?array{
    return $this->roles;
}
// Setters

public function setRol(int $rol){
    try{
        $conexion=new Conexion();
        $usuario = $this->getCodUsuario();
        $sql="INSERT INTO tusuariorol (COD_ROL,COD_USUARIO) VALUES (:rol,:usuario)";
        $stmt = $conexion->conexion->prepare($sql);
        $stmt->bindValue('rol',$rol,PDO::PARAM_INT);
        $stmt->bindValue('usuario',$usuario,PDO::PARAM_INT);
        $stmt->execute();
    }catch(PDOException $e){
        error_log("Error al asignar Rol - " . date('Y-m-d H:i:s') . ": ".$rol." ".$usuario." ". $e->getMessage());
        throw new Exception("Error al asignar el rol. Detalles: ".$rol." ".$usuario." ". $e->getMessage());
    }
}

public function unsetRol(int $rol){
    try{
        $conexion=new Conexion();
        $usuario = $this->getCodUsuario();
        $sql="DELETE FROM tusuariorol WHERE COD_ROL=:rol AND COD_USUARIO=:usuario";
        $stmt = $conexion->conexion->prepare($sql);
        $stmt->bindValue('rol',$rol,PDO::PARAM_INT);
        $stmt->bindValue('usuario',$usuario,PDO::PARAM_INT);
        $stmt->execute();
    }catch(PDOException $e){
        error_log("Error al quitar Rol - " . date('Y-m-d H:i:s') . ": ".$rol." ".$usuario." ". $e->getMessage());
        throw new Exception("Error al quitar el rol. Detalles: ".$rol." ".$usuario." ". $e->getMessage());
    }
}

public function setCodUsuario(int $cod_usuario): void {
    $this->cod_usuario = $cod_usuario;
}

public function setNomLogin(string $nom_login): void {
    $this->nom_login = $nom_login;
}

public function setDesContrasena(string $des_contrasena): void {
    //Encripta el password
    $hashedPassword = password_hash($des_contrasena, PASSWORD_DEFAULT);
    $this->des_contrasena = $hashedPassword;
}

public function setDesCorreo(string $des_correo): void {
    $this->des_correo = $des_correo;
}

public function setFecAlta(DateTime $fec_alta): void {
    $this->fec_alta = $fec_alta;
}

public function setNomUsuarioAlta(string $nom_usuario_alta): void {
    $this->nom_usuario_alta = $nom_usuario_alta;
}

public function setFecBaja(?DateTime $fec_baja): void {
    $this->fec_baja = $fec_baja;
}

public function setNomUsuarioBaja(?string $nom_usuario_baja): void {
    $this->nom_usuario_baja = $nom_usuario_baja;
}
}