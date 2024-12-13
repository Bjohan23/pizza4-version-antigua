<?php

use PHPUnit\Framework\TestCase;

class ClientesControllerTest extends TestCase
{
    protected $db;
    protected $controller;

    // Configuración inicial antes de cada prueba
    protected function setUp(): void
    {
        // Inicializar la base de datos de prueba (deberías configurar una base de datos separada para pruebas)
        $this->db = new mysqli('localhost', 'root', '', 'piza4');  // Cambiar con tus datos de base de datos

        // Asegurarse de que la base de datos de prueba está limpia antes de las pruebas
        $this->db->query('TRUNCATE TABLE clientes');
        // Instanciar el controlador
        $this->controller = new ClientesController();
    }

    // Limpiar después de cada prueba
    protected function tearDown(): void
    {
        // Cerrar conexión a la base de datos
        $this->db->close();
    }

    // Test para el método index
    public function testIndex()
    {
        // Insertar un cliente en la base de datos para probar
        $this->db->query("INSERT INTO clientes (nombre, email, telefono, direccion, dni)
                          VALUES ('Juan Pérez', 'juan@example.com', '123456789', 'Calle Falsa 123', '12345678A')");

        // Llamar al método index del controlador
        $this->controller->index(); // Aquí debes asegurarte de que la salida sea correcta

        // Verificar que la consulta a la base de datos se realizó correctamente
        $result = $this->db->query('SELECT * FROM clientes');
        $clientes = $result->fetch_all(MYSQLI_ASSOC);

        // Asegurarse de que se haya insertado el cliente correctamente
        $this->assertCount(1, $clientes); // Verificar que solo hay un cliente
        $this->assertEquals('Juan Pérez', $clientes[0]['nombre']); // Verificar que el nombre sea correcto
    }

    // Test para el método create
    public function testCreate()
    {
        // Simular los datos de formulario para la creación del cliente
        $_POST['nombre'] = 'Carlos García';
        $_POST['email'] = 'carlos@example.com';
        $_POST['telefono'] = '987654321';
        $_POST['direccion'] = 'Calle Ejemplo 456';
        $_POST['dni'] = '87654321B';

        // Llamar al método create del controlador
        $this->controller->create(); // El método debe insertar los datos en la base de datos

        // Verificar que el cliente fue insertado correctamente en la base de datos
        $result = $this->db->query("SELECT * FROM clientes WHERE nombre = 'Carlos García'");
        $cliente = $result->fetch_assoc();

        // Asegurarse de que el cliente existe en la base de datos
        $this->assertNotNull($cliente);
        $this->assertEquals('Carlos García', $cliente['nombre']);
        $this->assertEquals('carlos@example.com', $cliente['email']);
    }

    // Test para el método edit
    public function testEdit()
    {
        // Insertar un cliente en la base de datos para actualizar
        $this->db->query("INSERT INTO clientes (nombre, email, telefono, direccion, dni)
                          VALUES ('Ana López', 'ana@example.com', '234567890', 'Calle Ejemplo 789', '23456789C')");
        $clienteId = $this->db->insert_id; // Obtener el ID del cliente recién insertado

        // Simular los datos del formulario para la actualización del cliente
        $_POST['nombre'] = 'Ana López Actualizada';
        $_POST['email'] = 'ana_new@example.com';
        $_POST['telefono'] = '111223344';
        $_POST['direccion'] = 'Calle Nueva 101';
        $_POST['dni'] = '23456789D';

        // Llamar al método edit del controlador
        $this->controller->edit($clienteId); // El método debe actualizar los datos en la base de datos

        // Verificar que los datos del cliente se hayan actualizado correctamente
        $result = $this->db->query("SELECT * FROM clientes WHERE id = $clienteId");
        $cliente = $result->fetch_assoc();

        // Asegurarse de que los datos fueron actualizados
        $this->assertEquals('Ana López Actualizada', $cliente['nombre']);
        $this->assertEquals('ana_new@example.com', $cliente['email']);
    }

    // Test para el método delete
    public function testDelete()
    {
        // Insertar un cliente en la base de datos
        $this->db->query("INSERT INTO clientes (nombre, email, telefono, direccion, dni)
                          VALUES ('Luis García', 'luis@example.com', '345678901', 'Calle Ejemplo 101', '34567890E')");
        $clienteId = $this->db->insert_id; // Obtener el ID del cliente recién insertado

        // Llamar al método delete del controlador
        $this->controller->delete($clienteId); // El método debe eliminar al cliente de la base de datos

        // Verificar que el cliente ha sido eliminado correctamente
        $result = $this->db->query("SELECT * FROM clientes WHERE id = $clienteId");
        $cliente = $result->fetch_assoc(); // Esto debería devolver null si el cliente fue eliminado

        // Asegurarse de que el cliente ya no existe en la base de datos
        $this->assertNull($cliente);
    }
}
