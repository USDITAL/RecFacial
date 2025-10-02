<?php

namespace Clases;

//Importamos la clase PDO y PDOException para conectar con la BBDD
use PDO;
use PDOException;
//Definimos la clase Conexion
class Conexion {
    //Atributos de la clase
    private $host;
    private $db;
    private $user;
    private $pass;
    private $dsn;
    public $conexion;
    
    //Constructor de la clase
    public function __construct(){
        //Compruebo el sistema operativo para saber la ruta del archivo de conexión
        $so = PHP_OS;
        if (stripos($so, 'WIN') !== false) {
            $ruta_clave = 'c:/xampp/conexion.txt';
        } else {
            $ruta_clave = '/var/www/conexion.txt';
        }

        //Compruebo si existe el archivo de conexión
        if (!file_exists($ruta_clave)) {
            die("Error: No se encontró el archivo de la conexión a la base de datos.");
        } else {
            //Leo los datos de conexión desde el archivo
            $datos = file($ruta_clave);
            //Vuelco los datos eliminando espacios en blanco, saltos de línea, etc...
            $this->host = trim($datos[0]);
            $this->db = trim($datos[1]);
            $this->user = trim($datos[2]);
            $this->pass = trim($datos[3]);
            //Ceo la cadena de conexión
            $this->dsn = "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4";
            //Creo la conexión con la base de datos
            $this->crearConexion();
        }
    }

    //Método para crear la conexión con la base de datos
    //Dejo método público durante desarrollo
    public function crearConexion() {
        try {
            //Creo la conexión con la base de datos
            $this->conexion = new PDO($this->dsn,
                $this->user,
                $this->pass
            );
            //Establezco el modo de error de la conexión
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            //En caso de error termina y muestra mensaje
            die("Error al conectar a la base de datos: " . $e->getMessage());
        }
        //Devuelvo la conexión
        return $this->conexion;
    }
}
?>