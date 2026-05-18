<?php
namespace Api\Routes;

use Slim\App;
use Api\Controllers\DepartamentoController;

class DepartamentoRouter {
    public function routes(App $app) {
        $app->get('/departamentos', [DepartamentoController::class, 'index']);
        $app->get('/departamentos/count', [DepartamentoController::class, 'count']);
        $app->get('/departamentos/{idDepartamento}', [DepartamentoController::class, 'show']);
        $app->post('/departamentos', [DepartamentoController::class, 'store']);
        $app->put('/departamentos/{idDepartamento}', [DepartamentoController::class, 'update']);
        $app->delete('/departamentos/{idDepartamento}', [DepartamentoController::class, 'destroy']);
    }
}
