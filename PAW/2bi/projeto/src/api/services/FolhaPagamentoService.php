<?php
namespace Api\Services;

use Api\DAO\FolhaPagamentoDAO;
use Api\Models\FolhaPagamento;
use Exception;

class FolhaPagamentoService {
    private FolhaPagamentoDAO $dao;

    public function __construct(FolhaPagamentoDAO $dao) {
        $this->dao = $dao;
    }

    public function listAll(): array {
        return $this->dao->getAll();
    }

    public function findById(int $id): FolhaPagamento {
        $folha = $this->dao->getById($id);
        if (!$folha) throw new Exception("Folha de pagamento não encontrada.");
        return $folha;
    }

    public function listByFuncionario(int $idFunc): array {
        return $this->dao->findByFuncionario($idFunc);
    }

    public function create(FolhaPagamento $folha): int {
        return $this->dao->create($folha);
    }

    public function update(FolhaPagamento $folha): bool {
        return $this->dao->update($folha);
    }

    public function delete(int $id): bool {
        return $this->dao->delete($id);
    }
}
