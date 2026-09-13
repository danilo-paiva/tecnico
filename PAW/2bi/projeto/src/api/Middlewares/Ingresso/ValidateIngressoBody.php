<?php

namespace Api\Middlewares\Ingresso;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Api\Http\ErrorResponse;

// Garante o formato {"ingresso": {"tipo", "preco", "quantidade_total", "id_evento"}}.
// O detalhe de cada campo e validado no Model.
class ValidateIngressoBody implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $objPHP = json_decode($request->getBody()->getContents());

        if (!isset($objPHP->ingresso)) {
            throw new ErrorResponse(400, "Erro na validacao de dados", [
                "message" => "O campo 'ingresso' e obrigatorio"
            ]);
        }

        foreach (['tipo', 'preco', 'quantidade_total', 'id_evento'] as $campo) {
            if (!isset($objPHP->ingresso->$campo) || $objPHP->ingresso->$campo === "" || $objPHP->ingresso->$campo === null) {
                throw new ErrorResponse(400, "Erro na validacao de dados", [
                    "message" => "O campo '{$campo}' e obrigatorio"
                ]);
            }
        }

        if (!is_numeric($objPHP->ingresso->preco) || !is_numeric($objPHP->ingresso->quantidade_total)) {
            throw new ErrorResponse(400, "Erro na validacao de dados", [
                "message" => "Os campos 'preco' e 'quantidade_total' devem ser numeros"
            ]);
        }

        return $handler->handle($request);
    }
}
