<?php
namespace Api\Routes;

use Slim\App;
use Api\Controllers\FolhaPagamentoController;
use Api\Middlewares\FolhaPagamento\ValidateFolhaPagamentoBody;

class FolhaPagamentoRouter {
    public function routes(App $app) {
        $app->get('/folhas', [FolhaPagamentoController::class, 'index']);
        $app->get('/folhas/funcionario/{idFuncionario}', [FolhaPagamentoController::class, 'listByFuncionario']);
        $app->get('/folhas/{idFolha}', [FolhaPagamentoController::class, 'show']);
        $app->post('/folhas', [FolhaPagamentoController::class, 'store'])->add(new ValidateFolhaPagamentoBody());
        $app->put('/folhas/{idFolha}', [FolhaPagamentoController::class, 'update']);
        $app->delete('/folhas/{idFolha}', [FolhaPagamentoController::class, 'destroy']);
    }
}
