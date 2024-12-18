<?php
require_once dirname(__DIR__) . '/bootstrap.php';

use PHPUnit\Framework\TestCase;

/**
 * Clase de pruebas para el controlador Home
 */
class HomeControllerTest extends TestCase
{
    private $db;
    private $homeController;

    protected function setUp(): void
    {
        parent::setUp();

        // Define constantes necesarias para pruebas
        if (!defined('TESTING')) define('TESTING', true);

        $this->db = new Database();
        echo "\nPreparando base de datos para pruebas...";
        $this->cleanDatabase();
        $this->createTestData();

        $this->homeController = new HomeController();
    }

    private function cleanDatabase()
    {
        try {
            $tables = [
                'detallespedido',
                'comprobanteventa',
                'pedidoscomanda',
                'mesas',
                'piso',
                'sede',
                'listroles',
                'usuarios',
                'personas',
                'roles'
            ];

            $this->db->beginTransaction();

            // Desactivar verificación de claves foráneas
            $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
            $this->db->execute();

            // Limpiar cada tabla
            foreach ($tables as $table) {
                $this->db->query("TRUNCATE TABLE `$table`");
                $this->db->execute();
            }

            // Reactivar verificación de claves foráneas
            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
            $this->db->execute();

            $this->db->commit();
            echo "\n✓ Base de datos limpiada";
        } catch (Exception $e) {
            $this->db->rollBack();
            echo "\nError limpiando base de datos: " . $e->getMessage();
            throw $e;
        }
    }

    private function createTestData()
    {
        try {
            echo "\nCreando datos de prueba:";

            // Crear rol
            $this->db->query('INSERT INTO roles (nombre) VALUES ("admin")');
            $this->db->execute();
            $rolId = $this->db->lastInsertId();
            echo "\n✓ Rol creado";

            // Crear persona
            $this->db->query('INSERT INTO personas (nombre, email) VALUES ("Test User", "test@test.com")');
            $this->db->execute();
            $personaId = $this->db->lastInsertId();
            echo "\n✓ Persona creada";

            // Crear usuario
            $this->db->query('INSERT INTO usuarios (persona_id, contrasena) VALUES (:persona_id, :contrasena)');
            $this->db->bind(':persona_id', $personaId);
            $this->db->bind(':contrasena', password_hash('test123', PASSWORD_DEFAULT));
            $this->db->execute();
            $usuarioId = $this->db->lastInsertId();
            echo "\n✓ Usuario creado";

            // Asignar rol
            $this->db->query('INSERT INTO listroles (usuario_id, rol_id, fecha_inicio) VALUES (:usuario_id, :rol_id, NOW())');
            $this->db->bind(':usuario_id', $usuarioId);
            $this->db->bind(':rol_id', $rolId);
            $this->db->execute();
            echo "\n✓ Rol asignado";

            // Crear sede
            $this->db->query('INSERT INTO sede (nombre, direccion) VALUES ("Test Sede", "Test Address")');
            $this->db->execute();
            echo "\n✓ Sede creada";
        } catch (Exception $e) {
            echo "\nError creando datos de prueba: " . $e->getMessage();
            throw $e;
        }
    }

    public function testRedirectWhenNotAuthenticated()
    {
        echo "\nPrueba acceso sin autenticación:";
        $_SESSION = []; // Asegúrate de que no hay sesión activa

        // Capturar salida
        $output = $this->homeController->index();

        // Verifica la redirección
        $this->assertEquals('/PIZZA4/public/auth/login', $output, "\nLa salida no coincide con la redirección esperada.");
        echo "\n✓ Redirección correcta al login";
    }

    public function testRedirectWhenNoSedes()
    {
        echo "\nPrueba acceso sin sedes:";
        $_SESSION['usuario_id'] = 1; // Simula un usuario autenticado

        // Limpiar sedes
        $this->db->query('DELETE FROM sede');
        $this->db->execute();

        // Capturar salida
        $output = $this->homeController->index();

        // Verifica la redirección
        $this->assertEquals('/PIZZA4/public/sede/registro', $output, "\nLa salida no coincide con la redirección esperada.");
        echo "\n✓ Redirección correcta a registro de sede";
    }

    protected function tearDown(): void
    {
        echo "\nLimpiando pruebas...";
        $this->cleanDatabase();
        $_SESSION = [];
        parent::tearDown();
    }
}
