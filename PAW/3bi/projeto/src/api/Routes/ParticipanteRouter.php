<?php

namespace Api\Routes;

use Slim\App;
use Api\Controllers\ParticipanteController;
use Api\Middlewares\Participante\ValidateParticipanteBody;
use Api\Middlewares\Participante\ValidateParticipanteId;
use Api\Middlewares\Participante\ValidateParticipanteToken;

// POST /participantes | GET /participantes | GET /participantes/count
// GET /participantes/{id} | PUT+PATCH /participantes/{id} | DELETE /participantes/{id}
// TODAS exigem "Authorization: Bearer <token>" (ficha item 2).
// O login (POST /login e POST /participantes/login) e publico e esta no AuthRouter.
// Para criar o PRIMEIRO usuario, use o seed do banco (docs/banco.sql) e faca login.
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
            ->add(ValidateParticipanteBody::class)
            ->add(ValidateParticipanteToken::class);

        $this->app->get('/participantes', [ParticipanteController::class, 'findAllController'])
            ->add(ValidateParticipanteToken::class);

        // Antes de /{id} para o "count" nao virar parametro
        $this->app->get('/participantes/count', [ParticipanteController::class, 'countController'])
            ->add(ValidateParticipanteToken::class);

        $this->app->get('/participantes/{id_participante}', [ParticipanteController::class, 'findByIdController'])
            ->add(ValidateParticipanteId::class)
            ->add(ValidateParticipanteToken::class);

        $this->app->put('/participantes/{id_participante}', [ParticipanteController::class, 'updateController'])
            ->add(ValidateParticipanteBody::class)
            ->add(ValidateParticipanteId::class)
            ->add(ValidateParticipanteToken::class);

        $this->app->patch('/participantes/{id_participante}', [ParticipanteController::class, 'updateController'])
            ->add(ValidateParticipanteBody::class)
            ->add(ValidateParticipanteId::class)
            ->add(ValidateParticipanteToken::class);

        $this->app->delete('/participantes/{id_participante}', [ParticipanteController::class, 'deleteController'])
            ->add(ValidateParticipanteId::class)
            ->add(ValidateParticipanteToken::class);
    }
}
