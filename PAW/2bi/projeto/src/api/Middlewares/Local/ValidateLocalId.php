<?php

namespace Api\Middlewares\Local;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Routing\RouteContext;
use Api\Http\ErrorResponse;

// Garante que /locais/{id_local} recebeu um id numerico.
class ValidateLocalId implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $route = RouteContext::fromRequest($request)->getRoute();

        if (!$route) {
            throw new ErrorResponse(400, "Erro na validacao de dados", ["message" => "Rota nao encontrada"]);
        }

        $args = $route->getArguments();
        if (!isset($args['id_local']) || !ctype_digit((string) $args['id_local']) || (int) $args['id_local'] <= 0) {
            throw new ErrorResponse(400, "Erro na validacao de dados", [
                "message" => "O parametro 'id_local' deve ser um numero maior que zero"
            ]);
        }

        return $handler->handle($request);
    }
}
