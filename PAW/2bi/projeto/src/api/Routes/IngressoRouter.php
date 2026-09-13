<?php

namespace Api\Routes;

use Slim\App;
use Api\Controllers\IngressoController;
use Api\Middlewares\Ingresso\ValidateIngressoBody;
use Api\Middlewares\Ingresso\ValidateIngressoId;

// POST /ingressos | GET /ingressos | GET /ingressos/count | GET /ingressos/{id}
// PUT+PATCH /ingressos/{id} | DELETE /ingressos/{id}
class IngressoRouter
{
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function setupRoutes(): void
    {
        $this->app->post('/ingressos', [IngressoController::class, 'createController'])
            ->add(ValidateIngressoBody::class);

        $this->app->get('/ingressos', [IngressoController::class, 'findAllController']);

        // Antes de /{id} para o "count" nao virar parametro
        $this->app->get('/ingressos/count', [IngressoController::class, 'countController']);

        $this->app->get('/ingressos/{id_ingresso}', [IngressoController::class, 'findByIdController'])
            ->add(ValidateIngressoId::class);

        $this->app->put('/ingressos/{id_ingresso}', [IngressoController::class, 'updateController'])
            ->add(ValidateIngressoBody::class)
            ->add(ValidateIngressoId::class);

        $this->app->patch('/ingressos/{id_ingresso}', [IngressoController::class, 'updateController'])
            ->add(ValidateIngressoBody::class)
            ->add(ValidateIngressoId::class);

        $this->app->delete('/ingressos/{id_ingresso}', [IngressoController::class, 'deleteController'])
            ->add(ValidateIngressoId::class);
    }
}
