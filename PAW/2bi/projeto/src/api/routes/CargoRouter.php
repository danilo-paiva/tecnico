<?php
namespace Api\Routes;

use Slim\App;
use Api\Controllers\CargoController;
use Api\Middlewares\Cargo\ValidateCargoBody;
use Api\Middlewares\Cargo\ValidateCargoId;

/**
 * CargoRouter
 * Define os endpoints da API para a gestão de cargos.
 */
class CargoRouter
{
    private App $app;

    public function __construct(App $app) {
        $this->app = $app;
    }

    public function setupRoutes(): void {
        // Criar novo cargo (com validação de corpo)
        $this->app->post('/cargos', [CargoController::class, 'createController'])->add(ValidateCargoBody::class);
        // Listar todos os cargos
        $this->app->get('/cargos', [CargoController::class, 'findAllController']);
        // Contar cargos
        $this->app->get('/cargos/count', [CargoController::class, 'countController']);
        // Buscar cargo por ID (com validação de ID)
        $this->app->get('/cargos/{idCargo}', [CargoController::class, 'findByIdController'])->add(ValidateCargoId::class);
        // Atualizar cargo (com validações de corpo e ID)
        $this->app->put('/cargos/{idCargo}', [CargoController::class, 'updateController'])->add(ValidateCargoBody::class)->add(ValidateCargoId::class);
        // Deletar cargo (com validação de ID)
        $this->app->delete('/cargos/{idCargo}', [CargoController::class, 'deleteController'])->add(ValidateCargoId::class);
    }
}
