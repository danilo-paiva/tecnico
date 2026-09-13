<?php

namespace Api\Middlewares\Participante;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Api\Http\ErrorResponse;

// Valida o body do login: {"participante": {"email": "...", "senha": "..."}}
class ValidateParticipanteLoginBody implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $objPHP = json_decode($request->getBody()->getContents());

        if (!isset($objPHP->participante)) {
            throw new ErrorResponse(400, "Erro na validacao de dados", [
                "message" => "O campo 'participante' e obrigatorio"
            ]);
        }

        $participante = $objPHP->participante;

        if (!isset($participante->email) || empty(trim((string) $participante->email))) {
            throw new ErrorResponse(400, "Erro na validacao de dados", [
                "message" => "O campo 'email' e obrigatorio"
            ]);
        }

        if (!filter_var($participante->email, FILTER_VALIDATE_EMAIL)) {
            throw new ErrorResponse(400, "Erro na validacao de dados", [
                "message" => "Email invalido"
            ]);
        }

        if (!isset($participante->senha) || empty(trim((string) $participante->senha))) {
            throw new ErrorResponse(400, "Erro na validacao de dados", [
                "message" => "O campo 'senha' e obrigatorio"
            ]);
        }

        return $handler->handle($request);
    }
}
