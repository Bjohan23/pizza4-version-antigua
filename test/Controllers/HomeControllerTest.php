<?php

use PHPUnit\Framework\TestCase;

class HomeControllerTest extends TestCase
{
    private $homeController;
    private $sessionMock;
    private $sedeModelMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear mocks de las dependencias
        $this->sessionMock = $this->createMock(Session::class);
        $this->sedeModelMock = $this->createMock(Sede::class);

        // Configurar el controlador con los mocks
        $this->homeController = new HomeController();
        $this->homeController->setSession($this->sessionMock);
        $this->homeController->setSedeModel($this->sedeModelMock);
    }

    public function testIndexUserNotAuthenticated()
    {
        // Configurar el mock de sesión para simular un usuario no autenticado
        $this->sessionMock->expects($this->once())
                          ->method('get')
                          ->with('usuario_id')
                          ->willReturn(null);

        ob_start(); // Capturar salida
        $this->homeController->index();
        $output = ob_get_clean(); // Obtener salida

        $this->assertStringContainsString('Location: ' . SALIR, $output);
    }

    public function testIndexRedirectToSedeRegistroWhenNoSedesExist()
    {
        // Configurar el mock de sesión para simular un usuario autenticado
        $this->sessionMock->expects($this->once())
                          ->method('get')
                          ->with('usuario_id')
                          ->willReturn(1);

        // Configurar el mock de Sede para que devuelva 0 sedes
        $this->sedeModelMock->expects($this->once())
                            ->method('countSedes')
                            ->willReturn(0);

        ob_start(); // Capturar salida
        $this->homeController->index();
        $output = ob_get_clean(); // Obtener salida

        $this->assertStringContainsString('Location: /PIZZA4/public/sede/registro', $output);
    }

    public function testIndexLoadsDashboardViewWithDefaultData()
    {
        // Configurar el mock de sesión para simular un usuario autenticado
        $this->sessionMock->expects($this->once())
                          ->method('get')
                          ->with('usuario_id')
                          ->willReturn(1);

        // Configurar el mock de Sede para devolver un conteo positivo de sedes
        $this->sedeModelMock->expects($this->once())
                            ->method('countSedes')
                            ->willReturn(1);

        // Sobrescribir la función view para capturar los datos pasados
        $this->homeController->setView(function ($view, $data) use (&$capturedData) {
            $capturedData = $data; // Capturar los datos pasados a la vista
        });

        $this->homeController->index();

        // Verificar que los datos predeterminados se cargan correctamente
        $this->assertArrayHasKey('usuariosCount', $capturedData);
        $this->assertEquals(0, $capturedData['usuariosCount']);
        $this->assertArrayHasKey('clientesCount', $capturedData);
        $this->assertEquals(0, $capturedData['clientesCount']);
        $this->assertArrayHasKey('productosMasVendidos', $capturedData);
        $this->assertIsArray($capturedData['productosMasVendidos']);
    }

    public function testIndexHandlesExceptionGracefully()
    {
        // Configurar el mock de sesión para simular un usuario autenticado
        $this->sessionMock->expects($this->once())
                          ->method('get')
                          ->with('usuario_id')
                          ->willReturn(1);

        // Configurar el mock de Sede para lanzar una excepción
        $this->sedeModelMock->expects($this->once())
                            ->method('countSedes')
                            ->willThrowException(new Exception('Simulated exception'));

        // Sobrescribir la función view para capturar la vista de error
        $this->homeController->setView(function ($view, $data) use (&$capturedData, &$capturedView) {
            $capturedData = $data;
            $capturedView = $view;
        });

        $this->homeController->index();

        // Verificar que la vista de error se carga correctamente
        $this->assertEquals('error/500', $capturedView);
        $this->assertArrayHasKey('message', $capturedData);
        $this->assertEquals('Ha ocurrido un error en el servidor', $capturedData['message']);
    }
}

