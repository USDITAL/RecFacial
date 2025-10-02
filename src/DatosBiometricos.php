<?php

namespace Clases;
//Cargo librerías
use DateTime;
use PDO;            //Librerías PDO
use PDOException;

class DatosBiometricos{

    // Atributos
    private int $cod_bio;
    private int $cod_Empleado;
    private int $cod_Tipo;
    private string $dato_Bio;
    private DateTime $fec_Alta;
    private string $nom_Usuario_Alta;

    

    // Constructor
    public function __construct() {
        $this->cod_bio=0; //no puede ser null
    }

    // Método para convertir fechas de UTC a Europe/Madrid
    private function convertirFecha(string $fechaUTC): string {
        $fecha = new DateTime($fechaUTC, new DateTimeZone('UTC'));
        $fecha->setTimezone(new DateTimeZone('Europe/Madrid'));
        return $fecha->format('Y-m-d H:i:s');
    }
    

   //Método para grabar en la base de datos un nuevo registro a partir del objeto
    public function grabar(): bool {
        // Crear la conexión
        try{
        $conexion = new Conexion();
        //Si no hay cod_bio se define un INSERT
        if ($this->cod_bio==0 || is_null($this->cod_bio)){
            // Crear la sentencia SQL
            $sql = "INSERT INTO tbio (COD_EMPLEADO, COD_TIPO_BIO, DATO_BIO, FEC_ALTA, NOM_USUARIO_ALTA) 
            VALUES (:cod_Empleado, :cod_Tipo, :dato_Bio, :fec_Alta, :nom_Usuario_Alta)";
            // Preparar la sentencia
            $stmt = $conexion->conexion->prepare($sql);
        //Si hay cod_bio prepara un UPDATE
        } else {
            //crea la sentencia
            $sql="UPDATE tbio SET COD_EMPLEADO = :cod_Empleado, COD_TIPO_BIO = :cod_Tipo, DATO_BIO = :dato_Bio, 
            FEC_ALTA = :fec_Alta, NOM_USUARIO_ALTA = :nom_Usuario_Alta 
            WHERE COD_BIO = :cod_Bio";
            //Prepara la sentencia
            $stmt = $conexion->conexion->prepare($sql);
            //Añade el parámetro cod_bio
            $stmt->bindValue('cod_Bio', $this->cod_bio, PDO::PARAM_INT);
        }
        // Asignar valores a los parámetros
        $stmt->bindValue('cod_Empleado', $this->cod_Empleado, PDO::PARAM_INT);
        $stmt->bindValue('cod_Tipo', $this->cod_Tipo, PDO::PARAM_INT);
        $stmt->bindValue('dato_Bio', $this->dato_Bio, PDO::PARAM_STR);
        $stmt->bindValue('fec_Alta', $this->fec_Alta->format('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmt->bindValue('nom_Usuario_Alta', $this->nom_Usuario_Alta, PDO::PARAM_STR);
        // Ejecutar la sentencia
        $stmt->execute();
        // Devolver el resultado de la sentencia
        return $stmt->rowCount() > 0;
        } catch(PDOException $e){
            //En caso de error muestra mensaje
            echo "Error al grabar:".$e;
        }
    }

    //Método para eliminar un registro de la base de datos a partir del objeto
    public function eliminar(): bool {
        // Crear la conexión
        $conexion = new Conexion();
        // Crear la sentencia SQL
        $sql = "DELETE FROM tbio WHERE COD_BIO = :cod_bio";
        // Preparar la sentencia
        $stmt = $conexion->conexion->prepare($sql);
        // Asignar valores a los parámetros
        $stmt->bindValue(':cod_bio', $this->cod_bio, PDO::PARAM_INT);
        // Ejecutar la sentencia
        $stmt->execute();
        // Devolver el resultado de la sentencia
        return $stmt->rowCount() > 0;
    }

    //Método para cargar de la base de datos un registro a partir del código
    public function cargar(int $cod_bio): ?DatosBiometricos {
        // Crear la conexión
        $conexion = new Conexion();
        // Crear la sentencia SQL
        $sql = "SELECT * FROM tbio WHERE COD_BIO = :cod_bio";
        // Preparar la sentencia
        $stmt = $conexion->conexion->prepare($sql);
        // Asignar valores a los parámetros
        $stmt->bindValue(':cod_bio', $cod_bio, PDO::PARAM_INT);
        // Ejecutar la sentencia
        $stmt->execute();
        // Obtener el resultado de la sentencia
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        // Si no hay resultado, devolver null
        if (!$resultado) {
            return null;
        }
        // Asignar los valores a los atributos del objeto
        $this->setCodBio($resultado['COD_BIO']);
        $this->setCodEmpleado($resultado['COD_EMPLEADO']);
        $this->setCodTipo($resultado['COD_TIPO_BIO']);
        $this->setDatoBio($resultado['DATO_BIO']);
        $this->setFecAlta(new DateTime($resultado['FEC_ALTA']));
        $this->setNomUsuarioAlta($resultado['NOM_USUARIO_ALTA']);
    
        // Devolver la instancia de DatosBiometricos
        return $this;
    }

    //Método para obtener todos los registros por tipo
    public static function biosPorEmpleado(int $cod_empleado): array {
        //Crea la conexión  
        $conexion = new Conexion();
        //Define la sentencia SQL
        $sql = "SELECT * FROM tbio WHERE COD_EMPLEADO = :cod_empleado";
        //Preparamos la consulta
        $stmt = $conexion->conexion->prepare($sql);
        //Parámetros
        $stmt->bindValue(':cod_empleado', $cod_empleado, PDO::PARAM_INT);
        //Ejecuta
        $stmt->execute();
        //Vuelca los resultados
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //Devuelve un array de objetos DatosBiometricos
        return $resultados;
    }
    //Método para obtener todos los registros por tipo
    public static function listarPorTipo(int $cod_tipo): array {
        //Crea la conexión  
        $conexion = new Conexion();
        //Define la sentencia SQL
        $sql = "SELECT * FROM tbio WHERE COD_TIPO_BIO = :cod_tipo";
        //Preparamos la consulta
        $stmt = $conexion->conexion->prepare($sql);
        //Parámetros
        $stmt->bindValue(':cod_tipo', $cod_tipo, PDO::PARAM_INT);
        //Ejecuta
        $stmt->execute();
        //Vuelca los resultados
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //Defino un array para almacenar los objetos de tipo DatosBiometricos
        $lista = [];
        //Recorre los resultados
        foreach ($resultados as $resultado) {
            //Crea un objeto y vuelca los datos en él
            $datosBiometricos = new DatosBiometricos();
            $datosBiometricos->setCodBio($resultado['COD_BIO']);
            $datosBiometricos->setCodEmpleado($resultado['COD_EMPLEADO']);
            $datosBiometricos->setCodTipo($resultado['COD_TIPO_BIO']);
            $datosBiometricos->setDatoBio($resultado['DATO_BIO']);
            $datosBiometricos->setFecAlta(new DateTime($resultado['FEC_ALTA']));
            $datosBiometricos->setNomUsuarioAlta($resultado['NOM_USUARIO_ALTA']);
            //Se añade a la lista
            $lista[] = $datosBiometricos;
        }
    //Devuelve un array de objetos DatosBiometricos
    return $lista;
}

//<<<<<<<<<<<<<<<<<<<<< GETTERS Y SETTERS >>>>>>>>>>>>>>>>>>>>>>>>>>

    // Getters
public function getCodBio(): int {
    return $this->cod_bio;
}

public function getCodEmpleado(): int {
    return $this->cod_Empleado;
}

public function getCodTipo(): int {
    return $this->cod_Tipo;
}

public function getDatoBio(): string {
    return $this->dato_Bio;
}

public function getFecAlta(): DateTime {
    return $this->fec_Alta;
}

public function getNomUsuarioAlta(): string {
    return $this->nom_Usuario_Alta;
}

// Setters
public function setCodBio(int $cod_bio): void {
    $this->cod_bio = $cod_bio;
}

public function setCodEmpleado(int $cod_Empleado): void {
    $this->cod_Empleado = $cod_Empleado;
}

public function setCodTipo(int $cod_Tipo): void {
    $this->cod_Tipo = $cod_Tipo;
}

public function setDatoBio(string $dato_Bio): void {
    $this->dato_Bio = $dato_Bio;
}

public function setFecAlta(DateTime $fec_Alta): void {
    $this->fec_Alta = $fec_Alta;
}

public function setNomUsuarioAlta(string $nom_Usuario_Alta): void {
    $this->nom_Usuario_Alta = $nom_Usuario_Alta;
}
}