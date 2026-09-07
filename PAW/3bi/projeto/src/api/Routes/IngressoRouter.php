<?php

declare(strict_types=1);

namespace Api\Routes;

use Api\Controllers\IngressoController;
use Api\Middlewares\ValidateIngressoBody;
use Api\Middlewares\ValidateIngressoId;
use Slim\App;

class IngressoRouter
{
    public static function routes(App $app): void
    {
        $app->get('/ingressos', [IngressoController::class, 'findAll']);

        $app->get('/ingressos/count', [IngressoController::class, 'count']);

        $app->get('/ingressos/evento/{idEvento}', [IngressoController::class, 'findByEvento'])
            ->add(ValidateIngressoId::class);

        $app->get('/ingressos/tipo/{tipo}/evento/{idEvento}', [IngressoController::class, 'findByTipoEvento'])
            ->add(ValidateIngressoId::class);

        $app->get('/ingressos/evento/{idEvento}/tipo/{tipo}', [IngressoController::class, 'findByTipoEvento'])
            ->add(ValidateIngressoId::class);

        $app->get('/ingressos/{id}', [IngressoController::class, 'findById'])
            ->add(ValidateIngressoId::class);

        $app->post('/ingressos', [IngressoController::class, 'create'])
            ->add(ValidateIngressoBody::class);

        $app->put('/ingressos/{id}', [IngressoController::class, 'update'])
            ->add(ValidateIngressoBody::class)
            ->add(ValidateIngressoId::class);

        $app->delete('/ingressos/{id}', [IngressoController::class, 'delete'])
            ->add(ValidateIngressoId::class);
    }
}
