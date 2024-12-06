<?php

use PHPUnit\Framework\TestCase;

class MesasControllerTest extends TestCase
{
    private $mesasController;

    protected function setUp(): void
    {
        parent::setUp();

        // Inicializar el controlador real
        $this->mesasController = new MesasController();

        // Configurar la base de datos de prueba
        $this->resetTestDatabase();
    }

    private function resetTestDatabase(): void
    {
        // Configurar tu conexión a la base de datos de pruebas
        $pdo = new PDO('mysql:host=localhost;dbname=test_db', 'root', '');
        $pdo->exec('
            SET FOREIGN_KEY_CHECKS=0;
            TRUNCATE TABLE mesas;
            TRUNCATE TABLE pisos;
            TRUNCATE TABLE usuarios;
            SET FOREIGN_KEY_CHECKS=1;
        ');

        // Insertar datos iniciales
        $pdo->exec("
            INSERT INTO usuarios (id, nombre, apellidos, correo, contraseña, rol) 
            VALUES (1, 'Admin', 'User', 'admin@example.com', '123456', 'admin');
            
            INSERT INTO pisos (id, nombre) VALUES (1, 'Piso 1');
            
            INSERT INTO mesas (id, numero, capacidad, piso_id) 
            VALUES (1, 'Mesa 1', 4, 1), (2, 'Mesa 2', 2, 1);
        ");
    }

    public function testIndexWithAuthenticatedUser()
    {
        // Simular sesión
        $_SESSION['usuario_id'] = 1;

        // Capturar la salida de la vista
        ob_start();
        $this->mesasController->index();
        $output = ob_get_clean();

        // Verificar que la salida incluye las mesas
        $this->assertStringContainsString('Mesa 1', $output);
        $this->assertStringContainsString('Mesa 2', $output);
        $this->assertStringContainsString('admin', $output);
    }

    public function testIndexWithUnauthenticatedUser()
    {
        // Asegurarse de que la sesión está vacía
        $_SESSION = [];

        // Capturar headers enviados
        $this->expectOutputRegex('/Location: .*login/i');

        // Llamar al método
        $this->mesasController->index();
    }

    public function testCreateWithValidData()
    {
        $_SESSION['usuario_id'] = 1;

        // Simular datos POST
        $_POST = [
            'piso_id' => 1,
            'numero' => 'Mesa 3',
            'capacidad' => 6,
        ];

        // Capturar headers enviados
        ob_start();
        $this->mesasController->create();
        ob_end_clean();

        // Verificar que la mesa fue creada en la base de datos
        $pdo = new PDO('mysql:host=localhost;dbname=test_db', 'root', '');
        $stmt = $pdo->query('SELECT * FROM mesas WHERE numero = "Mesa 3"');
        $mesa = $stmt->fetch();

        $this->assertNotEmpty($mesa);
        $this->assertEquals('Mesa 3', $mesa['numero']);
        $this->assertEquals(6, $mesa['capacidad']);
    }

    public function testDelete()
    {
        $_SESSION['usuario_id'] = 1;

        // Capturar headers enviados
        ob_start();
        $this->mesasController->delete(1);
        ob_end_clean();

        // Verificar que la mesa fue eliminada de la base de datos
        $pdo = new PDO('mysql:host=localhost;dbname=test_db', 'root', '');
        $stmt = $pdo->query('SELECT * FROM mesas WHERE id = 1');
        $mesa = $stmt->fetch();

        $this->assertEmpty($mesa);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_POST = [];
        parent::tearDown();
    }
}
