<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use Clases\Usuario;
use Clases\Empleado;
use Clases\Marcaje;
use Clases\Conexion;
use DateTime;

class IntegracionTest extends TestCase
{
    private static $testUserId;
    private static $testEmpleadoId;
    private static $testMarcajeIds = [];
    private static $testInitialized = false;

    protected function setUp(): void
    {
        if (!self::$testInitialized) {
            $this->initializeTestEnvironment();
            self::$testInitialized = true;
        }
    }

    private function initializeTestEnvironment(): void
    {
        try {
            $conexion = new Conexion();
            $conexion->conexion->beginTransaction();

            // 1. Crear usuario de prueba
            $usuario = new Usuario();
            $usuario->setNomLogin('test_integracion');
            $usuario->setDesContrasena('test_pass');
            $usuario->setDesCorreo('test_integracion@example.com');
            $usuario->setFecAlta(new DateTime());
            $usuario->setNomUsuarioAlta('system');
            $usuario->grabar();
            self::$testUserId = $usuario->getCodUsuario();
            $usuario->setRol(3);

            // 2. Crear empleado de prueba asociado al usuario
            $empleado = new Empleado();
            $empleado->setCodUsuario(self::$testUserId);
            $empleado->setNombre("Empleado");
            $empleado->setApellido1("Prueba");
            $empleado->setApellido2("Integración");
            $empleado->setFecAlta(new DateTime());
            $empleado->grabar();
            self::$testEmpleadoId = $empleado->getCodEmpleado();

            $conexion->conexion->commit();
        } catch (\Exception $e) {
            $conexion->conexion->rollBack();
            throw new \RuntimeException("Error al inicializar entorno de prueba: " . $e->getMessage());
        }
    }

    public static function tearDownAfterClass(): void
    {
        /*try {
            $conexion = new Conexion();
            $conexion->conexion->beginTransaction();

            // Limpiar marcajes de prueba
            if (!empty(self::$testMarcajeIds)) {
                $placeholders = implode(',', array_fill(0, count(self::$testMarcajeIds), '?'));
                $stmt = $conexion->conexion->prepare(
                    "DELETE FROM tmarcaje WHERE COD_MARCAJE IN ($placeholders)"
                );
                $stmt->execute(self::$testMarcajeIds);
            }

            // Limpiar empleado de prueba
            if (self::$testEmpleadoId) {
                $conexion->conexion->exec(
                    "DELETE FROM templeado WHERE COD_EMPLEADO = " . self::$testEmpleadoId
                );
            }

            // Limpiar usuario de prueba
            if (self::$testUserId) {
                $conexion->conexion->exec(
                    "DELETE FROM tusuario WHERE COD_USUARIO = " . self::$testUserId
                );
            }

            $conexion->conexion->commit();
        } catch (\Exception $e) {
            $conexion->conexion->rollBack();
            error_log("Error en limpieza de pruebas de integración: " . $e->getMessage());
        }*/
    }

