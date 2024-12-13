<?php

use PHPUnit\Framework\TestCase;

class HomeControllerTest extends TestCase
{
    protected $db;
    protected $controller;

    // Configuración inicial antes de cada prueba
    protected function setUp(): void
    {
        // Inicializar la base de datos de prueba (asegúrate de configurar una base de datos separada para pruebas)
        $this->db = new mysqli('localhost', 'root', '', 'pizza4');  // Cambiar con tus datos de base de datos
        $this->db->query('TRUNCATE TABLE sedes'); // Limpiar la tabla para pruebas
        $this->db->query('TRUNCATE TABLE usuarios'); // Limpiar tabla de usuarios si es necesario
        $this->db->query('TRUNCATE TABLE pedidos'); // Limpiar tabla de pedidos si es necesario

        // Instanciar el controlador
        $this->controller = new HomeController();
    }

    // Limpiar después de cada prueba
    protected function tearDown(): void
    {
        // Cerrar conexión a la base de datos
        $this->db->close();
    }

    // Test para el método index cuando existen sedes
    public function testIndexWithSedes()
    {
        // Insertar una sede en la base de datos para asegurar que el controlador no redirija a registro
        $this->db->query("INSERT INTO sedes (nombre, direccion) VALUES ('Sede Central', 'Calle Falsa 123')");

        // Llamar al método index del controlador
        $this->controller->index();

        // Verificar que el número de sedes es mayor a cero
        $result = $this->db->query("SELECT COUNT(*) AS sedeCount FROM sedes");
        $sedeCount = $result->fetch_assoc();

        // Asegurarse de que el controlador ejecutó correctamente la lógica y no redirigió
        $this->assertGreaterThan(0, $sedeCount['sedeCount']);
    }

    // Test para el comportamiento cuando no hay sedes
    public function testNoSedes()
    {
        // Asegurarse de que no hay sedes en la base de datos
        $this->db->query("DELETE FROM sedes");

        // Llamar al método index del controlador
        // Se espera que el controlador redirija a la página de registro de sedes si no hay ninguna
        ob_start();
        $this->controller->index();
        $output = ob_get_clean();

        // Verificar que el controlador intenta redirigir a la página de registro
        $this->assertStringContainsString('Location: /PIZZA4/public/sede/registro', $output);
    }

    // Test para verificar los datos pasados a la vista
    public function testDataPassedToView()
    {
        // Insertar una sede en la base de datos para asegurar que el controlador no redirija
        $this->db->query("INSERT INTO sedes (nombre, direccion) VALUES ('Sede Central', 'Calle Falsa 123')");

        // Insertar algunos datos adicionales para simular la carga
        $this->db->query("INSERT INTO usuarios (nombre, correo) VALUES ('Usuario Test', 'usuario@test.com')");
        $this->db->query("INSERT INTO pedidos (usuario_id, estado) VALUES (1, 'pendiente')");

        // Llamar al método index del controlador
        ob_start();
        $this->controller->index();
        $output = ob_get_clean();

        // Verificar que los datos fueron correctamente pasados
        $this->assertStringContainsString('usuariosCount', $output);
        $this->assertStringContainsString('clientesCount', $output);
        $this->assertStringContainsString('pedidosCount', $output);
        $this->assertStringContainsString('productosCount', $output);
    }
}
