<?php

namespace Clases;
//Clases a usar
use PDO;
use PDOException;
use DateTime;

class Rol {
    // Atributos    
    private int $cod_Rol;
    private string $nom_Rol;
    private string $descripcion;
    private DateTime $fec_Alta;
    private string $nom_Usuario_Alta;
    private ?DateTime $fec_Baja=null;
    private ?string $nom_Usuario_Baja=null;
    private Privilegio $privilegios;
// Constructor
    public function __construct() {
        $this->cod_Rol=0;
        $this->fec_Baja = null;
        $this->nom_Usuario_Baja = null;
    }



// Destructor
    public function __destruct() {
        unset($this->cod_Rol);
        unset($this->nom_Rol);
        unset($this->descripcion);
        unset($this->fec_Alta);
        unset($this->nom_Usuario_Alta);
        unset($this->fec_Baja);
        unset($this->nom_Usuario_Baja);
        unset($this->privilegios);
    }
// Modificar Rol en una sola función con todos los parámetros. Cambia el objeto
//NO actualiza la BBDD
    public function modificarRol(
        string $nom_Rol,
        string $descripcion,
        DateTime $fec_Alta,
        string $nom_Usuario_Alta,
        ?DateTime $fec_Baja ,
        ?string $nom_Usuario_Baja ,
        Privilegio $privilegios
    ): void {
        $this->nom_Rol = $nom_Rol;
        $this->descripcion = $descripcion;
        $this->fec_Alta = $fec_Alta;
        $this->nom_Usuario_Alta = $nom_Usuario_Alta;
        $this->fec_Baja = $fec_Baja;
        $this->nom_Usuario_Baja = $nom_Usuario_Baja;
        $this->privilegios = $privilegios;
    }

    //Método para eliminar el rol de la bbdd
    public function eliminar(): bool {
        try{
            // Crear la conexión
            $conexion = new Conexion();
            // Consulta DELETE
            $sql = "DELETE FROM trol WHERE COD_ROL = :cod_Rol";
            $stmt = $conexion->conexion->prepare($sql);
            $stmt->bindValue(':cod_Rol', $this->cod_Rol, PDO::PARAM_INT);
            $stmt->execute();
            //elimina la consulta y devuelve true
            $stmt=null;
            return true;
        }catch(PDOException $e){
            //Muestra error y devuelve false
            error_log("Error al eliminar el rol: " . $e->getMessage());
            return false;
        }
    }

//Método para cargar rol de la bbdd a partir del código de rol
    public function cargarRol(int $cod_Rol): bool {
        try{
            // Crear la conexión
            $conexion = new Conexion();
            // Consulta SELECT
            $sql = "SELECT * FROM trol WHERE COD_ROL = :rol";
            $stmt = $conexion->conexion->prepare($sql);
            $stmt->bindValue(':rol', $cod_Rol);
            $stmt->execute();
            //Vuelca el resultado
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            //Si no hay resultado devuelve false
            if (!$resultado) {
                return false;
            }
            //Vuelca el resultado en los parámetros
            $this->cod_Rol = $resultado['COD_ROL'];
            $this->nom_Rol= $resultado['NOM_ROL'];
            $this->descripcion= $resultado['DES_ROL'];
            $this->fec_Alta= new DateTime($resultado['FEC_ALTA']);
            $this->nom_Usuario_Alta= $resultado['NOM_USUARIO_ALTA'];
            $this->fec_Baja= $resultado['FEC_BAJA'] ? new DateTime($resultado['FEC_BAJA']) : null;
            $this->nom_Usuario_Baja= $resultado['NOM_USUARIO_BAJA'] ?? null;
            if ($resultado['PRIVILEGIOS']){
                $this->privilegios=unserialize($resultado['PRIVILEGIOS']);   
            }
            //Devuelve true
            return true;
        }catch(PDOException $e){
            //Muestra error y devuelve false
            error_log("Error al cargar el rol: " . $e->getMessage());
            return false;
        }
    }

    //Método para cargar rol de la bbdd a partir del nombre de rol
    public function cargarRolNombre(string $rol): bool {
        try{
            // Crear la conexión
            $conexion = new Conexion();
            // Consulta SELECT
            $sql = "SELECT * FROM trol WHERE NOM_ROL = :rol";
            $stmt = $conexion->conexion->prepare($sql);
            $stmt->bindValue(':rol', $rol);
            $stmt->execute();
            //Vuelca el resultado
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            //Si no hay resultado devuelve false
            if (!$resultado) {
                return false;
            }
            //Vuelca el resultado en los parámetros
            $this->cod_Rol = $resultado['COD_ROL'];
            $this->nom_Rol= $resultado['NOM_ROL'];
            $this->descripcion= $resultado['DES_ROL'];
            $this->fec_Alta= new DateTime($resultado['FEC_ALTA']);
            $this->nom_Usuario_Alta= $resultado['NOM_USUARIO_ALTA'];
            $this->fec_Baja= $resultado['FEC_BAJA'] ? new DateTime($resultado['FEC_BAJA']) : null;
            $this->nom_Usuario_Baja= $resultado['NOM_USUARIO_BAJA'] ?? null;
            if ($resultado['PRIVILEGIOS']){
                $this->privilegios=unserialize($resultado['PRIVILEGIOS']);   
            }
            //Devuelve true
            return true;
        }catch(PDOException $e){
            //Muestra error y devuelve false
            error_log("Error al cargar el rol: " . $e->getMessage());
            return false;
        }
    }


