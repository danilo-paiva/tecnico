<?php
namespace Api\Middlewares\Departamento;

use Api\Http\ErrorResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class ValidateDepartamentoBody
{
    public function __invoke(Request $request, Handler $handler): Response
    {
        $body = json_decode($request->getBody()->getContents(), true);
        if (!isset($body['departamento']) || empty($body['departamento']['nomeDepartamento'])) {
            throw new ErrorResponse(400, "Corpo da requisição inválido", ["message" => "O campo nomeDepartamento é obrigatório."]);
        }
        $request->getBody()->rewind();
        return $handler->handle($request);
    }
}
