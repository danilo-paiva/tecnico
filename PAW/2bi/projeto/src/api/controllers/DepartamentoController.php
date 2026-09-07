<?php
namespace Api\Controllers;

use Api\Services\DepartamentoService;
use Api\Http\ErrorResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;
use stdClass;

/**
 * DepartamentoController
 * Gerencia as requisições HTTP relacionadas aos departamentos.
 */
class DepartamentoController
{
    private DepartamentoService $service;

    public function __construct(DepartamentoService $service) {
        $this->service = $service;
    }

    /**
     * Retorna a lista de todos os departamentos.
     */
    public function index(Request $request, Response $response): Response {
        $data = $this->service->listAll();
        $response->getBody()->write(json_encode(["success" => true, "message" => "Busca realizada com sucesso", "data" => ["departamentos" => $data]]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Retorna os detalhes de um departamento específico.
     */
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

    /**
     * Cria um novo departamento.
     */
    public function store(Request $request, Response $response): Response {
        $body = json_decode($request->getBody()->getContents(), true);
        $dept = new \Api\Models\Departamento();
        $dept->setNomeDepartamento($body['departamento']['nomeDepartamento']);
        if (isset($body['departamento']['idDepartamento']) && $body['departamento']['idDepartamento'] > 0) {
            $dept->setIdDepartamento($body['departamento']['idDepartamento']);
        }
        $this->service->create($dept);
        $response->getBody()->write(json_encode(["success" => true, "message" => "Cadastro realizado com sucesso"]));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    /**
     * Atualiza as informações de um departamento.
     */
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

    /**
     * Remove um departamento do sistema.
     */
    public function destroy(Request $request, Response $response, array $args): Response {
        $id = (int)$args['idDepartamento'];
        $this->service->delete($id);
        return $response->withStatus(204);
    }

    /**
     * Retorna a contagem total de departamentos.
     */
    public function count(Request $request, Response $response): Response {
        $count = $this->service->count();
        $response->getBody()->write(json_encode(["success" => true, "message" => "Total de departamentos", "data" => ["total" => $count]]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
