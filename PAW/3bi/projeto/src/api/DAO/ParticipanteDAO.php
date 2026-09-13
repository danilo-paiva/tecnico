<?php

namespace Api\DAO;

use Api\Models\Participante;
use Api\Database\MysqlDatabase;
use PDO;

// SQL da tabela participantes. A senha e gravada com hash e nunca e lida de volta.
class ParticipanteDAO
{
    private MysqlDatabase $database;

    public function __construct(MysqlDatabase $databaseInstance)
    {
        $this->database = $databaseInstance;
    }

    public function create(Participante $participante): Participante
    {
        $sql = "INSERT INTO participantes (nome, email, cpf, telefone, senha)
                VALUES (:nome, :email, :cpf, :telefone, :senha)";
        $stmt = $this->database->getConnection()->prepare($sql);
        $stmt->execute([
            ':nome' => $participante->getNome(),
            ':email' => $participante->getEmail(),
            ':cpf' => $participante->getCpf(),
            ':telefone' => $participante->getTelefone(),
            ':senha' => password_hash($participante->getSenha(), PASSWORD_DEFAULT)
        ]);
        $participante->setIdParticipante((int) $this->database->getConnection()->lastInsertId());
        return $participante;
    }

    public function findAll(): array
    {
        $stmt = $this->database->getConnection()->query(
            "SELECT id_participante, nome, email, cpf, telefone FROM participantes ORDER BY id_participante"
        );
        $participantes = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $participantes[] = $this->mapRow($linha);
        }
        return $participantes;
    }

    public function findById(int $id): ?Participante
    {
        $sql = "SELECT id_participante, nome, email, cpf, telefone FROM participantes WHERE id_participante = :id";
        $stmt = $this->database->getConnection()->prepare($sql);
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);
        return $linha ? $this->mapRow($linha) : null;
    }

    public function findByEmail(string $email): ?Participante
    {
        $sql = "SELECT id_participante, nome, email, cpf, telefone FROM participantes WHERE email = :email";
        $stmt = $this->database->getConnection()->prepare($sql);
        $stmt->execute([':email' => $email]);
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);
        return $linha ? $this->mapRow($linha) : null;
    }

    public function findByCpf(string $cpf): ?Participante
    {
        $sql = "SELECT id_participante, nome, email, cpf, telefone FROM participantes WHERE cpf = :cpf";
        $stmt = $this->database->getConnection()->prepare($sql);
        $stmt->execute([':cpf' => $cpf]);
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);
        return $linha ? $this->mapRow($linha) : null;
    }

    public function update(Participante $participante): bool
    {
        $sql = "UPDATE participantes
                SET nome = :nome, email = :email, cpf = :cpf, telefone = :telefone, senha = :senha
                WHERE id_participante = :id";
        $stmt = $this->database->getConnection()->prepare($sql);
        $stmt->execute([
            ':nome' => $participante->getNome(),
            ':email' => $participante->getEmail(),
            ':cpf' => $participante->getCpf(),
            ':telefone' => $participante->getTelefone(),
            ':senha' => password_hash($participante->getSenha(), PASSWORD_DEFAULT),
            ':id' => $participante->getIdParticipante()
        ]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->database->getConnection()->prepare("DELETE FROM participantes WHERE id_participante = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    // Confere email + senha para o login. Devolve o participante
    // sem a senha quando confere, ou null quando nao confere.
    public function verificarLogin(Participante $participante): ?Participante
    {
        $sql = "SELECT id_participante, nome, email, cpf, telefone, senha
                FROM participantes WHERE email = :email LIMIT 1";
        $stmt = $this->database->getConnection()->prepare($sql);
        $stmt->execute([':email' => $participante->getEmail()]);
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$linha) {
            return null;
        }

        if (!password_verify($participante->getSenha(), $linha['senha'])) {
            return null;
        }

        unset($linha['senha']);
        return $this->mapRow($linha);
    }

    public function count(): int
    {
        $linha = $this->database->getConnection()->query("SELECT COUNT(*) AS qtd FROM participantes")->fetch(PDO::FETCH_ASSOC);
        return (int) $linha['qtd'];
    }

    private function mapRow(array $linha): Participante
    {
        $participante = new Participante();
        $participante->setIdParticipante((int) $linha['id_participante']);
        $participante->setNome($linha['nome']);
        $participante->setEmail($linha['email']);
        $participante->setCpf($linha['cpf']);
        $participante->setTelefone($linha['telefone']);
        return $participante;
    }
}
