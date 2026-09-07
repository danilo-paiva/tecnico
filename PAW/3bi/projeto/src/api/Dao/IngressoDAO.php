<?php

declare(strict_types=1);

namespace Api\Dao;

use Api\Database\MysqlDatabase;
use Api\Models\Ingresso;
use PDO;

class IngressoDAO
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

    private function mapRow(array $row): Ingresso
    {
        return Ingresso::fromArray([
            'idIngresso'           => $row['id_ingresso'] ?? $row['idIngresso'] ?? null,
            'tipo'                 => $row['tipo'] ?? '',
            'preco'                => $row['preco'] ?? 0,
            'quantidadeTotal'      => $row['quantidade_total'] ?? $row['quantidadeTotal'] ?? 0,
            'quantidadeDisponivel' => $row['quantidade_disponivel'] ?? $row['quantidadeDisponivel'] ?? 0,
            'idEvento'             => $row['id_evento'] ?? $row['idEvento'] ?? 0,
        ]);
    }

    /**
     * @return Ingresso[]
     */
    public function getAll(): array
    {
        $sql = 'SELECT id_ingresso, tipo, preco, quantidade_total, quantidade_disponivel, id_evento FROM ingressos ORDER BY id_ingresso ASC';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->mapRow($row);
        }
        return $result;
    }

    public function getById(int $id): ?Ingresso
    {
        $sql = 'SELECT id_ingresso, tipo, preco, quantidade_total, quantidade_disponivel, id_evento FROM ingressos WHERE id_ingresso = :id LIMIT 1';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }
        return $this->mapRow($row);
    }

    public function create(Ingresso $model): int
    {
        $sql = 'INSERT INTO ingressos (tipo, preco, quantidade_total, quantidade_disponivel, id_evento) VALUES (:tipo, :preco, :quantidade_total, :quantidade_disponivel, :id_evento)';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([
            ':tipo'                   => $model->getTipo(),
            ':preco'                  => $model->getPreco(),
            ':quantidade_total'       => $model->getQuantidadeTotal(),
            ':quantidade_disponivel'  => $model->getQuantidadeDisponivel(),
            ':id_evento'              => $model->getIdEvento(),
        ]);
        return (int) $this->getPdo()->lastInsertId();
    }

    public function update(Ingresso $model): bool
    {
        $sql = 'UPDATE ingressos SET tipo = :tipo, preco = :preco, quantidade_total = :quantidade_total, quantidade_disponivel = :quantidade_disponivel, id_evento = :id_evento WHERE id_ingresso = :id';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([
            ':tipo'                   => $model->getTipo(),
            ':preco'                  => $model->getPreco(),
            ':quantidade_total'       => $model->getQuantidadeTotal(),
            ':quantidade_disponivel'  => $model->getQuantidadeDisponivel(),
            ':id_evento'              => $model->getIdEvento(),
            ':id'                     => $model->getIdIngresso(),
        ]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM ingressos WHERE id_ingresso = :id';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function count(): int
    {
        $sql = 'SELECT COUNT(*) AS total FROM ingressos';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Retorna ingressos vinculados a um evento.
     * @return Ingresso[]
     */
    public function getByEvento(int $idEvento): array
    {
        $sql = 'SELECT id_ingresso, tipo, preco, quantidade_total, quantidade_disponivel, id_evento FROM ingressos WHERE id_evento = :id_evento ORDER BY tipo ASC';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([':id_evento' => $idEvento]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->mapRow($row);
        }
        return $result;
    }

    /**
     * Busca ingresso por tipo e evento (único por uq_ingresso_tipo_evento).
     */
    public function findByTipoEvento(string $tipo, int $idEvento): ?Ingresso
    {
        $sql = 'SELECT id_ingresso, tipo, preco, quantidade_total, quantidade_disponivel, id_evento FROM ingressos WHERE tipo = :tipo AND id_evento = :id_evento LIMIT 1';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([
            ':tipo'      => trim(mb_strtolower($tipo)),
            ':id_evento' => $idEvento,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }
        return $this->mapRow($row);
    }
}
