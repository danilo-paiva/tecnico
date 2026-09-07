<?php

declare(strict_types=1);

namespace Api\Controllers;

use Api\Services\AuthService;
use Api\Http\ErrorResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $request->getParsedBody();
            if (!is_array($data)) {
                $raw = (string)$request->getBody();
                $decoded = json_decode($raw, true);
                $data = is_array($decoded) ? $decoded : [];
            }

            // Suporta tanto {"email":"...","senha":"..."} quanto {"participante":{"email":..., "senha":...}} e {"usuario":{}}
            $email = null;
            $senha = null;
            if (isset($data['email'])) {
                $email = $data['email'];
                $senha = $data['senha'] ?? $data['password'] ?? null;
            } elseif (isset($data['participante']) && is_array($data['participante'])) {
                $email = $data['participante']['email'] ?? null;
                $senha = $data['participante']['senha'] ?? null;
            } elseif (isset($data['usuario']) && is_array($data['usuario'])) {
                $email = $data['usuario']['email'] ?? null;
                $senha = $data['usuario']['senha'] ?? null;
            }

            if ($email === null || $senha === null) {
                throw new ErrorResponse('E-mail e senha são obrigatórios.', 400);
            }

            $result = $this->authService->login((string)$email, (string)$senha);

            $payload = json_encode([
                'success' => true,
                'message' => 'Login realizado com sucesso.',
                'data' => $result,
            ], JSON_UNESCAPED_UNICODE);

            $response->getBody()->write($payload !== false ? $payload : '{}');
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (ErrorResponse $e) {
            $payload = json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getError() ?? new \stdClass(),
            ], JSON_UNESCAPED_UNICODE);
            $response->getBody()->write($payload !== false ? $payload : '{}');
            return $response->withHeader('Content-Type', 'application/json')->withStatus($e->getHttpCode());
        } catch (\Throwable $e) {
            error_log('[AuthController::login] ' . $e->getMessage());
            $payload = json_encode([
                'success' => false,
                'message' => 'Erro interno no login.',
                'error' => ['details' => $e->getMessage()],
            ], JSON_UNESCAPED_UNICODE);
            $response->getBody()->write($payload !== false ? $payload : '{}');
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function me(Request $request, Response $response, array $args): Response
    {
        try {
            // O AuthMiddleware/ValidateParticipanteToken já validou e injetou o payload
            $jwt = $request->getAttribute('jwtPayload') ?? $request->getAttribute('jwt');
            if (!$jwt) {
                throw new ErrorResponse('Não autenticado.', 401);
            }
            // Suporta payload novo (participante{name,email,role,id}) e legado (nome/email/sub)
            $part = is_object($jwt) ? ($jwt->participante ?? null) : null;
            $id = $part->idParticipante ?? (is_object($jwt) ? ($jwt->idParticipante ?? null) : null);
            if ($id === null && is_object($jwt)) {
                $id = is_numeric($jwt->sub ?? null) ? (int) $jwt->sub : ($jwt->sub ?? null);
            }
            $email = $part->email ?? (is_object($jwt) ? ($jwt->email ?? null) : null);
            $nome = $part->name ?? (is_object($jwt) ? ($jwt->name ?? $jwt->nome ?? null) : null);
            $payload = json_encode([
                'success' => true,
                'message' => 'Usuário autenticado.',
                'data' => [
                    'id' => $id,
                    'email' => $email,
                    'nome' => $nome,
                    'iat' => is_object($jwt) ? ($jwt->iat ?? null) : null,
                    'exp' => is_object($jwt) ? ($jwt->exp ?? null) : null,
                ],
            ], JSON_UNESCAPED_UNICODE);
            $response->getBody()->write($payload !== false ? $payload : '{}');
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (ErrorResponse $e) {
            $payload = json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
            $response->getBody()->write($payload !== false ? $payload : '{}');
            return $response->withHeader('Content-Type', 'application/json')->withStatus($e->getHttpCode());
        }
    }

    public function register(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $request->getParsedBody();
            if (!is_array($data)) {
                $raw = (string)$request->getBody();
                $decoded = json_decode($raw, true);
                $data = is_array($decoded) ? $decoded : [];
            }
            // Normaliza: se vier {"participante":{...}} extrai
            if (isset($data['participante']) && is_array($data['participante'])) {
                $data = $data['participante'];
            }

            $result = $this->authService->register($data);

            $payload = json_encode([
                'success' => true,
                'message' => 'Cadastro realizado com sucesso. Faça login.',
                'data' => $result,
            ], JSON_UNESCAPED_UNICODE);
            $response->getBody()->write($payload !== false ? $payload : '{}');
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (ErrorResponse $e) {
            $payload = json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getError() ?? new \stdClass(),
            ], JSON_UNESCAPED_UNICODE);
            $response->getBody()->write($payload !== false ? $payload : '{}');
            return $response->withHeader('Content-Type', 'application/json')->withStatus($e->getHttpCode());
        } catch (\Throwable $e) {
            error_log('[AuthController::register] ' . $e->getMessage());
            $payload = json_encode([
                'success' => false,
                'message' => 'Erro interno no cadastro.',
            ], JSON_UNESCAPED_UNICODE);
            $response->getBody()->write($payload !== false ? $payload : '{}');
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
