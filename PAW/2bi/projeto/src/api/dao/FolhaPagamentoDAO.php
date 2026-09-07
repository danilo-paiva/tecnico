<?php
namespace Api\DAO;

use Api\Database\MysqlDatabase;
use Api\Models\FolhaPagamento;
use PDO;
use Exception;

/**
 * FolhaPagamentoDAO
 * Gerencia a persistência dos registros de folha de pagamento.
 */
class FolhaPagamentoDAO {
    private PDO $db;

    public function __construct(MysqlDatabase $mysqlDb) {
        $this->db = $mysqlDb->getConnection();
    }

    /**
     * Retorna todas as folhas de pagamento.
     */
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM folha_pagamento");
        $results = [];
        while ($row = $stmt->fetch()) {
            $folha = new FolhaPagamento();
            $folha->setIdFolha((int)$row['idFolha']);
            $folha->setDataPagamento($row['dataPagamento']);
            $folha->setValorLiquido((float)$row['valorLiquido']);
            $folha->setIdFuncionario((int)$row['idFuncionario']);
            $results[] = $folha;
        }
        return $results;
    }

    /**
     * Busca folha de pagamento por ID.
     */
    public function getById(int $id): ?FolhaPagamento {
        $stmt = $this->db->prepare("SELECT * FROM folha_pagamento WHERE idFolha = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $folha = new FolhaPagamento();
        $folha->setIdFolha((int)$row['idFolha']);
        $folha->setDataPagamento($row['dataPagamento']);
        $folha->setValorLiquido((float)$row['valorLiquido']);
        $folha->setIdFuncionario((int)$row['idFuncionario']);
        return $folha;
    }

    /**
     * Insere novo registro de folha.
     */
    public function create(FolhaPagamento $folha): int {
        $stmt = $this->db->prepare("INSERT INTO folha_pagamento (dataPagamento, valorLiquido, idFuncionario) VALUES (?, ?, ?)");
        if (!$stmt->execute([$folha->getDataPagamento(), $folha->getValorLiquido(), $folha->getIdFuncionario()])) {
            error_log("Erro ao criar folha de pagamento para funcionário ID: " . $folha->getIdFuncionario());
            throw new Exception("Erro ao inserir folha no banco de dados.");
        }
        return (int)$this->db->lastInsertId();
    }

    /**
     * Atualiza dados de folha.
     */
    public function update(FolhaPagamento $folha): bool {
        $stmt = $this->db->prepare("UPDATE folha_pagamento SET dataPagamento = ?, valorLiquido = ?, idFuncionario = ? WHERE idFolha = ?");
        if (!$stmt->execute([$folha->getDataPagamento(), $folha->getValorLiquido(), $folha->getIdFuncionario(), $folha->getIdFolha()])) {
            error_log("Erro ao atualizar folha ID: " . $folha->getIdFolha());
            return false;
        }
        return true;
    }

    /**
     * Remove registro de folha.
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM folha_pagamento WHERE idFolha = ?");
        if (!$stmt->execute([$id])) {
            error_log("Erro ao deletar folha ID: " . $id);
            return false;
        }
        return true;
    }

    /**
     * Retorna folhas de um funcionário específico.
     */
    public function findByFuncionario(int $idFuncionario): array {
        $stmt = $this->db->prepare("SELECT * FROM folha_pagamento WHERE idFuncionario = ?");
        $stmt->execute([$idFuncionario]);
        $results = [];
        while ($row = $stmt->fetch()) {
            $folha = new FolhaPagamento();
            $folha->setIdFolha((int)$row['idFolha']);
            $folha->setDataPagamento($row['dataPagamento']);
            $folha->setValorLiquido((float)$row['valorLiquido']);
            $folha->setIdFuncionario((int)$row['idFuncionario']);
            $results[] = $folha;
        }
        return $results;
    }
}
