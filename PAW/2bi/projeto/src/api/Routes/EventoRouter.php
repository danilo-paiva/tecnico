<?php

namespace Api\Routes;

use Slim\App;
use Api\Controllers\EventoController;
use Api\Middlewares\Evento\ValidateEventoBody;
use Api\Middlewares\Evento\ValidateEventoId;

// POST /eventos | GET /eventos | GET /eventos/count | GET /eventos/{id}
// PUT+PATCH /eventos/{id} | DELETE /eventos/{id}
class EventoRouter
{
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function setupRoutes(): void
    {
        $this->app->post('/eventos', [EventoController::class, 'createController'])
            ->add(ValidateEventoBody::class);

        $this->app->get('/eventos', [EventoController::class, 'findAllController']);

        // Antes de /{id} para o "count" nao virar parametro
        $this->app->get('/eventos/count', [EventoController::class, 'countController']);

        $this->app->get('/eventos/{id_evento}', [EventoController::class, 'findByIdController'])
            ->add(ValidateEventoId::class);

        $this->app->put('/eventos/{id_evento}', [EventoController::class, 'updateController'])
            ->add(ValidateEventoBody::class)
            ->add(ValidateEventoId::class);

        $this->app->patch('/eventos/{id_evento}', [EventoController::class, 'updateController'])
            ->add(ValidateEventoBody::class)
            ->add(ValidateEventoId::class);

        $this->app->delete('/eventos/{id_evento}', [EventoController::class, 'deleteController'])
            ->add(ValidateEventoId::class);
    }
}
