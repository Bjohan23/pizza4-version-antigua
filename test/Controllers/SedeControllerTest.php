<?php
use PHPUnit\Framework\TestCase;

/**
* Pruebas unitarias para el controlador de Sedes
*/
class SedeControllerTest extends TestCase
{
   private $sedeController;
   private $db;

   /**
    * Configura el entorno para las pruebas
    */
   protected function setUp(): void
   {
       parent::setUp();

       // Definir constantes necesarias
       if (!defined('TESTING')) define('TESTING', true);
       if (!defined('SALIR')) define('SALIR', '/logout');
       if (!defined('SEDE')) define('SEDE', '/sede');
       if (!defined('SEDE_CREATE')) define('SEDE_CREATE', '/sede/registro');

       $this->db = new Database();
       echo "\nPreparando base de datos para pruebas...";
       $this->cleanDatabase();
       $this->createTestData();
       echo "✓ Base de datos lista\n";

       $this->sedeController = new SedeController();
   }

   /**
    * Crea datos iniciales de prueba
    */
   private function createTestData()
   {
       try {
           // Crear rol admin
           $this->db->query('INSERT INTO roles (nombre) VALUES ("admin")');
           $this->db->execute();
           $rolId = $this->db->lastInsertId();
           echo "✓ Rol admin creado\n";

           // Crear usuario de prueba
           $this->db->query('INSERT INTO personas (nombre, email) VALUES ("Test User", "test@test.com")');
           $this->db->execute();
           $personaId = $this->db->lastInsertId();
           
           $this->db->query('INSERT INTO usuarios (persona_id, contrasena) VALUES (:persona_id, :contrasena)');
           $this->db->bind(':persona_id', $personaId);
           $this->db->bind(':contrasena', password_hash('test123', PASSWORD_DEFAULT));
           $this->db->execute();
           $usuarioId = $this->db->lastInsertId();
           echo "✓ Usuario de prueba creado\n";

           // Asignar rol admin al usuario
           $this->db->query('INSERT INTO listroles (usuario_id, rol_id, fecha_inicio) VALUES (:usuario_id, :rol_id, NOW())');
           $this->db->bind(':usuario_id', $usuarioId);
           $this->db->bind(':rol_id', $rolId);
           $this->db->execute();
           echo "✓ Rol asignado al usuario\n";

           // Crear sede inicial
           $this->db->query('INSERT INTO sede (nombre, direccion) VALUES ("Sede Test", "Dirección Test")');
           $this->db->execute();
           echo "✓ Sede de prueba creada\n";

       } catch (Exception $e) {
           echo "Error creando datos de prueba: " . $e->getMessage() . "\n";
           throw $e;
       }
   }

   /**
    * Limpia la base de datos antes de cada prueba
    */
   private function cleanDatabase()
   {
       try {
           $tables = [
               'listroles',
               'mesas', 
               'piso',
               'sede',
               'usuarios',
               'personas',
               'roles'
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

           echo "✓ Base de datos limpiada correctamente\n";

       } catch (Exception $e) {
           echo "Error limpiando base de datos: " . $e->getMessage() . "\n";
           throw $e;
       }
   }

   /**
    * Prueba el listado de sedes con usuario autenticado
    */
   public function testIndexWithAuthenticatedUser() 
   {
       echo "\nPrueba de listado de sedes:";
       $_SESSION['usuario_id'] = 1;
       $result = $this->sedeController->index();
       
       $this->assertIsArray($result, "El resultado debe ser un array");
       $this->assertArrayHasKey('sedes', $result, "El array debe contener la clave 'sedes'");
       $this->assertEquals('Sede Test', $result['sedes'][0]['nombre'], "La sede de prueba debe existir");
       
       echo "\n✓ Listado de sedes recuperado correctamente";
       echo "\n✓ Se encontró la sede de prueba";
   }
   
   /**
    * Prueba el registro de una nueva sede
    */
   public function testRegistroSede()
   {
       echo "\nPrueba de registro de sede:";
       $_SESSION['usuario_id'] = 1;
       $_SERVER['REQUEST_METHOD'] = 'POST';
       $_POST = [
           'nombre' => 'Nueva Sede',
           'direccion' => 'Nueva Dirección'
       ];
   
       $result = $this->sedeController->registro();
       $this->assertTrue($result, "El registro debe ser exitoso");
       echo "\n✓ Sede registrada correctamente";
   
       $this->db->query('SELECT * FROM sede WHERE nombre = :nombre');
       $this->db->bind(':nombre', 'Nueva Sede');
       $sede = $this->db->single();
       $this->assertEquals('Nueva Sede', $sede['nombre'], "La nueva sede debe existir en la base de datos");
       echo "\n✓ Sede verificada en base de datos";
   }

   /**
    * Limpia el entorno después de cada prueba
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