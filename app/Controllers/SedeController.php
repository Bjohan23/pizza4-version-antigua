<?php

class SedeController extends Controller
{
    public function index()
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR);
            if (TESTING) return;
            exit();
        }

        $sedeModel = $this->model('Sede');
        $sedes = $sedeModel->getAllSedes();
        $usuarioModel = $this->model('Usuario');
        $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));

        if (TESTING) {
            return ['sedes' => $sedes, 'rolUsuario' => $rolUsuario];
        }

        $this->view('sede/index', ['sedes' => $sedes, 'rolUsuario' => $rolUsuario]);
    }

    // Mostrar sede por ID
    public function mostrar($id)
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR);
            exit();
        } else {
            $sedeModel = $this->model('Sede');
            $sede = $sedeModel->getSedeById($id);

            $usuarioModel = $this->model('Usuario');
            $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));
            $this->view('sede/mostrar', ['sede' => $sede, 'rolUsuario' => $rolUsuario]);
        }
    }

    public function registro()
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR);
            if (TESTING) return;
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'direccion' => trim($_POST['direccion'] ?? '')
            ];

            $sedeModel = $this->model('Sede');
            if ($sedeModel->createSede($data)) {
                header('Location: ' . SEDE_CREATE . '?success=Sede registrada correctamente');
                if (TESTING) return true;
                exit();
            }
            
            $data['error'] = 'Error al registrar la sede.';
        }

        $usuarioModel = $this->model('Usuario');
        $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));
        
        if (TESTING) return $data ?? [];

        $this->view('sede/registro', ($data ?? []) + ['rolUsuario' => $rolUsuario]);
    }
}
