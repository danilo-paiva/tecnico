<?php

namespace Api\Middlewares\Participante;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;

use Api\Http\ErrorResponse;
use Api\Http\MeuTokenJWT;

// Barreira de autenticacao: toda rota protegida passa por aqui.
// Espera o header "Authorization: Bearer <token>".
// Token valido -> guarda o payload em $request->getAttribute('jwtPayload') e segue.
// Token ausente/invalido -> 401.
class ValidateParticipanteToken implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $authorization = $request->getHeaderLine('Authorization');

        if (empty($authorization)) {
            throw new ErrorResponse(401, "Acesso nao autorizado", [
                "message" => "Token de autenticacao nao informado"
            ]);
        }

        if (!str_starts_with($authorization, 'Bearer ')) {
            throw new ErrorResponse(401, "Acesso nao autorizado", [
                "message" => "Formato do token invalido. Use: Bearer <token>"
            ]);
        }

        $jwt = new MeuTokenJWT();
        if (!$jwt->validateToken($authorization)) {
            throw new ErrorResponse(401, "Acesso nao autorizado", [
                "message" => "Token invalido ou expirado"
            ]);
        }

        $request = $request->withAttribute('jwtPayload', $jwt->getPayload());
        return $handler->handle($request);
    }
}
