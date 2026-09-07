<?php
namespace Api\Controllers;

use Api\Services\CargoService;
use Api\Http\ErrorResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * CargoController
 * Gerencia as requisições HTTP relacionadas aos cargos.
 */
class CargoController
{
    private CargoService $cargoService;

    public function __construct(CargoService $cargoServiceDependency) {
        $this->cargoService = $cargoServiceDependency;
    }

    /**
     * Cria um novo cargo.
     */
    public function createController(Request $request, Response $response, array $args): Response {
        $body = json_decode($request->getBody()->getContents());
        $novoCargo = $this->cargoService->createService($body);
        $resposta = ['success' => true, 'message' => 'Cadastro realizado com sucesso', 'data' => ['cargos' => [$novoCargo]]];
        $response->getBody()->write(json_encode($resposta));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    /**
     * Retorna a lista de todos os cargos.
     */
    public function findAllController(Request $request, Response $response, array $args): Response {
        $cargos = $this->cargoService->findAllService();
        $resposta = ['success' => true, 'message' => 'Busca realizada com sucesso', 'data' => ['cargos' => $cargos]];
        $response->getBody()->write(json_encode($resposta));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    /**
     * Busca um cargo por ID.
     */
    public function findByIdController(Request $request, Response $response, array $args): Response {
        $idCargo = (int) $args['idCargo'];
        $cargo = $this->cargoService->findByIdService($idCargo);
        $resposta = ['success' => true, 'message' => 'Executado com sucesso', 'data' => ['cargos' => $cargo]];
        $response->getBody()->write(json_encode($resposta));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    /**
     * Atualiza os dados de um cargo.
     */
    public function updateController(Request $request, Response $response, array $args): Response {
        $idCargo = (int) $args['idCargo'];
        $body = json_decode($request->getBody()->getContents());
        $this->cargoService->updateService($idCargo, $body->cargo->nomeCargo, $body->cargo->idDepartamento);
        $resposta = ['success' => true, 'message' => 'Atualizado com sucesso', 'data' => ['cargos' => [['idCargo' => $idCargo, 'nomeCargo' => $body->cargo->nomeCargo]]]];
        $response->getBody()->write(json_encode($resposta));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    /**
     * Remove um cargo do sistema.
     */
    public function deleteController(Request $request, Response $response, array $args): Response {
        $idCargo = (int) $args['idCargo'];
        $this->cargoService->deleteService($idCargo);
        return $response->withStatus(204);
    }

    /**
     * Retorna a contagem total de cargos.
     */
    public function countController(Request $request, Response $response, array $args): Response {
        $total = $this->cargoService->countService();
        $resposta = ['success' => true, 'message' => 'Executado com sucesso', 'data' => ['count' => $total]];
        $response->getBody()->write(json_encode($resposta));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
