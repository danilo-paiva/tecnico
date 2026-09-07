<?php

declare(strict_types=1);

namespace Api\Controllers;

use Api\Http\ErrorResponse;
use Api\Services\CompraService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class CompraController
{
    private CompraService $service;

    public function __construct(CompraService $service)
    {
        $this->service = $service;
    }

    public function findAll(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $items = $this->service->findAll();
            $data = array_map(static fn($e) => $e->toArray(), $items);
            return $this->json($response, true, 'Compras listadas com sucesso.', $data, 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao listar compras.', null, 500);
        }
    }

    public function findById(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $args['idCompra'] ?? 0);
            $compra = $this->service->findById($id);
            return $this->json($response, true, 'Compra encontrada.', $compra->toArray(), 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao buscar compra.', null, 500);
        }
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            if (!is_array($data)) {
                $data = [];
            }
            $compra = $this->service->create($data);
            return $this->json($response, true, 'Compra criada com sucesso.', $compra->toArray(), 201);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao criar compra.', null, 500);
        }
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $args['idCompra'] ?? 0);
            $data = $request->getParsedBody();
            if (!is_array($data)) {
                $data = [];
            }
            $compra = $this->service->update($id, $data);
            return $this->json($response, true, 'Compra atualizada com sucesso.', $compra->toArray(), 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao atualizar compra.', null, 500);
        }
    }

    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $id = (int) ($args['id'] ?? $args['idCompra'] ?? 0);
            $this->service->delete($id);
            return $this->json($response, true, 'Compra removida com sucesso.', null, 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao remover compra.', null, 500);
        }
    }

    public function count(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $total = $this->service->count();
            return $this->json($response, true, 'Total de compras.', ['total' => $total], 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao contar compras.', null, 500);
        }
    }

    public function findByParticipante(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $query = $request->getQueryParams();
            $idParticipante = (int) ($args['idParticipante'] ?? $args['id'] ?? $query['idParticipante'] ?? $query['participante'] ?? 0);
            $items = $this->service->getByParticipante($idParticipante);
            $data = array_map(static fn($e) => $e->toArray(), $items);
            return $this->json($response, true, 'Compras encontradas por participante.', $data, 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao buscar compras por participante.', null, 500);
        }
    }

    public function findByIngresso(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $query = $request->getQueryParams();
            $idIngresso = (int) ($args['idIngresso'] ?? $args['id'] ?? $query['idIngresso'] ?? $query['ingresso'] ?? 0);
            $items = $this->service->getByIngresso($idIngresso);
            $data = array_map(static fn($e) => $e->toArray(), $items);
            return $this->json($response, true, 'Compras encontradas por ingresso.', $data, 200);
        } catch (ErrorResponse $e) {
            return $this->json($response, false, $e->getMessage(), $e->getError(), $e->getHttpCode());
        } catch (Throwable $e) {
            return $this->json($response, false, 'Erro interno ao buscar compras por ingresso.', null, 500);
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
