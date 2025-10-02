<?php

namespace Clases;
//Librerías
use DateTime;
use PDO;
use PDOException;
class Empleado {
    // Atributos
    private int $cod_Empleado;
    private int $cod_Usuario;
    private string $nombre;
    private string $apellido1;
    private string $apellido2;
    private string $contacto;
    private DateTime $fec_Alta;
    private string $nom_Usuario_Alta;
    private ?DateTime $fec_Baja;//Opcional
    private ?string $nom_Usuario_Baja;//Opcional
    private string $foto_Empleado;
    private string $horario;
    private bool $flex;
    private int $maxHorasDia;
    private float $bolsa;

    // Método constructor
    public function __construct() {
        //Inicia datos necesarios
        $this->cod_Empleado= 0;
        $this->fec_Baja = null;
        $this->nom_Usuario_Baja = null;
        $this->foto_Empleado="";
        $this->maxHorasDia=0;
        $this->cod_Usuario=0;
        $this->nombre="";
        $this->apellido1="";
        $this->apellido2="";
        $this->contacto="";
        $this->fec_Alta = new DateTime();
        $this->nom_Usuario_Alta = "";
        $this->horario = "";
        $this->flex = false;
        $this->bolsa = 0;

    }

    

    // Método para cargar datos de un determinado empleado de la base de datos por usuario
    public function cargarDatosPorUsuario(int $cod_Usuario): bool {
        try {
            //Crea la conexión
            $conexion = new Conexion();
            //Define la consulta
            $sql = "SELECT * FROM templeado WHERE COD_USUARIO = :cod_Usuario";
            //La prepara
            $stmt = $conexion->conexion->prepare($sql);
            //Parametriza
            $stmt->bindValue(':cod_Usuario', $cod_Usuario, PDO::PARAM_INT);
            //Ejecuta la consulta
            $stmt->execute();
            //Vuelca los resultados
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$resultado) {
                //Si no hay resultados devuelve false
                return false;
            }
            //Vuelca los resultados en los parámetros del objeto
            $this->cod_Empleado = $resultado['COD_EMPLEADO'];
            $this->cod_Usuario = $resultado['COD_USUARIO'];
            $this->nombre = $resultado['NOM_EMPLEADO'];
            $this->apellido1 = $resultado['APE1_EMPLEADO'];
            $this->apellido2 = $resultado['APE2_EMPLEADO'];
            $this->contacto = $resultado['CONTACTO_EMPLEADO'];
            $this->fec_Alta = new DateTime($resultado['FEC_ALTA']);
            $this->nom_Usuario_Alta = $resultado['NOM_USUARIO_ALTA'];
            $this->fec_Baja = $resultado['FEC_BAJA'] ? new DateTime($resultado['FEC_BAJA']) : null;
            $this->nom_Usuario_Baja = $resultado['NOM_USUARIO_BAJA'] ?? null;
            $this->foto_Empleado = $resultado['FOTO'] ?? "";
            $this->horario = $resultado['HORARIO'] ?? "";
            $this->flex = $resultado['FLEX'] ?? false;
            $this->maxHorasDia = $resultado['MAX_HORA_DIA'] ?? 0;
            $this->bolsa = $resultado['BOLSA_HORAS'] ?? 0;

            //Devuelve true
            return true;
        } catch (PDOException $e) {
            // Manejo de excepciones
            error_log("Error al cargar datos del empleado: " . $e->getMessage());
            return false;
        }
    }
    
    // Método para cargar datos de un determinado empleado de la base de datos
    public function cargarDatosEmpleado(int $cod_Empleado): bool {
        try {
            //Crea la conexión
            $conexion = new Conexion();
            //Define la consulta
            $sql = "SELECT * FROM templeado WHERE COD_EMPLEADO = :cod_Empleado";
            //La prepara
            $stmt = $conexion->conexion->prepare($sql);
            //Parametriza
            $stmt->bindValue(':cod_Empleado', $cod_Empleado, PDO::PARAM_INT);
            //Ejecuta la consulta
            $stmt->execute();
            //Vuelca los resultados
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$resultado) {
                //Si no hay resultados devuelve false
                return false;
            }
            //Vuelca los resultados en los parámetros del objeto
            $this->cod_Empleado = $resultado['COD_EMPLEADO'];
            $this->cod_Usuario = $resultado['COD_USUARIO'];
            $this->nombre = $resultado['NOM_EMPLEADO'];
            $this->apellido1 = $resultado['APE1_EMPLEADO'];
            $this->apellido2 = $resultado['APE2_EMPLEADO'];
            $this->contacto = $resultado['CONTACTO_EMPLEADO'];
            $this->fec_Alta = new DateTime($resultado['FEC_ALTA']);
            $this->nom_Usuario_Alta = $resultado['NOM_USUARIO_ALTA'];
            $this->fec_Baja = $resultado['FEC_BAJA'] ? new DateTime($resultado['FEC_BAJA']) : null;
            $this->nom_Usuario_Baja = $resultado['NOM_USUARIO_BAJA'] ?? null;
            if ($resultado['FOTO']){
                $this->foto_Empleado = $resultado['FOTO'];
            }
            $this->horario = $resultado['HORARIO'] ?? "";
            $this->flex = $resultado['FLEX'] ?? false;
            $this->maxHorasDia = $resultado['MAX_HORA_DIA'];
            $this->bolsa = $resultado['BOLSA_HORAS'] ?? 0;

            //Devuelve true
            return true;
        } catch (PDOException $e) {
            // Manejo de excepciones
            error_log("Error al cargar datos del empleado: " . $e->getMessage());
            return false;
        }
    }

    // Método para grabar un nuevo empleado en la base de datos
    public function grabar(): bool {
        try {
            //Crea la conexión
            $conexion = new Conexion();
            //Si no hay cod_empleado realiza un INSERT
            if ($this->cod_Empleado==0 || is_null($this->cod_Empleado)){
                $sql = "INSERT INTO templeado (COD_USUARIO, NOM_EMPLEADO, APE1_EMPLEADO, APE2_EMPLEADO, CONTACTO_EMPLEADO, FEC_ALTA, NOM_USUARIO_ALTA, FEC_BAJA, NOM_USUARIO_BAJA, FOTO, HORARIO, FLEX, MAX_HORA_DIA, BOLSA_HORAS) 
                    VALUES (:cod_Usuario, :nombre, :apellido1, :apellido2, :contacto, :fec_Alta, :nom_Usuario_Alta, :fec_Baja, :nom_Usuario_Baja, :foto, :horario, :flex, :max_dia, :bolsa)";
                $stmt = $conexion->conexion->prepare($sql);
            //En caso contrario realiza un UPDATE
            } else{
                $sql = "UPDATE templeado SET COD_USUARIO = :cod_Usuario, NOM_EMPLEADO = :nombre, APE1_EMPLEADO = :apellido1, APE2_EMPLEADO = :apellido2, CONTACTO_EMPLEADO = :contacto 
                , FEC_ALTA = :fec_Alta, NOM_USUARIO_ALTA = :nom_Usuario_Alta, FEC_BAJA=:fec_Baja, NOM_USUARIO_BAJA = :nom_Usuario_Baja, FOTO = :foto, HORARIO =:horario, FLEX = :flex
                , MAX_HORA_DIA = :max_dia, BOLSA_HORAS = :bolsa
                WHERE COD_EMPLEADO = :cod_Empleado";
                $stmt = $conexion->conexion->prepare($sql);
                $stmt->bindValue(':cod_Empleado', $this->cod_Empleado, PDO::PARAM_INT);
            }
            //Se parametriza la consulta
            $stmt->bindValue(':cod_Usuario', $this->cod_Usuario, PDO::PARAM_INT);
            $stmt->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
            $stmt->bindValue(':apellido1', $this->apellido1, PDO::PARAM_STR);
            $stmt->bindValue(':apellido2', $this->apellido2, PDO::PARAM_STR);
            $stmt->bindValue(':contacto', $this->contacto, PDO::PARAM_STR);
            $stmt->bindValue(':fec_Alta', $this->fec_Alta->format('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue(':nom_Usuario_Alta', $this->nom_Usuario_Alta, PDO::PARAM_STR);
            $stmt->bindValue(':fec_Baja', $this->fec_Baja ? $this->fec_Baja->format('Y-m-d H:i:s') : null, PDO::PARAM_STR);
            $stmt->bindValue(':nom_Usuario_Baja', $this->nom_Usuario_Baja, PDO::PARAM_STR);
            $stmt->bindValue(':foto', $this->foto_Empleado, PDO::PARAM_STR);
            $stmt->bindValue(':horario', $this->horario, PDO::PARAM_STR);
            $stmt->bindValue(':flex', $this->flex, PDO::PARAM_BOOL);
            $stmt->bindValue(':max_dia', $this->maxHorasDia, PDO::PARAM_INT);
            $stmt->bindValue(':bolsa', $this->bolsa, PDO::PARAM_INT);

            //Devuelve directamente el resultado como true o false
            $resultado = $stmt->execute();
            if ($this->cod_Empleado==0 || is_null($this->cod_Empleado)){$this->setCodEmpleado($conexion->conexion->lastInsertId());}
            return $resultado;
        } catch (PDOException $e) {
            //Si hay error devuelve false y su mensaje
            error_log("Error al grabar empleado: " . $e->getMessage());
            return false;
        }
    }

    //Método para descontar las horas Extras pagadas a la bolsa del empleado
    public function procesarHorasExtrasMensuales(int $maxHorasExtras): void {
        try {
            // Obtiene la bolsa actual
            $bolsaActual = $this->getBolsa();
    
            // Calcula las horas extras permitidas y el excedente
            $horasExtrasPermitidas = min($bolsaActual, $maxHorasExtras);
            $excedente = $bolsaActual - $horasExtrasPermitidas;
    
            // Actualiza la bolsa con el excedente
            $conexion = new Conexion();
            $sql = "UPDATE templeado SET BOLSA_HORAS = :bolsa WHERE COD_EMPLEADO = :codEmpleado";
            $stmt = $conexion->conexion->prepare($sql);
            $stmt->bindValue(':bolsa', $excedente);
            $stmt->bindValue(':codEmpleado', $this->cod_Empleado);
            $stmt->execute();
            $this->setBolsa($excedente);
            // Elimina el objeto conexión
            $conexion = null;
        } catch (PDOException $e) {
            error_log("Error al procesar horas extras mensuales: " . $e->getMessage());
        }
    }

    // Método para dar de baja a un empleado
    public function darBaja(string $nom_Usuario_Baja, DateTime $fec_Baja): bool {
        try {
            //Realiza la conexión con un update
            $conexion = new Conexion();
            $sql = "UPDATE templeado SET FEC_BAJA = :fec_Baja, NOM_USUARIO_BAJA = :nom_Usuario_Baja WHERE COD_EMPLEADO = :cod_Empleado";
            $stmt = $conexion->conexion->prepare($sql);
            //Define la fecha de baja y el usuario
            $stmt->bindValue(':cod_Empleado', $this->cod_Empleado, PDO::PARAM_INT);
            $stmt->bindValue(':fec_Baja', $fec_Baja->format('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue(':nom_Usuario_Baja', $nom_Usuario_Baja, PDO::PARAM_STR);
            //Devuelve true o false
            return $stmt->execute();
        } catch (PDOException $e) {
            //si da error devuelve false y el error
            error_log("Error al dar de baja al empleado: " . $e->getMessage());
            return false;
        }
    }

    // Método para listar todos los empleados
    public function listarEmpleados() {
        try {
            $conexion = new Conexion();
            // Prepara la consulta
            $consulta = "SELECT * FROM templeado";
            // Ejecuta la consulta
            $stmt = $conexion->conexion->prepare($consulta);
            $stmt->execute();
            // Devuelve el resultado de la consulta directamente
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            //Si hay error devuelve el error y un array vacío
            error_log("Error al listar empleados: " . $e->getMessage());
            return [];
        }
    }

    // Método para eliminar el empleado actual en la BBDD, solo para pruebas
    public function eliminar(): bool {
        try {
            //Crea una conexión y sentencia DELETE
            $conexion = new Conexion();
            $sql = "DELETE FROM templeado WHERE COD_EMPLEADO = :cod_Empleado";
            $stmt = $conexion->conexion->prepare($sql);
            //Parametriza
            $stmt->bindValue(':cod_Empleado', $this->cod_Empleado, PDO::PARAM_INT);
            //Devuelve true o false
            return $stmt->execute();
        } catch (PDOException $e) {
            //Muestra error y devuelve false
            error_log("Error al eliminar empleado: " . $e->getMessage());
            return false;
        }
    }

/*
<<<<<<<<<<<<<<<<<<<<<<<<<GETTERS Y SETTERS>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
*/
        // Getters
        public function getCodEmpleado(): int {
            return $this->cod_Empleado;
        }
    
        public function getCodUsuario(): int {
            return $this->cod_Usuario;
        }
    
        public function getNombre(): string {
            return $this->nombre;
        }
    
        public function getApellido1(): string {
            return $this->apellido1;
        }
    
        public function getApellido2(): string {
            return $this->apellido2;
        }
    
        public function getContacto(): string {
            return $this->contacto;
        }
    
        public function getFecAlta(): DateTime {
            return $this->fec_Alta;
        }
    
        public function getNomUsuarioAlta(): string {
            return $this->nom_Usuario_Alta;
        }
    
        public function getFecBaja(): ?DateTime {
            return $this->fec_Baja;
        }
    
        public function getNomUsuarioBaja(): ?string {
            return $this->nom_Usuario_Baja;
        }

        public function getFoto(): string{
            return $this->foto_Empleado;
        }
    
        public function getHorario(): string {
            return $this->horario;
        }
        
        public function getFlex(): bool {
            return $this->flex;
        }
        
        public function getMaxHorasDia(): int {
            return $this->maxHorasDia;
        }
        
        public function getBolsa(): float {
            return $this->bolsa;
        }
        // Setters
        public function setCodEmpleado(int $cod_Empleado): void {
            $this->cod_Empleado = $cod_Empleado;
        }
    
        public function setCodUsuario(int $cod_Usuario): void {
            $this->cod_Usuario = $cod_Usuario;
        }
    
        public function setNombre(string $nombre): void {
            $this->nombre = $nombre;
        }
    
        public function setApellido1(string $apellido1): void {
            $this->apellido1 = $apellido1;
        }
    
        public function setApellido2(string $apellido2): void {
            $this->apellido2 = $apellido2;
        }
    
        public function setContacto(string $contacto): void {
            $this->contacto = $contacto;
        }
    
        public function setFecAlta(DateTime $fec_Alta): void {
            $this->fec_Alta = $fec_Alta;
        }
    
        public function setNomUsuarioAlta(string $nom_Usuario_Alta): void {
            $this->nom_Usuario_Alta = $nom_Usuario_Alta;
        }

        public function setFecBaja(DateTime $fec_Baja): void {
            $this->fec_Baja = $fec_Baja;
        }
    
        public function setNomUsuarioBaja(string $nom_Usuario_Baja): void {
            $this->nom_Usuario_Baja = $nom_Usuario_Baja;
        }

        public function setFoto(string $foto):void {
            $this->foto_Empleado = $foto;
        }
        public function setHorario(string $horario): void {
            $this->horario = $horario;
        }
        
        public function setFlex(bool $flex): void {
            $this->flex = $flex;
        }
        
        public function setMaxHorasDia(int $maxHorasDia): void {
            $this->maxHorasDia = $maxHorasDia;
        }
        
        public function setBolsa(float $bolsa): void {
            $this->bolsa = $bolsa;
        }
}