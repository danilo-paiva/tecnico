<?php

namespace Api\Middlewares\Funcionario;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Routing\RouteContext;
use Api\Http\ErrorResponse;

/**
 * ValidateFuncionarioId
 * Middleware responsável por validar se o parâmetro 'idFuncionario' está presente na URL da rota.
 * Essencial para rotas de Detalhes, Atualização e Exclusão.
 */
class ValidateFuncionarioId implements MiddlewareInterface
{
    /**
     * Intercepta a requisição para verificar a existência do ID do funcionário.
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Obtém o contexto da rota através do Slim
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        // Caso a rota não seja identificada
        if (!$route) {
            throw new ErrorResponse(400, "Erro na validação de dados", ["message" => "Rota não encontrada!"]);
        }

        // Recupera os argumentos da rota (ex: {idFuncionario})
        $routeArgs = $route->getArguments();
        if (!isset($routeArgs['idFuncionario']) || $routeArgs['idFuncionario'] === "") {
            throw new ErrorResponse(400, "Erro na validação de dados", ["message" => "O parâmetro 'idFuncionario' é obrigatório!"]);
        }

        // Permite que a requisição siga para o próximo handler ou controller
        return $handler->handle($request);
    }
}
