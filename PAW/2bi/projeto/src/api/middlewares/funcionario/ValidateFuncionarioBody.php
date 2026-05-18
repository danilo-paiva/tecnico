<?php

namespace Api\Middlewares\Funcionario;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Api\Http\ErrorResponse;

/**
 * Middleware responsável por validar o corpo (body)
 * das requisições relacionadas ao recurso Funcionário.
 *
 * Objetivo:
 * Garantir que os dados mínimos necessários estejam presentes
 * antes de a requisição chegar ao Controller.
 *
 * Se algum campo estiver inválido ou ausente,
 * uma exceção ErrorResponse será lançada com HTTP 400.
 *
 * Estrutura esperada do JSON:
 *
 * {
 *   "funcionario": {
 *     "nomeFuncionario": "João Silva",
 *     "email": "joao@email.com",
 *     "senha": "123456",
 *     "recebeValeTransporte": 1,
 *     "cargo": {
 *       "idCargo": 2
 *     }
 *   }
 * }
 */
class ValidateFuncionarioBody implements MiddlewareInterface
{
    /**
     * Método executado automaticamente pelo Slim
     * antes da requisição seguir para o próximo middleware
     * ou para o Controller.
     *
     * Fluxo:
     * 1. Lê o body enviado na requisição
     * 2. Valida estrutura principal
     * 3. Valida campos obrigatórios
     * 4. Valida regras específicas
     * 5. Libera continuidade da execução
     *
     * @param Request $request Requisição HTTP recebida
     * @param RequestHandler $handler Próximo item da fila
     *
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        /**
         * Lê o JSON bruto enviado no body
         * e converte para objeto stdClass.
         *
         * Exemplo:
         * $objPHP->funcionario->nomeFuncionario
         */
        $body = $request->getBody()->getContents();
        $objPHP = json_decode($body);

        /**
         * Verifica se o objeto principal "funcionario" existe.
         */
        if (!isset($objPHP->funcionario)) {
            throw new ErrorResponse(
                httpCode: 400,
                message: "Erro na validação de dados",
                error: [
                    "message" => "O campo 'funcionario' é obrigatório!"
                ]
            );
        }

        /**
         * Armazena os dados internos do funcionário
         * para facilitar leitura do código.
         */
        $funcionario = $objPHP->funcionario;

        /**
         * Lista de campos obrigatórios.
         */
        $camposObrigatorios = [
            "nomeFuncionario",
            "email",
            "senha",
            "recebeValeTransporte"
        ];

        /**
         * Percorre cada campo obrigatório.
         */
        foreach ($camposObrigatorios as $campo) {
            if (
                !isset($funcionario->$campo) ||
                $funcionario->$campo === "" ||
                $funcionario->$campo === null
            ) {
                throw new ErrorResponse(
                    httpCode: 400,
                    message: "Erro na validação de dados",
                    error: [
                        "message" => "O campo '{$campo}' é obrigatório!"
                    ]
                );
            }
        }

        /**
         * Validação do campo recebeValeTransporte.
         *
         * Permitido:
         * 0 = Não
         * 1 = Sim
         */
        if (!in_array($funcionario->recebeValeTransporte, [0, 1], true)) {
            throw new ErrorResponse(
                httpCode: 400,
                message: "Erro na validação de dados",
                error: [
                    "message" =>
                        "O campo 'recebeValeTransporte' deve ser 0 ou 1"
                ]
            );
        }

        /**
         * Valida vínculo com cargo.
         */
        if (
            !isset($funcionario->cargo) ||
            !isset($funcionario->cargo->idCargo) ||
            !is_int($funcionario->cargo->idCargo) ||
            $funcionario->cargo->idCargo <= 0
        ) {
            throw new ErrorResponse(
                httpCode: 400,
                message: "Erro na validação de dados",
                error: [
                    "message" =>
                        "O campo 'idCargo' deve ser um número inteiro positivo"
                ]
            );
        }

        /**
         * Se chegou aqui, está válido.
         */
        return $handler->handle($request);
    }
}