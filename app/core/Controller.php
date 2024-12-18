<?php

class Controller
{
    public function model($model)
    {
        $modelPath = defined('BASE_PATH')
            ? BASE_PATH . '/app/Models/' . $model . '.php'
            : '../app/Models/' . $model . '.php';

        require_once $modelPath;
        return new $model();
    }

    public function view($view, $data = [])
    {
        // Si estamos en modo prueba, solo simulamos la vista
        if (defined('TESTING') && TESTING === true) {
            // En pruebas, solo necesitamos verificar que la vista existe
            if (!empty($data['error'])) {
                echo $data['error'];
            }
            return;
        }

        // Extraer los datos para hacerlos disponibles en la vista
        if (!empty($data)) {
            extract($data);
        }

        require_once __DIR__ . '/Session.php';
        Session::init();

        // Definir la ruta base de las vistas usando BASE_PATH si está definido
        $viewPath = defined('BASE_PATH')
            ? BASE_PATH . '/app/Views/'
            : '../app/Views/';

        // Array de archivos a incluir
        $includes = [
            'head' => $viewPath . 'inc/head.php',
            'navbar' => $viewPath . 'inc/navbar.php',
            'sidebar' => $viewPath . 'inc/sidebar.php',
            'footer' => $viewPath . 'inc/footer.php',
            'script' => $viewPath . 'inc/script.php',
            'alertas' => $viewPath . 'inc/js/alertas.php'
        ];

        // Verificar y cargar los archivos requeridos
        foreach ($includes as $name => $file) {
            if (file_exists($file)) {
                include_once $file;
            } else if ($name !== 'footer' && $name !== 'script' && $name !== 'alertas') {
                // Solo mostrar error para archivos obligatorios
                die("Error: No se encuentra el archivo {$name}.php");
            }
        }

        // Cargar la vista principal
        $mainView = $viewPath . $view . '.php';
        if (file_exists($mainView)) {
            require_once $mainView;
        } else {
            die('Error: No se encuentra la vista ' . $view);
        }
    }
}
