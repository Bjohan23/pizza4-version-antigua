# 📚 Guía de Testing para el Proyecto Pizza4

## 📚 Documentación

- [Guía de Instalación](README.md)

## 📋 Tabla de Contenidos

1. [Introducción](#introducción)
2. [Configuración](#configuración)
3. [Estructura de las Pruebas](#estructura-de-las-pruebas)
4. [Controladores y sus Tests](#controladores-y-sus-tests)
5. [Funciones PHPUnit Más Usadas](#funciones-phpunit-más-usadas)
6. [Patrones y Mejores Prácticas](#patrones-y-mejores-prácticas)
7. [Solución de Problemas](#solución-de-problemas)

## 🎯 Introducción

Esta guía explica cómo se realizan las pruebas en el proyecto Pizza4. Utilizamos PHPUnit como framework de testing, siguiendo un enfoque de pruebas unitarias y de integración.

## ⚙️ Configuración

### Requisitos

- PHP 7.4 o superior
- PHPUnit 9.6
- Composer
- MySQL/MariaDB

### Instalación y Ejecución

```bash
# Instalar dependencias
composer install

# Ejecutar todas las pruebas
composer test

# Ejecutar pruebas por controlador
composer test:auth      # Pruebas de autenticación
composer test:roles     # Pruebas de roles
composer test:usuarios  # Pruebas de usuarios
composer test:clientes  # Pruebas de clientes
composer test:sede      # Pruebas de sede
composer test:pisos     # Pruebas de pisos
composer test:mesas     # Pruebas de mesas
composer test:productos # Pruebas de productos
composer test:pedidos   # Pruebas de pedidos
composer test:ventas    # Pruebas de ventas
```

## 🏗️ Estructura de las Pruebas

### Organización de Archivos

```
test/
├── Controllers/
│   ├── AuthControllerTest.php
│   ├── RolesControllerTest.php
│   ├── UsuariosControllerTest.php
│   ├── ClientesControllerTest.php
│   ├── SedeControllerTest.php
│   ├── PisosControllerTest.php
│   ├── MesasControllerTest.php
│   ├── ProductosControllerTest.php
│   ├── PedidosControllerTest.php
│   └── VentasControllerTest.php
├── Models/
└── bootstrap.php
```

## 🎯 Controladores y sus Tests

### 1. AuthControllerTest

```php
class AuthControllerTest extends TestCase
{
    public function testLoginConCredencialesValidas()
    {
        $_POST['email'] = 'test@example.com';
        $_POST['contraseña'] = 'password123';
        $result = $this->authController->login();
        $this->assertEquals(INICIO, $result);
    }
}
```

### 2. RolesControllerTest

```php
class RolesControllerTest extends TestCase
{
    public function testCrearRol()
    {
        $_SESSION['usuario_id'] = 1;
        $_POST['nombre'] = 'Nuevo Rol';
        $result = $this->rolesController->create();
        $this->assertTrue($result);
    }
}
```

### 3. UsuariosControllerTest

```php
class UsuariosControllerTest extends TestCase
{
    public function testCreate()
    {
        $_SESSION['usuario_id'] = 1;
        $_POST = [
            'nombre' => 'Nuevo Usuario',
            'email' => 'nuevo@test.com',
            'telefono' => '123456789',
            'rol_id' => 1
        ];
        $result = $this->usuariosController->create();
        $this->assertTrue($result);
    }
}
```

### 4. ClientesControllerTest

```php
class ClientesControllerTest extends TestCase
{
    public function testCreate()
    {
        $_SESSION['usuario_id'] = 1;
        $_POST = [
            'nombre' => 'Nuevo Cliente',
            'email' => 'cliente@test.com',
            'telefono' => '987654321'
        ];
        $result = $this->clientesController->create();
        $this->assertTrue($result);
    }
}
```

### 5. SedeControllerTest

```php
class SedeControllerTest extends TestCase
{
    public function testRegistro()
    {
        $_SESSION['usuario_id'] = 1;
        $_POST = [
            'nombre' => 'Nueva Sede',
            'direccion' => 'Dirección Test'
        ];
        $result = $this->sedeController->registro();
        $this->assertTrue($result);
    }
}
```

### 6. PisosControllerTest

```php
class PisosControllerTest extends TestCase
{
    public function testCreate()
    {
        $_SESSION['usuario_id'] = 1;
        $_POST = [
            'nombre' => 'Nuevo Piso',
            'sede_id' => 1
        ];
        $result = $this->pisosController->create();
        $this->assertTrue($result);
    }
}
```

### 7. MesasControllerTest

```php
class MesasControllerTest extends TestCase
{
    public function testCreate()
    {
        $_SESSION['usuario_id'] = 1;
        $_POST = [
            'piso_id' => 1,
            'numero' => 1,
            'capacidad' => 4
        ];
        $result = $this->mesasController->create();
        $this->assertTrue($result);
    }
}
```

### 8. ProductosControllerTest

```php
class ProductosControllerTest extends TestCase
{
    public function testCreate()
    {
        $_SESSION['usuario_id'] = 1;
        $_POST = [
            'nombre' => 'Nuevo Producto',
            'precio' => 10.50,
            'categoria_id' => 1
        ];
        $result = $this->productosController->create();
        $this->assertTrue($result);
    }
}
```

### 9. PedidosControllerTest

```php
class PedidosControllerTest extends TestCase
{
    public function testCreate()
    {
        $_SESSION['usuario_id'] = 1;
        $_POST = [
            'cliente_id' => 1,
            'mesa_id' => 1,
            'productos' => [
                ['id' => 1, 'cantidad' => 2]
            ]
        ];
        $result = $this->pedidosController->create(1);
        $this->assertTrue($result);
    }
}
```

### 10. VentasControllerTest

```php
class VentasControllerTest extends TestCase
{
    public function testIndex()
    {
        $_SESSION['usuario_id'] = 1;
        $result = $this->ventasController->index();
        $this->assertArrayHasKey('ventas', $result);
    }
}
```

## 🔍 Patrones Comunes en los Tests

### 1. Estructura Base para Cada Test

```php
protected function setUp(): void
{
    parent::setUp();
    // 1. Definir constantes
    if (!defined('TESTING')) define('TESTING', true);

    // 2. Inicializar base de datos
    $this->db = new Database();

    // 3. Limpiar y preparar datos
    $this->cleanDatabase();
    $this->createTestData();

    // 4. Instanciar controlador
    $this->controller = new Controller();
}

protected function tearDown(): void
{
    // Limpiar después de cada prueba
    $this->cleanDatabase();
    $_SESSION = [];
    $_POST = [];
    parent::tearDown();
}
```

### 2. Creación de Datos de Prueba

```php
private function createTestData()
{
    // 1. Crear roles
    $this->db->query('INSERT INTO roles (nombre) VALUES ("admin")');
    $rolId = $this->db->lastInsertId();

    // 2. Crear persona
    $this->db->query('INSERT INTO personas (nombre, email) VALUES (...)');
    $personaId = $this->db->lastInsertId();

    // 3. Crear usuario
    $this->db->query('INSERT INTO usuarios (persona_id, contrasena) VALUES (...)');
    $usuarioId = $this->db->lastInsertId();

    // 4. Asignar rol
    $this->db->query('INSERT INTO listroles (usuario_id, rol_id) VALUES (...)');
}
```

### 3. Limpieza de Base de Datos

```php
private function cleanDatabase()
{
    $tables = [
        'pedidoscomanda',
        'detallespedido',
        'productos',
        'mesas',
        'piso',
        'sede',
        'clientes',
        'listroles',
        'usuarios',
        'personas',
        'roles'
    ];

    $this->db->beginTransaction();
    $this->db->query('SET FOREIGN_KEY_CHECKS = 0');

    foreach ($tables as $table) {
        $this->db->query("TRUNCATE TABLE `$table`");
        $this->db->execute();
    }

    $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
    $this->db->commit();
}
```
# 🛠️ PHPUnit 9: Funciones y Configuración

## 📋 Funciones de Aserción PHPUnit 9

### Aserciones Básicas
```php
// Compara valores esperando que sean iguales
$this->assertEquals($esperado, $actual);
// Descripción: Verifica que dos valores sean iguales usando el operador ==

// Compara valores esperando que sean idénticos
$this->assertSame($esperado, $actual);
// Descripción: Verifica que dos valores sean idénticos usando el operador ===

// Verifica que un valor sea verdadero
$this->assertTrue($valor);
// Descripción: Comprueba que un valor sea exactamente true

// Verifica que un valor sea falso
$this->assertFalse($valor);
// Descripción: Comprueba que un valor sea exactamente false

// Verifica que un valor sea null
$this->assertNull($valor);
// Descripción: Comprueba que un valor sea exactamente null

// Verifica que un valor no sea null
$this->assertNotNull($valor);
// Descripción: Comprueba que un valor no sea null
```

### Aserciones de Arrays
```php
// Verifica que un array tenga una clave específica
$this->assertArrayHasKey('clave', $array);
// Descripción: Comprueba si existe una clave en el array

// Verifica que un array no tenga una clave específica
$this->assertArrayNotHasKey('clave', $array);
// Descripción: Comprueba que no exista una clave en el array

// Verifica que un array esté vacío
$this->assertEmpty($array);
// Descripción: Comprueba que un array no tenga elementos

// Verifica que un array no esté vacío
$this->assertNotEmpty($array);
// Descripción: Comprueba que un array tenga al menos un elemento

// Verifica el tamaño de un array
$this->assertCount(3, $array);
// Descripción: Comprueba que un array tenga exactamente n elementos
```

### Aserciones de Strings
```php
// Verifica que una cadena contenga un texto
$this->assertStringContainsString('texto', $cadena);
// Descripción: Busca un texto dentro de una cadena

// Verifica que una cadena comience con un texto
$this->assertStringStartsWith('inicio', $cadena);
// Descripción: Comprueba el inicio de una cadena

// Verifica que una cadena termine con un texto
$this->assertStringEndsWith('final', $cadena);
// Descripción: Comprueba el final de una cadena
```

### Aserciones de Tipos
```php
// Verifica que un valor sea del tipo esperado
$this->assertIsArray($valor);
$this->assertIsString($valor);
$this->assertIsInt($valor);
$this->assertIsFloat($valor);
$this->assertIsBool($valor);
// Descripción: Comprueba el tipo de dato de un valor

// Verifica que un valor sea una instancia de una clase
$this->assertInstanceOf(MiClase::class, $objeto);
// Descripción: Comprueba el tipo de un objeto
```

### Aserciones de Excepciones
```php
// Verifica que se lance una excepción
$this->expectException(MiExcepcion::class);
// Descripción: Indica que se espera una excepción específica

// Verifica el mensaje de la excepción
$this->expectExceptionMessage('mensaje');
// Descripción: Verifica el mensaje exacto de la excepción

// Verifica que se lance una excepción con un código
$this->expectExceptionCode(404);
// Descripción: Verifica el código de la excepción
```

## 🚀 Scripts de Composer para Testing

```json
{
  "scripts": {
    // Ejecuta todas las pruebas
    "test": "phpunit",

    // Pruebas específicas por controlador
    "test:auth": "phpunit --colors=always test/Controllers/AuthControllerTest.php",
    // Muestra resultados con colores para mejor legibilidad

    "test:categoria": "phpunit --colors=always test/Controllers/CategoriaControllerTest.php",
    
    "test:sede": "phpunit --colors=always test/Controllers/SedeControllerTest.php",
    
    "test:usuario": "phpunit --colors=always test/Controllers/UsuariosControllerTest.php",
    
    "test:cliente": "phpunit --colors=always test/Controllers/ClientesControllerTest.php",
    
    // Pruebas con formato TestDox sin configuración personalizada
    "test:home": "phpunit --no-configuration --testdox --colors=always test/Controllers/HomeControllerTest.php",
    
    "test:mesas": "phpunit --colors=always test/Controllers/MesasControllerTest.php",
    
    // Pruebas con salida detallada y depuración
    "test:productos": "phpunit --colors=always --verbose --testdox --debug test/Controllers/ProductosControllerTest.php",
    
    // Pruebas con formato TestDox y salida detallada
    "test:pedidos": "phpunit --colors=always --verbose --testdox test/Controllers/PedidosControllerTest.php",
    "test:ventas": "phpunit --colors=always --verbose --testdox test/Controllers/VentasControllerTest.php",
    "test:roles": "phpunit --colors=always --verbose --testdox test/Controllers/RolesControllerTest.php",
    "test:pisos": "phpunit --colors=always --verbose --testdox test/Controllers/PisosControllerTest.php",
    
    // Prueba todos los controladores
    "test:controllers": "phpunit --colors=always test/Controllers/",
    
    // Genera reporte de cobertura HTML
    "test:coverage": "phpunit --coverage-html coverage"
  }
}
```

### 📌 Flags de PHPUnit Explicados

- `--colors=always`: Muestra los resultados con colores para mejor legibilidad
  - Verde: Pruebas exitosas
  - Rojo: Pruebas fallidas
  - Amarillo: Pruebas incompletas/saltadas

- `--verbose`: Muestra información detallada durante la ejecución
  - Incluye el nombre de cada prueba
  - Muestra el tiempo de ejecución
  - Muestra mensajes de depuración

- `--testdox`: Genera documentación legible de las pruebas
  - Convierte nombres de pruebas a oraciones
  - Facilita la lectura de resultados
  - Útil para documentación

- `--debug`: Muestra información adicional de depuración
  - Incluye stack traces completos
  - Muestra variables internas
  - Útil para resolver problemas

- `--no-configuration`: Ignora el archivo phpunit.xml
  - Usa configuración por defecto
  - Útil para pruebas rápidas

- `--coverage-html`: Genera reporte de cobertura HTML
  - Muestra qué código fue probado
  - Indica porcentaje de cobertura
  - Ayuda a identificar código sin pruebas

## 📚 Referencias

- [Documentación PHPUnit](https://phpunit.de/documentation.html)
- [Testing en PHP](https://phptherightway.com/#testing)
- [Best Practices](https://phpunit.de/manual/6.5/en/testing-practices.html)
