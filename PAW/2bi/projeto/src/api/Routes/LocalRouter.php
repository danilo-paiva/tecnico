<?php

namespace Api\Routes;

use Slim\App;
use Api\Controllers\LocalController;
use Api\Middlewares\Local\ValidateLocalBody;
use Api\Middlewares\Local\ValidateLocalId;

// POST /locais | GET /locais | GET /locais/count | GET /locais/{id}
// PUT+PATCH /locais/{id} | DELETE /locais/{id}
class LocalRouter
{
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function setupRoutes(): void
    {
        $this->app->post('/locais', [LocalController::class, 'createController'])
            ->add(ValidateLocalBody::class);

        $this->app->get('/locais', [LocalController::class, 'findAllController']);

        // Antes de /{id} para o "count" nao virar parametro
        $this->app->get('/locais/count', [LocalController::class, 'countController']);

        $this->app->get('/locais/{id_local}', [LocalController::class, 'findByIdController'])
            ->add(ValidateLocalId::class);

        $this->app->put('/locais/{id_local}', [LocalController::class, 'updateController'])
            ->add(ValidateLocalBody::class)
            ->add(ValidateLocalId::class);

        $this->app->patch('/locais/{id_local}', [LocalController::class, 'updateController'])
            ->add(ValidateLocalBody::class)
            ->add(ValidateLocalId::class);

        $this->app->delete('/locais/{id_local}', [LocalController::class, 'deleteController'])
            ->add(ValidateLocalId::class);
    }
}
