<?php

use PHPUnit\Framework\TestCase;

class MesasControllerTest extends TestCase
{
    private $mesasController;
    private $db;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('TESTING')) define('TESTING', true);
        if (!defined('LOGIN')) define('LOGIN', '/login');
        if (!defined('TABLE')) define('TABLE', '/mesas/piso/');

        $this->db = new Database();
        echo "\nPreparando base de datos para pruebas...";
        $this->cleanDatabase();
        $this->createTestData();

        $this->mesasController = new MesasController();
        echo "\n✓ Controlador MesasController inicializado";
    }

    private function cleanDatabase()
    {
        try {
            $tables = [
                'mesas',
                'piso',
                'sede',
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
                echo "\n  ✓ Tabla $table limpiada";
            }

            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
            $this->db->execute();

            $this->db->commit();
            echo "\n✓ Base de datos limpiada completamente";
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

            // Crear rol admin
            $this->db->query('INSERT INTO roles (nombre) VALUES ("admin")');
            $this->db->execute();
            $rolId = $this->db->lastInsertId();
            echo "\n  ✓ Rol admin creado con ID: $rolId";

            // Crear persona
            $this->db->query('INSERT INTO personas (nombre, email) VALUES ("Test User", "test@test.com")');
            $this->db->execute();
            $personaId = $this->db->lastInsertId();
            echo "\n  ✓ Persona creada con ID: $personaId";

            // Crear usuario
            $this->db->query('INSERT INTO usuarios (persona_id, contrasena) VALUES (:persona_id, :contrasena)');
            $this->db->bind(':persona_id', $personaId);
            $this->db->bind(':contrasena', password_hash('test123', PASSWORD_DEFAULT));
            $this->db->execute();
            $usuarioId = $this->db->lastInsertId();
            echo "\n  ✓ Usuario creado con ID: $usuarioId";

            // Asignar rol
            $this->db->query('INSERT INTO listroles (usuario_id, rol_id, fecha_inicio) VALUES (:usuario_id, :rol_id, NOW())');
            $this->db->bind(':usuario_id', $usuarioId);
            $this->db->bind(':rol_id', $rolId);
            $this->db->execute();
            echo "\n  ✓ Rol asignado al usuario";

            // Crear sede
            $this->db->query('INSERT INTO sede (nombre, direccion) VALUES ("Sede Test", "Dirección Test")');
            $this->db->execute();
            $sedeId = $this->db->lastInsertId();
            echo "\n  ✓ Sede creada con ID: $sedeId";

            // Crear piso
            $this->db->query('INSERT INTO piso (sede_id, nombre) VALUES (:sede_id, "Piso Test")');
            $this->db->bind(':sede_id', $sedeId);
            $this->db->execute();
            $pisoId = $this->db->lastInsertId();
            echo "\n  ✓ Piso creado con ID: $pisoId";

            // Crear mesa de prueba
            $this->db->query('INSERT INTO mesas (piso_id, numero, capacidad) VALUES (:piso_id, 1, 4)');
            $this->db->bind(':piso_id', $pisoId);
            $this->db->execute();
            echo "\n  ✓ Mesa creada con ID: " . $this->db->lastInsertId();

            echo "\n✓ Datos de prueba creados exitosamente";
        } catch (Exception $e) {
            echo "\nError creando datos de prueba: " . $e->getMessage();
            throw $e;
        }
    }

    public function testRedirectWhenNotAuthenticated()
    {
        echo "\nPrueba de acceso sin autenticación:";
        $_SESSION = [];

        $result = $this->mesasController->index();
        $this->assertEquals(['redirect' => LOGIN], $result);
        echo "\n✓ Redirección correcta al login";
    }

    public function testIndexWithAuthenticatedUser()
    {
        echo "\nPrueba listado de mesas:";
        $_SESSION['usuario_id'] = 1;

        $result = $this->mesasController->index();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('mesas', $result);
        $this->assertArrayHasKey('rolUsuario', $result);

        echo "\n✓ Listado recuperado correctamente";
        echo "\n✓ Estructura del resultado validada";
    }

    public function testCreate()
    {
        echo "\nPrueba crear mesa:";
        $_SESSION['usuario_id'] = 1;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'piso_id' => 1,
            'numero' => 2,
            'capacidad' => 4
        ];

        $result = $this->mesasController->create();
        $this->assertTrue($result);

        $this->db->query('SELECT * FROM mesas WHERE numero = 2');
        $mesa = $this->db->single();

        $this->assertNotNull($mesa);
        $this->assertEquals(4, $mesa['capacidad']);
        echo "\n✓ Mesa creada correctamente";
        echo "\n✓ Datos verificados en la base de datos";
    }

    public function testEdit()
    {
        echo "\nPrueba editar mesa:";
        $_SESSION['usuario_id'] = 1;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'piso_id' => 1,
            'numero' => 5,
            'capacidad' => 6
        ];

        $result = $this->mesasController->edit(1);
        $this->assertTrue($result);

        $this->db->query('SELECT * FROM mesas WHERE id = 1');
        $mesa = $this->db->single();

        $this->assertEquals(5, $mesa['numero']);
        $this->assertEquals(6, $mesa['capacidad']);
        echo "\n✓ Mesa actualizada correctamente";
        echo "\n✓ Cambios verificados en la base de datos";
    }

    public function testDelete()
    {
        echo "\nPrueba eliminar mesa:";
        $_SESSION['usuario_id'] = 1;

        $this->db->query('SELECT COUNT(*) as count FROM mesas WHERE id = 1');
        $beforeCount = $this->db->single()['count'];
        $this->assertEquals(1, $beforeCount);
        echo "\n✓ Existencia de la mesa verificada";

        $result = $this->mesasController->delete(1);
        $this->assertTrue($result);

        $this->db->query('SELECT COUNT(*) as count FROM mesas WHERE id = 1');
        $afterCount = $this->db->single()['count'];
        $this->assertEquals(0, $afterCount);
        echo "\n✓ Mesa eliminada correctamente";
    }

    protected function tearDown(): void
    {
        echo "\nLimpiando entorno de pruebas...";
        $this->cleanDatabase();
        $_SESSION = [];
        $_POST = [];
        parent::tearDown();
        echo "\n✓ Limpieza completada\n";
    }
}
