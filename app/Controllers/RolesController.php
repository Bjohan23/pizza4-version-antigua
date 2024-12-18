<?php
class RolesController extends Controller
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

        try {
            $rolModel = $this->model('Rol');
            $roles = $rolModel->getAllRoles();
            $usuarioModel = $this->model('Usuario');
            $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));

            if (TESTING) {
                return [
                    'roles' => $roles,
                    'rolUsuario' => $rolUsuario
                ];
            }

            $this->view('roles/index', [
                'roles' => $roles,
                'rolUsuario' => $rolUsuario
            ]);
        } catch (Exception $e) {
            if (TESTING) {
                throw $e;
            }
            $this->view('error/index', ['mensaje' => 'Error al cargar los roles']);
        }
    }

    public function create()
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            if (TESTING) {
                return ['redirect' => SALIR];
            }
            header('Location: ' . SALIR);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $nombre = $_POST['nombre'];
                $rolModel = $this->model('Rol');
                $rolModel->createRol($nombre);

                if (TESTING) {
                    return true;
                }

                header('Location: ' . ROL . '?success=Rol creado correctamente');
                exit();
            } catch (Exception $e) {
                if (TESTING) {
                    throw $e;
                }
                header('Location: ' . ROL . '?error=Error al crear el rol');
                exit();
            }
        }

        try {
            $usuarioModel = $this->model('Usuario');
            $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));

            if (TESTING) {
                return ['rolUsuario' => $rolUsuario];
            }

            $this->view('roles/create', ['rolUsuario' => $rolUsuario]);
        } catch (Exception $e) {
            if (TESTING) {
                throw $e;
            }
            $this->view('error/index', ['mensaje' => 'Error al cargar el formulario']);
        }
    }

    public function edit($id)
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            if (TESTING) {
                return ['redirect' => SALIR];
            }
            header('Location: ' . SALIR);
            exit();
        }

        try {
            $rolModel = $this->model('Rol');

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $nombre = $_POST['nombre'];
                $rolModel->updateRol($id, $nombre);

                if (TESTING) {
                    return true;
                }

                header('Location: ' . ROL . '?success=Rol actualizado correctamente');
                exit();
            } else {
                $rol = $rolModel->getRolById($id);
                $usuarioModel = $this->model('Usuario');
                $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));

                if (TESTING) {
                    return [
                        'rol' => $rol,
                        'rolUsuario' => $rolUsuario
                    ];
                }

                $this->view('roles/edit', [
                    'rol' => $rol,
                    'rolUsuario' => $rolUsuario
                ]);
            }
        } catch (Exception $e) {
            if (TESTING) {
                throw $e;
            }

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                header('Location: ' . ROL . '?error=Error al actualizar el rol');
                exit();
            }

            $this->view('error/index', ['mensaje' => 'Error al cargar el formulario de edición']);
        }
    }

    public function delete($id)
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            if (TESTING) {
                return ['redirect' => SALIR];
            }
            header('Location: ' . SALIR);
            exit();
        }

        try {
            $rolModel = $this->model('Rol');
            $rolModel->deleteRol($id);

            if (TESTING) {
                return true;
            }

            header('Location: ' . ROL . '?success=Rol eliminado correctamente');
            exit();
        } catch (Exception $e) {
            if (TESTING) {
                throw $e;
            }
            header('Location: ' . ROL . '?error=Error al eliminar el rol');
            exit();
        }
    }
}
