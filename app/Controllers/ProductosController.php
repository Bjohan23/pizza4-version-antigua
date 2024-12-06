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
                if (TESTING) return false;
                exit();
            }

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $data = [
                    'nombre' => trim($_POST['nombre']),
                    'descripcion' => trim($_POST['descripcion']),
                    'precio' => trim($_POST['precio']),
                    'disponible' => isset($_POST['disponible']) ? 1 : 0,
                    'categoria_id' => trim($_POST['categoria_id'])
                ];

                $result = $this->productoModel->createProducto($data);

                if (TESTING) return $result;

                if ($result) {
                    header('Location: ' . PRODUCT . '?success=Producto registrado correctamente');
                    exit();
                }
            }

            if (TESTING) return false;

            $rolUsuario = $this->usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));
            $categorias = $this->categoriaModel->getCategorias();
            $this->view('productos/create', [
                'categorias' => $categorias,
                'rolUsuario' => $rolUsuario
            ]);
        } catch (Exception $e) {
            if (TESTING) throw $e;
            error_log($e->getMessage());
            $this->view('error/index', ['mensaje' => 'Error al crear producto']);
        }
    }

    public function edit($id)
    {
        try {
            Session::init();
            if (!Session::get('usuario_id')) {
                header('Location: ' . SALIR);
                if (TESTING) return false;
                exit();
            }

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $data = [
                    'id' => $id,
                    'nombre' => trim($_POST['nombre']),
                    'descripcion' => trim($_POST['descripcion']),
                    'precio' => trim($_POST['precio']),
                    'disponible' => isset($_POST['disponible']) ? 1 : 0,
                    'categoria_id' => trim($_POST['categoria_id'])
                ];

                $result = $this->productoModel->updateProducto($data);

                if (TESTING) return $result;

                if ($result) {
                    header('Location: ' . PRODUCT . '?success=Producto actualizado correctamente');
                    exit();
                }
            }

            if (TESTING) return false;

            $producto = $this->productoModel->getProductoById($id);
            $categorias = $this->categoriaModel->getCategorias();
            $rolUsuario = $this->usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));

            $this->view('productos/edit', [
                'producto' => $producto,
                'categorias' => $categorias,
                'rolUsuario' => $rolUsuario
            ]);
        } catch (Exception $e) {
            if (TESTING) throw $e;
            error_log($e->getMessage());
            $this->view('error/index', ['mensaje' => 'Error al editar producto']);
        }
    }

    public function delete($id)
    {
        try {
            Session::init();
            if (!Session::get('usuario_id')) {
                header('Location: ' . SALIR);
                if (TESTING) return false;
                exit();
            }

            $result = $this->productoModel->deleteProducto($id);

            if (TESTING) return $result;

            if ($result) {
                header('Location: ' . PRODUCT . '?success=Producto eliminado correctamente');
                exit();
            }
        } catch (Exception $e) {
            if (TESTING) throw $e;
            error_log($e->getMessage());
            $this->view('error/index', ['mensaje' => 'Error al eliminar producto']);
        }
    }
}
