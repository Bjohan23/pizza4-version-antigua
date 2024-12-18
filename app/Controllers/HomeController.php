<?php

class HomeController extends Controller
{
    public function index()
    {
        try {
            Session::init();

            // Redirección si no hay usuario autenticado
            if (!Session::get('usuario_id')) {
                $redirectUrl = SALIR;
                if (defined('TESTING') && TESTING === true) {
                    return $redirectUrl; // Devuelve la URL para pruebas
                }
                header('Location: ' . $redirectUrl);
                exit();
            }

            // Verificar si hay sedes registradas
            $sedeModel = $this->model('Sede');
            $sedeCount = $sedeModel->countSedes();

            if ($sedeCount == 0) {
                $redirectUrl = '/PIZZA4/public/sede/registro';
                if (defined('TESTING') && TESTING === true) {
                    return $redirectUrl; // Devuelve la URL para pruebas
                }
                header('Location: ' . $redirectUrl);
                exit();
            }

            // Obtener todos los conteos
            $usuarioModel = $this->model('Usuario');
            $clienteModel = $this->model('Cliente');
            $pedidoModel = $this->model('Pedido');
            $productoModel = $this->model('Producto');
            $pisoModel = $this->model('Piso');
            $rolModel = $this->model('Rol');
            $mesaModel = $this->model('Mesa');
            $categoriaModel = $this->model('Categoria');

            // Obtener datos del usuario actual
            $usuario = $usuarioModel->getUsuarioById(Session::get('usuario_id'));
            $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));

            // Preparar los datos para la vista
            $data = [
                'usuariosCount' => $usuarioModel->countUsuarios(),
                'clientesCount' => $clienteModel->countClientes(),
                'pedidosCount' => $pedidoModel->countPedidos(),
                'productosCount' => $productoModel->countProductos(),
                'pisoCount' => $pisoModel->pisosCount(),
                'rolesCount' => $rolModel->contadorDeRoles(),
                'mesasCount' => $mesaModel->mesasCount(),
                'categoriasCount' => $categoriaModel->categoriasCount(),
                'totalPedidosPorEstado' => $pedidoModel->getTotalPedidosPorEstado(),
                'productosMasVendidos' => $pedidoModel->getProductosMasVendidos(),
                'usuario' => $usuario,
                'rolUsuario' => $rolUsuario
            ];

            if (defined('TESTING') && TESTING === true) {
                return $data; // Devuelve datos para pruebas
            }

            $this->view('dashboard', $data); // Renderiza vista en producción
        } catch (Exception $e) {
            error_log("Error en HomeController: " . $e->getMessage());
            if (defined('TESTING') && TESTING === true) {
                throw $e; // Re-lanza excepciones en pruebas
            }
            $this->view('error/500', ['message' => 'Ha ocurrido un error en el servidor']);
        }
    }
}
