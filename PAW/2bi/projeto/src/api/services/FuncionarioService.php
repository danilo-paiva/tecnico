<?php
namespace Api\Services;

use Api\DAO\FuncionarioDAO;
use Api\DAO\CargoDAO;
use Api\Models\Funcionario;
use Api\Models\Cargo;
use Api\Http\ErrorResponse;
use stdClass;

class FuncionarioService
{
    private FuncionarioDAO $funcionarioDAO;
    private CargoDAO $cargoDAO;

    public function __construct(FuncionarioDAO $funcionarioDAODependency, CargoDAO $cargoDAODependency) {
        $this->funcionarioDAO = $funcionarioDAODependency;
        $this->cargoDAO = $cargoDAODependency;
    }

    public function createService(stdClass $jsonFuncionario): Funcionario {
        $cargo = new Cargo();
        $cargo->setIdCargo($jsonFuncionario->funcionario->cargo->idCargo);
        $cargoExiste = $this->cargoDAO->findById($cargo->getIdCargo());
        if (!$cargoExiste) {
            throw new ErrorResponse(404, "Cargo não encontrado", ["message" => "Cargo informado não existe"]);
        }

        $funcionario = new Funcionario();
        $funcionario->setNomeFuncionario($jsonFuncionario->funcionario->nomeFuncionario);
        $funcionario->setEmail($jsonFuncionario->funcionario->email);
        $funcionario->setSenha($jsonFuncionario->funcionario->senha);
        $funcionario->setRecebeValeTransporte($jsonFuncionario->funcionario->recebeValeTransporte);
        $funcionario->setCargo($cargoExiste);

        $emailExiste = $this->funcionarioDAO->findByField('email', $funcionario->getEmail());
        if (count($emailExiste) > 0) {
            throw new ErrorResponse(400, "Email já cadastrado", ["message" => "O email {$funcionario->getEmail()} já existe"]);
        }

        $idCriado = $this->funcionarioDAO->create($funcionario);
        $funcionario->setIdFuncionario($idCriado);
        return $funcionario;
    }

    public function loginService(array $jsonFuncionario): array {
        $funcionario = new Funcionario();
        $funcionario->setEmail($jsonFuncionario['funcionario']['email']);
        $funcionario->setSenha($jsonFuncionario['funcionario']['senha']);
        $encontrado = $this->funcionarioDAO->login($funcionario);
        if (!$encontrado) {
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

    public function findAll(): array {
        return $this->funcionarioDAO->findAll();
    }

    public function findByIdService(int $idFuncionario): Funcionario {
        $funcionario = $this->funcionarioDAO->findById($idFuncionario);
        if (!$funcionario) throw new ErrorResponse(404, "Funcionário não encontrado", ["message" => "Não existe funcionário com id {$idFuncionario}"]);
        return $funcionario;
    }

    public function updateService(int $idFuncionario, array $requestBody): bool {
        $funcionarioExiste = $this->funcionarioDAO->findById($idFuncionario);
        if (!$funcionarioExiste) throw new ErrorResponse(404, "Funcionário não encontrado", ["message" => "Não existe funcionário com id {$idFuncionario}"]);

        $jsonFuncionario = $requestBody['funcionario'];
        $cargo = $this->cargoDAO->findById($jsonFuncionario['cargo']['idCargo']);
        if (!$cargo) throw new ErrorResponse(404, "Cargo não encontrado", ["message" => "Cargo informado não existe"]);

        $funcionario = new Funcionario();
        $funcionario->setIdFuncionario($idFuncionario);
        $funcionario->setNomeFuncionario($jsonFuncionario['nomeFuncionario']);
        $funcionario->setEmail($jsonFuncionario['email']);
        if (!empty($jsonFuncionario['senha'])) $funcionario->setSenha($jsonFuncionario['senha']);
        $funcionario->setRecebeValeTransporte($jsonFuncionario['recebeValeTransporte']);
        $funcionario->setCargo($cargo);

        return $this->funcionarioDAO->update($funcionario);
    }

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
