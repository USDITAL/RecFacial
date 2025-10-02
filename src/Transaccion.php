<?php

namespace Clases;
//Carga las clases
use PDO;
use PDOException;
use DateTime;

class Transaccion{
    // Atributos
    private int $cod_Transaccion;
    private string $tip_trans;
    private string $des_trans;
    private int $cod_obj;
    private string $nom_obj;
    private int $cod_usuario;
    private DateTime $fec_sis;
    private string $ip_usuario;

    // Constructor sin parámetros
    public function __construct() {
        $this->cod_Transaccion=0;
    }

    

//Método crear transaccion que escribe en la bbdd a partir del objeto
    public function nueva() {
        try{
            //Crea la conexión y prepara un insert
            $conexion = new Conexion();    
            $sql = "INSERT INTO ttransacciones (TIP_TRANS, DESC_TRANS, COD_OBJ, NOM_OBJ, COD_USUARIO, FEC_SIS, IP_USUARIO) 
                VALUES (:tip_trans, :desc_trans, :cod_obj, :nom_obj, :cod_usuario, :fec_sis, :ip_usuario)";
            $stmt = $conexion->conexion->prepare($sql);
            //Parametriza con los atributos del objeto
            $stmt->bindValue('tip_trans', $this->tip_trans, PDO::PARAM_STR);
            $stmt->bindValue('desc_trans', $this->des_trans, PDO::PARAM_STR);
            $stmt->bindValue('cod_obj', $this->cod_obj, PDO::PARAM_INT);
            $stmt->bindValue('nom_obj', $this->nom_obj, PDO::PARAM_STR);
            $stmt->bindValue('cod_usuario', $this->cod_usuario, PDO::PARAM_INT);
            $stmt->bindValue('fec_sis', $this->fec_sis->format('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue('ip_usuario', $this->ip_usuario, PDO::PARAM_STR);
            //Ejecuta
            $stmt->execute();
            $stmt=null;
            return;
            } catch(PDOException $e) {
                //Si hay error muestra mensaje
                echo("Error al grabar la transacción: " . $e->getMessage());
                return;
            }
    }

    //Método para obtener transacciones de la bbdd
    public function obtenerTransacciones(): array {
        try {
            $conexion = new Conexion();
            // Preparo la consulta
            $consulta = "SELECT * FROM ttransacciones";
            // Ejecuto la consulta
            $stmt = $conexion->conexion->prepare($consulta);
            $stmt->execute();
            // Devuelvo el resultado de la consulta
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            //Devuelve el error
            error_log("Error al listar transacciones: " . $e->getMessage());
            return [];
        }
        
    }

    //Método para obtener una transacción
    public function cargar(int $cod_tran) {
        try {
            $conexion = new Conexion();
            // Preparo la consulta
            $consulta = "SELECT * FROM ttransacciones WHERE COD_TRANSACCION = :cod_tran";
            // Ejecuto la consulta
            $stmt = $conexion->conexion->prepare($consulta);
            $stmt->bindValue('cod_tran', $cod_tran, PDO::PARAM_INT);
            $stmt->execute();
            // Devuelvo el resultado de la consulta
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$resultado){
                return;
            }
            //Vuelca el resultado en el objeto
            $this->setCodTransaccion($$resultado['COD_TRANSACCION']);
            $this->setTipoTrans($resultado['TIP_TRANS']);
            $this->setDesTrans($resultado['DESC_TRANS']);
            $this->setCodObj($resultado['COD_OBJ']);
            $this->setNomObj($resultado['NOM_OBJ']);
            $this->setCodUsuario($resultado['COD_USUARIO']);
            $this->setFecSis(new DateTime($resultado['FEC_SIS']));
            $this->setIpUsuario($resultado['IP_USUARIO']);
        } catch (PDOException $e) {
            //Si hay error lo muestra
            error_log("Error al listar transacciones: " . $e->getMessage());
            return [];
        }
    }

    //Método para obtener transacciones de la bbdd a partir del código de usuario
    public function obtenerTransaccionesUsuario(int $cod_usuario): array {
        try {
            $conexion = new Conexion();
            // Preparo la consulta
            $consulta = "SELECT * FROM ttransacciones WHERE COD_USUARIO = :cod_usuario";
            // Ejecuto la consulta
            $stmt = $conexion->conexion->prepare($consulta);
            $stmt->bindValue('cod_usuario', $cod_usuario, PDO::PARAM_STR);
            $stmt->execute();
            // Devuelvo el resultado de la consulta
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            //Muestra el error
            error_log("Error al listar transacciones: " . $e->getMessage());
            return [];
        }
    }

    //Método para obtener transacciones de la bbdd entre 2 fechas
    public function obtenerTransaccionesEntreFechas(DateTime $fec_Ini, DateTime $fec_Fin): array {
        try {
            $conexion = new Conexion();
            // Preparo la consulta
            $consulta = "SELECT * FROM ttransacciones WHERE FEC_SIS BETWEEN :fec1 AND :fec2";
            // Ejecuto la consulta
            $stmt = $conexion->conexion->prepare($consulta);
            $stmt->bindValue('fec1', $fec_Ini->format('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue('fec2', $fec_Fin->format('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->execute();
            // Devuelvo el resultado de la consulta
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al listar transacciones: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerTransaccionesFiltradas(DateTime $fec_Ini, DateTime $fec_Fin, int $user_Ini, int $user_Fin, string $tipo_Ini, string $tipo_Fin ): array {
        try {
            $conexion = new Conexion();
            // Preparo la consulta
            $consulta = "SELECT * FROM ttransacciones WHERE FEC_SIS BETWEEN :fec1 AND :fec2 AND COD_USUARIO BETWEEN :user1 AND :user2 AND TIP_TRANS BETWEEN :tipo1 AND :tipo2";
            // Ejecuto la consulta
            $stmt = $conexion->conexion->prepare($consulta);
            $stmt->bindValue('fec1', $fec_Ini->format('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue('fec2', $fec_Fin->format('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue('user1', $user_Ini, PDO::PARAM_INT);
            $stmt->bindValue('user2', $user_Fin, PDO::PARAM_INT);
            $stmt->bindValue('tipo1', $tipo_Ini, PDO::PARAM_STR);
            $stmt->bindValue('tipo2', $tipo_Fin, PDO::PARAM_STR);
            $stmt->execute();
            // Devuelvo el resultado de la consulta
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al listar transacciones: " . $e->getMessage());
            return [];
        }
    }

    //<<<<<<<<<<<<<<<<<<<<<<<<<< GETTER Y SETTER >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
// set y get
public function setCodTransaccion(int $cod_Transaccion): void {
    $this->cod_Transaccion = $cod_Transaccion;
}

public function getCodTransaccion(): int {
    return $this->cod_Transaccion;
}

public function setTipoTrans(string $tip_trans): void {
    $this->tip_trans = $tip_trans;
}

public function getTipoTrans(): string {
    return $this->tip_trans;
}

public function setDesTrans(string $des_trans): void {
    $this->des_trans = $des_trans;
}

public function getDesTrans(): string {
    return $this->des_trans;
}

public function setCodObj(int $cod_obj): void {
    $this->cod_obj = $cod_obj;
}

public function getCodObj(): int {
    return $this->cod_obj;
}

public function setNomObj(string $nom_obj): void {
    $this->nom_obj = $nom_obj;
}

public function getNomObj(): string {
    return $this->nom_obj;
}

public function setCodUsuario(int $cod_Usuario): void {
    $this->cod_usuario = $cod_Usuario;
}

public function getCodUsuario(): int {
    return $this->cod_usuario;
}

public function setFecSis($fecha): void {
    $this->fec_sis = new DateTime($fecha);
}

public function getFecSis(): DateTime {
    return $this->fec_sis;
}

public function setIpUsuario(string $ip_usuario): void {
    $this->ip_usuario = $ip_usuario;
}

public function getIpUsuario(): string {
    return $this->ip_usuario;
}
}

        