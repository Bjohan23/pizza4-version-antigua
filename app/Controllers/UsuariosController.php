<?php
class UsuariosController extends Controller
{
    public function index()
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR);
            if (TESTING) return;
            exit();
        }

        $usuarioModel = $this->model('Usuario');
        $usuarios = $usuarioModel->getUsuarios();
        $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));

        if (TESTING) {
            return ['usuarios' => $usuarios, 'rolUsuario' => $rolUsuario];
        }

        $this->view('usuarios/index', ['usuarios' => $usuarios, 'rolUsuario' => $rolUsuario]);
    }

    public function create()
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR);
            if (TESTING) return false;
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'nombre' => $_POST['nombre'] ?? '',
                'email' => $_POST['email'] ?? '',
                'telefono' => $_POST['telefono'] ?? '',
                'direccion' => $_POST['direccion'] ?? '',
                'contrasena' => password_hash($_POST['contrasena'], PASSWORD_DEFAULT),
                'rol_id' => $_POST['rol_id'],
                'dni' => $_POST['dni']
            ];

            try {
                $personaModel = $this->model('Persona');
                $usuarioModel = $this->model('Usuario');
                $listRolesModel = $this->model('ListRoles');

                $persona_id = $personaModel->create($data['nombre'], $data['email'], $data['telefono'], $data['direccion'], $data['dni']);

                $usuario_id = $usuarioModel->createUsuario([
                    'persona_id' => $persona_id,
                    'contrasena' => $data['contrasena']
                ]);

                $listRolesModel->assignRole($usuario_id, $data['rol_id']);

                if (TESTING) return true;

                header('Location: ' . USER . '?success=Usuario creado con éxito');
                exit();
            } catch (Exception $e) {
                if (TESTING) return false;
                header('Location: ' . USER . '?error=' . $e->getMessage());
                exit();
            }
        }

        if (TESTING) return false;

        $rolModel = $this->model('Rol');
        $roles = $rolModel->getAllRoles();
        $this->view('usuarios/create', ['roles' => $roles]);
    }

    public function edit($id)
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR);
            if (TESTING) return false;
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $data = [
                    'id' => $id,
                    'nombre' => $_POST['nombre'],
                    'email' => $_POST['email'],
                    'telefono' => $_POST['telefono'],
                    'direccion' => $_POST['direccion'],
                    'dni' => $_POST['dni'],
                    'contrasena' => !empty($_POST['contrasena']) ? password_hash($_POST['contrasena'], PASSWORD_DEFAULT) : null,
                    'rol_id' => $_POST['rol_id']
                ];

                $usuarioModel = $this->model('Usuario');
                $personaModel = $this->model('Persona');
                $listRolesModel = $this->model('ListRoles');

                // Actualizar persona
                $persona_id = $usuarioModel->getPersonaIdByUsuarioId($id);
                $personaModel->update(
                    $persona_id,
                    $data['nombre'],
                    $data['email'],
                    $data['telefono'],
                    $data['direccion'],
                    $data['dni']
                );

                // Actualizar contraseña si se proporcionó una nueva
                if ($data['contrasena']) {
                    $usuarioModel->updateUsuarioContrasenia([
                        'id' => $id,
                        'contrasena' => $data['contrasena']
                    ]);
                }

                // Actualizar rol
                $listRolesModel->updateRole($id, $data['rol_id']);

                if (TESTING) {
                    return true;
                }

                header('Location: ' . USER . '?success=Usuario actualizado con éxito');
                exit();
            } catch (Exception $e) {
                if (TESTING) return false;
                header('Location: ' . USER . '?error=' . $e->getMessage());
                exit();
            }
        } else {
            if (TESTING) return [
                'title' => 'Editar Usuario',
                'submit' => 'Actualizar'
            ];

            $usuarioModel = $this->model('Usuario');
            $rolModel = $this->model('Rol');

            $usuario = $usuarioModel->getUsuarioById($id);
            $roles = $rolModel->getAllRoles();
            $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));

            $this->view('usuarios/edit', [
                'usuario' => $usuario,
                'roles' => $roles,
                'rolUsuario' => $rolUsuario
            ]);
        }
    }

    public function delete($id)
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR);
            if (TESTING) return false;
            exit();
        }

        try {
            $usuarioModel = $this->model('Usuario');
            $usuarioModel->deleteUsuario($id);

            if (TESTING) return true;

            header('Location: ' . USER . '?success=Usuario eliminado');
            exit();
        } catch (Exception $e) {
            if (TESTING) throw $e;

            header('Location: ' . USER . '?error=' . $e->getMessage());
            exit();
        }
    }


    public function cuentaUsuario($id)
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR . '');
            exit();
        } else {
            $usuarioModel = $this->model('Usuario');
            $usuario = $usuarioModel->getUsuarioById($id);
            if ($usuario) {
                $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));
                $this->view('cuenta/index', ['usuario' => $usuario, 'rolUsuario' => $rolUsuario]);
            }
        }
    }
}
