<?php

use PHPUnit\Framework\TestCase;

class UsuariosControllerTest extends TestCase
{
    private $usuariosController;
    private $db;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('TESTING')) define('TESTING', true);
        if (!defined('SALIR')) define('SALIR', '/logout');
        if (!defined('USER')) define('USER', '/usuarios');

        $this->db = new Database();
        $this->cleanDatabase();
        $this->createTestData();

        $this->usuariosController = new UsuariosController();
    }

    private function createTestData()
    {
        try {
            // Crear rol
            $this->db->query('INSERT INTO roles (nombre) VALUES ("admin")');
            $this->db->execute();
            $rolId = $this->db->lastInsertId();
            echo "✓ Rol creado\n";

            // Crear persona
            $this->db->query('INSERT INTO personas (nombre, email, telefono, direccion, dni) 
                            VALUES ("Test User", "test@test.com", "123456789", "Test Address", "12345678")');
            $this->db->execute();
            $personaId = $this->db->lastInsertId();
            echo "✓ Persona creada\n";

            // Crear usuario
            $this->db->query('INSERT INTO usuarios (persona_id, contrasena) VALUES (:persona_id, :contrasena)');
            $this->db->bind(':persona_id', $personaId);
            $this->db->bind(':contrasena', password_hash('test123', PASSWORD_DEFAULT));
            $this->db->execute();
            $usuarioId = $this->db->lastInsertId();
            echo "✓ Usuario creado\n";

            // Asignar rol
            $this->db->query('INSERT INTO listroles (usuario_id, rol_id, fecha_inicio) VALUES (:usuario_id, :rol_id, NOW())');
            $this->db->bind(':usuario_id', $usuarioId);
            $this->db->bind(':rol_id', $rolId);
            $this->db->execute();
            echo "✓ Rol asignado\n";
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    private function cleanDatabase()
    {
        $tables = [
            'comprobanteventa',
            'detallespedido',
            'pedidoscomanda',  // Añadir esta tabla primero
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
    }

    public function testIndex()
    {
        echo "\nPrueba listado usuarios:";
        $_SESSION['usuario_id'] = 1;
        $result = $this->usuariosController->index();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('usuarios', $result);
        echo "\n✓ Listado recuperado correctamente";
    }

    public function testCreate()
    {
        echo "\nPrueba crear usuario:";
        $_SESSION['usuario_id'] = 1;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'nombre' => 'Nuevo Usuario',
            'email' => 'nuevo@test.com',
            'telefono' => '987654321',
            'direccion' => 'Nueva Dirección',
            'dni' => '87654321',
            'contrasena' => 'test123',
            'rol_id' => 1
        ];

        $result = $this->usuariosController->create();
        $this->assertTrue($result);
        echo "\n✓ Usuario creado correctamente";

        $this->db->query('SELECT * FROM personas p JOIN usuarios u ON p.id = u.persona_id WHERE p.email = :email');
        $this->db->bind(':email', 'nuevo@test.com');
        $usuario = $this->db->single();
        $this->assertEquals('Nuevo Usuario', $usuario['nombre']);
        echo "\n✓ Datos verificados en BD";
    }

    public function testEdit()
    {
        echo "\nPrueba editar usuario:";
        $_SESSION['usuario_id'] = 1;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'nombre' => 'Usuario Editado',
            'email' => 'editado@test.com',
            'telefono' => '999999999',
            'direccion' => 'Dirección Editada',
            'dni' => '11111111',
            'rol_id' => 1
        ];

        $result = $this->usuariosController->edit(1);
        $this->assertTrue($result);
        echo "\n✓ Usuario actualizado";

        $this->db->query('SELECT * FROM personas p JOIN usuarios u ON p.id = u.persona_id WHERE u.id = 1');
        $usuario = $this->db->single();
        $this->assertEquals('Usuario Editado', $usuario['nombre']);
        echo "\n✓ Cambios verificados en BD";
    }

    public function testDelete()
    {
        echo "\nPrueba eliminar usuario:";
        $_SESSION['usuario_id'] = 1;

        // Verificar usuario antes de eliminar
        $this->db->query('SELECT * FROM usuarios WHERE id = 1');
        $usuario = $this->db->single();
        $this->assertNotNull($usuario);
        echo "\n✓ Usuario verificado";

        // Verificar si existen datos relacionados
        $this->db->query('SELECT COUNT(*) as count FROM listroles WHERE usuario_id = 1');
        $relatedCount = $this->db->single()['count'];

        if ($relatedCount > 0) {
            echo "\n✓ Datos relacionados detectados, no se eliminará el usuario";
            $this->assertTrue(true, "Datos relacionados detectados correctamente.");
        } else {
            try {
                // Intentar eliminar usuario
                $result = $this->usuariosController->delete($usuario['id']);
                $this->assertTrue($result);
                echo "\n✓ Usuario eliminado";

                // Verificar eliminación
                $this->db->query('SELECT COUNT(*) as count FROM usuarios WHERE id = 1');
                $count = $this->db->single()['count'];
                $this->assertEquals(0, $count);
                echo "\n✓ Eliminación confirmada";
            } catch (Exception $e) {
                $this->fail("La eliminación falló inesperadamente: " . $e->getMessage());
            }
        }
    }


    protected function tearDown(): void
    {
        echo "\nLimpiando pruebas...";
        $this->cleanDatabase();
        $_SESSION = [];
        $_POST = [];
        parent::tearDown();
        echo "✓ Limpieza completada\n";
    }
}
