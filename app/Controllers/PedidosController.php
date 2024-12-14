<?php
class PedidosController extends Controller
{
    public function index()
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR);
            if (TESTING) return false;
            exit();
        }

        $pisoModel = $this->model('Piso');
        $pisos = $pisoModel->getPisos();
        $usuarioModel = $this->model('Usuario');
        $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));

        if (TESTING) {
            return ['pisos' => $pisos, 'rolUsuario' => $rolUsuario];
        }

        $this->view('pedidos/index', [
            'pisos' => $pisos,
            'rolUsuario' => $rolUsuario
        ]);
    }
    public function selectMesa($piso_id)
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR);
            exit();
        }

        $mesaModel = $this->model('Mesa');
        $mesas = $mesaModel->getMesasByPiso($piso_id);
        $usuarioModel = $this->model('Usuario');
        $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));
        $this->view('pedidos/selectMesa', [
            'mesas' => $mesas,
            'piso_id' => $piso_id,
            'rolUsuario' => $rolUsuario
        ]);
    }

    public function viewMesa($mesa_id)
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR);
            exit();
        }

        $pedidoModel = $this->model('Pedido');
        $pedidos = $pedidoModel->getPedidosByMesa($mesa_id);
        $productoModel = $this->model('Producto');
        $productos = $productoModel->getAllProductos();
        $clienteModel = $this->model('Cliente');
        $cliente = $clienteModel->getClienteById($pedidos[0]['cliente_id']);
        $usuarioModel = $this->model('Usuario');
        $usuario = $usuarioModel->getUsuarioById(Session::get('usuario_id'));
        $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));
        if ($usuario) {
            $usuario = (object) $usuario;
        }

        $mesaModel = $this->model('Mesa');
        $mesa = $mesaModel->getMesaById($mesa_id);

        if ($mesa) {
            $mesa = (object) $mesa;
        }

        $pedido = isset($pedidos[0]) ? $pedidos[0] : null;


        $this->view('pedidos/viewMesa', [
            'mesa_id' => $mesa_id,
            'pedidos' => $pedidos,
            'productos' => $productos,
            'cliente' => $cliente,
            'mesa' => $mesa,
            'usuario' => $usuario,
            'pedido' => $pedido,
            'rolUsuario' => $rolUsuario
        ]);
    }

    public function create($mesa_id)
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR);
            if (TESTING) return false;
            exit();
        }
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $pedidoData = [
                    'mesa_id' => $mesa_id,
                    'cliente_id' => $_POST['cliente_id'],
                    'usuario_id' => Session::get('usuario_id'),
                    'estado' => 'pendiente',
                    'total' => $_POST['total'],
                    'fecha' => date('Y-m-d H:i:s')
                ];
    
                $pedidoModel = $this->model('Pedido');
                $pedidoId = $pedidoModel->createPedido($pedidoData);
    
                if (!$pedidoId) {
                    if (TESTING) return false;
                    throw new Exception("Error al crear el pedido");
                }
    
                // Actualizar estado de mesa
                $mesaModel = $this->model('Mesa');
                $mesaModel->updateEstado($mesa_id, 'ocupada');
    
                // Agregar detalles del pedido
                if (isset($_POST['productos']) && is_array($_POST['productos'])) {
                    foreach ($_POST['productos'] as $producto) {
                        if ($producto['cantidad'] > 0) {
                            $detalleData = [
                                'pedido_id' => $pedidoId,
                                'producto_id' => $producto['id'],
                                'cantidad' => $producto['cantidad'],
                                'precio' => $producto['precio'],
                                'descripcion' => $producto['descripcion2'] ?? '',
                                'estado' => 'pendiente'
                            ];
                            
                            if (!$pedidoModel->addDetalle($detalleData)) {
                                if (TESTING) return false;
                                throw new Exception("Error al agregar detalle del pedido");
                            }
                        }
                    }
                }
    
                if (TESTING) return true;
    
                header('Location: /PIZZA4/public/pedidos/viewMesa/' . $mesa_id . "?success=pedido registrado con exito");
                exit();
    
            } catch (Exception $e) {
                if (TESTING) return false;
                
                $clienteModel = $this->model('Cliente');
                $productoModel = $this->model('Producto');
    
                $this->view('pedidos/create', [
                    'error' => $e->getMessage(),
                    'mesa_id' => $mesa_id,
                    'clientes' => $clienteModel->getAllClientes(),
                    'productos' => $productoModel->getAllProductos()
                ]);
            }
        } else {
            try {
                $clienteModel = $this->model('Cliente');
                $productoModel = $this->model('Producto');
                $usuarioModel = $this->model('Usuario');
    
                $data = [
                    'mesa_id' => $mesa_id,
                    'clientes' => $clienteModel->getAllClientes(),
                    'productos' => $productoModel->getAllProductos(),
                    'cliente_id' => $_GET['cliente_id'] ?? null,
                    'rolUsuario' => $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'))
                ];
    
                if (TESTING) return $data;
    
                $this->view('pedidos/create', $data);
    
            } catch (Exception $e) {
                if (TESTING) return false;
                throw $e;
            }
        }
    }

    public function update($id)
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR);
            exit();
        }

        $pedidoModel = $this->model('Pedido');
        $mesaModel = $this->model('Mesa');
        $clienteModel = $this->model('Cliente');
        $productoModel = $this->model('Producto');



        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'id' => $id,
                'mesa_id' => $_POST['mesa_id'],
                'cliente_id' => $_POST['cliente_id'],
                'usuario_id' => Session::get('usuario_id'),
                'estado' => $_POST['estado'],
                'total' => $_POST['total'],
                'fecha' => date('Y-m-d H:i:s')
            ];

            if ($pedidoModel->updatePedido($data)) {
                $pedidoModel->deleteDetallesByPedido($id);

                foreach ($_POST['productos'] as $producto_id => $detalle) {
                    if ($detalle['cantidad'] > 0) {
                        $producto = $productoModel->getProductoById($producto_id);
                        $detalleData = [
                            'pedido_id' => $id,
                            'producto_id' => $producto_id,
                            'cantidad' => $detalle['cantidad'],
                            'precio' => $producto->precio
                        ];
                        $pedidoModel->addDetalle($detalleData);
                    }
                }

                header('Location: ' . VIEW_MESA . $_POST['mesa_id']);
                exit();
            } else {
                die('Error al actualizar el pedido');
            }
        } else {
            $pedido = $pedidoModel->getPedidoById($id);
            $mesas = $mesaModel->getAllMesas();
            $productos = $productoModel->getAllProductos();
            $clientes = $clienteModel->getAllClientes();

            $this->view('pedidos/update', [
                'pedido' => $pedido,
                'mesas' => $mesas,
                'clientes' => $clientes,
                'productos' => $productos
            ]);
        }
    }
    public function actualizarProducto($pedido_id)
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            Session::init();
            if (!Session::get('usuario_id')) {
                header('Location: ' . SALIR);
                exit();
            }

            $pedidoModel = $this->model('Pedido');
            $data = [
                "pedido_id" => $pedido_id,
                "cantidad" => $_POST["cantidad"]
            ];

            $success = $pedidoModel->updateDetallePedido($data);

            if ($success) {
                header("Location: " . $_SERVER["HTTP_REFERER"]);
            } else {
                echo "ERROR";
                exit();
            }
        }
    }

    public function eliminarProducto($pedido_id, $producto_id)
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR);
            exit();
        }

        $pedidoModel = $this->model('Pedido');
        if ($pedidoModel->deleteDetalle($pedido_id, $producto_id)) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            die('Error al eliminar el producto del pedido');
        }
    }

    public function liberarMesa($mesa_id)
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR);
            exit();
        }

        $pedidoModel = $this->model('Pedido');

        // Obtener todos los pedidos asociados a la mesa
        $pedidos = $pedidoModel->getPedidosByMesa($mesa_id);

        // Eliminar cada pedido y sus detalles
        foreach ($pedidos as $pedido) {
            $pedidoModel->deletePedido(['detalle_id' => $pedido['id'], 'comanda_id' => $pedido['pedido_id']]);
        }

        // Actualizar el estado de la mesa a 'libre'
        $mesaModel = $this->model('Mesa');
        $mesaModel->updateEstado($mesa_id, 'libre');

        header('Location: ' . ORDER_SELECTMESA . $_POST['id']);
        exit();
    }

    public function allPedidos()
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR);
            exit();
        }
        $pedidosModel = $this->model('Pedido');
        $pedidos = $pedidosModel->getAllPedidosWithDetails();
        $usuarioModel = $this->model('Usuario');
        $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));

        $pedidosAgrupados = [];
        foreach ($pedidos as $pedido) {
            $mesa = $pedido['mesa'];
            if (!isset($pedidosAgrupados[$mesa])) {
                $pedidosAgrupados[$mesa] = [
                    'mesa' => $mesa,
                    'usuario' => $pedido['usuario'],
                    'fecha' => $pedido['fecha'],
                    'estado' => $pedido['estado'],
                    'descripcion' => $pedido['descripcion']
                ];
            } else {
                $pedidosAgrupados[$mesa]['descripcion'] .= ', ' . $pedido['descripcion'];
            }
        }
        $this->view('pedidos/allPedidos', ['pedidosAgrupados' => $pedidosAgrupados, 'rolUsuario' => $rolUsuario]);
    }

    public function cobrar($id)
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR);
            if (TESTING) return false;
            exit();
        }

        try {
            $pedidoModel = $this->model('Pedido');
            $pedido = $pedidoModel->getPedidoById($_POST['pedido_id']);

            if (!$pedido) {
                if (TESTING) return false;
                throw new Exception('Pedido no encontrado');
            }

            $pedidoData = [
                'pedido_id' => $_POST['pedido_id'],
                'fecha' => date('Y-m-d H:i:s'),
                'estado' => 'pagado',
                'monto' => $_POST['total'],
                'tipo' => $_POST['tipo']
            ];

            if ($pedidoModel->updateEstadoPedido($pedidoData)) {
                $mesaModel = $this->model('Mesa');
                $mesaModel->updateEstado($pedido['mesa_id'], 'libre');

                $comprobanteModel = $this->model('ComprobanteVenta');
                if ($comprobanteModel->createComprobante($pedidoData)) {
                    if (TESTING) return true;

                    if ($_POST['boleta'] === 'si') {
                        $boletaData = $pedidoModel->getDetailedPedidoById($pedidoData['pedido_id']);
                        $this->imprimirBoleta($boletaData);
                    }

                    header('Location: ' . ORDER . '?success=Pedido pagado con éxito');
                    exit();
                }
            }

            if (TESTING) return false;
            throw new Exception('Error al procesar el pago');
        } catch (Exception $e) {
            if (TESTING) return false;
            error_log($e->getMessage());
            die($e->getMessage());
        }
    }

    public function imprimirBoleta($datos)
    {

        Session::init();
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR);
            exit();
        }


        return true;
    }

    public function enviarCorreo($datos)
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR);
            exit();
        }

        try {

            return 'Correo enviado exitosamente';
        } catch (Exception $e) {
            exit();
            error_log('Error al enviar el correo: ');
            return null;
        }
    }
}
