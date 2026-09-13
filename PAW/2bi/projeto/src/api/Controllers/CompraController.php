<?php

namespace Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Api\Services\CompraService;

// Recebe o HTTP, chama o Service e devolve JSON padrao.
// Nao tem regra de negocio aqui.
class CompraController
{
    private CompraService $compraService;

    public function __construct(CompraService $compraService)
    {
        $this->compraService = $compraService;
    }

    public function createController(Request $request, Response $response, array $args): Response
    {
        $objPHP = json_decode($request->getBody()->getContents());
        $nova = $this->compraService->createService($objPHP);

        $resposta = [
            'success' => true,
            'message' => 'Cadastro realizado com sucesso',
            'data' => ['compras' => [$nova]]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function findAllController(Request $request, Response $response, array $args): Response
    {
        $resposta = [
            'success' => true,
            'message' => 'Busca realizada com sucesso',
            'data' => ['compras' => $this->compraService->findAllService()]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function findByIdController(Request $request, Response $response, array $args): Response
    {
        $compra = $this->compraService->findByIdService((int) $args['id_compra']);

        $resposta = [
            'success' => true,
            'message' => 'Executado com sucesso',
            'data' => ['compras' => $compra]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function updateController(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id_compra'];
        $objPHP = json_decode($request->getBody()->getContents());
        $atualizada = $this->compraService->updateService($id, $objPHP);

        $resposta = [
            'success' => true,
            'message' => 'Atualizado com sucesso',
            'data' => ['compras' => [$atualizada]]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function deleteController(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id_compra'];
        $this->compraService->deleteService($id);

        $resposta = [
            'success' => true,
            'message' => 'Excluido com sucesso',
            'data' => ['compras' => [['id_compra' => $id]]]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function countController(Request $request, Response $response, array $args): Response
    {
        $resposta = [
            'success' => true,
            'message' => 'Executado com sucesso',
            'data' => ['count' => $this->compraService->countService()]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
