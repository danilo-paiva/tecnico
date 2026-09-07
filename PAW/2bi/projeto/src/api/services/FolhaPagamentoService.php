<?php
namespace Api\Services;

use Api\DAO\FolhaPagamentoDAO;
use Api\Models\FolhaPagamento;
use Exception;

/**
 * FolhaPagamentoService
 * Gerencia a lógica de negócio para a emissão e controle de folhas de pagamento.
 */
class FolhaPagamentoService {
    private FolhaPagamentoDAO $dao;

    public function __construct(FolhaPagamentoDAO $dao) {
        $this->dao = $dao;
    }

    /**
     * Retorna todas as folhas de pagamento registradas.
     */
    public function listAll(): array {
        return $this->dao->getAll();
    }

    /**
     * Busca uma folha de pagamento por ID.
     */
    public function findById(int $id): FolhaPagamento {
        $folha = $this->dao->getById($id);
        if (!$folha) {
            error_log("Busca de folha de pagamento falhou: ID {$id} não encontrado.");
            throw new Exception("Folha de pagamento não encontrada.");
        }
        return $folha;
    }

    /**
     * Retorna a folha de pagamento de um funcionário específico.
     */
    public function listByFuncionario(int $idFunc): array {
        return $this->dao->findByFuncionario($idFunc);
    }

    /**
     * Registra um novo pagamento.
     */
    public function create(FolhaPagamento $folha): int {
        return $this->dao->create($folha);
    }

    /**
     * Atualiza os dados de um pagamento existente.
     */
    public function update(FolhaPagamento $folha): bool {
        return $this->dao->update($folha);
    }

    /**
     * Remove um registro de folha de pagamento.
     */
    public function delete(int $id): bool {
        return $this->dao->delete($id);
    }
}
