<?php
namespace Api\Routes;

use Slim\App;
use Api\Controllers\FuncionarioController;
use Api\Middlewares\Funcionario\ValidateFuncionarioBody;
use Api\Middlewares\Funcionario\ValidateFuncionarioId;

class FuncionarioRouter
{
    private App $app;
    private FuncionarioController $controller;

    public function __construct(App $app, FuncionarioController $controller) {
        $this->app = $app;
        $this->controller = $controller;
    }

    public function setupRoutes(): void {
        $this->app->post('/funcionarios', [$this->controller, 'createController'])->add(ValidateFuncionarioBody::class);
        $this->app->get('/funcionarios', [$this->controller, 'findAllController']);
        $this->app->get('/funcionarios/count', [$this->controller, 'countController']);
        $this->app->get('/funcionarios/{idFuncionario}', [$this->controller, 'findByidController'])->add(ValidateFuncionarioId::class);
        $this->app->put('/funcionarios/{idFuncionario}', [$this->controller, 'updateController'])->add(ValidateFuncionarioBody::class)->add(ValidateFuncionarioId::class);
        $this->app->delete('/funcionarios/{idFuncionario}', [$this->controller, 'deleteController'])->add(ValidateFuncionarioId::class);
        $this->app->post('/funcionarios/login', [$this->controller, 'loginController']);
    }
}
