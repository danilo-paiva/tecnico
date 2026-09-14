<?php

namespace Api\Middlewares\Participante;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Api\Http\ErrorResponse;

// Garante o formato {"participante": {"nome", "email", "cpf", ...}}.
// A senha e obrigatoria so na criacao (POST); na atualizacao (PUT/PATCH)
// ela e opcional — ausente/vazia mantem o hash atual.
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

        $obrigatorios = ['nome', 'email', 'cpf'];
        if (strtoupper($request->getMethod()) === 'POST') {
            $obrigatorios[] = 'senha';
        }

        foreach ($obrigatorios as $campo) {
            if (!isset($objPHP->participante->$campo) || $objPHP->participante->$campo === "" || $objPHP->participante->$campo === null) {
                throw new ErrorResponse(400, "Erro na validacao de dados", [
                    "message" => "O campo '{$campo}' e obrigatorio"
                ]);
            }
        }

        return $handler->handle($request);
    }
}
