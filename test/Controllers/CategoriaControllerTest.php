<?php

use PHPUnit\Framework\TestCase;

class CategoriaControllerTest extends TestCase
{
    private $categoriaController;
    private $db;
    private $startTime;


    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('TESTING')) define('TESTING', true);
        if (!defined('SALIR')) define('SALIR', '/logout');

        $this->db = new Database();
        echo "\nPreparando base de datos para pruebas...";
        $this->cleanDatabase();
        $this->createTestData();

        $this->categoriaController = new CategoriasController();
        $this->startTime = microtime(true); // Iniciar temporizador
    }

    private function createTestData()
    {
        try {
            $this->db->query('INSERT INTO categoría (nombre) VALUES ("Categoría Prueba")');
            $this->db->execute();
            echo "\n✓ Categoría de prueba creada";
        } catch (Exception $e) {
            echo "\nError al crear datos de prueba: " . $e->getMessage();
        }
    }

    private function cleanDatabase()
    {
        $tables = ['categoría'];

        $this->db->beginTransaction();
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        $this->db->execute();

        foreach ($tables as $table) {
            $this->db->query("TRUNCATE TABLE $table");
            $this->db->execute();
        }

        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
        $this->db->execute();
        $this->db->commit();
        echo "\n✓ Base de datos limpiada";
    }

    /**
     * @test
     * @testdox Verifica que se puedan listar las categorías
     */
    public function testIndex()
    {
        echo "\nPrueba listado de categorías:";
        $_SESSION['usuario_id'] = 1;

        $result = $this->categoriaController->index();
        $this->assertIsArray($result, "El resultado debe ser un array.");
        $this->assertCount(1, $result, "Debe haber al menos una categoría listada.");
        $this->assertEquals('Categoría Prueba', $result[0]['nombre'], "El nombre de la categoría debe coincidir.");
        echo "\n✓ Listado de categorías correcto";
    }

    /**
     * @test
     * @testdox Verifica que se pueda crear una categoría
     */
    public function testCreate()
    {
        echo "\nPrueba creación de categoría:";
        $_SESSION['usuario_id'] = 1;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['nombre' => 'Nueva Categoría'];

        $result = $this->categoriaController->create();
        $this->assertNull($result, "La creación debe redirigir sin retornar un valor.");

        $this->db->query('SELECT * FROM categoría WHERE nombre = :nombre');
        $this->db->bind(':nombre', 'Nueva Categoría');
        $categoria = $this->db->single();

        $this->assertNotNull($categoria, "La categoría debe haberse creado en la base de datos.");
        $this->assertEquals('Nueva Categoría', $categoria['nombre'], "El nombre de la categoría debe coincidir.");
        echo "\n✓ Categoría creada correctamente";
    }

    protected function tearDown(): void
    {
        echo "\nLimpiando pruebas...";
        $this->cleanDatabase();
        $_SESSION = [];
        $_POST = [];
        parent::tearDown();
        echo "\n✓ Limpieza completada";
    }
}
