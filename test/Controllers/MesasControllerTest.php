<?php

use PHPUnit\Framework\TestCase;

class MesasControllerTest extends TestCase
{
    private $mesasController;
    private $mesaModelMock;
    private $pisoModelMock;
    private $usuarioModelMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mockear los modelos
        $this->mesaModelMock = $this->createMock(Mesa::class);
        $this->pisoModelMock = $this->createMock(Piso::class);
        $this->usuarioModelMock = $this->createMock(Usuario::class);

        // Crear una instancia del controlador con los mocks
        $this->mesasController = new MesasController();

        // Reemplazar el método `model` del controlador para devolver los mocks
        $this->mesasController->model = function ($modelName) {
            switch ($modelName) {
                case 'Mesa':
                    return $this->mesaModelMock;
                case 'Piso':
                    return $this->pisoModelMock;
                case 'Usuario':
                    return $this->usuarioModelMock;
            }
        };
    }

    public function testIndexWithAuthenticatedUser()
    {
        // Simular sesión
        $_SESSION['usuario_id'] = 1;

        // Configurar mocks
        $this->mesaModelMock->method('getMesas')->willReturn([
            ['id' => 1, 'numero' => 'Mesa 1', 'capacidad' => 4],
            ['id' => 2, 'numero' => 'Mesa 2', 'capacidad' => 2],
        ]);

        $this->usuarioModelMock->method('getRolesUsuarioAutenticado')
            ->with(1)
            ->willReturn(['admin']);

        // Capturar la salida de la vista
        ob_start();
        $this->mesasController->index();
        $output = ob_get_clean();

        // Verificar que la salida incluye las mesas y el rol
        $this->assertStringContainsString('Mesa 1', $output);
        $this->assertStringContainsString('admin', $output);
    }

    public function testIndexWithUnauthenticatedUser()
    {
        // Asegurarse de que la sesión está vacía
        $_SESSION = [];

        // Capturar headers enviados
        $this->expectOutputRegex('/Location: .*login/i');

        // Llamar al método
        $this->mesasController->index();
    }

    public function testCreateWithValidData()
    {
        $_SESSION['usuario_id'] = 1;

        // Simular pisos y roles
        $this->pisoModelMock->method('getPisos')->willReturn([
            ['id' => 1, 'nombre' => 'Piso 1'],
        ]);
        $this->usuarioModelMock->method('getRolesUsuarioAutenticado')
            ->willReturn(['admin']);

        // Simular datos POST
        $_POST = [
            'piso_id' => 1,
            'numero' => 'Mesa 3',
            'capacidad' => 6,
        ];

        $this->mesaModelMock->expects($this->once())
            ->method('createMesa')
            ->with($_POST)
            ->willReturn(true);

        // Capturar headers enviados
        $this->expectOutputRegex('/Location: .*TABLE.*/i');

        $this->mesasController->create();
    }

    public function testEditWithValidData()
    {
        $_SESSION['usuario_id'] = 1;

        $this->mesaModelMock->method('getMesaById')
            ->with(1)
            ->willReturn(['id' => 1, 'numero' => 'Mesa 1', 'capacidad' => 4]);

        $this->pisoModelMock->method('getPisos')->willReturn([
            ['id' => 1, 'nombre' => 'Piso 1'],
        ]);

        $_POST = [
            'piso_id' => 1,
            'numero' => 'Mesa Editada',
            'capacidad' => 8,
        ];

        $this->mesaModelMock->expects($this->once())
            ->method('updateMesa')
            ->with(['id' => 1] + $_POST)
            ->willReturn(true);

        // Capturar headers enviados
        $this->expectOutputRegex('/Location: .*TABLE.*/i');

        $this->mesasController->edit(1);
    }

    public function testDelete()
    {
        $_SESSION['usuario_id'] = 1;

        $this->mesaModelMock->method('getMesaById')
            ->with(1)
            ->willReturn(['id' => 1, 'piso_id' => 1]);

        $this->mesaModelMock->expects($this->once())
            ->method('deleteMesa')
            ->with(1)
            ->willReturn(true);

        // Capturar headers enviados
        $this->expectOutputRegex('/Location: .*TABLE.*/i');

        $this->mesasController->delete(1);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_POST = [];
        parent::tearDown();
    }
}
