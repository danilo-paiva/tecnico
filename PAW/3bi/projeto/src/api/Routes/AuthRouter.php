<?php

namespace Api\Routes;

use Slim\App;
use Api\Controllers\ParticipanteController;
use Api\Middlewares\Participante\ValidateParticipanteLoginBody;
use Api\Middlewares\Participante\ValidateParticipanteToken;

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

        // Quem sou eu: devolve o participante do token (usado pelo
        // verifyAuth() do frontend e pelo index.html no redirecionamento).
        $this->app->get('/auth/me', [ParticipanteController::class, 'meController'])
            ->add(ValidateParticipanteToken::class);
    }
}
