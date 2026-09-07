<?php
namespace Api\Controllers;

use Api\Services\FolhaPagamentoService;
use Api\Http\ErrorResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * FolhaPagamentoController
 * Gerencia as requisições HTTP relacionadas às folhas de pagamento.
 */
class FolhaPagamentoController
{
    private FolhaPagamentoService $service;

    public function __construct(FolhaPagamentoService $service) {
        $this->service = $service;
    }

    /**
     * Retorna a lista de todas as folhas de pagamento.
     */
    public function index(Request $request, Response $response): Response {
        $data = $this->service->listAll();
        $response->getBody()->write(json_encode(["success" => true, "message" => "Busca realizada com sucesso", "data" => ["folhas" => $data]]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Retorna os detalhes de uma folha de pagamento específica.
     */
    public function show(Request $request, Response $response, array $args): Response {
        try {
            $id = (int)$args['idFolha'];
            $data = $this->service->findById($id);
            $response->getBody()->write(json_encode(["success" => true, "message" => "Executado com sucesso", "data" => ["folha" => $data]]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            throw new ErrorResponse(404, "Erro", ["message" => $e->getMessage()]);
        }
    }

    /**
     * Registra um novo pagamento.
     */
    public function store(Request $request, Response $response): Response {
        $body = json_decode($request->getBody()->getContents());
        $folha = new \Api\Models\FolhaPagamento();
        $folha->setDataPagamento($body->folha->dataPagamento);
        $folha->setValorLiquido($body->folha->valorLiquido);
        $folha->setIdFuncionario($body->folha->idFuncionario);
        $this->service->create($folha);
        $response->getBody()->write(json_encode(["success" => true, "message" => "Cadastro realizado com sucesso"]));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    /**
     * Atualiza os dados de uma folha de pagamento.
     */
    public function update(Request $request, Response $response, array $args): Response {
        $body = json_decode($request->getBody()->getContents(), true);
        $id = (int)$args['idFolha'];
        $folha = new \Api\Models\FolhaPagamento();
        $folha->setIdFolha($id);
        $folha->setDataPagamento($body['folha']['dataPagamento']);
        $folha->setValorLiquido($body['folha']['valorLiquido']);
        $folha->setIdFuncionario($body['folha']['idFuncionario']);
        $this->service->update($folha);
        $response->getBody()->write(json_encode(["success" => true, "message" => "Atualização realizada com sucesso"]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Remove um registro de folha de pagamento.
     */
    public function destroy(Request $request, Response $response, array $args): Response {
        $id = (int)$args['idFolha'];
        $this->service->delete($id);
        return $response->withStatus(204);
    }

    /**
     * Retorna as folhas de pagamento de um funcionário específico.
     */
    public function listByFuncionario(Request $request, Response $response, array $args): Response {
        $idFunc = (int)$args['idFuncionario'];
        $data = $this->service->listByFuncionario($idFunc);
        $response->getBody()->write(json_encode(["success" => true, "message" => "Busca realizada com sucesso", "data" => ["folhas" => $data]]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
