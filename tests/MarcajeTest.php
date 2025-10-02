<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use Clases\Marcaje;
use Clases\Empleado;
use Clases\Conexion;
use DateTime;
use PDOException;

class MarcajeTest extends TestCase
{
    private $marcaje;
    private static $testEmpleadoId = null;
    private static $testInitialized = false;

    protected function setUp(): void
    {
        $this->marcaje = new Marcaje();
        
        if (!self::$testInitialized) {
            $this->initializeTestEnvironment();
            self::$testInitialized = true;
        }
    }

    private function initializeTestEnvironment(): void
{
    try {
        $conexion = new Conexion();
        
        // 1. Crear o obtener usuario de prueba
        $usuarioTestId = $this->getOrCreateTestUser($conexion);
        
        // 2. Crear empleado de prueba asociado a ese usuario
        $empleado = new Empleado();
        $empleado->cargarDatosEmpleado(1);
        $existingEmpleadoId = ($empleado->getCodEmpleado()>0)?true:false;
        
        if (!$existingEmpleadoId) {
            
            self::$testEmpleadoId = $empleado->getCodEmpleado();
            error_log("Empleado de prueba creado con ID: ".self::$testEmpleadoId);
        } else {
            self::$testEmpleadoId = $existingEmpleadoId;
            error_log("Usando empleado de prueba existente con ID: ".self::$testEmpleadoId);
        }
        
        // 3. Verificar que el empleado es accesible
        $empleadoVerif = new Empleado();
        if (!$empleadoVerif->cargarDatosEmpleado(self::$testEmpleadoId)) {
            throw new \RuntimeException("No se pudo cargar el empleado de prueba");
        }
        
    } catch (PDOException $e) {
        throw new \RuntimeException(
            "Error al inicializar entorno de prueba: ".$e->getMessage()
        );
    }
}

private function getOrCreateTestUser($conexion): int
{
    // Buscar usuario de prueba existente
    $stmt = $conexion->conexion->prepare(
        "SELECT COD_USUARIO FROM tusuario 
         WHERE NOM_LOGIN = 'TEST_USER_UNITARIO' LIMIT 1"
    );
    $stmt->execute();
    $existingUserId = $stmt->fetchColumn();
    
    if ($existingUserId) {
        return $existingUserId;
    }
    
    // Crear nuevo usuario de prueba
    $conexion->conexion->beginTransaction();
    try {
        // Insertar usuario
        $conexion->conexion->exec("
            INSERT INTO tusuario 
            (NOM_LOGIN, DES_CONTRASENA, NOM_USUARIO_ALTA, FEC_ALTA) 
            VALUES 
            ('TEST_USER_UNITARIO', '".password_hash('testpass', PASSWORD_DEFAULT)."', 'TEST_SYSTEM', NOW())
        ");
        $userId = $conexion->conexion->lastInsertId();
        
        $conexion->conexion->commit();
        return $userId;
        
    } catch (PDOException $e) {
        $conexion->conexion->rollBack();
        throw new \RuntimeException("No se pudo crear usuario de prueba: ".$e->getMessage());
    }
}

    public static function tearDownAfterClass(): void
    {
        // Limpieza opcional (descomentar si se desea)
        /*
        if (self::$testEmpleadoId) {
            try {
                $conexion = new Conexion();
                // Eliminar marcajes del empleado
                $conexion->conexion->exec(
                    "DELETE FROM tmarcaje WHERE COD_EMPLEADO = ".self::$testEmpleadoId
                );
                // Eliminar empleado
                $conexion->conexion->exec(
                    "DELETE FROM templeado WHERE COD_EMPLEADO = ".self::$testEmpleadoId
                );
                error_log("Datos de prueba eliminados correctamente");
            } catch (PDOException $e) {
                error_log("Error en limpieza: ".$e->getMessage());
            }
        }
        */
    }

    /** Prueba la creación de un marcaje */
    public function testGrabarMarcaje()
    {
        $this->assertNotNull(self::$testEmpleadoId, "Empleado de prueba no disponible");
        
        // Configurar marcaje
        $this->marcaje->setCodTipoMarcaje(1); // Entrada
        $this->marcaje->setCodEmpleado(self::$testEmpleadoId);
        $this->marcaje->setFecMarcaje(new DateTime());
        $this->marcaje->setFecGrabacion(new DateTime());
        $this->marcaje->setIncidencia(false);
        $this->marcaje->setPendiente(false);
        $this->marcaje->setTipoAcceso('1');
        $this->marcaje->setFoto('');
        $this->marcaje->setObs('Prueba unitaria');
        $this->marcaje->setCodBio(14);
        
        // Ejecutar prueba
        $resultado = $this->marcaje->grabar();
        $this->assertTrue($resultado, "Fallo al grabar el marcaje");
        
        // Verificar que se creó
        $marcajeId = $this->marcaje->getCodMarcaje();
        $this->assertGreaterThan(0, $marcajeId, "ID de marcaje no válido".$marcajeId);
        
        // Limpieza
        $conexion = new Conexion();
        $conexion->conexion->exec("DELETE FROM tmarcaje WHERE COD_MARCAJE = $marcajeId");
    }

    /** Prueba el cálculo de horas trabajadas */
public function testCalcularHorasTrabajadas()
{
    $this->assertNotNull(self::$testEmpleadoId, "Empleado de prueba no disponible");
    
    $hoy = new DateTime();
    
    // Crear marcaje de entrada usando el método marcar() de la clase Marcaje
    $marcajeEntrada = new Marcaje();
    $marcajeEntrada->marcar(
        1, // Tipo marcaje (entrada)
        self::$testEmpleadoId,
        14, // cod_bio
        $hoy->format('Y-m-d 09:00:00'), // fec_Mar
        $hoy->format('Y-m-d H:i:s'), // fec_Grab
        false, // incidencia
        false, // pendiente
        '', // foto
        1, // tipo_acceso (normal)
        'Prueba unitaria entrada' // obs
    );
    
    // Crear marcaje de salida usando el método marcar()
    $marcajeSalida = new Marcaje();
    $marcajeSalida->marcar(
        2, // Tipo marcaje (salida)
        self::$testEmpleadoId,
        14, // cod_bio
        $hoy->format('Y-m-d 11:00:00'), // fec_Mar
        $hoy->format('Y-m-d H:i:s'), // fec_Grab
        false, // incidencia
        false, // pendiente
        '', // foto
        1, // tipo_acceso (normal)
        'Prueba unitaria salida' // obs
    );
    
    // Ejecutar prueba del cálculo de horas
    $horas = $this->marcaje->calcularHorasTrabajadas(
        self::$testEmpleadoId, 
        $hoy, 
        0, // cMin
        90  // cMax
    );
    
    $this->assertEquals(2.0, $horas, "El cálculo de horas no es correcto");
    
    // Limpieza - usar método eliminar si existe, o directamente desde Marcaje
    $this->cleanTestMarkings(self::$testEmpleadoId, $hoy);
}

private function cleanTestMarkings(int $empleadoId, DateTime $fecha): void
{
    /*try {
        
        // Obtener marcajes del día usando el método de la clase
        $marcajes = $this->marcaje->marcajesHoy($empleadoId, $fecha);
        
        // Eliminar cada marcaje
        foreach ($marcajes as $m) {
            $marcaje = new Marcaje();
            $marcaje->cargar($m['COD_MARCAJE']);
            
            // Si existe método eliminar en la clase Marcaje:
            if (method_exists($marcaje, 'eliminar')) {
                $marcaje->eliminar();
            } 
            // Alternativa si no existe método eliminar:
            else {
                $conexion = new Conexion();
                $conexion->conexion->exec(
                    "DELETE FROM tmarcaje WHERE COD_MARCAJE = ".$m['COD_MARCAJE']
                );
            }
        }
    } catch (PDOException $e) {
        error_log("Error en limpieza de marcajes: ".$e->getMessage());
    }*/
}

    /** Prueba la obtención de marcajes de un día */
    public function testMarcajesHoy()
    {
        $this->assertNotNull(self::$testEmpleadoId, "Empleado de prueba no disponible");
        
        $conexion = new Conexion();
        $hoy = new DateTime();
        
        $conexion->conexion->beginTransaction();
        
        try {
            // Insertar marcaje de prueba
            $conexion->conexion->exec("
                INSERT INTO tmarcaje 
                (COD_TIPO_MARCAJE, COD_EMPLEADO, FEC_MARCAJE, FEC_GRABACION, COD_TIPO_ACCESO) 
                VALUES 
                (1, ".self::$testEmpleadoId.", '".$hoy->format('Y-m-d H:i:s')."', NOW(), 1)
            ");
            
            // Ejecutar prueba
            $marcajes = $this->marcaje->marcajesHoy(self::$testEmpleadoId, $hoy);
            
            $this->assertIsArray($marcajes, "No se recibió un array de marcajes");
            $this->assertNotEmpty($marcajes, "El array de marcajes está vacío");
            
        } finally {
            // Limpieza
            $conexion->conexion->rollBack();
        }
    }
}