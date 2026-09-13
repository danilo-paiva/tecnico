<?php

namespace Api\Routes;

use Slim\App;
use Api\Controllers\EventoController;
use Api\Middlewares\Evento\ValidateEventoBody;
use Api\Middlewares\Evento\ValidateEventoId;
use Api\Middlewares\Participante\ValidateParticipanteToken;
use Api\Middlewares\Participante\ValidateAdministrador;

// POST /eventos | GET /eventos | GET /eventos/count | GET /eventos/{id}
// PUT+PATCH /eventos/{id} | DELETE /eventos/{id}
// TODAS exigem "Authorization: Bearer <token>" (ficha item 2).
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
            ->add(ValidateEventoBody::class)
            ->add(ValidateAdministrador::class)
            ->add(ValidateParticipanteToken::class);

        $this->app->get('/eventos', [EventoController::class, 'findAllController'])
            ->add(ValidateParticipanteToken::class);

        // Antes de /{id} para o "count" nao virar parametro
        $this->app->get('/eventos/count', [EventoController::class, 'countController'])
            ->add(ValidateParticipanteToken::class);

        $this->app->get('/eventos/{id_evento}', [EventoController::class, 'findByIdController'])
            ->add(ValidateEventoId::class)
            ->add(ValidateParticipanteToken::class);

        $this->app->put('/eventos/{id_evento}', [EventoController::class, 'updateController'])
            ->add(ValidateEventoBody::class)
            ->add(ValidateEventoId::class)
            ->add(ValidateAdministrador::class)
            ->add(ValidateParticipanteToken::class);

        $this->app->patch('/eventos/{id_evento}', [EventoController::class, 'updateController'])
            ->add(ValidateEventoBody::class)
            ->add(ValidateEventoId::class)
            ->add(ValidateAdministrador::class)
            ->add(ValidateParticipanteToken::class);

        $this->app->delete('/eventos/{id_evento}', [EventoController::class, 'deleteController'])
            ->add(ValidateEventoId::class)
            ->add(ValidateAdministrador::class)
            ->add(ValidateParticipanteToken::class);
    }
}
