<?php
namespace Api\Services;

use Api\DAO\DepartamentoDAO;
use Api\Models\Departamento;
use Exception;

/**
 * DepartamentoService
 * Orquestra as regras de negócio para a gestão de departamentos.
 */
class DepartamentoService {
    private DepartamentoDAO $dao;

    public function __construct(DepartamentoDAO $dao) {
        $this->dao = $dao;
    }

    /**
     * Retorna a lista completa de departamentos.
     */
    public function listAll(): array {
        return $this->dao->getAll();
    }

    /**
     * Busca um departamento por ID. Lança exceção se não encontrado.
     */
    public function findById(int $id): Departamento {
        $dept = $this->dao->getById($id);
        if (!$dept) {
            error_log("Busca de departamento falhou: ID {$id} não encontrado.");
            throw new Exception("Departamento não encontrado.");
        }
        return $dept;
    }

    /**
     * Registra um novo departamento e atualiza o modelo com o ID gerado.
     */
    public function create(Departamento $dept): Departamento {
        $id = $this->dao->create($dept);
        $dept->setIdDepartamento($id);
        return $dept;
    }

    /**
     * Atualiza as informações de um departamento.
     */
    public function update(Departamento $dept): bool {
        return $this->dao->update($dept);
    }

    /**
     * Remove um departamento do sistema.
     */
    public function delete(int $id): bool {
        return $this->dao->delete($id);
    }

    /**
     * Retorna a contagem total de departamentos.
     */
    public function count(): int {
        return $this->dao->count();
    }
}
