<?php

declare(strict_types=1);

namespace Api\Controllers;

use Api\Http\ErrorResponse;
use Api\Services\ParticipanteService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ParticipanteController
{
    private ParticipanteService $service;

    public function __construct(ParticipanteService $service)
    {
        $this->service = $service;
    }

    public function findAll(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $items = $this->service->findAll();
            $data = array_map(static fn($e) => $e->toArray(), $items);
            return $this->json($response, true, 'Participantes listados com sucesso.', $data, 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao listar participantes.', null, 500);
        }
    }

    public function findById(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $args['idParticipante'] ?? 0);
            $participante = $this->service->findById($id);
            return $this->json($response, true, 'Participante encontrado.', $participante->toArray(), 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao buscar participante.', null, 500);
        }
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            if (!is_array($data)) {
                $data = [];
            }
            $participante = $this->service->create($data);
            return $this->json($response, true, 'Participante criado com sucesso.', $participante->toArray(), 201);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao criar participante.', null, 500);
        }
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $args['idParticipante'] ?? 0);
            $data = $request->getParsedBody();
            if (!is_array($data)) {
                $data = [];
            }
            $participante = $this->service->update($id, $data);
            return $this->json($response, true, 'Participante atualizado com sucesso.', $participante->toArray(), 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao atualizar participante.', null, 500);
        }
    }

    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $args['idParticipante'] ?? 0);
            $this->service->delete($id);
            return $this->json($response, true, 'Participante removido com sucesso.', null, 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao remover participante.', null, 500);
        }
    }

    public function count(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $total = $this->service->count();
            return $this->json($response, true, 'Total de participantes.', ['total' => $total], 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao contar participantes.', null, 500);
        }
    }

    public function findByEmail(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $query = $request->getQueryParams();
            $email = (string) ($args['email'] ?? $query['email'] ?? '');
            $participante = $this->service->getByEmail($email);
            return $this->json($response, true, 'Participante encontrado por e-mail.', $participante->toArray(), 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao buscar participante por e-mail.', null, 500);
        }
    }

    public function findByCpf(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $query = $request->getQueryParams();
            $cpf = (string) ($args['cpf'] ?? $query['cpf'] ?? '');
            $participante = $this->service->getByCpf($cpf);
            return $this->json($response, true, 'Participante encontrado por CPF.', $participante->toArray(), 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao buscar participante por CPF.', null, 500);
        }
    }

    private function json(ResponseInterface $response, bool $success, string $message, mixed $data, int $status): ResponseInterface
    {
        $payload = json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $response->getBody()->write($payload !== false ? $payload : '{}');
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
