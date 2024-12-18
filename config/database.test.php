<?php
// Configuración de base de datos para pruebas
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'piza4');

// Configuración adicional para pruebas
error_reporting(E_ALL);
ini_set('display_errors', 1);