<?php
namespace Api\Services;

use Api\DAO\DependenteDAO;
use Api\Models\Dependente;
use Exception;

/**
 * DependenteService
 * Orquestra a lógica de negócio para a gestão de dependentes.
 */
class DependenteService {
    private DependenteDAO $dao;

    public function __construct(DependenteDAO $dao) {
        $this->dao = $dao;
    }

    /**
     * Retorna a lista de todos os dependentes.
     */
    public function listAll(): array {
        return $this->dao->getAll();
    }

    /**
     * Busca um dependente por ID. Lança exceção se não for encontrado.
     */
    public function findById(int $id): Dependente {
        $dep = $this->dao->getById($id);
        if (!$dep) {
            error_log("Busca de dependente falhou: ID {$id} não encontrado.");
            throw new Exception("Dependente não encontrado.");
        }
        return $dep;
    }

    /**
     * Retorna os dependentes vinculados a um funcionário específico.
     */
    public function listByFuncionario(int $idFunc): array {
        return $this->dao->findByFuncionario($idFunc);
    }

    /**
     * Registra um novo dependente.
     */
    public function create(Dependente $dep): int {
        return $this->dao->create($dep);
    }

    /**
     * Atualiza os dados de um dependente.
     */
    public function update(Dependente $dep): bool {
        return $this->dao->update($dep);
    }

    /**
     * Remove um dependente do sistema.
     */
    public function delete(int $id): bool {
        return $this->dao->delete($id);
    }
}
