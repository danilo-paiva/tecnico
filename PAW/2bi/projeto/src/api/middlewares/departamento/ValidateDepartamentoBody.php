<?php
namespace Api\Middlewares\Departamento;

use Api\Http\ErrorResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

/**
 * ValidateDepartamentoBody
 * Middleware responsável por validar se o corpo da requisição de Departamento
 * contém as informações necessárias antes de chegar ao Controller.
 */
class ValidateDepartamentoBody
{
    /**
     * Executa a validação do corpo JSON.
     */
    public function __invoke(Request $request, Handler $handler): Response
    {
        $body = json_decode($request->getBody()->getContents(), true);

        // Verifica se a estrutura principal 'departamento' existe e se o nome foi preenchido
        if (!isset($body['departamento']) || empty($body['departamento']['nomeDepartamento'])) {
            throw new ErrorResponse(400, "Corpo da requisição inválido", ["message" => "O campo nomeDepartamento é obrigatório."]);
        }

        // Reinicia o ponteiro do corpo da requisição para que o Controller possa lê-lo novamente
        $request->getBody()->rewind();
        return $handler->handle($request);
    }
}
