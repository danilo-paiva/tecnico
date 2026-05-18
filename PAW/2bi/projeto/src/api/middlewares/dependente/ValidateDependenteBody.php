<?php
namespace Api\Middlewares\Dependente;

use Api\Http\ErrorResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class ValidateDependenteBody
{
    public function __invoke(Request $request, Handler $handler): Response
    {
        $body = json_decode($request->getBody()->getContents(), true);
        if (!isset($body['dependente']) || empty($body['dependente']['nomeDependente']) || empty($body['dependente']['idFuncionario'])) {
            throw new ErrorResponse(400, "Corpo da requisição inválido", ["message" => "Campos nomeDependente e idFuncionario são obrigatórios."]);
        }
        $request->getBody()->rewind();
        return $handler->handle($request);
    }
}
