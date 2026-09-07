<?php
namespace Api\Routes;

use Slim\App;
use Api\Controllers\DepartamentoController;

/**
 * DepartamentoRouter
 * Define os endpoints da API para a gestão de departamentos.
 */
class DepartamentoRouter {
    public function routes(App $app) {
        // Listar todos os departamentos
        $app->get('/departamentos', [DepartamentoController::class, 'index']);
        // Retornar a contagem total
        $app->get('/departamentos/count', [DepartamentoController::class, 'count']);
        // Buscar departamento por ID
        $app->get('/departamentos/{idDepartamento}', [DepartamentoController::class, 'show']);
        // Criar novo departamento
        $app->post('/departamentos', [DepartamentoController::class, 'store']);
        // Atualizar departamento existente
        $app->put('/departamentos/{idDepartamento}', [DepartamentoController::class, 'update']);
        // Excluir departamento
        $app->delete('/departamentos/{idDepartamento}', [DepartamentoController::class, 'destroy']);
    }
}
