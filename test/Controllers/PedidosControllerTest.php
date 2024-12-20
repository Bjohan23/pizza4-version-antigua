<?php

use PHPUnit\Framework\TestCase;

class PedidosControllerTest extends TestCase
{
    // Propiedades para almacenar el controlador y la conexión a la base de datos
    private $pedidosController;
    private $db;

    /**
     * Método setUp(): Se ejecuta antes de cada método de prueba
     * 
     * Funciones de PHPUnit utilizadas:
     * - parent::setUp(): Llama al método setUp() de la clase padre TestCase
     * 
     * Casos de uso:
     * - Preparar el entorno de pruebas
     * - Definir constantes necesarias para las pruebas
     * - Limpiar la base de datos
     * - Crear datos de prueba
     * - Inicializar el controlador de pedidos
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Definir constantes para pruebas
        if (!defined('TESTING')) define('TESTING', true);
        if (!defined('SALIR')) define('SALIR', '/logout');
        if (!defined('ORDER')) define('ORDER', '/pedidos');

        // Inicializar conexión a base de datos
        $this->db = new Database();
        echo "\nPreparando base de datos para pruebas...";
        
        // Limpiar y preparar base de datos
        $this->cleanDatabase();
        $this->createTestData();

        // Inicializar controlador de pedidos
        $this->pedidosController = new PedidosController();
    }

    /**
     * Método createTestData(): Crea datos de prueba en la base de datos
     * 
     * Casos de uso:
     * - Insertar registros de prueba para realizar test
     * - Crear datos mínimos necesarios para las pruebas
     * 
     * @throws Exception Si hay error al crear datos
     */
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

    /**
     * Método cleanDatabase(): Limpia todas las tablas de la base de datos
     * 
     * Funciones de PHPUnit utilizadas:
     * - Gestión de transacciones para seguridad
     * 
     * Casos de uso:
     * - Preparar base de datos para cada prueba
     * - Evitar interferencia entre pruebas
     * - Reiniciar estado de base de datos
     */
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
     * Método testIndex(): Prueba el método index del controlador de pedidos
     * 
     * Funciones de PHPUnit utilizadas:
     * - assertIsArray(): Verifica que el resultado sea un array
     * - assertArrayHasKey(): Comprueba que el array tenga una clave específica
     * 
     * Casos de uso:
     * - Verificar que se puede listar pedidos con sesión iniciada
     * - Comprobar estructura de respuesta del método index
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
     * Método testCreatePedidoSinSesion(): Prueba creación de pedido sin sesión
     * 
     * Funciones de PHPUnit utilizadas:
     * - assertFalse(): Verifica que el resultado sea falso
     * 
     * Casos de uso:
     * - Verificar que no se puede crear pedidos sin iniciar sesión
     * - Probar seguridad del controlador
     */
    public function testCreatePedidoSinSesion()
    {
        echo "\nPrueba crear pedido sin sesión:";
        $_SESSION = []; // Limpiar sesión
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $result = $this->pedidosController->create(1);
        $this->assertFalse($result);
        echo "\n✓ No se permite crear pedido sin sesión";
    }

    /**
     * Método testIndexSinSesion(): Prueba listado de pedidos sin sesión
     * 
     * Funciones de PHPUnit utilizadas:
     * - assertFalse(): Verifica que el resultado sea falso
     * 
     * Casos de uso:
     * - Verificar que no se pueden listar pedidos sin iniciar sesión
     * - Probar seguridad del controlador
     */
    public function testIndexSinSesion()
    {
        echo "\nPrueba listar pedidos sin sesión:";
        $_SESSION = []; // Limpiar sesión

        $result = $this->pedidosController->index();
        $this->assertFalse($result);
        echo "\n✓ No se permite listar pedidos sin sesión";
    }
    
    /**
     * Método testCreate(): Prueba creación de un nuevo pedido
     * 
     * Funciones de PHPUnit utilizadas:
     * - assertTrue(): Verifica que el resultado sea verdadero
     * 
     * Casos de uso:
     * - Verificar creación de pedidos con datos válidos
     * - Probar funcionalidad principal del método create
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

    /**
     * Método tearDown(): Se ejecuta después de cada método de prueba
     * 
     * Funciones de PHPUnit utilizadas:
     * - parent::tearDown(): Llama al método tearDown() de la clase padre TestCase
     * 
     * Casos de uso:
     * - Limpiar base de datos
     * - Restablecer variables de sesión y POST
     * - Preparar el entorno para la próxima prueba
     */
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