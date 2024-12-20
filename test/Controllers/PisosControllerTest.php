<?php

use PHPUnit\Framework\TestCase;

class PisosControllerTest extends TestCase
{
    private $pisosController;
    private $db;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('TESTING')) define('TESTING', true);
        if (!defined('SALIR')) define('SALIR', '/logout');
        if (!defined('PISOS')) define('PISOS', '/pisos');

        $this->db = new Database();
        echo "\nPreparando base de datos para pruebas...";
        $this->cleanDatabase();
        $this->createTestData();

        $this->pisosController = new PisosController();
        echo "\n✓ Controlador PisosController inicializado";
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

            // Desactivar verificación de claves foráneas
            $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
            $this->db->execute();

            foreach ($tables as $table) {
                $this->db->query("TRUNCATE TABLE `$table`");
                $this->db->execute();
                echo "\n  ✓ Tabla $table limpiada";
            }

            // Reactivar verificación de claves foráneas
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

            // Crear piso de prueba
            $this->db->query('INSERT INTO piso (sede_id, nombre) VALUES (:sede_id, "Piso Test")');
            $this->db->bind(':sede_id', $sedeId);
            $this->db->execute();
            $pisoId = $this->db->lastInsertId();
            echo "\n  ✓ Piso creado con ID: $pisoId";

            echo "\n✓ Datos de prueba creados exitosamente";
        } catch (Exception $e) {
            echo "\nError creando datos de prueba: " . $e->getMessage();
            throw $e;
        }
    }

    public function testRedirectWhenNotAuthenticated()
    {
        echo "\nPrueba de acceso sin autenticación:";
        $_SESSION = []; // Asegurarse que no hay sesión activa

        $result = $this->pisosController->index();
        $this->assertEquals(['redirect' => SALIR], $result, "Debería redirigir al login cuando no hay sesión");
        echo "\n✓ Redirección correcta al login";
    }

    public function testIndexWithAuthenticatedUser()
    {
        echo "\nPrueba listado de pisos:";
        $_SESSION['usuario_id'] = 1;

        $result = $this->pisosController->index();

        $this->assertIsArray($result, "El resultado debe ser un array");
        $this->assertArrayHasKey('pisos', $result, "El array debe contener la clave 'pisos'");
        $this->assertArrayHasKey('rolUsuario', $result, "El array debe contener la clave 'rolUsuario'");

        echo "\n✓ Listado recuperado correctamente";
        echo "\n✓ Estructura del resultado validada";
    }

    public function testCreate()
    {
        echo "\nPrueba crear piso:";
        $_SESSION['usuario_id'] = 1;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'nombre' => 'Nuevo Piso Test',
            'sede_id' => 1
        ];

        $result = $this->pisosController->create();

        // Verificar que el piso fue creado
        $this->db->query('SELECT * FROM piso WHERE nombre = :nombre');
        $this->db->bind(':nombre', 'Nuevo Piso Test');
        $piso = $this->db->single();

        $this->assertNotNull($piso, "El piso debe existir en la base de datos");
        $this->assertEquals('Nuevo Piso Test', $piso['nombre'], "El nombre del piso debe coincidir");
        echo "\n✓ Piso creado correctamente";
        echo "\n✓ Datos verificados en la base de datos";
    }

    public function testEdit()
    {
        echo "\nPrueba editar piso:";
        $_SESSION['usuario_id'] = 1;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'nombre' => 'Piso Actualizado',
            'sede_id' => 1
        ];

        $this->pisosController->edit(1);

        $this->db->query('SELECT * FROM piso WHERE id = 1');
        $piso = $this->db->single();

        $this->assertEquals('Piso Actualizado', $piso['nombre'], "El nombre del piso debe estar actualizado");
        echo "\n✓ Piso actualizado correctamente";
        echo "\n✓ Cambios verificados en la base de datos";
    }

    public function testDelete()
    {
        echo "\nPrueba eliminar piso:";
        $_SESSION['usuario_id'] = 1;

        // Verificar que el piso existe antes de eliminar
        $this->db->query('SELECT COUNT(*) as count FROM piso WHERE id = 1');
        $beforeCount = $this->db->single()['count'];
        $this->assertEquals(1, $beforeCount, "Debe existir un piso antes de eliminarlo");
        echo "\n✓ Existencia del piso verificada";

        $this->pisosController->delete(1);

        // Verificar que el piso ya no existe
        $this->db->query('SELECT COUNT(*) as count FROM piso WHERE id = 1');
        $afterCount = $this->db->single()['count'];
        $this->assertEquals(0, $afterCount, "El piso no debe existir después de eliminarlo");
        echo "\n✓ Piso eliminado correctamente";
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
