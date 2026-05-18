<?php
namespace Api\Services;

use Api\DAO\DepartamentoDAO;
use Api\Models\Departamento;
use Exception;

class DepartamentoService {
    private DepartamentoDAO $dao;

    public function __construct(DepartamentoDAO $dao) {
        $this->dao = $dao;
    }

    public function listAll(): array {
        return $this->dao->getAll();
    }

    public function findById(int $id): Departamento {
        $dept = $this->dao->getById($id);
        if (!$dept) throw new Exception("Departamento não encontrado.");
        return $dept;
    }

    public function create(Departamento $dept): Departamento {
        $id = $this->dao->create($dept);
        $dept->setIdDepartamento($id);
        return $dept;
    }

    public function update(Departamento $dept): bool {
        return $this->dao->update($dept);
    }

    public function delete(int $id): bool {
        return $this->dao->delete($id);
    }

    public function count(): int {
        return $this->dao->count();
    }
}
