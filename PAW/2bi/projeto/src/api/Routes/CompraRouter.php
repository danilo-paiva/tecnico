<?php

namespace Api\Routes;

use Slim\App;
use Api\Controllers\CompraController;
use Api\Middlewares\Compra\ValidateCompraBody;
use Api\Middlewares\Compra\ValidateCompraId;

// POST /compras | GET /compras | GET /compras/count | GET /compras/{id}
// PUT+PATCH /compras/{id} | DELETE /compras/{id}
class CompraRouter
{
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function setupRoutes(): void
    {
        $this->app->post('/compras', [CompraController::class, 'createController'])
            ->add(ValidateCompraBody::class);

        $this->app->get('/compras', [CompraController::class, 'findAllController']);

        // Antes de /{id} para o "count" nao virar parametro
        $this->app->get('/compras/count', [CompraController::class, 'countController']);

        $this->app->get('/compras/{id_compra}', [CompraController::class, 'findByIdController'])
            ->add(ValidateCompraId::class);

        $this->app->put('/compras/{id_compra}', [CompraController::class, 'updateController'])
            ->add(ValidateCompraBody::class)
            ->add(ValidateCompraId::class);

        $this->app->patch('/compras/{id_compra}', [CompraController::class, 'updateController'])
            ->add(ValidateCompraBody::class)
            ->add(ValidateCompraId::class);

        $this->app->delete('/compras/{id_compra}', [CompraController::class, 'deleteController'])
            ->add(ValidateCompraId::class);
    }
}
