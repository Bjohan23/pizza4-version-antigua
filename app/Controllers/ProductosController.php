<?php
class ProductosController extends Controller
{
    private $productoModel;
    private $usuarioModel;
    private $categoriaModel;

    public function __construct()
    {
        // Inicializar los modelos que necesitamos
        $this->productoModel = $this->model('Producto');
        $this->usuarioModel = $this->model('Usuario');
        $this->categoriaModel = $this->model('Categoria');
    }

    public function index()
    {
        try {
            Session::init();
            if (!Session::get('usuario_id')) {
                header('Location: ' . SALIR);
                if (TESTING) return;
                exit();
            }

            $productos = $this->productoModel->getAllProductos();
            $rolUsuario = $this->usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));

            if (TESTING) {
                return ['productos' => $productos, 'rolUsuario' => $rolUsuario];
            }

            $this->view('productos/index', ['productos' => $productos, 'rolUsuario' => $rolUsuario]);
        } catch (Exception $e) {
            if (TESTING) throw $e;
            error_log($e->getMessage());
            $this->view('error/index', ['mensaje' => 'Error al cargar productos']);
        }
    }

    public function create()
    {
        try {
            Session::init();

            if (!Session::get('usuario_id')) {
                header('Location: ' . SALIR);
                exit();
            }

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // Validar datos del POST
                if (
                    !isset($_POST['nombre']) || !isset($_POST['descripcion']) ||
                    !isset($_POST['precio']) || !isset($_POST['categoria_id'])
                ) {
                    throw new Exception("Datos de formulario incompletos");
                }

                $data = [
                    'nombre' => trim($_POST['nombre']),
                    'descripcion' => trim($_POST['descripcion']),
                    'precio' => trim($_POST['precio']),
                    'disponible' => isset($_POST['disponible']) ? 1 : 0,
                    'categoria_id' => trim($_POST['categoria_id'])
                ];

                if ($this->productoModel->createProducto($data)) {
                    header('Location: ' . PRODUCT . '?success=Producto registrado correctamente');
                    exit();
                } else {
                    throw new Exception("Error al crear el producto");
                }
            } else {
                $rolUsuario = $this->usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));
                $categorias = $this->categoriaModel->getCategorias();

                if ($categorias === false || $rolUsuario === false) {
                    throw new Exception("Error al obtener datos para el formulario");
                }

                $this->view('productos/create', [
                    'categorias' => $categorias,
                    'rolUsuario' => $rolUsuario
                ]);
            }
        } catch (Exception $e) {
            error_log("Error en ProductosController->create: " . $e->getMessage());
            $this->view('error/index', [
                'mensaje' => 'Ha ocurrido un error al crear el producto'
            ]);
        }
    }

    public function edit($id)
    {
        try {
            Session::init();

            if (!Session::get('usuario_id')) {
                header('Location: ' . SALIR);
                exit();
            }

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // Validar datos del POST
                if (
                    !isset($_POST['nombre']) || !isset($_POST['descripcion']) ||
                    !isset($_POST['precio']) || !isset($_POST['categoria_id'])
                ) {
                    throw new Exception("Datos de formulario incompletos");
                }

                $data = [
                    'id' => $id,
                    'nombre' => trim($_POST['nombre']),
                    'descripcion' => trim($_POST['descripcion']),
                    'precio' => trim($_POST['precio']),
                    'disponible' => isset($_POST['disponible']) ? 1 : 0,
                    'categoria_id' => trim($_POST['categoria_id'])
                ];

                if ($this->productoModel->updateProducto($data)) {
                    header('Location: ' . PRODUCT . '?success=Producto actualizado correctamente');
                    exit();
                } else {
                    throw new Exception("Error al actualizar el producto");
                }
            }

            $producto = $this->productoModel->getProductoById($id);
            $categorias = $this->categoriaModel->getCategorias();
            $rolUsuario = $this->usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));

            if ($producto === false || $categorias === false || $rolUsuario === false) {
                throw new Exception("Error al obtener datos para la edición");
            }

            $this->view('productos/edit', [
                'producto' => $producto,
                'categorias' => $categorias,
                'rolUsuario' => $rolUsuario
            ]);
        } catch (Exception $e) {
            error_log("Error en ProductosController->edit: " . $e->getMessage());
            $this->view('error/index', [
                'mensaje' => 'Ha ocurrido un error al editar el producto'
            ]);
        }
    }

    public function delete($id)
    {
        try {
            Session::init();

            if (!Session::get('usuario_id')) {
                header('Location: ' . SALIR);
                exit();
            }

            if ($this->productoModel->deleteProducto($id)) {
                header('Location: ' . PRODUCT . '?success=Producto eliminado correctamente');
                exit();
            } else {
                throw new Exception("Error al eliminar el producto");
            }
        } catch (Exception $e) {
            error_log("Error en ProductosController->delete: " . $e->getMessage());
            $this->view('error/index', [
                'mensaje' => 'Ha ocurrido un error al eliminar el producto'
            ]);
        }
    }
}
