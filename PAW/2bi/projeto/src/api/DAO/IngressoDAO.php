<?php

namespace Api\DAO;

use Api\Models\Ingresso;
use Api\Database\MysqlDatabase;
use PDO;

// SQL da tabela ingressos. Regras de negocio ficam no Service.
class IngressoDAO
{
    private MysqlDatabase $database;

    public function __construct(MysqlDatabase $databaseInstance)
    {
        $this->database = $databaseInstance;
    }

    public function create(Ingresso $ingresso): Ingresso
    {
        $sql = "INSERT INTO ingressos (tipo, preco, quantidade_total, quantidade_disponivel, id_evento)
                VALUES (:tipo, :preco, :total, :disponivel, :id_evento)";
        $stmt = $this->database->getConnection()->prepare($sql);
        $stmt->execute([
            ':tipo' => $ingresso->getTipo(),
            ':preco' => $ingresso->getPreco(),
            ':total' => $ingresso->getQuantidadeTotal(),
            ':disponivel' => $ingresso->getQuantidadeDisponivel(),
            ':id_evento' => $ingresso->getIdEvento()
        ]);
        $ingresso->setIdIngresso((int) $this->database->getConnection()->lastInsertId());
        return $ingresso;
    }

    public function findAll(): array
    {
        $stmt = $this->database->getConnection()->query("SELECT * FROM ingressos ORDER BY id_ingresso");
        $ingressos = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $ingressos[] = $this->mapRow($linha);
        }
        return $ingressos;
    }

    public function findById(int $id): ?Ingresso
    {
        $stmt = $this->database->getConnection()->prepare("SELECT * FROM ingressos WHERE id_ingresso = :id");
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);
        return $linha ? $this->mapRow($linha) : null;
    }

    // Verifica se ja existe outro ingresso com o mesmo tipo no mesmo evento
    public function existsTipoEvento(string $tipo, int $idEvento, int $ignorarId = 0): bool
    {
        $sql = "SELECT COUNT(*) AS qtd FROM ingressos
                WHERE tipo = :tipo AND id_evento = :evento AND id_ingresso != :id";
        $stmt = $this->database->getConnection()->prepare($sql);
        $stmt->execute([':tipo' => $tipo, ':evento' => $idEvento, ':id' => $ignorarId]);
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $linha['qtd'] > 0;
    }

    // Quantos ingressos um evento possui (para bloquear exclusao com vinculo)
    public function countByEvento(int $idEvento): int
    {
        $stmt = $this->database->getConnection()->prepare("SELECT COUNT(*) AS qtd FROM ingressos WHERE id_evento = :id");
        $stmt->execute([':id' => $idEvento]);
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $linha['qtd'];
    }

    public function update(Ingresso $ingresso): bool
    {
        $sql = "UPDATE ingressos
                SET tipo = :tipo, preco = :preco, quantidade_total = :total,
                    quantidade_disponivel = :disponivel, id_evento = :id_evento
                WHERE id_ingresso = :id";
        $stmt = $this->database->getConnection()->prepare($sql);
        $stmt->execute([
            ':tipo' => $ingresso->getTipo(),
            ':preco' => $ingresso->getPreco(),
            ':total' => $ingresso->getQuantidadeTotal(),
            ':disponivel' => $ingresso->getQuantidadeDisponivel(),
            ':id_evento' => $ingresso->getIdEvento(),
            ':id' => $ingresso->getIdIngresso()
        ]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->database->getConnection()->prepare("DELETE FROM ingressos WHERE id_ingresso = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function count(): int
    {
        $linha = $this->database->getConnection()->query("SELECT COUNT(*) AS qtd FROM ingressos")->fetch(PDO::FETCH_ASSOC);
        return (int) $linha['qtd'];
    }

    private function mapRow(array $linha): Ingresso
    {
        $ingresso = new Ingresso();
        $ingresso->setIdIngresso((int) $linha['id_ingresso']);
        $ingresso->setTipo($linha['tipo']);
        $ingresso->setPreco((float) $linha['preco']);
        $ingresso->setQuantidadeTotal((int) $linha['quantidade_total']);
        $ingresso->setQuantidadeDisponivel((int) $linha['quantidade_disponivel']);
        $ingresso->setIdEvento((int) $linha['id_evento']);
        return $ingresso;
    }
}