    //Método para grabar el rol en la bbdd
    public function grabar(): bool {
        try{
            // Crear la conexión
            $conexion = new Conexion();
            // Consulta INSERT si no hay cod_rol
            if ($this->cod_Rol==0 || is_null($this->cod_Rol)){$sql = "INSERT INTO trol (NOM_ROL, DES_ROL, FEC_ALTA, NOM_USUARIO_ALTA, FEC_BAJA, NOM_USUARIO_BAJA, PRIVILEGIOS) 
                VALUES (:nom_Rol, :descripcion, :fec_Alta, :nom_Usuario_Alta, :fec_Baja, :nom_Usuario_Baja, :privilegios)";
            $stmt = $conexion->conexion->prepare($sql);
            //Consulta Update en caso contrario    
            } else {
                $sql = "UPDATE trol SET 
                NOM_ROL = :nom_Rol, 
                DES_ROL = :descripcion, 
                FEC_ALTA = :fec_Alta, 
                NOM_USUARIO_ALTA = :nom_Usuario_Alta, 
                FEC_BAJA = :fec_Baja, 
                NOM_USUARIO_BAJA = :nom_Usuario_Baja, 
                PRIVILEGIOS = :privilegios 
                WHERE COD_ROL = :cod_Rol";
                $stmt = $conexion->conexion->prepare($sql);
                $stmt->bindValue(':cod_Rol', $this->cod_Rol, PDO::PARAM_INT);
            }
            //Parametriza
            $stmt->bindValue(':nom_Rol', $this->nom_Rol, PDO::PARAM_STR);
            $stmt->bindValue(':descripcion', $this->descripcion, PDO::PARAM_STR);
            $stmt->bindValue(':fec_Alta', $this->fec_Alta->format('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue(':nom_Usuario_Alta', $this->nom_Usuario_Alta, PDO::PARAM_STR);
            $stmt->bindValue(':fec_Baja', $this->fec_Baja ? $this->fec_Baja->format('Y-m-d H:i:s') : null, PDO::PARAM_STR);
            $stmt->bindValue(':nom_Usuario_Baja', $this->nom_Usuario_Baja, PDO::PARAM_STR);
            $stmt->bindValue(':privilegios', serialize($this->privilegios), PDO::PARAM_STR);
            $stmt->execute();
            //elimina la consulta y devuelve true
            $stmt=null;
            return true;
        }catch(PDOException $e){
            //Muestra error y devuelve false
            error_log("Error al grabar el rol: " . $e->getMessage());
            return false;
        }
    }

    //Método para cargar los roles de la bbdd como array de roles
    //<<<<<<<<<<<<<<< BACK-END >>>>>>>>>>>>>>>><<
    //Modificar si es mejor volcar solo datos
    public static function cargarRoles(): array {
        //Creo aray de roles
        $roles = [];
        try{
            // Crear la conexión
            $conexion = new Conexion();
            // Consulta SELECT
            $sql = "SELECT * FROM trol";
            $stmt = $conexion->conexion->prepare($sql);
            $stmt->execute();
            //Vuelca el resultado
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $resultados;
        }catch(PDOException $e){
            //Muestra error y devuelve false
            error_log("Error al cargar roles: " . $e->getMessage());
            return [];
        }
    }

    //Método para cargar usuarios por rol
    public function cargarUsuariosPorRol($codRol):array{
        try{
            $conexion=new Conexion();
            $sql="SELECT * FROM tusuariorol WHERE COD_ROL = :rol";
            $stmt = $conexion->conexion->prepare($sql);
            $stmt->bindValue('rol',$codRol??0,PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            //Muestra error y devuelve false
            error_log("Error al cargar usuarios por rol: " . $e->getMessage());
            return [];
        }
        
    }

/*
<<<<<<<<<<<<<<<<<<<<< GETTERS Y SETTERS >>>>>>>>>>>>>>>>>>>>>>
*/

    
    // Getters
    public function getPermisos(): Privilegio {
        return $this->privilegios;
    }

    public function getNombreRol(): string {
        return $this->nom_Rol;
    }

    public function getDescripcion(): string {
        return $this->descripcion;
    }

    public function getFechaAlta(): DateTime {
        return $this->fec_Alta;
    }

    public function getUsuarioAlta(): string {
        return $this->nom_Usuario_Alta;
    }

    public function getFechaBaja(): ?DateTime {
        return $this->fec_Baja;
    }

    public function getUsuarioBaja(): ?string {
        return $this->nom_Usuario_Baja;
    }

    public function getCodigoRol(): int {
        return $this->cod_Rol;
    }

    // Setters
    public function setNombreRol(string $nom_Rol): void {
        $this->nom_Rol = $nom_Rol;
    }

    public function setDescripcion(string $descripcion): void {
        $this->descripcion = $descripcion;
    }

    public function setFechaAlta(DateTime $fec_Alta): void {
        $this->fec_Alta = $fec_Alta;
    }

    public function setUsuarioAlta(string $nom_Usuario_Alta): void {
        $this->nom_Usuario_Alta = $nom_Usuario_Alta;
    }

    public function setFechaBaja(?DateTime $fec_Baja): void {
        $this->fec_Baja = $fec_Baja;
    }

    public function setUsuarioBaja(?string $nom_Usuario_Baja): void {
        $this->nom_Usuario_Baja = $nom_Usuario_Baja;
    }

    public function setPermisos(Privilegio $privilegios): void {
        $this->privilegios = $privilegios;
    }


}