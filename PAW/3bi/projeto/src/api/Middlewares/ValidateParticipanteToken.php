<?php

declare(strict_types=1);

namespace Api\Middlewares;

use Api\Http\ErrorResponse;
use Api\Http\MeuTokenJWT;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * ValidateParticipanteToken
 * Equivalente ao ValidateFuncionarioToken da aula (slide 56).
 * Exige header Authorization: Bearer <token>, valida via MeuTokenJWT
 * e injeta o payload em `jwtPayload` para os próximos middlewares.
 */
class ValidateParticipanteToken implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authorization = $request->getHeaderLine('Authorization');
        if (empty($authorization)) {
            throw new ErrorResponse(
                'Acesso não autorizado',
                401,
                ['message' => 'Token de autenticação não informado']
            );
        }
        if (!str_starts_with($authorization, 'Bearer ')) {
            throw new ErrorResponse(
                'Acesso não autorizado',
                401,
                ['message' => 'Formato do token inválido']
            );
        }
        $jwt = new MeuTokenJWT();
        if (!$jwt->validateToken($authorization)) {
            throw new ErrorResponse(
                'Acesso não autorizado',
                401,
                ['message' => 'Token inválido ou expirado']
            );
        }
        // Adiciona o payload na requisição (padrão da aula)
        $request = $request->withAttribute('jwtPayload', $jwt->getPayload());
        $request = $request->withAttribute('jwt', $jwt->getPayload());
        return $handler->handle($request);
    }
}
