<?php
namespace Api\Controllers;

use Api\Services\DependenteService;
use Api\Http\ErrorResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DependenteController
{
    private DependenteService $service;

    public function __construct(DependenteService $service) {
        $this->service = $service;
    }

    public function index(Request $request, Response $response): Response {
        $data = $this->service->listAll();
        $response->getBody()->write(json_encode(["success" => true, "message" => "Busca realizada com sucesso", "data" => ["dependentes" => $data]]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function show(Request $request, Response $response, array $args): Response {
        try {
            $id = (int)$args['idDependente'];
            $data = $this->service->findById($id);
            $response->getBody()->write(json_encode(["success" => true, "message" => "Executado com sucesso", "data" => ["dependente" => $data]]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            throw new ErrorResponse(404, "Erro", ["message" => $e->getMessage()]);
        }
    }

    public function store(Request $request, Response $response): Response {
        $body = json_decode($request->getBody()->getContents());
        $dep = new \Api\Models\Dependente();
        $dep->setNomeDependente($body->dependente->nomeDependente);
        $dep->setParentesco($body->dependente->parentesco);
        $dep->setIdFuncionario($body->dependente->idFuncionario);
        $this->service->create($dep);
        $response->getBody()->write(json_encode(["success" => true, "message" => "Cadastro realizado com sucesso"]));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    public function update(Request $request, Response $response, array $args): Response {
        $body = json_decode($request->getBody()->getContents(), true);
        $id = (int)$args['idDependente'];
        $dep = new \Api\Models\Dependente();
        $dep->setIdDependente($id);
        $dep->setNomeDependente($body['dependente']['nomeDependente']);
        $dep->setParentesco($body['dependente']['parentesco']);
        $dep->setIdFuncionario($body['dependente']['idFuncionario']);
        $this->service->update($dep);
        $response->getBody()->write(json_encode(["success" => true, "message" => "Atualização realizada com sucesso"]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function destroy(Request $request, Response $response, array $args): Response {
        $id = (int)$args['idDependente'];
        $this->service->delete($id);
        return $response->withStatus(204);
    }

    public function listByFuncionario(Request $request, Response $response, array $args): Response {
        $idFunc = (int)$args['idFuncionario'];
        $data = $this->service->listByFuncionario($idFunc);
        $response->getBody()->write(json_encode(["success" => true, "message" => "Busca realizada com sucesso", "data" => ["dependentes" => $data]]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
