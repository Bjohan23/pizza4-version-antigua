# 📚 Guía de Testing para el Proyecto Pizza4
## 📚 Documentación

- [Guía de Instalación](README.md)

## 📋 Tabla de Contenidos
1. [Introducción](#introducción)
2. [Configuración](#configuración)
3. [Estructura de las Pruebas](#estructura-de-las-pruebas)
4. [Funciones PHPUnit Más Usadas](#funciones-phpunit-más-usadas)
5. [Patrones y Mejores Prácticas](#patrones-y-mejores-prácticas)
6. [Ejemplos Prácticos](#ejemplos-prácticos)

## 🎯 Introducción

Esta documentación explica cómo se realizan las pruebas en el proyecto Pizza4. Utilizamos PHPUnit como framework de testing y seguimos un enfoque de pruebas unitarias y de integración.

## ⚙️ Configuración

### Requisitos
- PHP 7.4 o superior
- PHPUnit 9.6
- Composer

### Instalación
```bash
# Instalar dependencias
composer install

# Ejecutar todas las pruebas
composer test

# Ejecutar pruebas específicas
composer test:roles    # Pruebas de roles
composer test:ventas   # Pruebas de ventas
composer test:auth     # Pruebas de autenticación
```

## 🏗️ Estructura de las Pruebas

### Organización de Archivos
```
test/
├── Controllers/           # Pruebas de controladores
│   ├── AuthControllerTest.php
│   ├── RolesControllerTest.php
│   └── VentasControllerTest.php
├── Models/               # Pruebas de modelos
└── bootstrap.php        # Archivo de inicialización
```

### Estructura Básica de una Prueba
```php
class RolesControllerTest extends TestCase
{
    protected function setUp(): void
    {
        // Preparación antes de cada prueba
    }

    public function testNombreDeLaPrueba()
    {
        // Arrange (Preparar)
        $datos = [...];

        // Act (Actuar)
        $resultado = $this->controlador->accion($datos);

        // Assert (Verificar)
        $this->assertEquals($esperado, $resultado);
    }

    protected function tearDown(): void
    {
        // Limpieza después de cada prueba
    }
}
```

## 🛠️ Funciones PHPUnit Más Usadas

### Aserciones Básicas
```php
// Verificar igualdad
$this->assertEquals($esperado, $actual);

// Verificar que algo es verdadero/falso
$this->assertTrue($condicion);
$this->assertFalse($condicion);

// Verificar que algo es null/no null
$this->assertNull($valor);
$this->assertNotNull($valor);

// Verificar que un array tiene una clave
$this->assertArrayHasKey('clave', $array);

// Verificar que un array está vacío/no vacío
$this->assertEmpty($array);
$this->assertNotEmpty($array);

// Verificar que una cadena contiene algo
$this->assertStringContainsString('buscar', $cadena);
```

### Manejo de Excepciones
```php
// Verificar que se lanza una excepción
$this->expectException(TipoDeExcepcion::class);
$this->expectExceptionMessage('mensaje esperado');

// O usando un bloque try-catch
try {
    $resultado = $funcion();
    $this->fail('Se esperaba una excepción');
} catch (Exception $e) {
    $this->assertInstanceOf(TipoDeExcepcion::class, $e);
}
```

## 🎯 Patrones y Mejores Prácticas

### 1. Patrón AAA (Arrange-Act-Assert)
```php
public function testCrearRol()
{
    // Arrange (Preparar)
    $_POST['nombre'] = 'Nuevo Rol';
    $_SESSION['usuario_id'] = 1;

    // Act (Actuar)
    $resultado = $this->rolesController->create();

    // Assert (Verificar)
    $this->assertTrue($resultado);
}
```

### 2. Datos de Prueba
```php
private function createTestData()
{
    // Crear datos de prueba de forma aislada
    $this->db->query('INSERT INTO roles (nombre) VALUES ("Rol Test")');
    // ... más inserciones
}
```

### 3. Limpieza de Ambiente
```php
protected function tearDown(): void
{
    $this->cleanDatabase();
    $_SESSION = [];
    $_POST = [];
}
```

## 📝 Ejemplos Prácticos

### Prueba de Autenticación
```php
public function testLoginConCredencialesValidas()
{
    // Preparar
    $_POST['email'] = 'test@example.com';
    $_POST['contraseña'] = 'password123';

    // Actuar
    $resultado = $this->authController->login();

    // Verificar
    $this->assertEquals(INICIO, $resultado);
    $this->assertNotNull($_SESSION['usuario_id']);
}
```

### Prueba de Crear Registro
```php
public function testCrearRolExitoso()
{
    // Preparar
    $_SESSION['usuario_id'] = 1;
    $_POST['nombre'] = 'Nuevo Rol';

    // Actuar
    $resultado = $this->rolesController->create();

    // Verificar en base de datos
    $this->db->query('SELECT nombre FROM roles WHERE nombre = :nombre');
    $this->db->bind(':nombre', 'Nuevo Rol');
    $rol = $this->db->single();
    
    $this->assertEquals('Nuevo Rol', $rol['nombre']);
}
```

### Prueba de Actualización
```php
public function testActualizarRol()
{
    // Preparar
    $_SESSION['usuario_id'] = 1;
    $_POST['nombre'] = 'Rol Actualizado';
    $id = 1;

    // Actuar
    $resultado = $this->rolesController->edit($id);

    // Verificar
    $this->db->query('SELECT nombre FROM roles WHERE id = :id');
    $this->db->bind(':id', $id);
    $rol = $this->db->single();
    
    $this->assertEquals('Rol Actualizado', $rol['nombre']);
}
```

## 🚀 Comandos Útiles

```bash
# Ejecutar todas las pruebas
composer test

# Ejecutar pruebas con cobertura
composer test:coverage

# Ejecutar pruebas específicas
composer test:roles
composer test:ventas
composer test:auth

# Ejecutar pruebas con detalles
composer test:debug

# Ejecutar pruebas en orden aleatorio
composer test:random
```

## 📌 Consejos Adicionales

1. **Aislamiento**: Cada prueba debe ser independiente y no depender de otras pruebas.

2. **Nombres Descriptivos**: Usar nombres que describan claramente qué se está probando:
   ```php
   testLoginRedireccionaAInicioConCredencialesValidas()
   testCrearRolFallaSiUsuarioNoEstaAutenticado()
   ```

3. **Datos de Prueba**: Usar datos específicos para pruebas, no datos de producción.

4. **Manejo de Errores**: Probar tanto casos exitosos como casos de error.

5. **Documentación**: Documentar casos especiales o configuraciones necesarias.

## ❓ Solución de Problemas Comunes

1. **Error de base de datos**: Verificar la configuración en `database.test.php`

2. **Pruebas no se ejecutan**: Verificar que PHPUnit está instalado correctamente

3. **Errores de sesión**: Limpiar `$_SESSION` en `tearDown()`

4. **Falsos positivos**: Verificar el orden de las pruebas y la limpieza de datos

## 🔍 Referencias

- [Documentación oficial de PHPUnit](https://phpunit.de/documentation.html)
- [Guía de testing en PHP](https://phptherightway.com/#testing)
- [Mejores prácticas de PHPUnit](https://phpunit.de/manual/6.5/en/writing-tests-for-phpunit.html)

