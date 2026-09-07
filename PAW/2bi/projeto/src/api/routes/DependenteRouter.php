<?php
namespace Api\Routes;

use Slim\App;
use Api\Controllers\DependenteController;
use Api\Middlewares\Dependente\ValidateDependenteBody;

/**
 * DependenteRouter
 * Define os endpoints da API para a gestão de dependentes.
 */
class DependenteRouter {
    public function routes(App $app) {
        // Listar todos os dependentes
        $app->get('/dependentes', [DependenteController::class, 'index']);
        // Listar dependentes de um funcionário específico
        $app->get('/dependentes/funcionario/{idFuncionario}', [DependenteController::class, 'listByFuncionario']);
        // Buscar dependente por ID
        $app->get('/dependentes/{idDependente}', [DependenteController::class, 'show']);
        // Criar novo dependente (com validação de corpo)
        $app->post('/dependentes', [DependenteController::class, 'store'])->add(new ValidateDependenteBody());
        // Atualizar dependente existente
        $app->put('/dependentes/{idDependente}', [DependenteController::class, 'update']);
        // Excluir dependente
        $app->delete('/dependentes/{idDependente}', [DependenteController::class, 'destroy']);
    }
}
