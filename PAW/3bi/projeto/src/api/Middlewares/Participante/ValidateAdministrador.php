<?php

namespace Api\Middlewares\Participante;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;

use Api\Http\ErrorResponse;

// Barreira de perfil (aula paw03x01): so administradores passam.
// Usa o payload que o ValidateParticipanteToken guardou em 'jwtPayload'.
// SEMPRE depois do token na cadeia: ->add(...)->add(ValidateAdministrador::class)
//   ->add(ValidateParticipanteToken::class) — o ultimo ->add executa primeiro.
// Sem login -> 401. Logado sem ser admin -> 403.
class ValidateAdministrador implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $payload = $request->getAttribute('jwtPayload');

        if (!$payload) {
            throw new ErrorResponse(401, "Acesso nao autorizado", [
                "message" => "Usuario nao autenticado"
            ]);
        }

        $perfil = $payload->participante->perfil ?? null;
        if ($perfil !== 'administrador') {
            throw new ErrorResponse(403, "Acesso negado", [
                "message" => "Apenas administradores possuem acesso"
            ]);
        }

        return $handler->handle($request);
    }
}
