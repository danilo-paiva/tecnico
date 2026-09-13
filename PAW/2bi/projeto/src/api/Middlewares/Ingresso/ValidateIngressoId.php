<?php

namespace Api\Middlewares\Ingresso;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Routing\RouteContext;
use Api\Http\ErrorResponse;

// Garante que /ingressos/{id_ingresso} recebeu um id numerico.
class ValidateIngressoId implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $route = RouteContext::fromRequest($request)->getRoute();

        if (!$route) {
            throw new ErrorResponse(400, "Erro na validacao de dados", ["message" => "Rota nao encontrada"]);
        }

        $args = $route->getArguments();
        if (!isset($args['id_ingresso']) || !ctype_digit((string) $args['id_ingresso']) || (int) $args['id_ingresso'] <= 0) {
            throw new ErrorResponse(400, "Erro na validacao de dados", [
                "message" => "O parametro 'id_ingresso' deve ser um numero maior que zero"
            ]);
        }

        return $handler->handle($request);
    }
}
