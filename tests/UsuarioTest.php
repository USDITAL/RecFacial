<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use Clases\Usuario;
use Clases\Conexion;
use Clases\Rol;
use DateTime;

class UsuarioTest extends TestCase
{
    private $usuario;
    private static $testUserId;

    protected function setUp(): void
    {
        $this->usuario = new Usuario();
        
        // Crear un usuario de prueba en la BD (solo una vez)
        if (!self::$testUserId) {
            $this->usuario->setNomLogin('test_user');
            $this->usuario->setDesContrasena('test_pass');
            $this->usuario->setDesCorreo('test@example.com');
            $this->usuario->setFecAlta(new DateTime());
            $this->usuario->setNomUsuarioAlta('system');
            $this->usuario->grabar();
            self::$testUserId = $this->usuario->getCodUsuario();
        }
    }

    protected function tearDown(): void
    {
        // No eliminamos el usuario para no romper otras pruebas
        // La limpieza se hará manualmente después
    }

    public static function tearDownAfterClass(): void
{
    if (self::$testUserId) {
        $usuario = new Usuario();
        $usuario->cargarUsuario(self::$testUserId);
        $usuario->darBajaUsuario('system');
    }
}

    /** Prueba la creación básica de un usuario */
    public function testCreacionUsuario()
    {
        $this->assertInstanceOf(Usuario::class, $this->usuario);
    }

    /** Prueba los getters y setters básicos */
    public function testGettersYSetters()
    {
        $this->usuario->setNomLogin('nuevo_login');
        $this->usuario->setDesCorreo('nuevo@email.com');
        
        $this->assertEquals('nuevo_login', $this->usuario->getNomLogin());
        $this->assertEquals('nuevo@email.com', $this->usuario->getDesCorreo());
    }

    /** Prueba el hash de contraseñas */
    public function testHashContrasena()
    {
        $password = 'mi_contraseña';
        $this->usuario->setDesContrasena($password);
        
        $hash = $this->usuario->getDesContrasena();
        $this->assertTrue(password_verify($password, $hash));
    }

    /** Prueba la carga de un usuario desde la BD */
    public function testCargarUsuario()
    {
        $resultado = $this->usuario->cargarUsuario(self::$testUserId);
        $this->assertEquals(self::$testUserId, $this->usuario->getCodUsuario());
    }

    /** Prueba el inicio de sesión */
    public function testIniciarSesion()
{
    $usuario = new Usuario();
    
    
    // Probar inicio de sesión
    $resultado = $usuario->iniciarSesion('David', 'Prueba');
    $this->assertTrue($resultado);
    
}

    /** Prueba la modificación de usuario */
    public function testModificarUsuario()
    {
        $this->usuario->cargarUsuario(self::$testUserId);
        $resultado = $this->usuario->modificarUsuario('nuevo_nombre', 'nueva_pass');
        $this->assertTrue($resultado);
    }

    /** Prueba la obtención de lista de usuarios */
    public function testObtenerUsuarios()
    {
        $usuarios = $this->usuario->obtenerUsuarios();
        $this->assertIsArray($usuarios);
        $this->assertNotEmpty($usuarios);
    }

    /** Prueba la gestión de roles */
    public function testGestionRoles()
{
    // 1. Obtener un usuario existente de la base de datos
    $conexion = new Conexion();
    $stmt = $conexion->conexion->query("SELECT COD_USUARIO FROM tusuario LIMIT 1");
    $userId = $stmt->fetchColumn();
    
    // Verificar que encontramos un usuario existente
    if (!$userId || $userId <= 0) {
        $this->markTestSkipped('No hay usuarios existentes en la base de datos para probar');
        return;
    }
    
    // 2. Cargar el usuario existente
    $usuario = new Usuario();
    $usuario->cargarUsuario($userId);
    $this->assertGreaterThan(0, $usuario->getCodUsuario(), "El usuario no se cargó correctamente");
    
    // 3. Obtener un rol existente
    $stmt = $conexion->conexion->query("SELECT COD_ROL FROM trol LIMIT 1");
    $rolId = $stmt->fetchColumn();
    
    if (!$rolId || $rolId <= 0) {
        $this->markTestSkipped('No hay roles existentes en la base de datos para probar');
        return;
    }
    
    // 4. Eliminar el rol si ya está asignado (para empezar limpio)
    $deleteStmt = $conexion->conexion->prepare(
        "DELETE FROM tusuariorol WHERE COD_USUARIO = ? AND COD_ROL = ?"
    );
    $deleteStmt->execute([$userId, $rolId]);
    
    // 5. Asignar el rol
    $usuario->setRol($rolId);
    
    // Verificar que el rol se asignó
    $checkStmt = $conexion->conexion->prepare(
        "SELECT COUNT(*) FROM tusuariorol WHERE COD_USUARIO = ? AND COD_ROL = ?"
    );
    $checkStmt->execute([$userId, $rolId]);
    $this->assertEquals(1, $checkStmt->fetchColumn(), "El rol no se asignó correctamente");
    
    // 6. Quitar el rol
    $usuario->unsetRol($rolId);
    
    // Verificar que el rol se eliminó
    $checkStmt->execute([$userId, $rolId]);
    $this->assertEquals(0, $checkStmt->fetchColumn(), "El rol no se eliminó correctamente");
}
}