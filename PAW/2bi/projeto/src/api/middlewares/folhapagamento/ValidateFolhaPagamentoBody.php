<?php
namespace Api\Middlewares\FolhaPagamento;

use Api\Http\ErrorResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

/**
 * ValidateFolhaPagamentoBody
 * Middleware responsável por validar os campos obrigatórios de uma folha de pagamento.
 */
class ValidateFolhaPagamentoBody
{
    /**
     * Executa a validação dos campos obrigatórios da folha.
     */
    public function __invoke(Request $request, Handler $handler): Response
    {
        $body = json_decode($request->getBody()->getContents(), true);

        // Valida a estrutura 'folha' e a presença de data, valor e funcionário
        if (!isset($body['folha']) || empty($body['folha']['dataPagamento']) || !isset($body['folha']['valorLiquido']) || empty($body['folha']['idFuncionario'])) {
            throw new ErrorResponse(400, "Corpo da requisição inválido", ["message" => "Campos dataPagamento, valorLiquido e idFuncionario são obrigatórios."]);
        }

        // Reinicia o ponteiro do corpo da requisição
        $request->getBody()->rewind();
        return $handler->handle($request);
    }
}
