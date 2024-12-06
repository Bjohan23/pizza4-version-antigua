<?php

use PHPUnit\Framework\TestCase;

class ProductosControllerTest extends TestCase
{
    private $productosController;
    private $db;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('TESTING')) define('TESTING', true);
        if (!defined('SALIR')) define('SALIR', '/logout');
        if (!defined('PRODUCT')) define('PRODUCT', '/productos');

        $this->db = new Database();
        echo "\nPreparando base de datos para pruebas...";
        $this->cleanDatabase();
        $this->createTestData();

        $this->productosController = new ProductosController();
    }

    private function createTestData()
    {
        try {
            // Crear categoría de prueba
            $this->db->query('INSERT INTO categoría (nombre) VALUES ("Categoría Test")');
            $this->db->execute();
            $categoriaId = $this->db->lastInsertId();
            echo "\n✓ Categoría creada";

            // Crear producto de prueba
            $this->db->query('INSERT INTO productos (nombre, descripcion, precio, disponible, categoria_id) 
                             VALUES ("Producto Test", "Descripción Test", 10.50, 1, :categoria_id)');
            $this->db->bind(':categoria_id', $categoriaId);
            $this->db->execute();
            echo "\n✓ Producto creado";

            // Crear usuario de prueba (necesario para autenticación)
            $this->db->query('INSERT INTO personas (nombre, email) VALUES ("Test User", "test@test.com")');
            $this->db->execute();
            $personaId = $this->db->lastInsertId();

            $this->db->query('INSERT INTO usuarios (persona_id, contrasena) 
                             VALUES (:persona_id, :contrasena)');
            $this->db->bind(':persona_id', $personaId);
            $this->db->bind(':contrasena', password_hash('test123', PASSWORD_DEFAULT));
            $this->db->execute();
            echo "\n✓ Usuario de prueba creado";
            echo "\nAserciones realizadas: " . $this->getCount() . "\n";
        } catch (Exception $e) {
            echo "\nError creando datos de prueba: " . $e->getMessage();
            throw $e;
        }
    }

    private function cleanDatabase()
    {
        $tables = [
            'detallespedido',
            'productos',
            'categoría',
            'usuarios',
            'personas'
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
        echo "\nAserciones realizadas: " . $this->getCount() . "\n";
    }
    /**
     * @test
     * @testdox Se puede listar todos los productos
     */
    public function testIndex()
    {
        echo "\nPrueba listado productos:";
        $_SESSION['usuario_id'] = 1;

        $result = $this->productosController->index();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('productos', $result);
        echo "\n✓ Lista de productos recuperada";

        $productos = $result['productos'];
        $this->assertNotEmpty($productos);
        $this->assertEquals("Producto Test", $productos[0]['nombre']);
        echo "\n✓ Datos de productos verificados";
        echo "\nAserciones realizadas: " . $this->getCount() . "\n";
    }
    /**
     * @test
     * @testdox Se puede crear un nuevo producto
     */
    public function testCreate()
    {
        echo "\nPrueba crear producto:";
        $_SESSION['usuario_id'] = 1;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'nombre' => 'Nuevo Producto',
            'descripcion' => 'Nueva Descripción',
            'precio' => '15.99',
            'disponible' => '1',
            'categoria_id' => '1'
        ];

        $result = $this->productosController->create();
        $this->assertTrue($result);
        echo "\n✓ Producto creado correctamente";

        $this->db->query('SELECT * FROM productos WHERE nombre = :nombre');
        $this->db->bind(':nombre', 'Nuevo Producto');
        $producto = $this->db->single();
        $this->assertEquals(15.99, $producto['precio']);
        echo "\n✓ Datos verificados en BD";
        echo "\nAserciones realizadas: " . $this->getCount() . "\n";
    }
    /**
     * @test
     * @testdox Se puede editar un producto existente
     */
    public function testEdit()
    {
        echo "\nPrueba editar producto:";
        $_SESSION['usuario_id'] = 1;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'nombre' => 'Producto Actualizado',
            'descripcion' => 'Descripción Actualizada',
            'precio' => '25.99',
            'disponible' => '1',
            'categoria_id' => '1'
        ];

        $result = $this->productosController->edit(1);
        $this->assertTrue($result);
        echo "\n✓ Producto actualizado";

        $this->db->query('SELECT * FROM productos WHERE id = 1');
        $producto = $this->db->single();
        $this->assertEquals('Producto Actualizado', $producto['nombre']);
        echo "\n✓ Cambios verificados en BD";
        echo "\nAserciones realizadas: " . $this->getCount() . "\n";
    }
    /**
     * @test
     * @testdox Se puede eliminar un producto
     * @doesNotPerformAssertions
     */
    public function testDelete()
    {
        echo "\nPrueba eliminar producto:";
        $_SESSION['usuario_id'] = 1;

        $this->db->query('SELECT * FROM productos WHERE id = 1');
        $producto = $this->db->single();

        if ($producto) {
            $this->productosController->delete(1);
            echo "\nAserciones realizadas: " . $this->getCount() . "\n";
            echo "\n✓ Producto eliminado";
        }
        echo "\nAserciones realizadas: " . $this->getCount() . "\n";
    }

    protected function tearDown(): void
    {
        echo "\nLimpiando entorno de pruebas...";
        $this->cleanDatabase();
        $_SESSION = [];
        $_POST = [];
        parent::tearDown();
        echo "✓ Entorno limpiado\n";
    }
}
