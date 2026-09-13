<?php

namespace Api\DAO;

use Api\Models\Local;
use Api\Database\MysqlDatabase;
use PDO;

// SQL da tabela locais. Regras de negocio ficam no Service.
class LocalDAO
{
    private MysqlDatabase $database;

    public function __construct(MysqlDatabase $databaseInstance)
    {
        $this->database = $databaseInstance;
    }

    public function create(Local $local): Local
    {
        $sql = "INSERT INTO locais (nome, endereco, capacidade)
                VALUES (:nome, :endereco, :capacidade)";
        $stmt = $this->database->getConnection()->prepare($sql);
        $stmt->execute([
            ':nome' => $local->getNome(),
            ':endereco' => $local->getEndereco(),
            ':capacidade' => $local->getCapacidade()
        ]);
        $local->setIdLocal((int) $this->database->getConnection()->lastInsertId());
        return $local;
    }

    public function findAll(): array
    {
        $stmt = $this->database->getConnection()->query("SELECT * FROM locais ORDER BY id_local");
        $locais = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $locais[] = $this->mapRow($linha);
        }
        return $locais;
    }

    public function findById(int $id): ?Local
    {
        $stmt = $this->database->getConnection()->prepare("SELECT * FROM locais WHERE id_local = :id");
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);
        return $linha ? $this->mapRow($linha) : null;
    }

    public function findByNome(string $nome): ?Local
    {
        $stmt = $this->database->getConnection()->prepare("SELECT * FROM locais WHERE nome = :nome");
        $stmt->execute([':nome' => $nome]);
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);
        return $linha ? $this->mapRow($linha) : null;
    }

    public function update(Local $local): bool
    {
        $sql = "UPDATE locais SET nome = :nome, endereco = :endereco, capacidade = :capacidade
                WHERE id_local = :id";
        $stmt = $this->database->getConnection()->prepare($sql);
        $stmt->execute([
            ':nome' => $local->getNome(),
            ':endereco' => $local->getEndereco(),
            ':capacidade' => $local->getCapacidade(),
            ':id' => $local->getIdLocal()
        ]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->database->getConnection()->prepare("DELETE FROM locais WHERE id_local = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function count(): int
    {
        $linha = $this->database->getConnection()->query("SELECT COUNT(*) AS qtd FROM locais")->fetch(PDO::FETCH_ASSOC);
        return (int) $linha['qtd'];
    }

    private function mapRow(array $linha): Local
    {
        $local = new Local();
        $local->setIdLocal((int) $linha['id_local']);
        $local->setNome($linha['nome']);
        $local->setEndereco($linha['endereco']);
        $local->setCapacidade((int) $linha['capacidade']);
        return $local;
    }
}
