<?php
ob_start();

// Cargar configuración principal
require_once dirname(__DIR__) . '/config/config.php';

// Constantes base
if (!defined('TESTING')) define('TESTING', true);
if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__));

// Constantes de rutas
if (!defined('INICIO')) define('INICIO', '/inicio');
if (!defined('LOGIN')) define('LOGIN', '/login');
if (!defined('SALIR')) define('SALIR', '/logout');
if (!defined('SEDE')) define('SEDE', '/sede');
if (!defined('SEDE_CREATE')) define('SEDE_CREATE', '/sede/registro');

// Base de datos
require_once BASE_PATH . '/config/database.test.php';

// Core classes
require_once BASE_PATH . '/app/core/App.php';
require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/core/Database.php';
require_once BASE_PATH . '/app/core/Model.php';
require_once BASE_PATH . '/app/core/Session.php';

// Controllers
require_once BASE_PATH . '/app/Controllers/AuthController.php';
require_once BASE_PATH . '/app/Controllers/CategoriasController.php';
require_once BASE_PATH . '/app/Controllers/ClientesController.php';
require_once BASE_PATH . '/app/Controllers/HomeController.php';
require_once BASE_PATH . '/app/Controllers/MesasController.php';
require_once BASE_PATH . '/app/Controllers/PedidosController.php';
require_once BASE_PATH . '/app/Controllers/SedeController.php';
require_once BASE_PATH . '/app/Controllers/UsuariosController.php';
require_once BASE_PATH . '/app/Controllers/ProductosController.php';
require_once BASE_PATH . '/app/Controllers/VentasController.php';
require_once BASE_PATH . '/app/Controllers/RolesController.php';

// Models
require_once BASE_PATH . '/app/Models/Usuario.php';
require_once BASE_PATH . '/app/Models/Categoria.php';
require_once BASE_PATH . '/app/Models/Cliente.php';
require_once BASE_PATH . '/app/Models/Mesa.php';
require_once BASE_PATH . '/app/Models/Pedido.php';
require_once BASE_PATH . '/app/Models/Sede.php';
require_once BASE_PATH . '/app/Models/Persona.php';
require_once BASE_PATH . '/app/Models/Piso.php';
require_once BASE_PATH . '/app/Models/Producto.php';
require_once BASE_PATH . '/app/Models/ComprobanteVenta.php'; 
require_once BASE_PATH . '/app/Models/Rol.php'; 