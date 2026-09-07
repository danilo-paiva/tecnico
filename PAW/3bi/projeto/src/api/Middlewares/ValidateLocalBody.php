<?php

declare(strict_types=1);

namespace Api\Middlewares;

use Api\Http\ErrorResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidateLocalBody implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            $raw = (string) $request->getBody();
            $decoded = json_decode($raw, true);
            $data = is_array($decoded) ? $decoded : [];
        }

        if (!isset($data['local']) || !is_array($data['local'])) {
            throw new ErrorResponse("Campo 'local' é obrigatório no corpo da requisição.", 400);
        }

        $payload = $data['local'];

        // nome obrigatório
        if (!isset($payload['nome']) || trim((string) $payload['nome']) === '') {
            throw new ErrorResponse("Campo 'nome' é obrigatório em 'local'.", 400);
        }
        $nome = trim((string) $payload['nome']);
        if (mb_strlen($nome) < 3) {
            throw new ErrorResponse("Nome do local deve ter pelo menos 3 caracteres.", 400);
        }
        if (mb_strlen($nome) > 150) {
            throw new ErrorResponse("Nome do local deve ter no máximo 150 caracteres.", 400);
        }

        // endereco obrigatório
        if (!isset($payload['endereco']) || trim((string) $payload['endereco']) === '') {
            throw new ErrorResponse("Campo 'endereco' é obrigatório em 'local'.", 400);
        }
        $endereco = trim((string) $payload['endereco']);
        if (mb_strlen($endereco) < 5) {
            throw new ErrorResponse("Endereço deve ter pelo menos 5 caracteres.", 400);
        }
        if (mb_strlen($endereco) > 255) {
            throw new ErrorResponse("Endereço deve ter no máximo 255 caracteres.", 400);
        }

        // capacidade obrigatório
        if (!isset($payload['capacidade']) || $payload['capacidade'] === '' || $payload['capacidade'] === null) {
            throw new ErrorResponse("Campo 'capacidade' é obrigatório em 'local'.", 400);
        }
        if (!is_numeric($payload['capacidade'])) {
            throw new ErrorResponse("Capacidade deve ser um número.", 400);
        }
        $capacidade = (int) $payload['capacidade'];
        if ($capacidade <= 0) {
            throw new ErrorResponse("Capacidade deve ser maior que zero.", 400);
        }
        if ($capacidade > 1000000) {
            throw new ErrorResponse("Capacidade excede o limite permitido.", 400);
        }

        // normaliza body para o controller receber payload plano
        $request = $request->withParsedBody($payload);

        return $handler->handle($request);
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->process($request, $handler);
    }
}
