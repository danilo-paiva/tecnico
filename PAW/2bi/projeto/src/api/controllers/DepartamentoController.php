<?php
namespace Api\Controllers;

use Api\Services\DepartamentoService;
use Api\Http\ErrorResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use stdClass;

class DepartamentoController
{
    private DepartamentoService $service;

    public function __construct(DepartamentoService $service) {
        $this->service = $service;
    }

    public function index(Request $request, Response $response): Response {
        $data = $this->service->listAll();
        $response->getBody()->write(json_encode(["success" => true, "message" => "Busca realizada com sucesso", "data" => ["departamentos" => $data]]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function show(Request $request, Response $response, array $args): Response {
        try {
            $id = (int)$args['idDepartamento'];
            $data = $this->service->findById($id);
            $response->getBody()->write(json_encode(["success" => true, "message" => "Executado com sucesso", "data" => ["departamento" => $data]]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            throw new ErrorResponse(404, "Erro", ["message" => $e->getMessage()]);
        }
    }

    public function store(Request $request, Response $response): Response {
        $body = json_decode($request->getBody()->getContents());
        $dept = new \Api\Models\Departamento();
        $dept->setNomeDepartamento($body->departamento->nomeDepartamento);
        $dept->setIdDepartamento($body->departamento->idDepartamento ?? 0);
        $this->service->create($dept);
        $response->getBody()->write(json_encode(["success" => true, "message" => "Cadastro realizado com sucesso"]));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    public function update(Request $request, Response $response, array $args): Response {
        $body = json_decode($request->getBody()->getContents(), true);
        $id = (int)$args['idDepartamento'];
        $dept = new \Api\Models\Departamento();
        $dept->setIdDepartamento($id);
        $dept->setNomeDepartamento($body['departamento']['nomeDepartamento']);
        $this->service->update($dept);
        $response->getBody()->write(json_encode(["success" => true, "message" => "Atualização realizada com sucesso"]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function destroy(Request $request, Response $response, array $args): Response {
        $id = (int)$args['idDepartamento'];
        $this->service->delete($id);
        return $response->withStatus(204);
    }

    public function count(Request $request, Response $response): Response {
        $count = $this->service->count();
        $response->getBody()->write(json_encode(["success" => true, "message" => "Total de departamentos", "data" => ["total" => $count]]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
