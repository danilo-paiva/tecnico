<?php

declare(strict_types=1);

namespace Api\Middlewares;

use Api\Http\ErrorResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

class ValidateCompraId implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $args = $this->getRouteArgs($request);

        $id = $args['id'] ?? $args['idCompra'] ?? $args['idParticipante'] ?? $args['idIngresso'] ?? null;

        if ($id === null) {
            throw new ErrorResponse("Parâmetro 'id' é obrigatório.", 400);
        }

        if (!is_numeric((string) $id) || (int) $id <= 0) {
            throw new ErrorResponse("ID da compra deve ser um número inteiro maior que zero.", 400);
        }

        return $handler->handle($request);
    }

    private function getRouteArgs(ServerRequestInterface $request): array
    {
        try {
            $routeContext = RouteContext::fromRequest($request);
            $route = $routeContext->getRoute();
            if ($route !== null) {
                return $route->getArguments();
            }
        } catch (\Throwable) {
        }
        $args = $request->getAttribute('route');
        if (is_object($args) && method_exists($args, 'getArguments')) {
            return $args->getArguments();
        }
        if (is_array($args)) {
            return $args;
        }
        return [];
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->process($request, $handler);
    }
}
