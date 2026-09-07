<?php
namespace Api\DAO;

use Api\Database\MysqlDatabase;
use Api\Models\Dependente;
use PDO;
use Exception;

/**
 * DependenteDAO
 * Responsável pela persistência de dependentes vinculados a funcionários.
 */
class DependenteDAO {
    private PDO $db;

    public function __construct(MysqlDatabase $mysqlDb) {
        $this->db = $mysqlDb->getConnection();
    }

    /**
     * Retorna todos os dependentes cadastrados.
     */
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

    /**
     * Busca dependente por ID.
     */
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

    /**
     * Insere um novo dependente.
     */
    public function create(Dependente $dep): int {
        $stmt = $this->db->prepare("INSERT INTO dependentes (nomeDependente, parentesco, idFuncionario) VALUES (?, ?, ?)");
        if (!$stmt->execute([$dep->getNomeDependente(), $dep->getParentesco(), $dep->getIdFuncionario()])) {
            error_log("Erro ao criar dependente: " . $dep->getNomeDependente());
            throw new Exception("Erro ao inserir dependente no banco de dados.");
        }
        return (int)$this->db->lastInsertId();
    }

    /**
     * Atualiza dados de um dependente.
     */
    public function update(Dependente $dep): bool {
        $stmt = $this->db->prepare("UPDATE dependentes SET nomeDependente = ?, parentesco = ?, idFuncionario = ? WHERE idDependente = ?");
        if (!$stmt->execute([$dep->getNomeDependente(), $dep->getParentesco(), $dep->getIdFuncionario(), $dep->getIdDependente()])) {
            error_log("Erro ao atualizar dependente ID: " . $dep->getIdDependente());
            return false;
        }
        return true;
    }

    /**
     * Remove um dependente.
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM dependentes WHERE idDependente = ?");
        if (!$stmt->execute([$id])) {
            error_log("Erro ao deletar dependente ID: " . $id);
            return false;
        }
        return true;
    }

    /**
     * Retorna dependentes de um funcionário específico.
     */
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
