<?php
namespace Api\Routes;

use Slim\App;
use Api\Controllers\FuncionarioController;
use Api\Middlewares\Funcionario\ValidateFuncionarioBody;
use Api\Middlewares\Funcionario\ValidateFuncionarioId;

/**
 * FuncionarioRouter
 * Define os endpoints da API para a gestão de funcionários e autenticação.
 */
class FuncionarioRouter
{
    private App $app;
    private FuncionarioController $controller;

    public function __construct(App $app, FuncionarioController $controller) {
        $this->app = $app;
        $this->controller = $controller;
    }

    public function setupRoutes(): void {
        // Cadastro de funcionário (com validação de corpo)
        $this->app->post('/funcionarios', [$this->controller, 'createController'])->add(ValidateFuncionarioBody::class);
        // Listar todos os funcionários
        $this->app->get('/funcionarios', [$this->controller, 'findAllController']);
        // Contar funcionários
        $this->app->get('/funcionarios/count', [$this->controller, 'countController']);
        // Buscar funcionário por ID (com validação de ID)
        $this->app->get('/funcionarios/{idFuncionario}', [$this->controller, 'findByidController'])->add(ValidateFuncionarioId::class);
        // Atualizar funcionário (com validações de corpo e ID)
        $this->app->put('/funcionarios/{idFuncionario}', [$this->controller, 'updateController'])->add(ValidateFuncionarioBody::class)->add(ValidateFuncionarioId::class);
        // Deletar funcionário (com validação de ID)
        $this->app->delete('/funcionarios/{idFuncionario}', [$this->controller, 'deleteController'])->add(ValidateFuncionarioId::class);
        // Autenticação de usuário
        $this->app->post('/funcionarios/login', [$this->controller, 'loginController']);
    }
}
