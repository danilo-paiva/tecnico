<?php

namespace Api\Middlewares\Local;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Api\Http\ErrorResponse;

// Garante o formato {"local": {"nome", "endereco", "capacidade"}}.
// O detalhe de cada campo (tamanho, numero) e validado no Model.
class ValidateLocalBody implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $objPHP = json_decode($request->getBody()->getContents());

        if (!isset($objPHP->local)) {
            throw new ErrorResponse(400, "Erro na validacao de dados", [
                "message" => "O campo 'local' e obrigatorio"
            ]);
        }

        foreach (['nome', 'endereco', 'capacidade'] as $campo) {
            if (!isset($objPHP->local->$campo) || $objPHP->local->$campo === "" || $objPHP->local->$campo === null) {
                throw new ErrorResponse(400, "Erro na validacao de dados", [
                    "message" => "O campo '{$campo}' e obrigatorio"
                ]);
            }
        }

        if (!is_numeric($objPHP->local->capacidade)) {
            throw new ErrorResponse(400, "Erro na validacao de dados", [
                "message" => "O campo 'capacidade' deve ser um numero"
            ]);
        }

        return $handler->handle($request);
    }
}
