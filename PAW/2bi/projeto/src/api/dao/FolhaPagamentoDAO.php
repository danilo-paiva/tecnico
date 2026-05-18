<?php
namespace Api\DAO;

use Api\Database\MysqlDatabase;
use Api\Models\FolhaPagamento;
use PDO;

class FolhaPagamentoDAO {
    private PDO $db;

    public function __construct(MysqlDatabase $mysqlDb) {
        $this->db = $mysqlDb->getConnection();
    }

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

    public function create(FolhaPagamento $folha): int {
        $stmt = $this->db->prepare("INSERT INTO folha_pagamento (dataPagamento, valorLiquido, idFuncionario) VALUES (?, ?, ?)");
        $stmt->execute([$folha->getDataPagamento(), $folha->getValorLiquido(), $folha->getIdFuncionario()]);
        return (int)$this->db->lastInsertId();
    }

    public function update(FolhaPagamento $folha): bool {
        $stmt = $this->db->prepare("UPDATE folha_pagamento SET dataPagamento = ?, valorLiquido = ?, idFuncionario = ? WHERE idFolha = ?");
        return $stmt->execute([$folha->getDataPagamento(), $folha->getValorLiquido(), $folha->getIdFuncionario(), $folha->getIdFolha()]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM folha_pagamento WHERE idFolha = ?");
        return $stmt->execute([$id]);
    }

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
