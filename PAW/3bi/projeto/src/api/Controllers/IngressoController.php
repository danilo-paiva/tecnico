<?php

namespace Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Api\Services\IngressoService;

// Recebe o HTTP, chama o Service e devolve JSON padrao.
// Nao tem regra de negocio aqui.
class IngressoController
{
    private IngressoService $ingressoService;

    public function __construct(IngressoService $ingressoService)
    {
        $this->ingressoService = $ingressoService;
    }

    public function createController(Request $request, Response $response, array $args): Response
    {
        $objPHP = json_decode($request->getBody()->getContents());
        $novo = $this->ingressoService->createService($objPHP);

        $resposta = [
            'success' => true,
            'message' => 'Cadastro realizado com sucesso',
            'data' => ['ingressos' => [$novo]]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function findAllController(Request $request, Response $response, array $args): Response
    {
        $resposta = [
            'success' => true,
            'message' => 'Busca realizada com sucesso',
            'data' => ['ingressos' => $this->ingressoService->findAllService()]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function findByIdController(Request $request, Response $response, array $args): Response
    {
        $ingresso = $this->ingressoService->findByIdService((int) $args['id_ingresso']);

        $resposta = [
            'success' => true,
            'message' => 'Executado com sucesso',
            'data' => ['ingressos' => $ingresso]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function updateController(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id_ingresso'];
        $objPHP = json_decode($request->getBody()->getContents());
        $atualizado = $this->ingressoService->updateService($id, $objPHP);

        $resposta = [
            'success' => true,
            'message' => 'Atualizado com sucesso',
            'data' => ['ingressos' => [$atualizado]]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function deleteController(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id_ingresso'];
        $this->ingressoService->deleteService($id);

        $resposta = [
            'success' => true,
            'message' => 'Excluido com sucesso',
            'data' => ['ingressos' => [['id_ingresso' => $id]]]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function countController(Request $request, Response $response, array $args): Response
    {
        $resposta = [
            'success' => true,
            'message' => 'Executado com sucesso',
            'data' => ['count' => $this->ingressoService->countService()]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
