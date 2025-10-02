<?php

namespace Clases;
//Clases a usar
use DateTime;
use DateTimeZone;
use PDO;
use PDOException;

class Incidencia{
    private int $id;
    private DateTime $fecha_rev;
    private DateTime $fecha_inc;
    private string $comentario;
    private int $prioridad;
    private int $empleado;
    private bool $resuelta;
    private ?int $cod_usuario;



    public function __construct(){
        $this->setId(0);
        $this->fecha_rev = new DateTime("");
        $this->fecha_inc = new DateTime("");
        $this->comentario = "";
        $this->prioridad = 0;
        $this->empleado = 0;
        $this->resuelta = false;
        $this->cod_usuario=0;
    }
    
    public function grabar():bool {
        $conexion = new Conexion();
        try{
            if ($this->getId() == 0){
                $sql="INSERT INTO tincidencia (FECHA_REV, FECHA_INC, COMENTARIO, PRIORIDAD, COD_EMPLEADO, RESUELTA, COD_USUARIO) 
                VALUES (:fec_rev, :fec_inc, :coment, :prio, :empleado, :result, :cod_usuario)";
                $stmt = $conexion->conexion->prepare($sql);
            } else{
                $sql="UPDATE tincidencia SET FECHA_REV=:fec_rev, FECHA_INC=:fec_inc, COMENTARIO=:coment, PRIORIDAD=:prio, COD_EMPLEADO=:empleado, RESUELTA=:result, COD_USUARIO=:cod_usuario WHERE ID = :id";
                $stmt = $conexion->conexion->prepare($sql);
                $stmt->bindValue("id", $this->getId(), PDO::PARAM_INT);
            }
            $stmt->bindValue("fec_rev",$this->getFecha_rev()->format('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue("fec_inc",$this->getFecha_inc()->format("Y-m-d H:i:s"), PDO::PARAM_STR);
            $stmt->bindValue("coment",$this->getComentario(),PDO::PARAM_STR);
            $stmt->bindValue("prio",$this->getPrioridad(),PDO::PARAM_INT);
            $stmt->bindValue("empleado",$this->getEmpleado(),PDO::PARAM_INT);
            $stmt->bindValue("result",$this->getResuelta(),PDO::PARAM_BOOL);
            $stmt->bindValue("cod_usuario",$this->getUsuario(),PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            //Muestra error y devuelve false
            error_log("Error al grabar incidencia: " . $e->getMessage());
            return false;
        }

    }

    public function cargarPendientes(int $empleado=0): array{
        $resultado = array_filter(
            $this->cargarFechas(),
            function ($registro) use($empleado) {
                return $registro['RESUELTA'] == 0 && 
                ($empleado==0 || $registro['COD_EMPLEADO'] == $empleado);
            });
        return $resultado;
    }

    public function cargarResueltas(int $empleado=0): array{
        $resultado = array_filter(
            $this->cargarFechas(),
            function ($registro) use ($empleado) {
                return $registro['RESUELTA'] == 1 && ($empleado==0 || $registro['COD_EMPLEADO'] == $empleado);
            });
        return $resultado;
    }
    
    public function cargar(int $codIncidencia):bool{
        $conexion= new Conexion();
        try{
            $sql="SELECT * FROM tincidencia WHERE ID = :id";
            $stmt = $conexion->conexion->prepare($sql);
            $stmt->bindValue('id',$codIncidencia,PDO::PARAM_INT);
            $stmt->execute();
            $resultado=$stmt->fetch(PDO::FETCH_ASSOC);
            $this->setId($resultado['ID']);
            $this->fecha_rev = new DateTime($resultado['FECHA_REV']);
            $this->fecha_inc = new DateTime($resultado['FECHA_INC']);
            $this->comentario = $resultado['COMENTARIO'];
            $this->prioridad = $resultado['PRIORIDAD'];
            $this->empleado = $resultado['COD_EMPLEADO'];
            $this->resuelta = $resultado['RESUELTA'];
            $this->cod_usuario = $resultado['COD_USUARIO'];
            return true;
        }catch(PDOException $e){
            //Muestra error y devuelve false
            error_log("Error al cargar incidencia: " . $e->getMessage());
            return false;
        }
    }
    public function cargarFechas(DateTime $fechaInim = null, DateTime $fechaFin = null): array{
        $conexion= new Conexion();
        $fechaInim = $fechaInim ?? new DateTime("2025-1-1 0:0:0");
        $fechaFin = $fechaFin ?? new DateTime("2999-12-31 0:0:0");
        try {
            $sql="SELECT * FROM tincidencia WHERE FECHA_REV BETWEEN :fecI AND :fecF ORDER BY FECHA_REV";
            $stmt = $conexion->conexion->prepare($sql);
            $stmt->bindValue("fecI",$fechaInim->format("Y-m-d H:i:s"),PDO::PARAM_STR);
            $stmt->bindValue("fecF",$fechaFin->format("Y-m-d H:i:s"),PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            //Muestra error y devuelve false
            error_log("Error al cargar incidencia: " . $e->getMessage());
            return [];
        }
    }
    
    //<<<<<<<<<<<<<<<<<<<<<< GETTERS Y SETTERS >>>>>>>>>>>>>>>>>>

    public function getId(): int{
        return $this->id;
    }

    public function setId(int $id){
        $this->id=$id;
    }
    public function getFecha_rev(): DateTime{
        return $this->fecha_rev;
    }
    public function setFecha_rev(DateTime $fecha){
        $this->fecha_rev=$fecha;
    }
    public function getFecha_inc(): DateTime{
        return $this->fecha_inc;
    }
    public function setFecha_inc(DateTime $fecha){
        $this->fecha_inc=$fecha;
    }
    public function getComentario(): string{
        return $this->comentario;
    }
    public function setComentario(string $comentario){
        $this->comentario=$comentario;
    }
    public function getPrioridad(): int{
        return $this->prioridad;
    }
    public function setPrioridad(int $prioridad){
        $this->prioridad=$prioridad;
    }
    public function getEmpleado(): int{
        return $this->empleado;
    }
    public function setEmpleado(int $empleado){
        $this->empleado=$empleado;
    }
    public function getResuelta(): bool{
        return $this->resuelta;
    }
    public function setResuelta(bool $resuelta){
        $this->resuelta=$resuelta;
    }

    public function getUsuario(): int{
        return $this->cod_usuario;
    }

    public function setUsuario($usuario){
        $this->cod_usuario=$usuario;
    }



}