    /** Prueba el flujo completo: usuario -> empleado -> marcaje */
    public function testFlujoCompleto()
    {
        // 1. Verificar que el usuario se creó correctamente
        $usuario = new Usuario();
        $usuario->cargarUsuario(self::$testUserId);
        $this->assertEquals(self::$testUserId, $usuario->getCodUsuario(), "El usuario de prueba no se cargó correctamente");
        $this->assertEquals('test_integracion', $usuario->getNomLogin(), "El nombre de usuario no coincide");

        // 2. Verificar que el empleado se creó correctamente y está asociado al usuario
        $empleado = new Empleado();
        $empleado->cargarDatosEmpleado(self::$testEmpleadoId);
        $this->assertEquals(self::$testEmpleadoId, $empleado->getCodEmpleado(), "El empleado de prueba no se cargó correctamente");
        $this->assertEquals(self::$testUserId, $empleado->getCodUsuario(), "El empleado no está asociado al usuario correcto");

        // 3. Crear marcaje de entrada
        $marcajeEntrada = new Marcaje();
        $hoy = new DateTime();
        $fechaEntrada = $hoy->format('Y-m-d 08:00:00');
        
        $resultadoEntrada = $marcajeEntrada->marcar(
            1, // Tipo marcaje (entrada)
            self::$testEmpleadoId,
            14, // cod_bio
            $fechaEntrada,
            $hoy->format('Y-m-d H:i:s'),
            false, // incidencia
            false, // pendiente
            '', // foto
            1, // tipo_acceso (normal)
            'Prueba integración entrada'
        );
        
        $this->assertTrue($resultadoEntrada, "Fallo al grabar el marcaje de entrada");
        $this->assertGreaterThan(0, $marcajeEntrada->getCodMarcaje(), "ID de marcaje no válido");
        self::$testMarcajeIds[] = $marcajeEntrada->getCodMarcaje();

        // 4. Crear marcaje de salida
        $marcajeSalida = new Marcaje();
        $fechaSalida = $hoy->format('Y-m-d 16:00:00');
        
        $resultadoSalida = $marcajeSalida->marcar(
            2, // Tipo marcaje (salida)
            self::$testEmpleadoId,
            14, // cod_bio
            $fechaSalida,
            $hoy->format('Y-m-d H:i:s'),
            false, // incidencia
            false, // pendiente
            '', // foto
            1, // tipo_acceso (normal)
            'Prueba integración salida'
        );
        
        $this->assertTrue($resultadoSalida, "Fallo al grabar el marcaje de salida");
        $this->assertGreaterThan(0, $marcajeSalida->getCodMarcaje(), "ID de marcaje no válido");
        self::$testMarcajeIds[] = $marcajeSalida->getCodMarcaje();

        // 5. Calcular horas trabajadas
        $marcaje = new Marcaje();
        $horasTrabajadas = $marcaje->calcularHorasTrabajadas(
            self::$testEmpleadoId,
            $hoy,
            0, // cMin
            90 // cMax
        );
        
        $this->assertEquals(8.0, $horasTrabajadas, "El cálculo de horas no es correcto");

        // 6. Verificar que los marcajes se pueden recuperar
        $marcajesHoy = $marcaje->marcajesHoy(self::$testEmpleadoId, $hoy);
        $this->assertCount(2, $marcajesHoy, "No se recuperaron los 2 marcajes esperados");
    }

    /** Prueba el inicio de sesión y marcaje */
    public function testInicioSesionYMarcaje()
    {
        // 1. Iniciar sesión con el usuario de prueba
        $usuario = new Usuario();
        $resultadoLogin = $usuario->iniciarSesion('test_integracion', 'test_pass');
        $this->assertTrue($resultadoLogin, "El inicio de sesión falló");

        // 2. Verificar que el empleado está asociado
        $empleado = new Empleado();
        $empleado->cargarDatosEmpleado(self::$testEmpleadoId);
        $this->assertEquals(self::$testUserId, $empleado->getCodUsuario(), "El empleado no está asociado al usuario correcto");

        // 3. Crear un marcaje
        $marcaje = new Marcaje();
        $hoy = new DateTime();
        $fechaMarcaje = $hoy->format('Y-m-d 12:00:00');
        
        $resultadoMarcaje = $marcaje->marcar(
            1, // Tipo marcaje (pausa)
            self::$testEmpleadoId,
            14, // cod_bio
            $fechaMarcaje,
            $hoy->format('Y-m-d H:i:s'),
            false, // incidencia
            false, // pendiente
            '', // foto
            1, // tipo_acceso (normal)
            'Prueba integración pausa'
        );
        
        $this->assertTrue($resultadoMarcaje, "Fallo al grabar el marcaje de pausa");
        $this->assertGreaterThan(0, $marcaje->getCodMarcaje(), "ID de marcaje no válido");
        self::$testMarcajeIds[] = $marcaje->getCodMarcaje();
    }
}