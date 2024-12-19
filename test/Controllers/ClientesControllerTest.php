<?php
use PHPUnit\Framework\TestCase;

class ClientesControllerTest extends TestCase
{
    private $controller;
    private $db;

    protected function setUp(): void
    {
        // Configurar la conexión a la base de datos para pruebas
        $this->db = new PDO('mysql:host=localhost;dbname=piza4', 'root', '');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Limpiar la base de datos antes de cada prueba
        $this->db->exec("DELETE FROM clientes");
        $this->db->exec("DELETE FROM personas");
        $this->db->exec("DELETE FROM usuarios");

        // Insertar datos iniciales necesarios
        $this->db->exec("INSERT INTO personas (id, nombre, telefono, direccion, email, dni) 
                         VALUES (1, 'UsuarioTest', '123456789', 'Calle Principal', 'test@example.com', '12345678')");
        $this->db->exec("INSERT INTO usuarios (id, persona_id, contrasena) 
                         VALUES (1, 1, 'testpassword')");

        // Crear una instancia del controlador
        $this->controller = new ClientesController();
    }

    protected function tearDown(): void
    {
        // Limpiar la base de datos después de cada prueba
        $this->db->exec("DELETE FROM clientes");
        $this->db->exec("DELETE FROM personas");
        $this->db->exec("DELETE FROM usuarios");
    }

    public function testCreateClienteSuccess()
    {
        // Simular datos POST
        $_POST = [
            'nombre' => 'Cliente Test',
            'email' => 'cliente@example.com',
            'telefono' => '123456789',
            'direccion' => 'Calle Falsa 123',
            'dni' => '12345678'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Simular sesión iniciada
        $_SESSION['usuario_id'] = 1;

        // Capturar salida (redirección o mensajes)
        ob_start();
        $this->controller->create();
        ob_end_clean();

        // Verificar que se insertó en la tabla `personas`
        $stmtPersona = $this->db->query("SELECT * FROM personas WHERE email = 'cliente@example.com'");
        $persona = $stmtPersona->fetch(PDO::FETCH_ASSOC);
        $this->assertNotNull($persona, 'La persona debería haberse creado en la base de datos');

        // Verificar que se insertó en la tabla `clientes`
        $stmtCliente = $this->db->query("SELECT * FROM clientes WHERE persona_id = {$persona['id']}");
        $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);
        $this->assertNotNull($cliente, 'El cliente debería haberse creado en la base de datos');
    }

    public function testCreateClienteWithoutSession()
    {
        // Simular datos POST
        $_POST = [
            'nombre' => 'Cliente Sin Sesión',
            'email' => 'sin_sesion@example.com',
            'telefono' => '987654321',
            'direccion' => 'Avenida Siempre Viva',
            'dni' => '87654321'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Simular sesión no iniciada
        unset($_SESSION['usuario_id']);

        // Capturar salida (redirección o mensajes)
        ob_start();
        $this->controller->create();
        $output = ob_get_clean();

        // Verificar redirección o mensaje de error
        $this->assertStringContainsString('Usuario no autenticado', $output, 'Debería mostrar un mensaje de error para usuarios no autenticados');
    }
}
