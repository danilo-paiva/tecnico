<?php

declare(strict_types=1);

namespace Api\Routes;

use Api\Controllers\EventoController;
use Api\Middlewares\ValidateEventoBody;
use Api\Middlewares\ValidateEventoId;
use Slim\App;

class EventoRouter
{
    public static function routes(App $app): void
    {
        $app->get('/eventos', [EventoController::class, 'findAll']);

        $app->get('/eventos/count', [EventoController::class, 'count']);

        $app->get('/eventos/local/{idLocal}', [EventoController::class, 'findByLocal'])
            ->add(ValidateEventoId::class);

        $app->get('/eventos/{id}', [EventoController::class, 'findById'])
            ->add(ValidateEventoId::class);

        $app->post('/eventos', [EventoController::class, 'create'])
            ->add(ValidateEventoBody::class);

        $app->put('/eventos/{id}', [EventoController::class, 'update'])
            ->add(ValidateEventoBody::class)
            ->add(ValidateEventoId::class);

        $app->delete('/eventos/{id}', [EventoController::class, 'delete'])
            ->add(ValidateEventoId::class);
    }
}
