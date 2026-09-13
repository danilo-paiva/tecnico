<?php

namespace Api\Routes;

use Slim\App;
use Api\Controllers\ParticipanteController;
use Api\Middlewares\Participante\ValidateParticipanteBody;
use Api\Middlewares\Participante\ValidateParticipanteId;

// POST /participantes | GET /participantes | GET /participantes/count
// GET /participantes/{id} | PUT+PATCH /participantes/{id} | DELETE /participantes/{id}
class ParticipanteRouter
{
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function setupRoutes(): void
    {
        $this->app->post('/participantes', [ParticipanteController::class, 'createController'])
            ->add(ValidateParticipanteBody::class);

        $this->app->get('/participantes', [ParticipanteController::class, 'findAllController']);

        // Antes de /{id} para o "count" nao virar parametro
        $this->app->get('/participantes/count', [ParticipanteController::class, 'countController']);

        $this->app->get('/participantes/{id_participante}', [ParticipanteController::class, 'findByIdController'])
            ->add(ValidateParticipanteId::class);

        $this->app->put('/participantes/{id_participante}', [ParticipanteController::class, 'updateController'])
            ->add(ValidateParticipanteBody::class)
            ->add(ValidateParticipanteId::class);

        $this->app->patch('/participantes/{id_participante}', [ParticipanteController::class, 'updateController'])
            ->add(ValidateParticipanteBody::class)
            ->add(ValidateParticipanteId::class);

        $this->app->delete('/participantes/{id_participante}', [ParticipanteController::class, 'deleteController'])
            ->add(ValidateParticipanteId::class);
    }
}
