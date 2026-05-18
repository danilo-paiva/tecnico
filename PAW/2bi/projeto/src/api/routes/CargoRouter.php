<?php
namespace Api\Routes;

use Slim\App;
use Api\Controllers\CargoController;
use Api\Middlewares\Cargo\ValidateCargoBody;
use Api\Middlewares\Cargo\ValidateCargoId;

class CargoRouter
{
    private App $app;

    public function __construct(App $app) {
        $this->app = $app;
    }

    public function setupRoutes(): void {
        $this->app->post('/cargos', [CargoController::class, 'createController'])->add(ValidateCargoBody::class);
        $this->app->get('/cargos', [CargoController::class, 'findAllController']);
        $this->app->get('/cargos/count', [CargoController::class, 'countController']);
        $this->app->get('/cargos/{idCargo}', [CargoController::class, 'findByIdController'])->add(ValidateCargoId::class);
        $this->app->put('/cargos/{idCargo}', [CargoController::class, 'updateController'])->add(ValidateCargoBody::class)->add(ValidateCargoId::class);
        $this->app->delete('/cargos/{idCargo}', [CargoController::class, 'deleteController'])->add(ValidateCargoId::class);
    }
}
