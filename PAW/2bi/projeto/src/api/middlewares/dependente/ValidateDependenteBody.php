<?php
namespace Api\Middlewares\Dependente;

use Api\Http\ErrorResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

/**
 * ValidateDependenteBody
 * Middleware responsável por validar os campos obrigatórios de um Dependente no corpo da requisição.
 */
class ValidateDependenteBody
{
    /**
     * Executa a validação dos campos obrigatórios do dependente.
     */
    public function __invoke(Request $request, Handler $handler): Response
    {
        $body = json_decode($request->getBody()->getContents(), true);

        // Valida se a estrutura 'dependente' existe e se nome e funcionário vínculo estão presentes
        if (!isset($body['dependente']) || empty($body['dependente']['nomeDependente']) || empty($body['dependente']['idFuncionario'])) {
            throw new ErrorResponse(400, "Corpo da requisição inválido", ["message" => "Campos nomeDependente e idFuncionario são obrigatórios."]);
        }

        // Reinicia o ponteiro do corpo da requisição
        $request->getBody()->rewind();
        return $handler->handle($request);
    }
}
