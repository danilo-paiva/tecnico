<?php

declare(strict_types=1);

namespace Api\Controllers;

use Api\Http\ErrorResponse;
use Api\Services\LocalService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class LocalController
{
    private LocalService $service;

    public function __construct(LocalService $service)
    {
        $this->service = $service;
    }

    public function findAll(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $items = $this->service->findAll();
            $data = array_map(static fn($e) => $e->toArray(), $items);
            return $this->json($response, true, 'Locais listados com sucesso.', $data, 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao listar locais.', null, 500);
        }
    }

    public function findById(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $args['idLocal'] ?? 0);
            $local = $this->service->findById($id);
            return $this->json($response, true, 'Local encontrado.', $local->toArray(), 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao buscar local.', null, 500);
        }
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            if (!is_array($data)) {
                $data = [];
            }
            $local = $this->service->create($data);
            return $this->json($response, true, 'Local criado com sucesso.', $local->toArray(), 201);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao criar local.', null, 500);
        }
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $args['idLocal'] ?? 0);
            $data = $request->getParsedBody();
            if (!is_array($data)) {
                $data = [];
            }
            $local = $this->service->update($id, $data);
            return $this->json($response, true, 'Local atualizado com sucesso.', $local->toArray(), 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao atualizar local.', null, 500);
        }
    }

    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $args['idLocal'] ?? 0);
            $this->service->delete($id);
            return $this->json($response, true, 'Local removido com sucesso.', null, 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao remover local.', null, 500);
        }
    }

    public function count(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $total = $this->service->count();
            return $this->json($response, true, 'Total de locais.', ['total' => $total], 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao contar locais.', null, 500);
        }
    }

    public function findByNome(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $query = $request->getQueryParams();
            $nome = (string) ($args['nome'] ?? $query['nome'] ?? $query['q'] ?? '');
            $items = $this->service->getByNome($nome);
            $data = array_map(static fn($e) => $e->toArray(), $items);
            return $this->json($response, true, 'Locais encontrados por nome.', $data, 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao buscar locais por nome.', null, 500);
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
