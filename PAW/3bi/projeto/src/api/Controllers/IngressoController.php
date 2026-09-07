<?php

declare(strict_types=1);

namespace Api\Controllers;

use Api\Http\ErrorResponse;
use Api\Services\IngressoService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class IngressoController
{
    private IngressoService $service;

    public function __construct(IngressoService $service)
    {
        $this->service = $service;
    }

    public function findAll(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $items = $this->service->findAll();
            $data = array_map(static fn($e) => $e->toArray(), $items);
            return $this->json($response, true, 'Ingressos listados com sucesso.', $data, 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao listar ingressos.', null, 500);
        }
    }

    public function findById(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $args['idIngresso'] ?? 0);
            $ingresso = $this->service->findById($id);
            return $this->json($response, true, 'Ingresso encontrado.', $ingresso->toArray(), 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao buscar ingresso.', null, 500);
        }
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            if (!is_array($data)) {
                $data = [];
            }
            $ingresso = $this->service->create($data);
            return $this->json($response, true, 'Ingresso criado com sucesso.', $ingresso->toArray(), 201);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao criar ingresso.', null, 500);
        }
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $args['idIngresso'] ?? 0);
            $data = $request->getParsedBody();
            if (!is_array($data)) {
                $data = [];
            }
            $ingresso = $this->service->update($id, $data);
            return $this->json($response, true, 'Ingresso atualizado com sucesso.', $ingresso->toArray(), 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao atualizar ingresso.', null, 500);
        }
    }

    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $args['idIngresso'] ?? 0);
            $this->service->delete($id);
            return $this->json($response, true, 'Ingresso removido com sucesso.', null, 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao remover ingresso.', null, 500);
        }
    }

    public function count(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $total = $this->service->count();
            return $this->json($response, true, 'Total de ingressos.', ['total' => $total], 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao contar ingressos.', null, 500);
        }
    }

    public function findByEvento(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $query = $request->getQueryParams();
            $idEvento = (int) ($args['idEvento'] ?? $args['id'] ?? $query['idEvento'] ?? $query['evento'] ?? 0);
            $items = $this->service->getByEvento($idEvento);
            $data = array_map(static fn($e) => $e->toArray(), $items);
            return $this->json($response, true, 'Ingressos encontrados por evento.', $data, 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao buscar ingressos por evento.', null, 500);
        }
    }

    public function findByTipoEvento(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $query = $request->getQueryParams();
            $tipo = (string) ($args['tipo'] ?? $query['tipo'] ?? '');
            $idEvento = (int) ($args['idEvento'] ?? $query['idEvento'] ?? 0);
            $ingresso = $this->service->getByTipoEvento($tipo, $idEvento);
            return $this->json($response, true, 'Ingresso encontrado por tipo e evento.', $ingresso->toArray(), 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao buscar ingresso por tipo e evento.', null, 500);
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
