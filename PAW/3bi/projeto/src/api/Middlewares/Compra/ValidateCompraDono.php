<?php

namespace Api\Middlewares\Compra;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;

use Api\Http\ErrorResponse;

// Regra de dono: perfil comum so pode comprar para si mesmo
// (compra.id_participante == id do token). Admin pode para qualquer um.
// Usa o payload que o ValidateParticipanteToken guardou em 'jwtPayload'.
// Ordem na rota: ->add(ValidateCompraBody)->add(ValidateCompraDono)
//   ->add(ValidateParticipanteToken) — o ultimo ->add executa primeiro.
class ValidateCompraDono implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $payload = $request->getAttribute('jwtPayload');

        if (!$payload) {
            throw new ErrorResponse(401, "Acesso nao autorizado", [
                "message" => "Usuario nao autenticado"
            ]);
        }

        if (($payload->participante->perfil ?? 'comum') === 'administrador') {
            return $handler->handle($request);
        }

        $objPHP = json_decode($request->getBody()->getContents());
        $dono = (int) ($payload->participante->id_participante ?? 0);
        $alvo = (int) ($objPHP->compra->id_participante ?? 0);

        if ($alvo !== $dono || $dono <= 0) {
            throw new ErrorResponse(403, "Acesso negado", [
                "message" => "Voce so pode comprar ingressos para voce mesmo"
            ]);
        }

        return $handler->handle($request);
    }
}
