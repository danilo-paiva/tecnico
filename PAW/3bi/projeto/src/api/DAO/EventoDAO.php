<?php

namespace Api\DAO;

use Api\Models\Evento;
use Api\Database\MysqlDatabase;
use PDO;

// SQL da tabela eventos. Regras de negocio ficam no Service.
class EventoDAO
{
    private MysqlDatabase $database;

    public function __construct(MysqlDatabase $databaseInstance)
    {
        $this->database = $databaseInstance;
    }

    public function create(Evento $evento): Evento
    {
        $sql = "INSERT INTO eventos (titulo, descricao, data_evento, status, id_local)
                VALUES (:titulo, :descricao, :data_evento, :status, :id_local)";
        $stmt = $this->database->getConnection()->prepare($sql);
        $stmt->execute([
            ':titulo' => $evento->getTitulo(),
            ':descricao' => $evento->getDescricao(),
            ':data_evento' => $evento->getDataEvento(),
            ':status' => $evento->getStatus(),
            ':id_local' => $evento->getIdLocal()
        ]);
        $evento->setIdEvento((int) $this->database->getConnection()->lastInsertId());
        return $evento;
    }

    public function findAll(): array
    {
        $stmt = $this->database->getConnection()->query("SELECT * FROM eventos ORDER BY id_evento");
        $eventos = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $eventos[] = $this->mapRow($linha);
        }
        return $eventos;
    }

    public function findById(int $id): ?Evento
    {
        $stmt = $this->database->getConnection()->prepare("SELECT * FROM eventos WHERE id_evento = :id");
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);
        return $linha ? $this->mapRow($linha) : null;
    }

    // Verifica se ja existe outro evento com o mesmo titulo na mesma data
    public function existsTituloData(string $titulo, string $dataEvento, int $ignorarId = 0): bool
    {
        $sql = "SELECT COUNT(*) AS qtd FROM eventos
                WHERE titulo = :titulo AND data_evento = :data AND id_evento != :id";
        $stmt = $this->database->getConnection()->prepare($sql);
        $stmt->execute([':titulo' => $titulo, ':data' => $dataEvento, ':id' => $ignorarId]);
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $linha['qtd'] > 0;
    }

    // Quantos eventos usam um local (para bloquear exclusao com vinculo)
    public function countByLocal(int $idLocal): int
    {
        $stmt = $this->database->getConnection()->prepare("SELECT COUNT(*) AS qtd FROM eventos WHERE id_local = :id");
        $stmt->execute([':id' => $idLocal]);
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $linha['qtd'];
    }

    public function update(Evento $evento): bool
    {
        $sql = "UPDATE eventos
                SET titulo = :titulo, descricao = :descricao, data_evento = :data_evento,
                    status = :status, id_local = :id_local
                WHERE id_evento = :id";
        $stmt = $this->database->getConnection()->prepare($sql);
        $stmt->execute([
            ':titulo' => $evento->getTitulo(),
            ':descricao' => $evento->getDescricao(),
            ':data_evento' => $evento->getDataEvento(),
            ':status' => $evento->getStatus(),
            ':id_local' => $evento->getIdLocal(),
            ':id' => $evento->getIdEvento()
        ]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->database->getConnection()->prepare("DELETE FROM eventos WHERE id_evento = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function count(): int
    {
        $linha = $this->database->getConnection()->query("SELECT COUNT(*) AS qtd FROM eventos")->fetch(PDO::FETCH_ASSOC);
        return (int) $linha['qtd'];
    }

    private function mapRow(array $linha): Evento
    {
        $evento = new Evento();
        $evento->setIdEvento((int) $linha['id_evento']);
        $evento->setTitulo($linha['titulo']);
        $evento->setDescricao($linha['descricao']);
        $evento->setDataEvento($linha['data_evento']);
        $evento->setStatus($linha['status']);
        $evento->setIdLocal((int) $linha['id_local']);
        return $evento;
    }
}
