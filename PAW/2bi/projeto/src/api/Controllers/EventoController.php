<?php

namespace Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Api\Services\EventoService;

// Recebe o HTTP, chama o Service e devolve JSON padrao.
// Nao tem regra de negocio aqui.
class EventoController
{
    private EventoService $eventoService;

    public function __construct(EventoService $eventoService)
    {
        $this->eventoService = $eventoService;
    }

    public function createController(Request $request, Response $response, array $args): Response
    {
        $objPHP = json_decode($request->getBody()->getContents());
        $novo = $this->eventoService->createService($objPHP);

        $resposta = [
            'success' => true,
            'message' => 'Cadastro realizado com sucesso',
            'data' => ['eventos' => [$novo]]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function findAllController(Request $request, Response $response, array $args): Response
    {
        $resposta = [
            'success' => true,
            'message' => 'Busca realizada com sucesso',
            'data' => ['eventos' => $this->eventoService->findAllService()]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function findByIdController(Request $request, Response $response, array $args): Response
    {
        $evento = $this->eventoService->findByIdService((int) $args['id_evento']);

        $resposta = [
            'success' => true,
            'message' => 'Executado com sucesso',
            'data' => ['eventos' => $evento]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function updateController(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id_evento'];
        $objPHP = json_decode($request->getBody()->getContents());
        $atualizado = $this->eventoService->updateService($id, $objPHP);

        $resposta = [
            'success' => true,
            'message' => 'Atualizado com sucesso',
            'data' => ['eventos' => [$atualizado]]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function deleteController(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id_evento'];
        $this->eventoService->deleteService($id);

        $resposta = [
            'success' => true,
            'message' => 'Excluido com sucesso',
            'data' => ['eventos' => [['id_evento' => $id]]]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function countController(Request $request, Response $response, array $args): Response
    {
        $resposta = [
            'success' => true,
            'message' => 'Executado com sucesso',
            'data' => ['count' => $this->eventoService->countService()]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
