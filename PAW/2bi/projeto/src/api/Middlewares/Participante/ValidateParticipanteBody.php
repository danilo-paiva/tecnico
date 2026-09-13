<?php

namespace Api\Middlewares\Participante;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Api\Http\ErrorResponse;

// Garante o formato {"participante": {"nome", "email", "cpf", "senha", ...}}.
// O detalhe de cada campo (email, cpf, senha) e validado no Model.
class ValidateParticipanteBody implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $objPHP = json_decode($request->getBody()->getContents());

        if (!isset($objPHP->participante)) {
            throw new ErrorResponse(400, "Erro na validacao de dados", [
                "message" => "O campo 'participante' e obrigatorio"
            ]);
        }

        foreach (['nome', 'email', 'cpf', 'senha'] as $campo) {
            if (!isset($objPHP->participante->$campo) || $objPHP->participante->$campo === "" || $objPHP->participante->$campo === null) {
                throw new ErrorResponse(400, "Erro na validacao de dados", [
                    "message" => "O campo '{$campo}' e obrigatorio"
                ]);
            }
        }

        return $handler->handle($request);
    }
}
