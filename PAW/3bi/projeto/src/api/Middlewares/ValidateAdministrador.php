<?php

declare(strict_types=1);

namespace Api\Middlewares;

use Api\Http\ErrorResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * ValidateAdministrador
 * Equivalente ao ValidateAdministrador da aula (slide 57).
 * Lê o payload injetado por ValidateParticipanteToken/AuthMiddleware
 * e exige role === 'Administrador' para criar/atualizar/excluir.
 *
 * Uso na rota (ordem da aula):
 *   ->add(ValidateLocalBody::class)
 *   ->add(ValidateAdministrador::class)
 *   ->add(ValidateParticipanteToken::class);
 *
 * NOTA: não wired por padrão porque participantes não têm coluna role
 * (todos nascem 'participante'). Para ativar, evoluir AuthService a
 * emitir role real e registrar este middleware nas rotas POST/PUT/DELETE.
 */
class ValidateAdministrador implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $payload = $request->getAttribute('jwtPayload') ?? $request->getAttribute('jwt');
        if (!$payload) {
            throw new ErrorResponse(
                'Acesso não autorizado',
                401,
                ['message' => 'Usuário não autenticado']
            );
        }
        $role = null;
        if (is_object($payload)) {
            $role = $payload->participante->role ?? $payload->role ?? null;
        } elseif (is_array($payload)) {
            $role = $payload['participante']['role'] ?? $payload['role'] ?? null;
        }
        if ($role !== 'Administrador') {
            throw new ErrorResponse(
                'Acesso negado',
                403,
                ['message' => 'Apenas administradores possuem acesso']
            );
        }
        return $handler->handle($request);
    }
}
