<?php
namespace Api\Services;

use Api\DAO\FuncionarioDAO;
use Api\DAO\CargoDAO;
use Api\Models\Funcionario;
use Api\Models\Cargo;
use Api\Http\ErrorResponse;
use stdClass;

/**
 * FuncionarioService
 * Orquestra as regras de negócio para a gestão de funcionários, incluindo autenticação.
 */
class FuncionarioService
{
    private FuncionarioDAO $funcionarioDAO;
    private CargoDAO $cargoDAO;

    public function __construct(FuncionarioDAO $funcionarioDAODependency, CargoDAO $cargoDAODependency) {
        $this->funcionarioDAO = $funcionarioDAODependency;
        $this->cargoDAO = $cargoDAODependency;
    }

    /**
     * Cria um novo funcionário. Valida a existência do cargo e a unicidade do e-mail.
     */
    public function createService(stdClass $jsonFuncionario): Funcionario {
        $cargo = new Cargo();
        $cargo->setIdCargo($jsonFuncionario->funcionario->cargo->idCargo);
        $cargoExiste = $this->cargoDAO->findById($cargo->getIdCargo());
        if (!$cargoExiste) {
            error_log("Falha ao criar funcionário: Cargo ID {$cargo->getIdCargo()} não existe.");
            throw new ErrorResponse(404, "Cargo não encontrado", ["message" => "Cargo informado não existe"]);
        }

        $funcionario = new Funcionario();
        $funcionario->setNomeFuncionario($jsonFuncionario->funcionario->nomeFuncionario);
        $funcionario->setEmail($jsonFuncionario->funcionario->email);
        $funcionario->setSenha($jsonFuncionario->funcionario->senha);
        $funcionario->setRecebeValeTransporte($jsonFuncionario->funcionario->recebeValeTransporte);
        $funcionario->setCargo($cargoExiste);

        // Validação de unicidade de e-mail para evitar contas duplicadas
        $emailExiste = $this->funcionarioDAO->findByField('email', $funcionario->getEmail());
        if (count($emailExiste) > 0) {
            error_log("Tentativa de cadastro com e-mail já existente: {$funcionario->getEmail()}");
            throw new ErrorResponse(400, "Email já cadastrado", ["message" => "O email {$funcionario->getEmail()} já existe"]);
        }

        $idCriado = $this->funcionarioDAO->create($funcionario);
        $funcionario->setIdFuncionario($idCriado);
        return $funcionario;
    }

    /**
     * Autentica o usuário verificando e-mail e senha (hash).
     */
    public function loginService(array $jsonFuncionario): array {
        $funcionario = new Funcionario();
        $funcionario->setEmail($jsonFuncionario['funcionario']['email']);
        $funcionario->setSenha($jsonFuncionario['funcionario']['senha']);
        $encontrado = $this->funcionarioDAO->login($funcionario);
        if (!$encontrado) {
            error_log("Falha de login para o e-mail: " . $jsonFuncionario['funcionario']['email']);
            throw new ErrorResponse(401, "Usuário ou senha inválidos", ["message" => "Não foi possível autenticar"]);
        }
        return [
            "user" => [
                "funcionario" => [
                    "email" => $encontrado->getEmail(),
                    "role" => $encontrado->getCargo()->getNomeCargo(),
                    "name" => $encontrado->getNomeFuncionario(),
                    "idFuncionario" => $encontrado->getIdFuncionario()
                ]
            ]
        ];
    }

    /**
     * Retorna a lista completa de funcionários.
     */
    public function findAll(): array {
        return $this->funcionarioDAO->findAll();
    }

    /**
     * Busca um funcionário por ID.
     */
    public function findByIdService(int $idFuncionario): Funcionario {
        $funcionario = $this->funcionarioDAO->findById($idFuncionario);
        if (!$funcionario) throw new ErrorResponse(404, "Funcionário não encontrado", ["message" => "Não existe funcionário com id {$idFuncionario}"]);
        return $funcionario;
    }

    /**
     * Atualiza os dados de um funcionário. Valida existência do funcionário e do cargo.
     */
    public function updateService(int $idFuncionario, array $requestBody): bool {
        $funcionarioExiste = $this->funcionarioDAO->findById($idFuncionario);
        if (!$funcionarioExiste) {
            error_log("Erro ao atualizar funcionário: ID {$idFuncionario} não encontrado.");
            throw new ErrorResponse(404, "Funcionário não encontrado", ["message" => "Não existe funcionário com id {$idFuncionario}"]);
        }

        $jsonFuncionario = $requestBody['funcionario'];
        $cargo = $this->cargoDAO->findById($jsonFuncionario['cargo']['idCargo']);
        if (!$cargo) {
            error_log("Erro ao atualizar funcionário: Cargo ID {$jsonFuncionario['cargo']['idCargo']} não existe.");
            throw new ErrorResponse(404, "Cargo não encontrado", ["message" => "Cargo informado não existe"]);
        }

        $funcionario = new Funcionario();
        $funcionario->setIdFuncionario($idFuncionario);
        $funcionario->setNomeFuncionario($jsonFuncionario['nomeFuncionario']);
        $funcionario->setEmail($jsonFuncionario['email']);
        if (!empty($jsonFuncionario['senha'])) $funcionario->setSenha($jsonFuncionario['senha']);
        $funcionario->setRecebeValeTransporte($jsonFuncionario['recebeValeTransporte']);
        $funcionario->setCargo($cargo);

        return $this->funcionarioDAO->update($funcionario);
    }

    /**
     * Remove um funcionário do sistema.
     */
    public function deleteService(int $idFuncionario): bool {
        $funcionarioExiste = $this->funcionarioDAO->findById($idFuncionario);
        if (!$funcionarioExiste) throw new ErrorResponse(404, "Funcionário não encontrado", ["message" => "Não existe funcionário com id {$idFuncionario}"]);
        $funcionario = new Funcionario();
        $funcionario->setIdFuncionario($idFuncionario);
        return $this->funcionarioDAO->delete($funcionario);
    }

    public function countService(): int {
        return $this->funcionarioDAO->count();
    }
}
