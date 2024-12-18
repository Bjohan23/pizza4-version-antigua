<?php

use PHPUnit\Framework\TestCase;

class VentasControllerTest extends TestCase
{
    private $ventasController;
    private $db;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('TESTING')) define('TESTING', true);
        if (!defined('SALIR')) define('SALIR', '/PIZZA4/public/auth/login');

        $this->db = new Database();
        echo "\nPreparando base de datos para pruebas...";
        $this->cleanDatabase();
        $this->createTestData();

        $this->ventasController = new VentasController();
    }

    private function cleanDatabase()
    {
        try {
            $tables = [
                'comprobanteventa',
                'detallespedido',
                'pedidoscomanda',
                'productos',
                'categoría',
                'mesas',
                'piso',
                'sede',
                'clientes',
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

            // Crear datos necesarios para ventas
            $this->createVentasTestData();
        } catch (Exception $e) {
            echo "\nError creando datos de prueba: " . $e->getMessage();
            throw $e;
        }
    }

    private function createVentasTestData()
    {
        try {
            // Crear sede
            $this->db->query('INSERT INTO sede (nombre, direccion) VALUES ("Sede Test", "Dirección Test")');
            $this->db->execute();
            $sedeId = $this->db->lastInsertId();
            echo "\n✓ Sede creada";

            // Crear piso
            $this->db->query('INSERT INTO piso (sede_id, nombre) VALUES (:sede_id, "Piso Test")');
            $this->db->bind(':sede_id', $sedeId);
            $this->db->execute();
            $pisoId = $this->db->lastInsertId();
            echo "\n✓ Piso creado";

            // Crear mesa
            $this->db->query('INSERT INTO mesas (piso_id, numero, capacidad) VALUES (:piso_id, 1, 4)');
            $this->db->bind(':piso_id', $pisoId);
            $this->db->execute();
            $mesaId = $this->db->lastInsertId();
            echo "\n✓ Mesa creada";

            // Crear persona para cliente
            $this->db->query('INSERT INTO personas (nombre, email, telefono, direccion, dni) 
                             VALUES ("Cliente Test", "cliente@test.com", "123456789", "Dirección Test", "12345678")');
            $this->db->execute();
            $personaClienteId = $this->db->lastInsertId();
            echo "\n✓ Persona cliente creada";

            // Crear cliente
            $this->db->query('INSERT INTO clientes (persona_id) VALUES (:persona_id)');
            $this->db->bind(':persona_id', $personaClienteId);
            $this->db->execute();
            $clienteId = $this->db->lastInsertId();
            echo "\n✓ Cliente creado";

            // Crear categoría
            $this->db->query('INSERT INTO categoría (nombre) VALUES ("Categoría Test")');
            $this->db->execute();
            $categoriaId = $this->db->lastInsertId();
            echo "\n✓ Categoría creada";

            // Crear producto
            $this->db->query('INSERT INTO productos (nombre, descripcion, precio, categoria_id) 
                             VALUES ("Producto Test", "Descripción Test", 50.00, :categoria_id)');
            $this->db->bind(':categoria_id', $categoriaId);
            $this->db->execute();
            $productoId = $this->db->lastInsertId();
            echo "\n✓ Producto creado";

            // Crear pedido
            $this->db->query('INSERT INTO pedidoscomanda (usuario_id, cliente_id, mesa_id, fecha, estado, total) 
                             VALUES (1, :cliente_id, :mesa_id, NOW(), "pagado", 100.00)');
            $this->db->bind(':cliente_id', $clienteId);
            $this->db->bind(':mesa_id', $mesaId);
            $this->db->execute();
            $pedidoId = $this->db->lastInsertId();
            echo "\n✓ Pedido creado";

            // Crear detalle de pedido
            $this->db->query('INSERT INTO detallespedido (pedido_id, producto_id, cantidad, precio) 
                             VALUES (:pedido_id, :producto_id, 2, 50.00)');
            $this->db->bind(':pedido_id', $pedidoId);
            $this->db->bind(':producto_id', $productoId);
            $this->db->execute();
            echo "\n✓ Detalle de pedido creado";

            // Crear comprobante de venta
            $this->db->query('INSERT INTO comprobanteventa (pedido_id, tipo, monto, fecha) 
                             VALUES (:pedido_id, "efectivo", 100.00, NOW())');
            $this->db->bind(':pedido_id', $pedidoId);
            $this->db->execute();
            echo "\n✓ Comprobante de venta creado";
        } catch (Exception $e) {
            echo "\nError creando datos de venta: " . $e->getMessage();
            throw $e;
        }
    }

    /**
     * @test
     * @testdox Redirige cuando el usuario no está autenticado
     */
    public function testRedirectWhenNotAuthenticated()
    {
        echo "\nPrueba acceso sin autenticación:";
        $_SESSION = [];

        $result = $this->ventasController->index();

        $this->assertEquals(['redirect' => SALIR], $result);
        echo "\n✓ Redirección correcta al login";
    }

    /**
     * @test
     * @testdox Muestra las ventas cuando el usuario está autenticado
     */
    public function testShowVentasWhenAuthenticated()
    {
        echo "\nPrueba acceso con autenticación:";
        $_SESSION['usuario_id'] = 1;

        $result = $this->ventasController->index();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('ventas', $result);
        $this->assertArrayHasKey('rolUsuario', $result);

        // Verificar que hay al menos una venta
        $this->assertNotEmpty($result['ventas']);
        echo "\n✓ Datos de ventas recuperados correctamente";
    }

    protected function tearDown(): void
    {
        echo "\nLimpiando pruebas...";
        $this->cleanDatabase();
        $_SESSION = [];
        parent::tearDown();
    }
}
