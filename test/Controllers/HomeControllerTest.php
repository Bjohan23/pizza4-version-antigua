<?php

use PHPUnit\Framework\TestCase;

class HomeControllerTest extends TestCase
{
    private $homeController;

    protected function setUp(): void
    {
        parent::setUp();

        // Inicializar controlador
        $this->homeController = new HomeController();

        // Configurar base de datos de prueba
        $db = new mysqli('localhost', 'root', '', 'piza4'); // Configura según tu entorno
        $db->query('TRUNCATE TABLE sedes');
        $db->query('TRUNCATE TABLE usuarios');
        $db->query('TRUNCATE TABLE clientes');
    }

    protected function tearDown(): void
    {
        // Limpiar base de datos después de cada prueba
        $db = new mysqli('localhost', 'root', '', 'piza4');
        $db->query('TRUNCATE TABLE sedes');
        $db->query('TRUNCATE TABLE usuarios');
        $db->query('TRUNCATE TABLE clientes');
        $db->close();
    }

    public function testIndexUserNotAuthenticated()
    {
        // Simular un usuario no autenticado
        $_SESSION['usuario_id'] = null;

        ob_start();
        $this->homeController->index();
        $output = ob_get_clean();

        // Verificar que redirige a la página de salida
        $this->assertStringContainsString('Location: ' . SALIR, $output);
    }

    public function testIndexRedirectToSedeRegistroWhenNoSedesExist()
    {
        // Simular un usuario autenticado
        $_SESSION['usuario_id'] = 1;

        // Limpiar sedes
        $db = new mysqli('localhost', 'root', '', 'piza4');
        $db->query('TRUNCATE TABLE sedes');

        ob_start();
        $this->homeController->index();
        $output = ob_get_clean();

        // Verificar que redirige a la página de registro de sedes
        $this->assertStringContainsString('Location: /PIZZA4/public/sede/registro', $output);
    }

    public function testIndexLoadsDashboardViewWithDefaultData()
    {
        // Simular un usuario autenticado
        $_SESSION['usuario_id'] = 1;

        // Insertar una sede
        $db = new mysqli('localhost', 'root', '', 'piza4');
        $db->query("INSERT INTO sedes (nombre, direccion) VALUES ('Sede Central', 'Calle Principal 123')");

        ob_start();
        $this->homeController->index();
        $output = ob_get_clean();

        // Verificar que se carga la vista del dashboard
        $this->assertStringContainsString('Dashboard', $output);
        $this->assertStringContainsString('Usuarios: 0', $output);
        $this->assertStringContainsString('Clientes: 0', $output);
    }

    public function testIndexHandlesExceptionGracefully()
    {
        // Simular un usuario autenticado
        $_SESSION['usuario_id'] = 1;

        // Sobrescribir método `countSedes` para lanzar una excepción
        $this->homeController->setSedeModel(new class {
            public function countSedes()
            {
                throw new Exception('Simulated exception');
            }
        });

        ob_start();
        $this->homeController->index();
        $output = ob_get_clean();

        // Verificar que muestra la página de error
        $this->assertStringContainsString('Ha ocurrido un error en el servidor', $output);
    }
}
