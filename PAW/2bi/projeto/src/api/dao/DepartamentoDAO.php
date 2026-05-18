<?php
namespace Api\Dao;

use Api\Database\MysqlDatabase;
use Api\Models\Departamento;
use PDO;

class DepartamentoDAO {
    private PDO $db;

    public function __construct(MysqlDatabase $mysqlDb) {
        $this->db = $mysqlDb->getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM departamentos");
        $results = [];
        while ($row = $stmt->fetch()) {
            $dept = new Departamento();
            $dept->setIdDepartamento((int)$row['idDepartamento']);
            $dept->setNomeDepartamento($row['nomeDepartamento']);
            $results[] = $dept;
        }
        return $results;
    }

    public function getById(int $id): ?Departamento {
        $stmt = $this->db->prepare("SELECT * FROM departamentos WHERE idDepartamento = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $dept = new Departamento();
        $dept->setIdDepartamento((int)$row['idDepartamento']);
        $dept->setNomeDepartamento($row['nomeDepartamento']);
        return $dept;
    }

    public function create(Departamento $dept): int {
        $stmt = $this->db->prepare("INSERT INTO departamentos (nomeDepartamento) VALUES (?)");
        $stmt->execute([$dept->getNomeDepartamento()]);
        return (int)$this->db->lastInsertId();
    }

    public function update(Departamento $dept): bool {
        $stmt = $this->db->prepare("UPDATE departamentos SET nomeDepartamento = ? WHERE idDepartamento = ?");
        return $stmt->execute([$dept->getNomeDepartamento(), $dept->getIdDepartamento()]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM departamentos WHERE idDepartamento = ?");
        return $stmt->execute([$id]);
    }

    public function count(): int {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM departamentos");
        $row = $stmt->fetch();
        return (int)$row['total'];
    }
}
