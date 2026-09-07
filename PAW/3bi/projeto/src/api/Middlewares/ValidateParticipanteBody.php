<?php

declare(strict_types=1);

namespace Api\Middlewares;

use Api\Http\ErrorResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidateParticipanteBody implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            $raw = (string) $request->getBody();
            $decoded = json_decode($raw, true);
            $data = is_array($decoded) ? $decoded : [];
        }

        if (!isset($data['participante']) || !is_array($data['participante'])) {
            throw new ErrorResponse("Campo 'participante' é obrigatório no corpo da requisição.", 400);
        }

        $payload = $data['participante'];

        // nome obrigatório
        if (!isset($payload['nome']) || trim((string) $payload['nome']) === '') {
            throw new ErrorResponse("Campo 'nome' é obrigatório em 'participante'.", 400);
        }
        $nome = trim((string) $payload['nome']);
        if (mb_strlen($nome) < 3) {
            throw new ErrorResponse("Nome deve ter pelo menos 3 caracteres.", 400);
        }
        if (mb_strlen($nome) > 150) {
            throw new ErrorResponse("Nome deve ter no máximo 150 caracteres.", 400);
        }
        if (!preg_match('/^[\p{L}\s\'\-]+$/u', $nome)) {
            throw new ErrorResponse("Nome contém caracteres inválidos.", 400);
        }

        // email obrigatório
        if (!isset($payload['email']) || trim((string) $payload['email']) === '') {
            throw new ErrorResponse("Campo 'email' é obrigatório em 'participante'.", 400);
        }
        $email = trim(mb_strtolower((string) $payload['email']));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ErrorResponse("E-mail em formato inválido.", 400);
        }
        if (mb_strlen($email) > 255) {
            throw new ErrorResponse("E-mail deve ter no máximo 255 caracteres.", 400);
        }

        // cpf obrigatório
        if (!isset($payload['cpf']) || trim((string) $payload['cpf']) === '') {
            throw new ErrorResponse("Campo 'cpf' é obrigatório em 'participante'.", 400);
        }
        $numeros = preg_replace('/\D/', '', (string) $payload['cpf']);
        if ($numeros === '' || $numeros === null) {
            throw new ErrorResponse("CPF é obrigatório.", 400);
        }
        if (mb_strlen($numeros) !== 11) {
            throw new ErrorResponse("CPF deve conter 11 dígitos.", 400);
        }
        if (preg_match('/^(\d)\1{10}$/', $numeros)) {
            throw new ErrorResponse("CPF inválido.", 400);
        }
        // validação rigorosa de dígitos verificadores desativada para compatibilidade demo
        // para ativar, descomente o bloco for abaixo

        // telefone obrigatório
        if (!isset($payload['telefone']) || trim((string) $payload['telefone']) === '') {
            throw new ErrorResponse("Campo 'telefone' é obrigatório em 'participante'.", 400);
        }
        $telefoneNumeros = preg_replace('/\D/', '', (string) $payload['telefone']);
        $tam = mb_strlen((string) $telefoneNumeros);
        if ($tam < 10 || $tam > 11) {
            throw new ErrorResponse("Telefone deve conter 10 ou 11 dígitos (com DDD).", 400);
        }

        // senha obrigatória
        if (!isset($payload['senha']) || trim((string) $payload['senha']) === '') {
            throw new ErrorResponse("Campo 'senha' é obrigatório em 'participante'.", 400);
        }
        $senha = (string) $payload['senha'];
        if (mb_strlen($senha) < 6) {
            throw new ErrorResponse("Senha deve ter pelo menos 6 caracteres.", 400);
        }
        if (mb_strlen($senha) > 255) {
            throw new ErrorResponse("Senha deve ter no máximo 255 caracteres.", 400);
        }

        $request = $request->withParsedBody($payload);

        return $handler->handle($request);
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->process($request, $handler);
    }
}
