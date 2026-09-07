<?php

declare(strict_types=1);

namespace Api\Dao;

use Api\Database\MysqlDatabase;
use Api\Models\Evento;
use PDO;

class EventoDAO
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

    private function mapRow(array $row): Evento
    {
        return Evento::fromArray([
            'idEvento'   => $row['id_evento'] ?? $row['idEvento'] ?? null,
            'titulo'     => $row['titulo'] ?? '',
            'descricao'  => $row['descricao'] ?? null,
            'dataEvento' => $row['data_evento'] ?? $row['dataEvento'] ?? '',
            'status'     => $row['status'] ?? 'planejado',
            'idLocal'    => $row['id_local'] ?? $row['idLocal'] ?? 0,
        ]);
    }

    /**
     * @return Evento[]
     */
    public function getAll(): array
    {
        $sql = 'SELECT id_evento, titulo, descricao, data_evento, status, id_local FROM eventos ORDER BY data_evento DESC';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->mapRow($row);
        }
        return $result;
    }

    public function getById(int $id): ?Evento
    {
        $sql = 'SELECT id_evento, titulo, descricao, data_evento, status, id_local FROM eventos WHERE id_evento = :id LIMIT 1';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }
        return $this->mapRow($row);
    }

    public function create(Evento $model): int
    {
        $sql = 'INSERT INTO eventos (titulo, descricao, data_evento, status, id_local) VALUES (:titulo, :descricao, :data_evento, :status, :id_local)';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([
            ':titulo'      => $model->getTitulo(),
            ':descricao'   => $model->getDescricao(),
            ':data_evento' => $model->getDataEvento(),
            ':status'      => $model->getStatus(),
            ':id_local'    => $model->getIdLocal(),
        ]);
        return (int) $this->getPdo()->lastInsertId();
    }

    public function update(Evento $model): bool
    {
        $sql = 'UPDATE eventos SET titulo = :titulo, descricao = :descricao, data_evento = :data_evento, status = :status, id_local = :id_local WHERE id_evento = :id';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([
            ':titulo'      => $model->getTitulo(),
            ':descricao'   => $model->getDescricao(),
            ':data_evento' => $model->getDataEvento(),
            ':status'      => $model->getStatus(),
            ':id_local'    => $model->getIdLocal(),
            ':id'          => $model->getIdEvento(),
        ]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM eventos WHERE id_evento = :id';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function count(): int
    {
        $sql = 'SELECT COUNT(*) AS total FROM eventos';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Retorna eventos vinculados a um local.
     * @return Evento[]
     */
    public function getByLocal(int $idLocal): array
    {
        $sql = 'SELECT id_evento, titulo, descricao, data_evento, status, id_local FROM eventos WHERE id_local = :id_local ORDER BY data_evento DESC';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([':id_local' => $idLocal]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->mapRow($row);
        }
        return $result;
    }

    /**
     * Busca eventos por título e data ( LIKE no título e igualdade de data ).
     * Suporta data no formato Y-m-d ou Y-m-d H:i:s.
     * @return Evento[]
     */
    public function findByTituloData(string $titulo, string $dataEvento): array
    {
        $sql = 'SELECT id_evento, titulo, descricao, data_evento, status, id_local FROM eventos WHERE titulo LIKE :titulo AND DATE(data_evento) = DATE(:data_evento) ORDER BY data_evento DESC';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([
            ':titulo'      => '%' . $titulo . '%',
            ':data_evento' => $dataEvento,
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->mapRow($row);
        }
        return $result;
    }
}
