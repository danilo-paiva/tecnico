<?php
namespace Api\Routes;

use Slim\App;
use Api\Controllers\FolhaPagamentoController;
use Api\Middlewares\FolhaPagamento\ValidateFolhaPagamentoBody;

/**
 * FolhaPagamentoRouter
 * Define os endpoints da API para a gestão de folhas de pagamento.
 */
class FolhaPagamentoRouter {
    public function routes(App $app) {
        // Listar todas as folhas de pagamento
        $app->get('/folhas', [FolhaPagamentoController::class, 'index']);
        // Listar folhas de um funcionário específico
        $app->get('/folhas/funcionario/{idFuncionario}', [FolhaPagamentoController::class, 'listByFuncionario']);
        // Buscar folha de pagamento por ID
        $app->get('/folhas/{idFolha}', [FolhaPagamentoController::class, 'show']);
        // Criar novo registro de folha (com validação de corpo)
        $app->post('/folhas', [FolhaPagamentoController::class, 'store'])->add(new ValidateFolhaPagamentoBody());
        // Atualizar folha existente
        $app->put('/folhas/{idFolha}', [FolhaPagamentoController::class, 'update']);
        // Excluir registro de folha
        $app->delete('/folhas/{idFolha}', [FolhaPagamentoController::class, 'destroy']);
    }
}
