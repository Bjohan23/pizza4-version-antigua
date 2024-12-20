<?php

use PHPUnit\Framework\TestCase;

/**
 * Pruebas unitarias para el controlador de Sedes (SedeController)
 * 
 * Esta clase contiene todas las pruebas relacionadas con la gestión de sedes.
 * Extiende de TestCase, que es la clase base de PHPUnit para pruebas unitarias.
 * 
 * @package Tests\Controllers
 */
class SedeControllerTest extends TestCase
{
    /**
     * Instancia del controlador a probar
     * @var SedeController
     */
    private $sedeController;

    /**
     * Instancia de la conexión a la base de datos
     * @var Database
     */
    private $db;

    /**
     * Método que se ejecuta antes de cada prueba
     * 
     * PHPUnit ejecuta este método antes de cada test. Se usa para:
     * - Configurar el entorno de pruebas
     * - Inicializar la base de datos
     * - Crear datos de prueba necesarios
     * - Instanciar el controlador
     * 
     * @return void
     */
    protected function setUp(): void
    {
        // Llamar al setUp del padre (TestCase) para mantener la funcionalidad base de PHPUnit
        parent::setUp();

        // Definir constantes necesarias para el entorno de pruebas
        // Estas constantes son utilizadas por el controlador para redirecciones y rutas
        if (!defined('TESTING')) define('TESTING', true);
        if (!defined('SALIR')) define('SALIR', '/logout');
        if (!defined('SEDE')) define('SEDE', '/sede');
        if (!defined('SEDE_CREATE')) define('SEDE_CREATE', '/sede/registro');

        // Inicializar la conexión a la base de datos y preparar el entorno
        $this->db = new Database();
        echo "\nPreparando base de datos para pruebas...";
        $this->cleanDatabase();    // Limpiar datos existentes
        $this->createTestData();   // Crear datos de prueba
        echo "✓ Base de datos lista\n";

        // Instanciar el controlador que vamos a probar
        $this->sedeController = new SedeController();
    }

    /**
     * Crea los datos iniciales necesarios para las pruebas
     * 
     * Este método:
     * - Crea un rol de administrador
     * - Crea un usuario de prueba
     * - Asigna el rol al usuario
     * - Crea una sede inicial para pruebas
     * 
     * @throws Exception Si hay algún error en la creación de datos
     */
    private function createTestData()
    {
        try {
            // 1. Crear rol de administrador
            $this->db->query('INSERT INTO roles (nombre) VALUES ("admin")');
            $this->db->execute();
            $rolId = $this->db->lastInsertId();
            echo "✓ Rol admin creado\n";

            // 2. Crear usuario de prueba (primero la persona, luego el usuario)
            $this->db->query('INSERT INTO personas (nombre, email) VALUES ("Test User", "test@test.com")');
            $this->db->execute();
            $personaId = $this->db->lastInsertId();

            $this->db->query('INSERT INTO usuarios (persona_id, contrasena) VALUES (:persona_id, :contrasena)');
            $this->db->bind(':persona_id', $personaId);
            $this->db->bind(':contrasena', password_hash('test123', PASSWORD_DEFAULT));
            $this->db->execute();
            $usuarioId = $this->db->lastInsertId();
            echo "✓ Usuario de prueba creado\n";

            // 3. Asignar rol de admin al usuario
            $this->db->query('INSERT INTO listroles (usuario_id, rol_id, fecha_inicio) VALUES (:usuario_id, :rol_id, NOW())');
            $this->db->bind(':usuario_id', $usuarioId);
            $this->db->bind(':rol_id', $rolId);
            $this->db->execute();
            echo "✓ Rol asignado al usuario\n";

            // 4. Crear una sede para pruebas
            $this->db->query('INSERT INTO sede (nombre, direccion) VALUES ("Sede Test", "Dirección Test")');
            $this->db->execute();
            echo "✓ Sede de prueba creada\n";
        } catch (Exception $e) {
            echo "Error creando datos de prueba: " . $e->getMessage() . "\n";
            throw $e;  // Re-lanzar la excepción para que PHPUnit la capture
        }
    }

