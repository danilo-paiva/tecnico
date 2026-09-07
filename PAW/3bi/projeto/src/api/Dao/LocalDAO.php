<?php

declare(strict_types=1);

namespace Api\Dao;

use Api\Database\MysqlDatabase;
use Api\Models\Local;
use PDO;

class LocalDAO
{
    private MysqlDatabase $db;

    public function __construct(MysqlDatabase $db)
    {
        $this->db = $db;
    }

    private function getPdo(): PDO
    {
        return $this->db->getConnection();
    }

    private function mapRow(array $row): Local
    {
        return Local::fromArray([
            'idLocal'    => $row['id_local'] ?? $row['idLocal'] ?? null,
            'nome'       => $row['nome'] ?? '',
            'endereco'   => $row['endereco'] ?? '',
            'capacidade' => $row['capacidade'] ?? 0,
        ]);
    }

    /**
     * @return Local[]
     */
    public function getAll(): array
    {
        $sql = 'SELECT id_local, nome, endereco, capacidade FROM locais ORDER BY nome ASC';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->mapRow($row);
        }
        return $result;
    }

    public function getById(int $id): ?Local
    {
        $sql = 'SELECT id_local, nome, endereco, capacidade FROM locais WHERE id_local = :id LIMIT 1';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }
        return $this->mapRow($row);
    }

    public function create(Local $model): int
    {
        $sql = 'INSERT INTO locais (nome, endereco, capacidade) VALUES (:nome, :endereco, :capacidade)';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([
            ':nome'       => $model->getNome(),
            ':endereco'   => $model->getEndereco(),
            ':capacidade' => $model->getCapacidade(),
        ]);
        return (int) $this->getPdo()->lastInsertId();
    }

    public function update(Local $model): bool
    {
        $sql = 'UPDATE locais SET nome = :nome, endereco = :endereco, capacidade = :capacidade WHERE id_local = :id';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([
            ':nome'       => $model->getNome(),
            ':endereco'   => $model->getEndereco(),
            ':capacidade' => $model->getCapacidade(),
            ':id'         => $model->getIdLocal(),
        ]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM locais WHERE id_local = :id';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function count(): int
    {
        $sql = 'SELECT COUNT(*) AS total FROM locais';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Busca locais por nome ( LIKE %nome% ).
     * @return Local[]
     */
    public function findByNome(string $nome): array
    {
        $sql = 'SELECT id_local, nome, endereco, capacidade FROM locais WHERE nome LIKE :nome ORDER BY nome ASC';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([':nome' => '%' . $nome . '%']);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->mapRow($row);
        }
        return $result;
    }
}
