<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use Clases\Ajuste;

class AjusteTest extends TestCase
{
    protected $ajuste;

    protected function setUp(): void
    {
        $this->ajuste = new Ajuste();
    }

    // Prueba para obtener ajustes
    public function testObtenerAjustes()
    {
        $result = $this->ajuste->obtenerAjustes();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result); // Asume que la tabla no está vacía
    }

    // Prueba para obtener un ajuste específico
    public function testObtenerAjuste()
    {
        // Asume que existe un ajuste con ID=1 en tu BD
        $result = $this->ajuste->obtenerAjuste(1);
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['ID_AJUSTE'] ?? null);
    }

    // Prueba para crear y grabar un ajuste
    public function testCrearYGrabarAjuste()
    {
        $this->ajuste->crear("Test_Nombre", "Test_Valor", "Test_Tipo", "Test_Desc");
        $result = $this->ajuste->grabar();
        $this->assertTrue($result);
    }

    // Prueba para actualizar un ajuste
    public function testActualizarAjuste()
    {
        $result = $this->ajuste->actualizarAjuste(1, "Nombre_Actualizado", "Valor_Actualizado");
        $this->assertTrue($result);
    }
}