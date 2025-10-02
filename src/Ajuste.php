<?php

namespace Clases; //Espacio de nombres

use PDO;            //Librerías PDO
use PDOException;
//La clase Ajuste maneja la BBDD de ajustes
class Ajuste {
    // Atributos de la clase
    private int $id;
    private string $nombre_ajuste;
    private string $valor;

    private string $tipo;
    private string $descripcion;
    
    // Constructor de la clase
    public function __construct() {
    }

    // Método para obtener todos los ajustes de la base de datos
    public function obtenerAjustes():array {
        $conexion = new Conexion();
        // Preparo la consulta
        $consulta = "SELECT * FROM tajuste";
        // Ejecuto la consulta
        $stmt = $conexion->conexion->prepare($consulta);
        $stmt->execute();
        // Devuelvo el resultado de la consulta
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para obtener un ajuste de la base de datos
    public function obtenerAjuste($id):array {
        try{
            $conexion = new Conexion();
            // Preparo la consulta
            $consulta = "SELECT * FROM tajuste WHERE id_ajuste = :id_ajuste";
            // Preparo el array de parámetros
            $stmt = $conexion->conexion->prepare($consulta);
            $stmt->bindParam(':id_ajuste', $id, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado ? $resultado : [];
        }catch(PDOException $e){
            return[];
        }
    }

    //Método para crear un nuevo ajuste
    public function crear(string $nombre, string $valor, string $tipo="",string $descripcion=""){
        $this->id=0;
        $this->setNombreAjuste($nombre);
        $this->setValor($valor);
        $this->tipo = $tipo;
        $this->descripcion = $descripcion;
        $this->grabar();
    }

    //Método para cargar un ajuste
    public function cargar(int $id_ajuste){
        $this->id = $id_ajuste;
        $ajuste = $this->obtenerAjuste($id_ajuste);
        if (count($ajuste)>0){
            $this->setNombreAjuste($ajuste['NOM_AJUSTE']);
            $this->setValor($ajuste['VALOR_AJUSTE']);
            $this->tipo = $ajuste['TIPO_AJUSTE'];
            $this->descripcion = $ajuste['DESC_AJUSTE'];
        }else{
            return false;
        }
    }
    // Método para grabar un ajuste en la base de datos
    public function grabar() {
        $conexion = new Conexion();
        // Preparo la consulta
        try {
            //Si no hay un id en los parámetros la consulta es un insert
            if ($this->id==0 || is_null($this->id)){
                $consulta = "INSERT INTO tajuste (NOM_AJUSTE, VALOR_AJUSTE, TIPO_AJUSTE, DESC_AJUSTE) VALUES (:nom_ajuste, :valor_ajuste, :tipo, :descr)";
                $stmt = $conexion->conexion->prepare($consulta); 
            //Si hay id la consulta es un update
            }else{
                $consulta = "UPDATE tajuste SET NOM_AJUSTE = :nom_ajuste, VALOR_AJUSTE = :valor_ajuste, TIPO_AJUSTE = :tipo, DESC_AJUSTE=:descr WHERE ID_AJUSTE = :id_ajuste";
                $stmt = $conexion->conexion->prepare($consulta); 
                //Al ser un update añade el parámetro de id
                $stmt->bindValue('id_ajuste', $this->id, PDO::PARAM_INT);
            }
            //Resto de parámetros/campos para la consulta
            $stmt->bindValue('nom_ajuste', $this->nombre_ajuste, PDO::PARAM_STR);
            $stmt->bindValue('valor_ajuste', $this->valor, PDO::PARAM_STR);
            $stmt->bindValue('tipo',$this->tipo??"",PDO::PARAM_STR);
            $stmt->bindValue('descr', $this->descripcion??"", PDO::PARAM_STR);
            //Ejecutamos la consulta
            $stmt->execute();
            //termina la función devolviendo true
            return true;
        } catch (PDOException $e) {
            //En caso de error de conexión muestra mensaje y devuelve false
            echo "Error al grabar el ajuste: " . $e->getMessage();
            return false;
        }
    }
    //Método para actualizar un ajuste directamente sin parametrizar el objeto
    public function actualizarAjuste($id, $nombre_ajuste, $valor, $tipo="", $descripcion="") {
        $conexion = new Conexion();
        // Preparo la consulta
        try {
            $consulta = "UPDATE tajuste SET NOM_AJUSTE = :nom_ajuste, VALOR_AJUSTE = :valor_ajuste, TIPO_AJUSTE = :tipo, DESC_AJUSTE = :descr WHERE ID_AJUSTE = :id_ajuste";
            $stmt = $conexion->conexion->prepare($consulta);
            //Parametriza la consulta
            $stmt->bindParam(':id_ajuste', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nom_ajuste', $nombre_ajuste, PDO::PARAM_STR);
            $stmt->bindParam(':valor_ajuste', $valor, PDO::PARAM_STR);
            $stmt->bindParam(':tipo',$tipo,PDO::PARAM_STR);
            $stmt->bindParam(':descr', $descripcion,PDO::PARAM_STR);
            //Ejecuta y develve true
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            //si falla muestra error y devuelve false
            echo "Error al actualizar el ajuste: " . $e->getMessage();
            return false;
        }
    }
//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< GETTERS Y SETTERS >>>>>>>>>>>>>>>>>>>>>>>>>>>>>
//Get y set para el Id
public function getId(): int {
    return $this->id;
}

public function setId(int $id): void {
    $this->id = $id;
}

public function getTipo():string {
    return $this->tipo;
}

public function setTipo(string $tipo){
    $this->tipo = $tipo;
}

public function getDescripcion():string {
    return $this->descripcion;
}

public function setDescripcion(string $descripcion){
    $this->descripcion = $descripcion;
}

// Getter y Setter para el atributo nombre_ajuste
public function getNombreAjuste(): string {
    return $this->nombre_ajuste;
}

public function setNombreAjuste(string $nombre_ajuste): void {
    $this->nombre_ajuste = $nombre_ajuste;
}

// Getter y Setter para el atributo valor
public function getValor(): string {
    return $this->valor;
}

public function setValor(string $valor): void {
    $this->valor = $valor;
}
}