<?php

namespace Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Api\Services\LocalService;

// Recebe o HTTP, chama o Service e devolve JSON padrao.
// Nao tem regra de negocio aqui.
class LocalController
{
    private LocalService $localService;

    public function __construct(LocalService $localService)
    {
        $this->localService = $localService;
    }

    public function createController(Request $request, Response $response, array $args): Response
    {
        $objPHP = json_decode($request->getBody()->getContents());
        $novo = $this->localService->createService($objPHP);

        $resposta = [
            'success' => true,
            'message' => 'Cadastro realizado com sucesso',
            'data' => ['locais' => [$novo]]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function findAllController(Request $request, Response $response, array $args): Response
    {
        $resposta = [
            'success' => true,
            'message' => 'Busca realizada com sucesso',
            'data' => ['locais' => $this->localService->findAllService()]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function findByIdController(Request $request, Response $response, array $args): Response
    {
        $local = $this->localService->findByIdService((int) $args['id_local']);

        $resposta = [
            'success' => true,
            'message' => 'Executado com sucesso',
            'data' => ['locais' => $local]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function updateController(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id_local'];
        $objPHP = json_decode($request->getBody()->getContents());
        $atualizado = $this->localService->updateService($id, $objPHP);

        $resposta = [
            'success' => true,
            'message' => 'Atualizado com sucesso',
            'data' => ['locais' => [$atualizado]]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function deleteController(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id_local'];
        $this->localService->deleteService($id);

        $resposta = [
            'success' => true,
            'message' => 'Excluido com sucesso',
            'data' => ['locais' => [['id_local' => $id]]]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function countController(Request $request, Response $response, array $args): Response
    {
        $resposta = [
            'success' => true,
            'message' => 'Executado com sucesso',
            'data' => ['count' => $this->localService->countService()]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
