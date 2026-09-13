<?php

namespace Api\Middlewares\Evento;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Api\Http\ErrorResponse;

// Garante o formato {"evento": {"titulo", "data_evento", "id_local", ...}}.
// O detalhe de cada campo (tamanho, data, status) e validado no Model.
class ValidateEventoBody implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $objPHP = json_decode($request->getBody()->getContents());

        if (!isset($objPHP->evento)) {
            throw new ErrorResponse(400, "Erro na validacao de dados", [
                "message" => "O campo 'evento' e obrigatorio"
            ]);
        }

        foreach (['titulo', 'data_evento', 'id_local'] as $campo) {
            if (!isset($objPHP->evento->$campo) || $objPHP->evento->$campo === "" || $objPHP->evento->$campo === null) {
                throw new ErrorResponse(400, "Erro na validacao de dados", [
                    "message" => "O campo '{$campo}' e obrigatorio"
                ]);
            }
        }

        return $handler->handle($request);
    }
}
