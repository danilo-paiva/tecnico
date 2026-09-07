<?php

declare(strict_types=1);

namespace Api\Dao;

use Api\Database\MysqlDatabase;
use Api\Models\Compra;
use PDO;

class CompraDAO
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

    private function mapRow(array $row): Compra
    {
        return Compra::fromArray([
            'idCompra'       => $row['id_compra'] ?? $row['idCompra'] ?? null,
            'dataCompra'     => $row['data_compra'] ?? $row['dataCompra'] ?? '',
            'quantidade'     => $row['quantidade'] ?? 0,
            'valorTotal'     => $row['valor_total'] ?? $row['valorTotal'] ?? 0,
            'idParticipante' => $row['id_participante'] ?? $row['idParticipante'] ?? 0,
            'idIngresso'     => $row['id_ingresso'] ?? $row['idIngresso'] ?? 0,
        ]);
    }

    /**
     * @return Compra[]
     */
    public function getAll(): array
    {
        $sql = 'SELECT id_compra, data_compra, quantidade, valor_total, id_participante, id_ingresso FROM compras ORDER BY data_compra DESC';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->mapRow($row);
        }
        return $result;
    }

    public function getById(int $id): ?Compra
    {
        $sql = 'SELECT id_compra, data_compra, quantidade, valor_total, id_participante, id_ingresso FROM compras WHERE id_compra = :id LIMIT 1';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }
        return $this->mapRow($row);
    }

    public function create(Compra $model): int
    {
        $sql = 'INSERT INTO compras (data_compra, quantidade, valor_total, id_participante, id_ingresso) VALUES (:data_compra, :quantidade, :valor_total, :id_participante, :id_ingresso)';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([
            ':data_compra'     => $model->getDataCompra(),
            ':quantidade'      => $model->getQuantidade(),
            ':valor_total'     => $model->getValorTotal(),
            ':id_participante' => $model->getIdParticipante(),
            ':id_ingresso'     => $model->getIdIngresso(),
        ]);
        return (int) $this->getPdo()->lastInsertId();
    }

    public function update(Compra $model): bool
    {
        $sql = 'UPDATE compras SET data_compra = :data_compra, quantidade = :quantidade, valor_total = :valor_total, id_participante = :id_participante, id_ingresso = :id_ingresso WHERE id_compra = :id';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([
            ':data_compra'     => $model->getDataCompra(),
            ':quantidade'      => $model->getQuantidade(),
            ':valor_total'     => $model->getValorTotal(),
            ':id_participante' => $model->getIdParticipante(),
            ':id_ingresso'     => $model->getIdIngresso(),
            ':id'              => $model->getIdCompra(),
        ]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM compras WHERE id_compra = :id';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function count(): int
    {
        $sql = 'SELECT COUNT(*) AS total FROM compras';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Retorna compras de um participante.
     * @return Compra[]
     */
    public function getByParticipante(int $idParticipante): array
    {
        $sql = 'SELECT id_compra, data_compra, quantidade, valor_total, id_participante, id_ingresso FROM compras WHERE id_participante = :id_participante ORDER BY data_compra DESC';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([':id_participante' => $idParticipante]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->mapRow($row);
        }
        return $result;
    }

    /**
     * Retorna compras de um ingresso.
     * @return Compra[]
     */
    public function getByIngresso(int $idIngresso): array
    {
        $sql = 'SELECT id_compra, data_compra, quantidade, valor_total, id_participante, id_ingresso FROM compras WHERE id_ingresso = :id_ingresso ORDER BY data_compra DESC';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([':id_ingresso' => $idIngresso]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->mapRow($row);
        }
        return $result;
    }
}
