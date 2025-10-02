<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use Clases\Empleado;
use DateTime;

class EmpleadoTest extends TestCase
{
    private $empleado;

    protected function setUp(): void
    {
        $this->empleado = new Empleado();
    }

    /** Prueba la creación básica de un empleado */
    public function testCreacionEmpleado()
    {
        $this->assertInstanceOf(Empleado::class, $this->empleado);
        $this->assertEquals(0, $this->empleado->getCodEmpleado());
    }

    /** Prueba los getters y setters básicos */
    public function testGettersYSetters()
    {
        $this->empleado->setNombre("Juan");
        $this->empleado->setApellido1("Pérez");
        $this->empleado->setApellido2("Gómez");
        
        $this->assertEquals("Juan", $this->empleado->getNombre());
        $this->assertEquals("Pérez", $this->empleado->getApellido1());
        $this->assertEquals("Gómez", $this->empleado->getApellido2());
    }

    /** Prueba el formato de fechas */
    public function testFechas()
    {
        $fecha = new DateTime('2023-01-01');
        $this->empleado->setFecAlta($fecha);
        $this->assertEquals($fecha, $this->empleado->getFecAlta());
    }

    /** Prueba la carga de datos desde la base de datos */
    public function testCargarDatosEmpleado()
    {
        // Asume que existe un empleado con ID=1 en tu BD de pruebas
        $resultado = $this->empleado->cargarDatosEmpleado(1);
        
        $this->assertTrue($resultado);
        $this->assertGreaterThan(0, $this->empleado->getCodEmpleado());
    }

    /** Prueba el grabado de un nuevo empleado */
    public function testGrabarNuevoEmpleado()
{
    // Asume que existe un usuario con ID=1
    $this->empleado->setCodUsuario(5); 
    $this->empleado->setNombre("Ana");
    $this->empleado->setApellido1("López");
    $this->empleado->setFecAlta(new DateTime());
    
    $resultado = $this->empleado->grabar();
    $this->assertTrue($resultado);
    
    // Limpieza
    if ($resultado && $this->empleado->getCodEmpleado() > 0) {
        $this->empleado->eliminar();
    }
}

    /** Prueba el listado de empleados */
    public function testListarEmpleados()
    {
        $empleados = $this->empleado->listarEmpleados();
        $this->assertIsArray($empleados);
    }
}