    /**
     * Limpia la base de datos antes de cada prueba
     * 
     * Este método:
     * - Desactiva temporalmente las restricciones de clave foránea
     * - Limpia todas las tablas relacionadas
     * - Reactiva las restricciones
     * 
     * @throws Exception Si hay algún error en la limpieza
     */
    private function cleanDatabase()
    {
        try {
            // Lista de tablas a limpiar en orden específico para evitar problemas de claves foráneas
            $tables = [
                'listroles',    // Primero las tablas relacionadas
                'mesas',
                'piso',
                'sede',
                'usuarios',
                'personas',
                'roles'         // Últimas las tablas base
            ];

            // Iniciar transacción para asegurar consistencia
            $this->db->beginTransaction();

            // Desactivar verificación de claves foráneas temporalmente
            $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
            $this->db->execute();

            // Limpiar cada tabla
            foreach ($tables as $table) {
                $this->db->query("TRUNCATE TABLE `$table`");
                $this->db->execute();
            }

            // Reactivar verificación de claves foráneas
            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
            $this->db->execute();

            // Confirmar los cambios
            $this->db->commit();
            echo "✓ Base de datos limpiada correctamente\n";
        } catch (Exception $e) {
            echo "Error limpiando base de datos: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    /**
     * Prueba el listado de sedes con usuario autenticado
     * 
     * Esta prueba verifica que:
     * - Se pueden listar las sedes cuando el usuario está autenticado
     * - El resultado tiene la estructura correcta
     * - Los datos de la sede de prueba están presentes
     * 
     * @test
     */
    public function testIndexWithAuthenticatedUser()
    {
        echo "\nPrueba de listado de sedes:";

        // Simular usuario autenticado
        $_SESSION['usuario_id'] = 1;

        // Ejecutar el método a probar
        $result = $this->sedeController->index();

        // Verificar el resultado usando assertions de PHPUnit
        $this->assertIsArray($result, "El resultado debe ser un array");
        $this->assertArrayHasKey('sedes', $result, "El array debe contener la clave 'sedes'");
        $this->assertEquals('Sede Test', $result['sedes'][0]['nombre'], "La sede de prueba debe existir");

        echo "\n✓ Listado de sedes recuperado correctamente";
        echo "\n✓ Se encontró la sede de prueba";
    }

    /**
     * Prueba el registro de una nueva sede
     * 
     * Esta prueba verifica que:
     * - Se puede registrar una nueva sede
     * - Los datos se guardan correctamente en la base de datos
     * - El método retorna el resultado esperado
     * 
     * @test
     */
    public function testRegistroSede()
    {
        echo "\nPrueba de registro de sede:";

        // Simular usuario autenticado y datos POST
        $_SESSION['usuario_id'] = 1;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'nombre' => 'Nueva Sede',
            'direccion' => 'Nueva Dirección'
        ];

        // Ejecutar el método a probar
        $result = $this->sedeController->registro();

        // Verificar el resultado de la operación
        $this->assertTrue($result, "El registro debe ser exitoso");
        echo "\n✓ Sede registrada correctamente";

        // Verificar que los datos se guardaron en la base de datos
        $this->db->query('SELECT * FROM sede WHERE nombre = :nombre');
        $this->db->bind(':nombre', 'Nueva Sede');
        $sede = $this->db->single();
        $this->assertEquals('Nueva Sede', $sede['nombre'], "La nueva sede debe existir en la base de datos");
        echo "\n✓ Sede verificada en base de datos";
    }

    /**
     * Método que se ejecuta después de cada prueba
     * 
     * PHPUnit ejecuta este método después de cada test para:
     * - Limpiar la base de datos
     * - Resetear variables de sesión
     * - Limpiar variables POST
     * - Restaurar el estado inicial
     * 
     * @return void
     */
    protected function tearDown(): void
    {
        echo "\nLimpiando entorno de pruebas...";
        $this->cleanDatabase();
        $_SESSION = [];
        $_POST = [];
        parent::tearDown();
        echo "✓ Entorno limpiado\n";
    }
}
