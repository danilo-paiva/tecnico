<?php

namespace Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Api\Services\ParticipanteService;

// Recebe o HTTP, chama o Service e devolve JSON padrao.
// Nao tem regra de negocio aqui.
class ParticipanteController
{
    private ParticipanteService $participanteService;

    public function __construct(ParticipanteService $participanteService)
    {
        $this->participanteService = $participanteService;
    }

    public function createController(Request $request, Response $response, array $args): Response
    {
        $objPHP = json_decode($request->getBody()->getContents());
        $novo = $this->participanteService->createService($objPHP);

        $resposta = [
            'success' => true,
            'message' => 'Cadastro realizado com sucesso',
            'data' => ['participantes' => [$novo]]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function findAllController(Request $request, Response $response, array $args): Response
    {
        $resposta = [
            'success' => true,
            'message' => 'Busca realizada com sucesso',
            'data' => ['participantes' => $this->participanteService->findAllService()]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function findByIdController(Request $request, Response $response, array $args): Response
    {
        $participante = $this->participanteService->findByIdService((int) $args['id_participante']);

        $resposta = [
            'success' => true,
            'message' => 'Executado com sucesso',
            'data' => ['participantes' => $participante]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function updateController(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id_participante'];
        $objPHP = json_decode($request->getBody()->getContents());
        $atualizado = $this->participanteService->updateService($id, $objPHP);

        $resposta = [
            'success' => true,
            'message' => 'Atualizado com sucesso',
            'data' => ['participantes' => [$atualizado]]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function deleteController(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id_participante'];
        $this->participanteService->deleteService($id);

        $resposta = [
            'success' => true,
            'message' => 'Excluido com sucesso',
            'data' => ['participantes' => [['id_participante' => $id]]]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function countController(Request $request, Response $response, array $args): Response
    {
        $resposta = [
            'success' => true,
            'message' => 'Executado com sucesso',
            'data' => ['count' => $this->participanteService->countService()]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    // POST /login — autentica e devolve o participante + token JWT.
    // Body: {"participante": {"email": "...", "senha": "..."}}
    public function loginController(Request $request, Response $response, array $args): Response
    {
        $objPHP = json_decode($request->getBody()->getContents(), true);
        $resultado = $this->participanteService->loginService($objPHP['participante']);

        $participante = $resultado['participante'];
        $resposta = [
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'data' => [
                'participante' => [
                    'id_participante' => $participante->getIdParticipante(),
                    'nome' => $participante->getNome(),
                    'email' => $participante->getEmail(),
                    'perfil' => $participante->getPerfil(),
                ],
                'token' => $resultado['token']
            ]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    // GET /auth/me — devolve o dono do token (plano, p/ o verifyAuth do frontend).
    public function meController(Request $request, Response $response, array $args): Response
    {
        $payload = $request->getAttribute('jwtPayload');
        $participante = $this->participanteService->findByIdService(
            (int) ($payload->participante->id_participante ?? 0));
        $resposta = [
            'success' => true,
            'message' => 'Autenticado',
            'data' => [
                'id_participante' => $participante->getIdParticipante(),
                'nome' => $participante->getNome(),
                'email' => $participante->getEmail(),
                'perfil' => $participante->getPerfil(),
            ]
        ];
        $response->getBody()->write(json_encode($resposta, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
