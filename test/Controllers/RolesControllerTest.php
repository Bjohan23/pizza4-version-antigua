<?php

use PHPUnit\Framework\TestCase;

class RolesControllerTest extends TestCase
{
    private $rolesController;
    private $db;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            if (!defined('TESTING')) define('TESTING', true);
            if (!defined('SALIR')) define('SALIR', '/PIZZA4/public/auth/login');
            if (!defined('ROL')) define('ROL', '/PIZZA4/public/roles');

            $this->db = new Database();
            echo "\nPreparando base de datos para pruebas...";
            $this->cleanDatabase();
            $this->createTestData();

            require_once dirname(__DIR__) . '/../app/Controllers/RolesController.php';
            $this->rolesController = new RolesController();
        } catch (Exception $e) {
            echo "\nError en setUp: " . $e->getMessage();
            throw $e;
        }
    }
    private function cleanDatabase()
    {
        try {
            $tables = [
                'listroles',
                'usuarios',
                'personas',
                'roles'
            ];

            $this->db->beginTransaction();
            $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
            $this->db->execute();

            foreach ($tables as $table) {
                $this->db->query("TRUNCATE TABLE `$table`");
                $this->db->execute();
            }

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

            // Crear rol inicial
            $this->db->query('INSERT INTO roles (nombre) VALUES ("Admin Test")');
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
        } catch (Exception $e) {
            echo "\nError creando datos de prueba: " . $e->getMessage();
            throw $e;
        }
    }
    public function testRedirectWhenNotAuthenticated()
    {
        try {
            echo "\nPrueba acceso sin autenticación:";
            $_SESSION = [];

            $result = $this->rolesController->index();

            $this->assertIsArray($result);
            $this->assertEquals(['redirect' => SALIR], $result);
            echo "\n✓ Redirección correcta al login";
        } catch (Exception $e) {
            echo "\nError en testRedirectWhenNotAuthenticated: " . $e->getMessage();
            throw $e;
        }
    }

    public function testIndex()
    {
        try {
            echo "\nPrueba listado de roles:";
            $_SESSION['usuario_id'] = 1;

            $result = $this->rolesController->index();

            $this->assertIsArray($result);
            $this->assertArrayHasKey('roles', $result);
            $this->assertArrayHasKey('rolUsuario', $result);
            $this->assertNotEmpty($result['roles']);
            $this->assertEquals('Admin Test', $result['roles'][0]['nombre']);
            echo "\n✓ Roles listados correctamente";
        } catch (Exception $e) {
            echo "\nError en testIndex: " . $e->getMessage();
            throw $e;
        }
    }
    public function testCreate()
    {
        try {
            echo "\nPrueba crear rol:";
            $_SESSION['usuario_id'] = 1;
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['nombre'] = 'Nuevo Rol Test';

            $this->rolesController->create();

            // Verificar que el rol se creó en la base de datos
            $this->db->query('SELECT nombre FROM roles WHERE nombre = :nombre');
            $this->db->bind(':nombre', 'Nuevo Rol Test');
            $dbResult = $this->db->single();

            $this->assertNotNull($dbResult);
            $this->assertEquals('Nuevo Rol Test', $dbResult['nombre']);
            echo "\n✓ Rol creado correctamente";
        } catch (Exception $e) {
            echo "\nError en testCreate: " . $e->getMessage();
            throw $e;
        }
    }

    public function testEdit()
    {
        try {
            echo "\nPrueba editar rol:";
            $_SESSION['usuario_id'] = 1;
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['nombre'] = 'Rol Actualizado';

            $this->rolesController->edit(1);

            // Verificar que el rol se actualizó en la base de datos
            $this->db->query('SELECT nombre FROM roles WHERE id = 1');
            $dbResult = $this->db->single();

            $this->assertNotNull($dbResult);
            $this->assertEquals('Rol Actualizado', $dbResult['nombre']);
            echo "\n✓ Rol actualizado correctamente";
        } catch (Exception $e) {
            echo "\nError en testEdit: " . $e->getMessage();
            throw $e;
        }
    }

    public function testDelete()
    {
        try {
            echo "\nPrueba eliminar rol:";
            $_SESSION['usuario_id'] = 1;

            // Crear rol para eliminar
            $this->db->query('INSERT INTO roles (nombre) VALUES ("Rol para eliminar")');
            $this->db->execute();
            $rolId = $this->db->lastInsertId();

            $this->rolesController->delete($rolId);

            // Verificar que el rol fue eliminado
            $this->db->query('SELECT COUNT(*) as count FROM roles WHERE id = :id');
            $this->db->bind(':id', $rolId);
            $dbResult = $this->db->single();

            $this->assertEquals(0, $dbResult['count']);
            echo "\n✓ Rol eliminado correctamente";
        } catch (Exception $e) {
            echo "\nError en testDelete: " . $e->getMessage();
            throw $e;
        }
    }

    protected function tearDown(): void
    {
        try {
            echo "\nLimpiando pruebas...";
            $this->cleanDatabase();
            $_SESSION = [];
            $_POST = [];
            $_SERVER['REQUEST_METHOD'] = 'GET';
            parent::tearDown();
            echo "\n✓ Limpieza completada";
        } catch (Exception $e) {
            echo "\nError en tearDown: " . $e->getMessage();
            throw $e;
        }
    }
}
