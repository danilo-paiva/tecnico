<?php

namespace Api\Middlewares\Compra;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Api\Http\ErrorResponse;

// Garante o formato {"compra": {"id_participante", "id_ingresso", "quantidade"}}.
class ValidateCompraBody implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $objPHP = json_decode($request->getBody()->getContents());

        if (!isset($objPHP->compra)) {
            throw new ErrorResponse(400, "Erro na validacao de dados", [
                "message" => "O campo 'compra' e obrigatorio"
            ]);
        }

        foreach (['id_participante', 'id_ingresso', 'quantidade'] as $campo) {
            if (!isset($objPHP->compra->$campo) || $objPHP->compra->$campo === "" || $objPHP->compra->$campo === null) {
                throw new ErrorResponse(400, "Erro na validacao de dados", [
                    "message" => "O campo '{$campo}' e obrigatorio"
                ]);
            }
        }

        return $handler->handle($request);
    }
}
