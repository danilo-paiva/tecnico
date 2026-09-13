<?php

namespace Api\Routes;

use Slim\App;
use Api\Controllers\CompraController;
use Api\Middlewares\Compra\ValidateCompraBody;
use Api\Middlewares\Compra\ValidateCompraId;
use Api\Middlewares\Participante\ValidateParticipanteToken;
use Api\Middlewares\Participante\ValidateAdministrador;

// POST /compras | GET /compras | GET /compras/count | GET /compras/{id}
// PUT+PATCH /compras/{id} | DELETE /compras/{id}
// TODAS exigem "Authorization: Bearer <token>" (ficha item 2).
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
            ->add(ValidateCompraBody::class)
            ->add(ValidateAdministrador::class)
            ->add(ValidateParticipanteToken::class);

        $this->app->get('/compras', [CompraController::class, 'findAllController'])
            ->add(ValidateParticipanteToken::class);

        // Antes de /{id} para o "count" nao virar parametro
        $this->app->get('/compras/count', [CompraController::class, 'countController'])
            ->add(ValidateParticipanteToken::class);

        $this->app->get('/compras/{id_compra}', [CompraController::class, 'findByIdController'])
            ->add(ValidateCompraId::class)
            ->add(ValidateParticipanteToken::class);

        $this->app->put('/compras/{id_compra}', [CompraController::class, 'updateController'])
            ->add(ValidateCompraBody::class)
            ->add(ValidateCompraId::class)
            ->add(ValidateAdministrador::class)
            ->add(ValidateParticipanteToken::class);

        $this->app->patch('/compras/{id_compra}', [CompraController::class, 'updateController'])
            ->add(ValidateCompraBody::class)
            ->add(ValidateCompraId::class)
            ->add(ValidateAdministrador::class)
            ->add(ValidateParticipanteToken::class);

        $this->app->delete('/compras/{id_compra}', [CompraController::class, 'deleteController'])
            ->add(ValidateCompraId::class)
            ->add(ValidateAdministrador::class)
            ->add(ValidateParticipanteToken::class);
    }
}
