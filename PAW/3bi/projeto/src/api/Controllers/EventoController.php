<?php

declare(strict_types=1);

namespace Api\Controllers;

use Api\Http\ErrorResponse;
use Api\Services\EventoService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class EventoController
{
    private EventoService $service;

    public function __construct(EventoService $service)
    {
        $this->service = $service;
    }

    public function findAll(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $items = $this->service->findAll();
            $data = array_map(static fn($e) => $e->toArray(), $items);
            return $this->json($response, true, 'Eventos listados com sucesso.', $data, 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao listar eventos.', null, 500);
        }
    }

    public function findById(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $args['idEvento'] ?? 0);
            $evento = $this->service->findById($id);
            return $this->json($response, true, 'Evento encontrado.', $evento->toArray(), 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao buscar evento.', null, 500);
        }
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            if (!is_array($data)) {
                $data = [];
            }
            $evento = $this->service->create($data);
            return $this->json($response, true, 'Evento criado com sucesso.', $evento->toArray(), 201);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao criar evento.', null, 500);
        }
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $args['idEvento'] ?? 0);
            $data = $request->getParsedBody();
            if (!is_array($data)) {
                $data = [];
            }
            $evento = $this->service->update($id, $data);
            return $this->json($response, true, 'Evento atualizado com sucesso.', $evento->toArray(), 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao atualizar evento.', null, 500);
        }
    }

    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $args['idEvento'] ?? 0);
            $this->service->delete($id);
            return $this->json($response, true, 'Evento removido com sucesso.', null, 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao remover evento.', null, 500);
        }
    }

    public function count(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $total = $this->service->count();
            return $this->json($response, true, 'Total de eventos.', ['total' => $total], 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao contar eventos.', null, 500);
        }
    }

    public function findByLocal(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $query = $request->getQueryParams();
            $idLocal = (int) ($args['idLocal'] ?? $args['id'] ?? $query['idLocal'] ?? $query['local'] ?? 0);
            $items = $this->service->getByLocal($idLocal);
            $data = array_map(static fn($e) => $e->toArray(), $items);
            return $this->json($response, true, 'Eventos encontrados por local.', $data, 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao buscar eventos por local.', null, 500);
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
