<?php
namespace Api\Services;

use Api\DAO\DependenteDAO;
use Api\Models\Dependente;
use Exception;

class DependenteService {
    private DependenteDAO $dao;

    public function __construct(DependenteDAO $dao) {
        $this->dao = $dao;
    }

    public function listAll(): array {
        return $this->dao->getAll();
    }

    public function findById(int $id): Dependente {
        $dep = $this->dao->getById($id);
        if (!$dep) throw new Exception("Dependente não encontrado.");
        return $dep;
    }

    public function listByFuncionario(int $idFunc): array {
        return $this->dao->findByFuncionario($idFunc);
    }

    public function create(Dependente $dep): int {
        return $this->dao->create($dep);
    }

    public function update(Dependente $dep): bool {
        return $this->dao->update($dep);
    }

    public function delete(int $id): bool {
        return $this->dao->delete($id);
    }
}
