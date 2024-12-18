<?php
class VentasController extends Controller
{
    public function index()
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            if (TESTING) {
                return ['redirect' => SALIR];
            }
            header('Location: ' . SALIR);
            exit();
        }

        $usuarioModel = $this->model('Usuario');
        $ventaModel = $this->model('ComprobanteVenta');

        try {
            $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));
            $ventas = $ventaModel->getVentas();

            if (TESTING) {
                return [
                    'ventas' => $ventas,
                    'rolUsuario' => $rolUsuario
                ];
            }

            $this->view('ventas/index', [
                'ventas' => $ventas,
                'rolUsuario' => $rolUsuario
            ]);
        } catch (Exception $e) {
            if (TESTING) {
                throw $e;
            }
            // En producción, maneja el error apropiadamente
            $this->view('error/index', ['mensaje' => 'Error al cargar las ventas']);
        }
    }
}
