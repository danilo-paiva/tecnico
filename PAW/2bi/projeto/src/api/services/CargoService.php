<?php
namespace Api\Services;

use Api\DAO\CargoDAO;
use Api\Models\Cargo;
use Api\Http\ErrorResponse;
use stdClass;

class CargoService
{
    private CargoDAO $cargoDAO;

    public function __construct(CargoDAO $cargoDAODependency) {
        $this->cargoDAO = $cargoDAODependency;
    }

    public function createService(stdClass $objPHP): Cargo {
        $cargo = new Cargo();
        $cargo->setNomeCargo($objPHP->cargo->nomeCargo);
        $cargo->setIdDepartamento($objPHP->cargo->idDepartamento);

        $resultado = $this->cargoDAO->findByField('nomeCargo', $cargo->getNomeCargo());
        if (count($resultado) > 0) {
            throw new ErrorResponse(400, "Cargo já existe", ["message" => "O cargo {$cargo->getNomeCargo()} já existe"]);
        }
        return $this->cargoDAO->create($cargo);
    }

    public function countService(): int {
        return $this->cargoDAO->count();
    }

    public function findAllService(): array {
        return $this->cargoDAO->findAll();
    }

    public function findByIdService(int $idCargo): ?Cargo {
        return $this->cargoDAO->findById($idCargo);
    }

    public function updateService(int $idCargo, string $nomeCargo, int $idDepartamento): bool {
        $cargoExistente = $this->cargoDAO->findById($idCargo);
        if (!$cargoExistente) {
            throw new ErrorResponse(404, "Cargo não encontrado", ["message" => "Não existe cargo com id {$idCargo}"]);
        }
        $cargo = new Cargo();
        $cargo->setIdCargo($idCargo);
        $cargo->setNomeCargo($nomeCargo);
        $cargo->setIdDepartamento($idDepartamento);
        return $this->cargoDAO->update($cargo);
    }

    public function deleteService(int $idCargo): bool {
        $cargoExistente = $this->cargoDAO->findById($idCargo);
        if (!$cargoExistente) {
            throw new ErrorResponse(404, "Cargo não encontrado", ["message" => "Não existe cargo com id {$idCargo}"]);
        }
        $cargo = new Cargo();
        $cargo->setIdCargo($idCargo);
        return $this->cargoDAO->delete($cargo);
    }
}
