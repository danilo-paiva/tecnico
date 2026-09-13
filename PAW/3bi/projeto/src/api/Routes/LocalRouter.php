<?php

namespace Api\Routes;

use Slim\App;
use Api\Controllers\LocalController;
use Api\Middlewares\Local\ValidateLocalBody;
use Api\Middlewares\Local\ValidateLocalId;
use Api\Middlewares\Participante\ValidateParticipanteToken;
use Api\Middlewares\Participante\ValidateAdministrador;

// POST /locais | GET /locais | GET /locais/count | GET /locais/{id}
// PUT+PATCH /locais/{id} | DELETE /locais/{id}
// TODAS exigem "Authorization: Bearer <token>" (ficha item 2).
// O token e conferido ANTES das demais validacoes (ultimo ->add executa primeiro).
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
            ->add(ValidateLocalBody::class)
            ->add(ValidateAdministrador::class)
            ->add(ValidateParticipanteToken::class);

        $this->app->get('/locais', [LocalController::class, 'findAllController'])
            ->add(ValidateParticipanteToken::class);

        // Antes de /{id} para o "count" nao virar parametro
        $this->app->get('/locais/count', [LocalController::class, 'countController'])
            ->add(ValidateParticipanteToken::class);

        $this->app->get('/locais/{id_local}', [LocalController::class, 'findByIdController'])
            ->add(ValidateLocalId::class)
            ->add(ValidateParticipanteToken::class);

        $this->app->put('/locais/{id_local}', [LocalController::class, 'updateController'])
            ->add(ValidateLocalBody::class)
            ->add(ValidateLocalId::class)
            ->add(ValidateAdministrador::class)
            ->add(ValidateParticipanteToken::class);

        $this->app->patch('/locais/{id_local}', [LocalController::class, 'updateController'])
            ->add(ValidateLocalBody::class)
            ->add(ValidateLocalId::class)
            ->add(ValidateAdministrador::class)
            ->add(ValidateParticipanteToken::class);

        $this->app->delete('/locais/{id_local}', [LocalController::class, 'deleteController'])
            ->add(ValidateLocalId::class)
            ->add(ValidateAdministrador::class)
            ->add(ValidateParticipanteToken::class);
    }
}
