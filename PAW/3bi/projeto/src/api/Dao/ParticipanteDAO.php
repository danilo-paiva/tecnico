<?php

declare(strict_types=1);

namespace Api\Dao;

use Api\Database\MysqlDatabase;
use Api\Models\Participante;
use PDO;

class ParticipanteDAO
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

    private function mapRow(array $row): Participante
    {
        return Participante::fromArray([
            'idParticipante' => $row['id_participante'] ?? $row['idParticipante'] ?? null,
            'nome'           => $row['nome'] ?? '',
            'email'          => $row['email'] ?? '',
            'cpf'            => $row['cpf'] ?? '',
            'telefone'       => $row['telefone'] ?? '',
            'senha'          => $row['senha'] ?? '',
        ]);
    }

    /**
     * @return Participante[]
     */
    public function getAll(): array
    {
        $sql = 'SELECT id_participante, nome, email, cpf, telefone, senha FROM participantes ORDER BY nome ASC';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->mapRow($row);
        }
        return $result;
    }

    public function getById(int $id): ?Participante
    {
        $sql = 'SELECT id_participante, nome, email, cpf, telefone, senha FROM participantes WHERE id_participante = :id LIMIT 1';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }
        return $this->mapRow($row);
    }

    public function create(Participante $model): int
    {
        $sql = 'INSERT INTO participantes (nome, email, cpf, telefone, senha) VALUES (:nome, :email, :cpf, :telefone, :senha)';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([
            ':nome'     => $model->getNome(),
            ':email'    => $model->getEmail(),
            ':cpf'      => $model->getCpf(),
            ':telefone' => $model->getTelefone(),
            ':senha'    => $model->getSenha(),
        ]);
        return (int) $this->getPdo()->lastInsertId();
    }

    public function update(Participante $model): bool
    {
        $sql = 'UPDATE participantes SET nome = :nome, email = :email, cpf = :cpf, telefone = :telefone, senha = :senha WHERE id_participante = :id';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([
            ':nome'     => $model->getNome(),
            ':email'    => $model->getEmail(),
            ':cpf'      => $model->getCpf(),
            ':telefone' => $model->getTelefone(),
            ':senha'    => $model->getSenha(),
            ':id'       => $model->getIdParticipante(),
        ]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM participantes WHERE id_participante = :id';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function count(): int
    {
        $sql = 'SELECT COUNT(*) AS total FROM participantes';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }

    public function findByEmail(string $email): ?Participante
    {
        $sql = 'SELECT id_participante, nome, email, cpf, telefone, senha FROM participantes WHERE email = :email LIMIT 1';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([':email' => trim(mb_strtolower($email))]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }
        return $this->mapRow($row);
    }

    public function findByCpf(string $cpf): ?Participante
    {
        $numeros = preg_replace('/\D/', '', $cpf);
        $sql = 'SELECT id_participante, nome, email, cpf, telefone, senha FROM participantes WHERE cpf = :cpf LIMIT 1';
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute([':cpf' => $numeros]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            // fallback: tenta com valor bruto (caso esteja formatado no banco)
            $stmt2 = $this->getPdo()->prepare('SELECT id_participante, nome, email, cpf, telefone, senha FROM participantes WHERE cpf = :cpf2 LIMIT 1');
            $stmt2->execute([':cpf2' => $cpf]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                return null;
            }
        }
        return $this->mapRow($row);
    }
}
