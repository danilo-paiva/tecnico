<?php

declare(strict_types=1);

namespace Api\Middlewares;

use Api\Http\ErrorResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidateIngressoBody implements MiddlewareInterface
{
    // tipos livres — aceita qualquer string 2..80

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            $raw = (string) $request->getBody();
            $decoded = json_decode($raw, true);
            $data = is_array($decoded) ? $decoded : [];
        }

        if (!isset($data['ingresso']) || !is_array($data['ingresso'])) {
            throw new ErrorResponse("Campo 'ingresso' é obrigatório no corpo da requisição.", 400);
        }

        $payload = $data['ingresso'];

        // tipo obrigatório
        if (!isset($payload['tipo']) || trim((string) $payload['tipo']) === '') {
            throw new ErrorResponse("Campo 'tipo' é obrigatório em 'ingresso'.", 400);
        }
        $tipo = trim((string) $payload['tipo']);
        if (mb_strlen($tipo) < 2) {
            throw new ErrorResponse("Tipo deve ter pelo menos 2 caracteres.", 400);
        }
        if (mb_strlen($tipo) > 80) {
            throw new ErrorResponse("Tipo deve ter no máximo 80 caracteres.", 400);
        }

        // preco obrigatório
        if (!isset($payload['preco']) || $payload['preco'] === '' || $payload['preco'] === null) {
            throw new ErrorResponse("Campo 'preco' é obrigatório em 'ingresso'.", 400);
        }
        if (!is_numeric((string) $payload['preco'])) {
            throw new ErrorResponse("Preço deve ser um número.", 400);
        }
        $preco = (float) $payload['preco'];
        if ($preco < 0) {
            throw new ErrorResponse("Preço não pode ser negativo.", 400);
        }
        if ($preco > 100000) {
            throw new ErrorResponse("Preço excede o limite permitido.", 400);
        }

        // quantidadeTotal obrigatório
        if (!isset($payload['quantidadeTotal']) || $payload['quantidadeTotal'] === '' || $payload['quantidadeTotal'] === null) {
            throw new ErrorResponse("Campo 'quantidadeTotal' é obrigatório em 'ingresso'.", 400);
        }
        if (!is_numeric((string) $payload['quantidadeTotal']) || (int) $payload['quantidadeTotal'] <= 0) {
            throw new ErrorResponse("Quantidade total deve ser maior que zero.", 400);
        }
        $quantidadeTotal = (int) $payload['quantidadeTotal'];

        // quantidadeDisponivel opcional
        if (isset($payload['quantidadeDisponivel']) && $payload['quantidadeDisponivel'] !== '' && $payload['quantidadeDisponivel'] !== null) {
            if (!is_numeric((string) $payload['quantidadeDisponivel'])) {
                throw new ErrorResponse("Quantidade disponível deve ser um número.", 400);
            }
            $qd = (int) $payload['quantidadeDisponivel'];
            if ($qd < 0) {
                throw new ErrorResponse("Quantidade disponível não pode ser negativa.", 400);
            }
            if ($qd > $quantidadeTotal) {
                throw new ErrorResponse("Quantidade disponível não pode ser maior que a quantidade total.", 400);
            }
        }

        // idEvento obrigatório
        if (!isset($payload['idEvento']) || $payload['idEvento'] === '' || $payload['idEvento'] === null) {
            throw new ErrorResponse("Campo 'idEvento' é obrigatório em 'ingresso'.", 400);
        }
        if (!is_numeric((string) $payload['idEvento']) || (int) $payload['idEvento'] <= 0) {
            throw new ErrorResponse("ID do evento deve ser um número inteiro maior que zero.", 400);
        }

        $request = $request->withParsedBody($payload);

        return $handler->handle($request);
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->process($request, $handler);
    }
}
