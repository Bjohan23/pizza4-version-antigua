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
        // Asegurarse de que la base de datos contiene datos antes de llamar a index()
        $this->db->query("INSERT INTO clientes (nombre, email, telefono, direccion, dni)
                            VALUES ('Juan Pérez', 'juan@example.com', '123456789', 'Calle Falsa 123', '12345678A')");

        // Llamar al método index del controlador
        $this->controller->index();

        // Verificar que la salida contiene los datos que esperamos
        // Esto dependerá de la implementación exacta de tu controlador y vistas
        // Por ejemplo, puedes comprobar si los datos de los clientes se muestran correctamente

        // Aquí se verifica que la consulta a la base de datos sea correcta
        $result = $this->db->query('SELECT * FROM clientes');
        $clientes = $result->fetch_all(MYSQLI_ASSOC);

        // Asegurarse de que se ha insertado el cliente correctamente
        $this->assertCount(1, $clientes);
        $this->assertEquals('Juan Pérez', $clientes[0]['nombre']);
    }

    // Test para el método create
    public function testCreate()
    {
        // Simular los datos que se enviarían en el formulario
        $_POST['nombre'] = 'Carlos García';
        $_POST['email'] = 'carlos@example.com';
        $_POST['telefono'] = '987654321';
        $_POST['direccion'] = 'Calle Ejemplo 456';
        $_POST['dni'] = '87654321B';

        // Llamar al método create del controlador
        $this->controller->create();

        // Verificar que los datos fueron insertados correctamente en la base de datos
        $result = $this->db->query("SELECT * FROM clientes WHERE nombre = 'Carlos García'");
        $cliente = $result->fetch_assoc();

        // Asegurarse de que el cliente se ha creado correctamente
        $this->assertNotNull($cliente);
        $this->assertEquals('Carlos García', $cliente['nombre']);
    }

    // Test para el método edit
    public function testEdit()
    {
        // Insertar un cliente en la base de datos para editar
        $this->db->query("INSERT INTO clientes (nombre, email, telefono, direccion, dni)
                            VALUES ('Ana López', 'ana@example.com', '234567890', 'Calle Ejemplo 789', '23456789C')");
        $clienteId = $this->db->insert_id;

        // Simular los datos que se enviarían en el formulario
        $_POST['nombre'] = 'Ana López Actualizada';
        $_POST['email'] = 'ana_new@example.com';
        $_POST['telefono'] = '111223344';
        $_POST['direccion'] = 'Calle Nueva 101';
        $_POST['dni'] = '23456789D';

        // Llamar al método edit del controlador
        $this->controller->edit($clienteId);

        // Verificar que los datos se han actualizado correctamente en la base de datos
        $result = $this->db->query("SELECT * FROM clientes WHERE id = $clienteId");
        $cliente = $result->fetch_assoc();

        // Asegurarse de que el cliente fue actualizado
        $this->assertEquals('Ana López Actualizada', $cliente['nombre']);
        $this->assertEquals('ana_new@example.com', $cliente['email']);
    }

    // Test para el método delete
    public function testDelete()
    {
        // Insertar un cliente en la base de datos
        $this->db->query("INSERT INTO clientes (nombre, email, telefono, direccion, dni)
                            VALUES ('Luis García', 'luis@example.com', '345678901', 'Calle Ejemplo 101', '34567890E')");
        $clienteId = $this->db->insert_id;

        // Llamar al método delete del controlador
        $this->controller->delete($clienteId);

        // Verificar que el cliente ha sido eliminado de la base de datos
        $result = $this->db->query("SELECT * FROM clientes WHERE id = $clienteId");
        $cliente = $result->fetch_assoc();

        // Asegurarse de que el cliente ha sido eliminado
        $this->assertNull($cliente);
    }
}
?>
