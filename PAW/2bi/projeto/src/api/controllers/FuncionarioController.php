<?php
namespace Api\Controllers;

use Api\Services\FuncionarioService;
use Api\Http\ErrorResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * FuncionarioController
 * Gerencia as requisições HTTP relacionadas aos funcionários e autenticação.
 */
class FuncionarioController
{
    private FuncionarioService $funcionarioService;

    public function __construct(FuncionarioService $funcionarioService) {
        $this->funcionarioService = $funcionarioService;
    }

    /**
     * Registra um novo funcionário.
     */
    public function createController(Request $request, Response $response, array $args): Response {
        $body = json_decode($request->getBody()->getContents());
        $resultado = $this->funcionarioService->createService($body);
        $resposta = ['success' => true, 'message' => 'Cadastro realizado com sucesso', 'data' => ['funcionarios' => [$resultado]]];
        $response->getBody()->write(json_encode($resposta));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    /**
     * Retorna a lista completa de funcionários.
     */
    public function findAllController(Request $request, Response $response, array $args): Response {
        $lista = $this->funcionarioService->findAll();
        $resposta = ['success' => true, 'message' => 'Executado com sucesso', 'data' => ['funcionarios' => $lista]];
        $response->getBody()->write(json_encode($resposta));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    /**
     * Busca um funcionário por ID.
     */
    public function findByidController(Request $request, Response $response, array $args): Response {
        $id = (int) $args['idFuncionario'];
        $funcionario = $this->funcionarioService->findByIdService($id);
        $resposta = ['success' => true, 'message' => 'Executado com sucesso', 'data' => ['funcionarios' => [$funcionario]]];
        $response->getBody()->write(json_encode($resposta));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    /**
     * Retorna a quantidade total de funcionários.
     */
    public function countController(Request $request, Response $response, array $args): Response {
        $qtd = $this->funcionarioService->countService();
        $resposta = ['success' => true, 'message' => 'Executado com sucesso', 'data' => ['count' => $qtd]];
        $response->getBody()->write(json_encode($resposta));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    /**
     * Atualiza os dados de um funcionário.
     */
    public function updateController(Request $request, Response $response, array $args): Response {
        $id = (int) $args['idFuncionario'];
        $body = json_decode($request->getBody()->getContents(), true);
        $resultado = $this->funcionarioService->updateService($id, $body);
        $resposta = ['success' => true, 'message' => 'Atualizado com sucesso', 'data' => ['funcionarios' => [$resultado]]];
        $response->getBody()->write(json_encode($resposta));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    /**
     * Remove um funcionário do sistema.
     */
    public function deleteController(Request $request, Response $response, array $args): Response {
        $id = (int) $args['idFuncionario'];
        $excluiu = $this->funcionarioService->deleteService($id);
        $resposta = ['success' => $excluiu, 'message' => $excluiu ? 'Excluído com sucesso' : 'Funcionário não encontrado'];
        $response->getBody()->write(json_encode($resposta));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($excluiu ? 204 : 404);
    }

    /**
     * Autentica um funcionário via e-mail e senha.
     */
    public function loginController(Request $request, Response $response, array $args): Response {
        $body = json_decode($request->getBody()->getContents(), true);
        $user = $this->funcionarioService->loginService($body);
        if (!$user) throw new ErrorResponse(401, "Não autorizado", ["message" => "Email ou senha inválidos"]);
        $resposta = ['success' => true, 'message' => 'Login realizado com sucesso', 'data' => $user];
        $response->getBody()->write(json_encode($resposta));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
