<?php

namespace Api\DAO;

use Api\Models\Compra;
use Api\Database\MysqlDatabase;
use Exception;
use PDO;
use Throwable;

// SQL da tabela compras. Criar, alterar e excluir mexem no estoque
// do ingresso dentro de uma transacao (tudo ou nada).
class CompraDAO
{
    private MysqlDatabase $database;

    public function __construct(MysqlDatabase $databaseInstance)
    {
        $this->database = $databaseInstance;
    }

    public function create(Compra $compra): Compra
    {
        $pdo = $this->database->getConnection();
        $pdo->beginTransaction();
        try {
            $ingresso = $this->lockIngresso($pdo, $compra->getIdIngresso());

            if ((int) $ingresso['quantidade_disponivel'] < $compra->getQuantidade()) {
                throw new Exception("Estoque insuficiente. Disponivel: {$ingresso['quantidade_disponivel']}.");
            }

            $valorTotal = (float) $ingresso['preco'] * $compra->getQuantidade();

            $stmt = $pdo->prepare(
                "UPDATE ingressos SET quantidade_disponivel = quantidade_disponivel - :qtd WHERE id_ingresso = :id"
            );
            $stmt->execute([':qtd' => $compra->getQuantidade(), ':id' => $compra->getIdIngresso()]);

            $stmt = $pdo->prepare(
                "INSERT INTO compras (quantidade, valor_total, id_participante, id_ingresso)
                 VALUES (:qtd, :valor, :participante, :ingresso)"
            );
            $stmt->execute([
                ':qtd' => $compra->getQuantidade(),
                ':valor' => $valorTotal,
                ':participante' => $compra->getIdParticipante(),
                ':ingresso' => $compra->getIdIngresso()
            ]);

            $idCompra = (int) $pdo->lastInsertId();
            $pdo->commit();
            return $this->findById($idCompra);
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // Troca apenas a quantidade: devolve ou reserva a diferenca no estoque
    public function updateQuantidade(Compra $compra): Compra
    {
        $pdo = $this->database->getConnection();
        $pdo->beginTransaction();
        try {
            $atual = $this->lockCompra($pdo, $compra->getIdCompra());
            $ingresso = $this->lockIngresso($pdo, (int) $atual['id_ingresso']);

            $diferenca = $compra->getQuantidade() - (int) $atual['quantidade'];
            if ($diferenca > 0 && (int) $ingresso['quantidade_disponivel'] < $diferenca) {
                throw new Exception("Estoque insuficiente. Disponivel: {$ingresso['quantidade_disponivel']}.");
            }

            $valorTotal = (float) $ingresso['preco'] * $compra->getQuantidade();

            $stmt = $pdo->prepare(
                "UPDATE ingressos SET quantidade_disponivel = quantidade_disponivel - :diff WHERE id_ingresso = :id"
            );
            $stmt->execute([':diff' => $diferenca, ':id' => $atual['id_ingresso']]);

            $stmt = $pdo->prepare(
                "UPDATE compras SET quantidade = :qtd, valor_total = :valor WHERE id_compra = :id"
            );
            $stmt->execute([
                ':qtd' => $compra->getQuantidade(),
                ':valor' => $valorTotal,
                ':id' => $compra->getIdCompra()
            ]);

            $pdo->commit();
            return $this->findById($compra->getIdCompra());
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // Exclui a compra e devolve a quantidade ao estoque
    public function delete(int $id): bool
    {
        $pdo = $this->database->getConnection();
        $pdo->beginTransaction();
        try {
            $atual = $this->lockCompra($pdo, $id);

            $stmt = $pdo->prepare(
                "UPDATE ingressos SET quantidade_disponivel = quantidade_disponivel + :qtd WHERE id_ingresso = :id"
            );
            $stmt->execute([':qtd' => $atual['quantidade'], ':id' => $atual['id_ingresso']]);

            $stmt = $pdo->prepare("DELETE FROM compras WHERE id_compra = :id");
            $stmt->execute([':id' => $id]);

            $pdo->commit();
            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function findAll(): array
    {
        $stmt = $this->database->getConnection()->query("SELECT * FROM compras ORDER BY id_compra");
        $compras = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $compras[] = $this->mapRow($linha);
        }
        return $compras;
    }

    public function findById(int $id): ?Compra
    {
        $stmt = $this->database->getConnection()->prepare("SELECT * FROM compras WHERE id_compra = :id");
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);
        return $linha ? $this->mapRow($linha) : null;
    }

    public function countByParticipante(int $idParticipante): int
    {
        $stmt = $this->database->getConnection()->prepare(
            "SELECT COUNT(*) AS qtd FROM compras WHERE id_participante = :id"
        );
        $stmt->execute([':id' => $idParticipante]);
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['qtd'];
    }

    public function countByIngresso(int $idIngresso): int
    {
        $stmt = $this->database->getConnection()->prepare(
            "SELECT COUNT(*) AS qtd FROM compras WHERE id_ingresso = :id"
        );
        $stmt->execute([':id' => $idIngresso]);
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['qtd'];
    }

    public function count(): int
    {
        $linha = $this->database->getConnection()->query("SELECT COUNT(*) AS qtd FROM compras")->fetch(PDO::FETCH_ASSOC);
        return (int) $linha['qtd'];
    }

    // Trava a linha do ingresso para ninguem vender o mesmo estoque junto
    private function lockIngresso(PDO $pdo, int $id): array
    {
        $stmt = $pdo->prepare("SELECT preco, quantidade_disponivel FROM ingressos WHERE id_ingresso = :id FOR UPDATE");
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$linha) {
            throw new Exception("Ingresso nao encontrado.");
        }
        return $linha;
    }

    // Trava a linha da compra antes de alterar ou excluir
    private function lockCompra(PDO $pdo, int $id): array
    {
        $stmt = $pdo->prepare("SELECT * FROM compras WHERE id_compra = :id FOR UPDATE");
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$linha) {
            throw new Exception("Compra nao encontrada.");
        }
        return $linha;
    }

    private function mapRow(array $linha): Compra
    {
        $compra = new Compra();
        $compra->setIdCompra((int) $linha['id_compra']);
        $compra->setDataCompra($linha['data_compra']);
        $compra->setQuantidade((int) $linha['quantidade']);
        $compra->setValorTotal((float) $linha['valor_total']);
        $compra->setIdParticipante((int) $linha['id_participante']);
        $compra->setIdIngresso((int) $linha['id_ingresso']);
        return $compra;
    }
}
