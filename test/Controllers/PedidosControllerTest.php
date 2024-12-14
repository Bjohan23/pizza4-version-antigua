<?php
use PHPUnit\Framework\TestCase;

class PedidosControllerTest extends TestCase 
{
    private $pedidosController;
    private $db;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('TESTING')) define('TESTING', true);
        if (!defined('SALIR')) define('SALIR', '/logout');
        if (!defined('ORDER')) define('ORDER', '/pedidos');

        $this->db = new Database();
        echo "\nPreparando base de datos para pruebas...";
        $this->cleanDatabase();
        $this->createTestData();

        $this->pedidosController = new PedidosController();
    }

    private function createTestData()
    {
        try {
            // Crear categoría y producto
            $this->db->query('INSERT INTO categoría (nombre) VALUES ("Categoría Test")');
            $this->db->execute();
            $categoriaId = $this->db->lastInsertId();
            echo "\n✓ Categoría creada";

            $this->db->query('INSERT INTO productos (nombre, precio, categoria_id) 
                             VALUES ("Producto Test", 10.00, :categoria_id)');
            $this->db->bind(':categoria_id', $categoriaId);
            $this->db->execute();
            echo "\n✓ Producto creado";

            // Crear piso y mesa
            $this->db->query('INSERT INTO sede (nombre) VALUES ("Sede Test")');
            $this->db->execute();
            $sedeId = $this->db->lastInsertId();

            $this->db->query('INSERT INTO piso (sede_id, nombre) VALUES (:sede_id, "Piso Test")');
            $this->db->bind(':sede_id', $sedeId);
            $this->db->execute();
            $pisoId = $this->db->lastInsertId();
            echo "\n✓ Piso creado";

            $this->db->query('INSERT INTO mesas (piso_id, numero, capacidad) 
                             VALUES (:piso_id, 1, 4)');
            $this->db->bind(':piso_id', $pisoId);
            $this->db->execute();
            echo "\n✓ Mesa creada";

            // Crear usuario y cliente
            $this->db->query('INSERT INTO personas (nombre, email) 
                             VALUES ("Usuario Test", "test@test.com")');
            $this->db->execute();
            $personaId = $this->db->lastInsertId();

            $this->db->query('INSERT INTO usuarios (persona_id, contrasena) 
                             VALUES (:persona_id, :contrasena)');
            $this->db->bind(':persona_id', $personaId);
            $this->db->bind(':contrasena', password_hash('test123', PASSWORD_DEFAULT));
            $this->db->execute();
            echo "\n✓ Usuario creado";

            $this->db->query('INSERT INTO clientes (persona_id) VALUES (:persona_id)');
            $this->db->bind(':persona_id', $personaId);
            $this->db->execute();
            echo "\n✓ Cliente creado";

        } catch (Exception $e) {
            echo "\nError: " . $e->getMessage();
            throw $e;
        }
    }

    private function cleanDatabase()
    {
        $tables = [
            'comprobanteventa',
            'detallespedido',
            'pedidoscomanda',
            'clientes',
            'usuarios',
            'personas',
            'productos',
            'categoría',
            'mesas',
            'piso',
            'sede'
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
    }

    /**
     * @test
     * @testdox Se puede listar los pedidos
     */
    public function testIndex()
    {
        echo "\nPrueba listado pedidos:";
        $_SESSION['usuario_id'] = 1;
        
        $result = $this->pedidosController->index();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('pisos', $result);
        echo "\n✓ Listado recuperado correctamente";
    }

    /**
     * @test
     * @testdox Se puede crear un nuevo pedido
     */
    public function testCreate()
    {
        echo "\nPrueba crear pedido:";
        $_SESSION['usuario_id'] = 1;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'cliente_id' => 1,
            'total' => 10.00,
            'productos' => [
                [
                    'id' => 1,
                    'cantidad' => 1,
                    'precio' => 10.00,
                    'descripcion2' => 'Test'
                ]
            ]
        ];

        $result = $this->pedidosController->create(1);
        $this->assertTrue($result);
        echo "\n✓ Pedido creado correctamente";
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