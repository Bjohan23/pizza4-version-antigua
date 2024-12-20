<?php

class MesasController extends Controller
{
    public function index()
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            if (defined('TESTING') && TESTING) {
                return ['redirect' => LOGIN];
            }
            header('Location: ' . LOGIN);
            exit();
        }

        $mesaModel = $this->model('Mesa');
        $mesas = $mesaModel->getMesas();

        $usuarioModel = $this->model('Usuario');
        $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));

        if (defined('TESTING') && TESTING) {
            return [
                'mesas' => $mesas,
                'rolUsuario' => $rolUsuario
            ];
        }

        $this->view('mesas/index', ['mesas' => $mesas, 'rolUsuario' => $rolUsuario]);
    }

    public function create()
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            if (defined('TESTING') && TESTING) {
                return ['redirect' => LOGIN];
            }
            header('Location: ' . LOGIN);
            exit();
        }

        $pisoModel = $this->model('Piso');
        $pisos = $pisoModel->getPisos();
        $usuarioModel = $this->model('Usuario');
        $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'piso_id' => $_POST['piso_id'],
                'numero' => $_POST['numero'],
                'capacidad' => $_POST['capacidad']
            ];

            $mesaModel = $this->model('Mesa');
            $result = $mesaModel->createMesa($data);

            if (defined('TESTING') && TESTING) {
                return $result;
            }

            if ($result) {
                header('Location: ' . TABLE . $data['piso_id'] . '?success=Mesa creada correctamente: ' . $data['numero']);
            } else {
                header('Location: ' . TABLE . $data['piso_id'] . '?error=No se pudo crear la mesa');
            }
            exit();
        }

        if (defined('TESTING') && TESTING) {
            return [
                'pisos' => $pisos,
                'rolUsuario' => $rolUsuario
            ];
        }

        $this->view('mesas/create', ['pisos' => $pisos, 'rolUsuario' => $rolUsuario]);
    }

    public function edit($id)
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            if (defined('TESTING') && TESTING) {
                return ['redirect' => LOGIN];
            }
            header('Location: ' . LOGIN);
            exit();
        }

        $mesaModel = $this->model('Mesa');
        $pisoModel = $this->model('Piso');
        $usuarioModel = $this->model('Usuario');
        $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));
        $pisos = $pisoModel->getPisos();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'id' => $id,
                'piso_id' => $_POST['piso_id'],
                'numero' => $_POST['numero'],
                'capacidad' => $_POST['capacidad']
            ];

            $result = $mesaModel->updateMesa($data);

            if (defined('TESTING') && TESTING) {
                return $result;
            }

            if ($result) {
                header('Location: ' . TABLE . $data['piso_id'] . '?success=Mesa actualizada correctamente');
            } else {
                header('Location: ' . TABLE . $data['piso_id'] . '?error=No se pudo actualizar la mesa');
            }
            exit();
        }

        $mesa = $mesaModel->getMesaById($id);

        if (defined('TESTING') && TESTING) {
            return [
                'mesa' => $mesa,
                'pisos' => $pisos,
                'rolUsuario' => $rolUsuario
            ];
        }

        $this->view('mesas/edit', ['mesa' => $mesa, 'pisos' => $pisos, 'rolUsuario' => $rolUsuario]);
    }

    public function delete($id)
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            if (defined('TESTING') && TESTING) {
                return ['redirect' => LOGIN];
            }
            header('Location: ' . LOGIN);
            exit();
        }

        $mesaModel = $this->model('Mesa');
        $piso_id = $mesaModel->getMesaById($id);
        $result = $mesaModel->deleteMesa($id);

        if (defined('TESTING') && TESTING) {
            return $result;
        }

        if ($result) {
            header('Location: ' . TABLE . $piso_id['piso_id'] . '?success=Mesa eliminada correctamente');
        } else {
            header('Location: ' . TABLE . $piso_id['piso_id'] . '?error=Error al eliminar la mesa');
        }
        exit();
    }
}
