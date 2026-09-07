<?php
namespace Api\Services;

use Api\DAO\CargoDAO;
use Api\Models\Cargo;
use Api\Http\ErrorResponse;
use stdClass;

/**
 * CargoService
 * Implementa a lógica de negócio para a gestão de cargos e suas vinculações.
 */
class CargoService
{
    private CargoDAO $cargoDAO;

    public function __construct(CargoDAO $cargoDAODependency) {
        $this->cargoDAO = $cargoDAODependency;
    }

    /**
     * Cria um novo cargo. Verifica se já existe um cargo com o mesmo nome para evitar duplicidade.
     */
    public function createService(stdClass $objPHP): Cargo {
        $cargo = new Cargo();
        $cargo->setNomeCargo($objPHP->cargo->nomeCargo);
        $cargo->setIdDepartamento($objPHP->cargo->idDepartamento);

        // Verifica se o cargo já existe para evitar duplicidade de nomes
        $resultado = $this->cargoDAO->findByField('nomeCargo', $cargo->getNomeCargo());
        if (count($resultado) > 0) {
            error_log("Tentativa de criar cargo duplicado: {$cargo->getNomeCargo()}");
            throw new ErrorResponse(400, "Cargo já existe", ["message" => "O cargo {$cargo->getNomeCargo()} já existe"]);
        }
        return $this->cargoDAO->create($cargo);
    }

    /**
     * Retorna a quantidade total de cargos.
     */
    public function countService(): int {
        return $this->cargoDAO->count();
    }

    /**
     * Retorna a lista de todos os cargos.
     */
    public function findAllService(): array {
        return $this->cargoDAO->findAll();
    }

    /**
     * Busca um cargo pelo ID.
     */
    public function findByIdService(int $idCargo): ?Cargo {
        return $this->cargoDAO->findById($idCargo);
    }

    /**
     * Atualiza as informações de um cargo. Valida a existência do cargo antes da operação.
     */
    public function updateService(int $idCargo, string $nomeCargo, int $idDepartamento): bool {
        $cargoExistente = $this->cargoDAO->findById($idCargo);
        if (!$cargoExistente) {
            error_log("Erro ao atualizar cargo: ID {$idCargo} não encontrado.");
            throw new ErrorResponse(404, "Cargo não encontrado", ["message" => "Não existe cargo com id {$idCargo}"]);
        }
        $cargo = new Cargo();
        $cargo->setIdCargo($idCargo);
        $cargo->setNomeCargo($nomeCargo);
        $cargo->setIdDepartamento($idDepartamento);
        return $this->cargoDAO->update($cargo);
    }

    /**
     * Remove um cargo do sistema. Valida se o cargo existe antes de tentar a remoção.
     */
    public function deleteService(int $idCargo): bool {
        $cargoExistente = $this->cargoDAO->findById($idCargo);
        if (!$cargoExistente) {
            error_log("Erro ao deletar cargo: ID {$idCargo} não encontrado.");
            throw new ErrorResponse(404, "Cargo não encontrado", ["message" => "Não existe cargo com id {$idCargo}"]);
        }
        $cargo = new Cargo();
        $cargo->setIdCargo($idCargo);
        return $this->cargoDAO->delete($cargo);
    }
}
