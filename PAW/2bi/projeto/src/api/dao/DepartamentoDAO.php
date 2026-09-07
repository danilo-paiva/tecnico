<?php
namespace Api\Dao;

use Api\Database\MysqlDatabase;
use Api\Models\Departamento;
use PDO;
use Exception;

/**
 * DepartamentoDAO
 * Responsável por todas as operações de banco de dados relacionadas à tabela de departamentos.
 */
class DepartamentoDAO {
    private PDO $db;

    public function __construct(MysqlDatabase $mysqlDb) {
        $this->db = $mysqlDb->getConnection();
    }

    /**
     * Retorna a lista de todos os departamentos cadastrados.
     */
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

    /**
     * Busca um único departamento pelo seu ID primário.
     */
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

    /**
     * Insere um novo registro de departamento no banco de dados.
     */
    public function create(Departamento $dept): int {
        $stmt = $this->db->prepare("INSERT INTO departamentos (nomeDepartamento) VALUES (?)");
        if (!$stmt->execute([$dept->getNomeDepartamento()])) {
            error_log("Erro ao criar departamento: " . $dept->getNomeDepartamento());
            throw new Exception("Erro ao inserir departamento no banco de dados.");
        }
        return (int)$this->db->lastInsertId();
    }

    /**
     * Atualiza os dados de um departamento existente.
     */
    public function update(Departamento $dept): bool {
        $stmt = $this->db->prepare("UPDATE departamentos SET nomeDepartamento = ? WHERE idDepartamento = ?");
        if (!$stmt->execute([$dept->getNomeDepartamento(), $dept->getIdDepartamento()])) {
            error_log("Erro ao atualizar departamento ID: " . $dept->getIdDepartamento());
            return false;
        }
        return true;
    }

    /**
     * Remove permanentemente um departamento do sistema.
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM departamentos WHERE idDepartamento = ?");
        if (!$stmt->execute([$id])) {
            error_log("Erro ao deletar departamento ID: " . $id);
            return false;
        }
        return true;
    }

    /**
     * Retorna a quantidade total de departamentos.
     */
    public function count(): int {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM departamentos");
        $row = $stmt->fetch();
        return (int)$row['total'];
    }
}
