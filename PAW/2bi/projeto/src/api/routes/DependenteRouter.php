<?php
namespace Api\Routes;

use Slim\App;
use Api\Controllers\DependenteController;
use Api\Middlewares\Dependente\ValidateDependenteBody;

class DependenteRouter {
    public function routes(App $app) {
        $app->get('/dependentes', [DependenteController::class, 'index']);
        $app->get('/dependentes/funcionario/{idFuncionario}', [DependenteController::class, 'listByFuncionario']);
        $app->get('/dependentes/{idDependente}', [DependenteController::class, 'show']);
        $app->post('/dependentes', [DependenteController::class, 'store'])->add(new ValidateDependenteBody());
        $app->put('/dependentes/{idDependente}', [DependenteController::class, 'update']);
        $app->delete('/dependentes/{idDependente}', [DependenteController::class, 'destroy']);
    }
}
