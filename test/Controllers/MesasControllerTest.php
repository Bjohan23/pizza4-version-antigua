<?php

use PHPUnit\Framework\TestCase;

class MesasControllerTest extends TestCase
{
    protected $db;
    protected $controller;

    // Configuración inicial antes de cada prueba
    protected function setUp(): void
    {
        // Inicializar la base de datos de prueba (asegúrate de configurar una base de datos separada para pruebas)
        $this->db = new mysqli('localhost', 'root', '', 'pizza4'); // Cambiar con tus datos de base de datos
        $this->db->query('TRUNCATE TABLE mesas'); // Limpiar la tabla de mesas
        $this->db->query('TRUNCATE TABLE usuarios'); // Limpiar tabla de usuarios si es necesario

        // Instanciar el controlador
        $this->controller = new MesasController();
    }

    // Limpiar después de cada prueba
    protected function tearDown(): void
    {
        // Cerrar conexión a la base de datos
        $this->db->close();
    }

    // Test para verificar que un usuario no autenticado sea redirigido a LOGIN
    public function testIndexWithNoUserSession()
    {
        // Simulamos que no hay usuario en la sesión
        $_SESSION['usuario_id'] = null;

        // Ejecutar el método index del controlador
        ob_start();
        $this->controller->index();
        $output = ob_get_clean();

        // Verificar que redirige a la página de login
        $this->assertStringContainsString('Location: ' . LOGIN, $output);
    }

    // Test para verificar que las mesas se obtienen correctamente
    public function testIndexWithMesas()
    {
        // Insertar una mesa en la base de datos de prueba
        $this->db->query("INSERT INTO mesas (piso_id, numero, capacidad) VALUES (1, 1, 4)");

        // Simulamos una sesión con un usuario autenticado
        $_SESSION['usuario_id'] = 1;

        // Ejecutar el método index del controlador
        ob_start();
        $this->controller->index();
        $output = ob_get_clean();

        // Verificar que las mesas se pasen a la vista
        $this->assertStringContainsString('mesas', $output);
    }

    // Test para verificar la creación de una mesa
    public function testCreateMesa()
    {
        // Simulamos una sesión con un usuario autenticado
        $_SESSION['usuario_id'] = 1;

        // Datos para crear una nueva mesa
        $_POST['piso_id'] = 1;
        $_POST['numero'] = 2;
        $_POST['capacidad'] = 4;

        // Ejecutar el método create del controlador
        ob_start();
        $this->controller->create();
        $output = ob_get_clean();

        // Verificar que la mesa se crea correctamente (redirección esperada)
        $this->assertStringContainsString('Location: ' . TABLE . '1?success=Mesa creada correctamente: 2', $output);
    }

    // Test para verificar que no se puede crear una mesa si falla la creación
    public function testCreateMesaFailure()
    {
        // Simulamos una sesión con un usuario autenticado
        $_SESSION['usuario_id'] = 1;

        // Datos para crear una nueva mesa con valores incorrectos (simula un error)
        $_POST['piso_id'] = 1;
        $_POST['numero'] = ''; // Número vacío para forzar un error
        $_POST['capacidad'] = 4;

        // Ejecutar el método create del controlador
        ob_start();
        $this->controller->create();
        $output = ob_get_clean();

        // Verificar que se muestra el mensaje de error
        $this->assertStringContainsString('Location: ' . TABLE . '1?error=nose pudo crear la mesa', $output);
    }

    // Test para verificar la edición de una mesa
    public function testEditMesa()
    {
        // Insertar una mesa en la base de datos
        $this->db->query("INSERT INTO mesas (piso_id, numero, capacidad) VALUES (1, 1, 4)");

        // Simulamos una sesión con un usuario autenticado
        $_SESSION['usuario_id'] = 1;

        // Datos para editar la mesa
        $_POST['piso_id'] = 1;
        $_POST['numero'] = 2;
        $_POST['capacidad'] = 6;

        // Ejecutar el método edit del controlador
        ob_start();
        $this->controller->edit(1);
        $output = ob_get_clean();

        // Verificar que la mesa se actualiza correctamente
        $this->assertStringContainsString('Location: ' . TABLE . '1?success=Mesa actualizada correctamente', $output);
    }

    // Test para verificar la eliminación de una mesa
    public function testDeleteMesa()
    {
        // Insertar una mesa en la base de datos
        $this->db->query("INSERT INTO mesas (piso_id, numero, capacidad) VALUES (1, 1, 4)");

        // Simulamos una sesión con un usuario autenticado
        $_SESSION['usuario_id'] = 1;

        // Ejecutar el método delete del controlador
        ob_start();
        $this->controller->delete(1);
        $output = ob_get_clean();

        // Verificar que la mesa se elimina correctamente
        $this->assertStringContainsString('Location: ' . TABLE . '1?success=Mesa eliminada correctamente', $output);
    }
}
//silvas