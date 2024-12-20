<?php

class PisosController extends Controller
{
    public function index()
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            if (defined('TESTING') && TESTING) {
                return ['redirect' => SALIR];
            }
            header('Location: ' . SALIR . '');
            exit();
        }

        $pisoModel = $this->model('Piso');
        $pisos = $pisoModel->getPisosWithMesasCount();

        $usuarioModel = $this->model('Usuario');
        $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));

        if (defined('TESTING') && TESTING) {
            return [
                'pisos' => $pisos,
                'rolUsuario' => $rolUsuario
            ];
        }

        $this->view('pisos/index', ['pisos' => $pisos, 'rolUsuario' => $rolUsuario]);
    }

    public function create()
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            if (defined('TESTING') && TESTING) {
                return ['redirect' => SALIR];
            }
            header('Location: ' . SALIR . '');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'nombre' => $_POST['nombre'],
                'sede_id' => $_POST['sede_id']
            ];
            $pisoModel = $this->model('Piso');
            $result = $pisoModel->createPiso($data);

            if (defined('TESTING') && TESTING) {
                return $result;
            }

            if ($result) {
                header('Location: ' . PISOS . '?success=Piso registrado correctamente');
            } else {
                header('Location: ' . PISOS . '?error=No se pudo registrar el piso');
            }
            exit();
        } else {
            $sedeModel = $this->model('Sede');
            $sedes = $sedeModel->getAllSedes();
            $usuarioModel = $this->model('Usuario');
            $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));

            if (defined('TESTING') && TESTING) {
                return [
                    'sedes' => $sedes,
                    'rolUsuario' => $rolUsuario
                ];
            }

            $this->view('pisos/create', ['sedes' => $sedes, 'rolUsuario' => $rolUsuario]);
        }
    }

    public function edit($id)
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            if (defined('TESTING') && TESTING) {
                return ['redirect' => SALIR];
            }
            header('Location: ' . SALIR);
            exit();
        }

        $pisoModel = $this->model('Piso');
        $piso = $pisoModel->getPisoById($id);

        if (!$piso) {
            if (defined('TESTING') && TESTING) {
                return ['error' => 'No se encontró el piso'];
            }
            header('Location: ' . PISOS . '?error=No se encontró el piso.');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'id' => $id,
                'nombre' => $_POST['nombre'],
                'sede_id' => $_POST['sede_id']
            ];
            $result = $pisoModel->updatePiso($data);

            if (defined('TESTING') && TESTING) {
                return $result;
            }

            if ($result) {
                header('Location: ' . PISOS . '?success=Piso actualizado correctamente');
            } else {
                header('Location: ' . PISOS . '?error=No se pudo actualizar el piso correctamente');
            }
            exit();
        }

        $sedeModel = $this->model('Sede');
        $sedes = $sedeModel->getAllSedes();
        $usuarioModel = $this->model('Usuario');
        $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));

        if (defined('TESTING') && TESTING) {
            return [
                'piso' => $piso,
                'sedes' => $sedes,
                'rolUsuario' => $rolUsuario
            ];
        }

        $this->view('pisos/edit', [
            'piso' => $piso,
            'sedes' => $sedes,
            'rolUsuario' => $rolUsuario
        ]);
    }

    public function delete($id)
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            if (defined('TESTING') && TESTING) {
                return ['redirect' => SALIR];
            }
            header('Location: ' . SALIR);
            exit();
        }

        $pisoModel = $this->model('Piso');
        $result = $pisoModel->deletePiso($id);

        if (defined('TESTING') && TESTING) {
            return $result;
        }

        if ($result) {
            header('Location: ' . PISOS . '?success=Piso eliminado correctamente');
        } else {
            header('Location: ' . PISOS . '?error=No se pudo eliminar el piso');
        }
        exit();
    }

    public function mesas($id)
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            if (defined('TESTING') && TESTING) {
                return ['redirect' => SALIR];
            }
            header('Location: ' . SALIR);
            exit();
        }

        $pisoModel = $this->model('Piso');
        $piso = $pisoModel->getPisoById($id);
        $mesaModel = $this->model('Mesa');
        $mesas = $mesaModel->getMesasByPisoId($id);
        $usuarioModel = $this->model('Usuario');
        $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));

        if (defined('TESTING') && TESTING) {
            return [
                'piso' => $piso,
                'mesas' => $mesas,
                'rolUsuario' => $rolUsuario
            ];
        }

        $this->view('pisos/mesas', [
            'piso' => $piso,
            'mesas' => $mesas,
            'rolUsuario' => $rolUsuario
        ]);
    }
}
