<?php

declare(strict_types=1);

namespace Api\Routes;

use Api\Controllers\ParticipanteController;
use Api\Middlewares\ValidateParticipanteBody;
use Api\Middlewares\ValidateParticipanteId;
use Slim\App;

class ParticipanteRouter
{
    public static function routes(App $app): void
    {
        $app->get('/participantes', [ParticipanteController::class, 'findAll']);

        $app->get('/participantes/count', [ParticipanteController::class, 'count']);

        $app->get('/participantes/email/{email}', [ParticipanteController::class, 'findByEmail']);

        $app->get('/participantes/cpf/{cpf}', [ParticipanteController::class, 'findByCpf']);

        $app->get('/participantes/busca/email', [ParticipanteController::class, 'findByEmail']);

        $app->get('/participantes/busca/cpf', [ParticipanteController::class, 'findByCpf']);

        $app->get('/participantes/{id}', [ParticipanteController::class, 'findById'])
            ->add(ValidateParticipanteId::class);

        $app->post('/participantes', [ParticipanteController::class, 'create'])
            ->add(ValidateParticipanteBody::class);

        $app->put('/participantes/{id}', [ParticipanteController::class, 'update'])
            ->add(ValidateParticipanteBody::class)
            ->add(ValidateParticipanteId::class);

        $app->delete('/participantes/{id}', [ParticipanteController::class, 'delete'])
            ->add(ValidateParticipanteId::class);
    }
}
