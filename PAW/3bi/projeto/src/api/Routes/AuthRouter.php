<?php

namespace Api\Routes;

use Slim\App;
use Api\Controllers\ParticipanteController;
use Api\Middlewares\Participante\ValidateParticipanteLoginBody;

// Rotas publicas de autenticacao (NAO exigem token):
// POST /login               -> {participante: {email, senha}} devolve {participante, token}
// POST /participantes/login -> apelido no padrao da aula (paw03x01)
class AuthRouter
{
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function setupRoutes(): void
    {
        $this->app->post('/login', [ParticipanteController::class, 'loginController'])
            ->add(ValidateParticipanteLoginBody::class);

        $this->app->post('/participantes/login', [ParticipanteController::class, 'loginController'])
            ->add(ValidateParticipanteLoginBody::class);
    }
}
