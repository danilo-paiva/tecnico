<?php
namespace Api\DAO;

use Api\Database\MysqlDatabase;
use Api\Models\Dependente;
use PDO;

class DependenteDAO {
    private PDO $db;

    public function __construct(MysqlDatabase $mysqlDb) {
        $this->db = $mysqlDb->getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM dependentes");
        $results = [];
        while ($row = $stmt->fetch()) {
            $dep = new Dependente();
            $dep->setIdDependente((int)$row['idDependente']);
            $dep->setNomeDependente($row['nomeDependente']);
            $dep->setParentesco($row['parentesco']);
            $dep->setIdFuncionario((int)$row['idFuncionario']);
            $results[] = $dep;
        }
        return $results;
    }

    public function getById(int $id): ?Dependente {
        $stmt = $this->db->prepare("SELECT * FROM dependentes WHERE idDependente = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $dep = new Dependente();
        $dep->setIdDependente((int)$row['idDependente']);
        $dep->setNomeDependente($row['nomeDependente']);
        $dep->setParentesco($row['parentesco']);
        $dep->setIdFuncionario((int)$row['idFuncionario']);
        return $dep;
    }

    public function create(Dependente $dep): int {
        $stmt = $this->db->prepare("INSERT INTO dependentes (nomeDependente, parentesco, idFuncionario) VALUES (?, ?, ?)");
        $stmt->execute([$dep->getNomeDependente(), $dep->getParentesco(), $dep->getIdFuncionario()]);
        return (int)$this->db->lastInsertId();
    }

    public function update(Dependente $dep): bool {
        $stmt = $this->db->prepare("UPDATE dependentes SET nomeDependente = ?, parentesco = ?, idFuncionario = ? WHERE idDependente = ?");
        return $stmt->execute([$dep->getNomeDependente(), $dep->getParentesco(), $dep->getIdFuncionario(), $dep->getIdDependente()]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM dependentes WHERE idDependente = ?");
        return $stmt->execute([$id]);
    }

    public function findByFuncionario(int $idFuncionario): array {
        $stmt = $this->db->prepare("SELECT * FROM dependentes WHERE idFuncionario = ?");
        $stmt->execute([$idFuncionario]);
        $results = [];
        while ($row = $stmt->fetch()) {
            $dep = new Dependente();
            $dep->setIdDependente((int)$row['idDependente']);
            $dep->setNomeDependente($row['nomeDependente']);
            $dep->setParentesco($row['parentesco']);
            $dep->setIdFuncionario((int)$row['idFuncionario']);
            $results[] = $dep;
        }
        return $results;
    }
}
