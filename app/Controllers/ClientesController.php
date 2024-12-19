<?php
class ClientesController extends Controller
{
    public function index()
    {
        Session::init();
        // Verificar si el usuario está autenticado
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR . '');
            exit();
        } else {
            $clienteModel = $this->model('Cliente');
            $clientes = $clienteModel->getAllClientes();

            $usuarioModel = $this->model('Usuario');
            $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));
            $this->view('clientes/index', ['clientes' => $clientes, 'rolUsuario' => $rolUsuario]);
        }
    }

    public function create()
    {
        Session::init();

        $usuarioModel = $this->model('Usuario');
        $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));
        // Verificar si el usuario está autenticado
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR . '');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'nombre' => trim($_POST['nombre']),
                'email' => trim($_POST['email']),
                'telefono' => trim($_POST['telefono']),
                'direccion' => trim($_POST['direccion']),
                'dni' => trim($_POST['dni'])
            ];
            $clienteModel = $this->model('Cliente');
            $result = $clienteModel->createCliente($data);

            if ($result) {
                header('Location: ' . CLIENT . '?success= cliente ' . $data['nombre'] . ' creado correctamente');
                exit();
            } else {
                header('Location: ' . CLIENT . '?error= nose pudo crear el cliente');
                exit();
            }
        } else {

            $usuarioModel = $this->model('Usuario');
            $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));
            $this->view('clientes/create', ['rolUsuario' => $rolUsuario]);
        }
    }
    public function edit($id)
    {
        Session::init();
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR);
            exit();
        } else {
            $clienteModel = $this->model('Cliente');
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $data = [
                    'id' => $id,
                    'nombre' => $_POST['nombre'],
                    'email' => $_POST['email'],
                    'telefono' => $_POST['telefono'],
                    'direccion' => $_POST['direccion'],
                    'dni' => $_POST['dni'],
                ];

                if ($clienteModel->updateCliente($data)) {
                    header('Location: ' . CLIENT . '?success= cliente actualizado correctamente');
                    exit();
                } else {
                    header('Location: ' . CLIENT . '?error=nose pudo actualizar el cliente ');
                    exit();
                }
            } else {
                $cliente = $clienteModel->getClienteById($id);
                $usuarioModel = $this->model('Usuario');
                $rolUsuario = $usuarioModel->getRolesUsuarioAutenticado(Session::get('usuario_id'));

                $this->view('clientes/edit', ['cliente' => $cliente, 'rolUsuario' => $rolUsuario]);
            }
        }
    }

    public function delete($id)
    {
        Session::init();
        // Verificar si el usuario está autenticado
        if (!Session::get('usuario_id')) {
            header('Location: ' . SALIR . '');
            exit();
        } else {
            $clienteModel = $this->model('Cliente');
            if ($clienteModel->deleteCliente($id)) {
                header('Location: ' . CLIENT . '?success= cliente eliminado correctamente');
                exit();
            } else {
                header('Location: ' . CLIENT . '?error= nose pudo elimnar el cliente');
                exit();
            }
        }
    }
}