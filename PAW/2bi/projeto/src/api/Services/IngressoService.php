<?php

namespace Api\Services;

use Api\Models\Ingresso;
use Api\DAO\IngressoDAO;
use Api\DAO\EventoDAO;
use Api\DAO\CompraDAO;
use Api\Http\ErrorResponse;
use stdClass;
use Throwable;

// Regras do ingresso: evento precisa existir; tipo unico por evento;
// o disponivel acompanha o total e nunca fica negativo.
class IngressoService
{
    private IngressoDAO $ingressoDAO;
    private EventoDAO $eventoDAO;
    private CompraDAO $compraDAO;

    public function __construct(IngressoDAO $ingressoDAO, EventoDAO $eventoDAO, CompraDAO $compraDAO)
    {
        $this->ingressoDAO = $ingressoDAO;
        $this->eventoDAO = $eventoDAO;
        $this->compraDAO = $compraDAO;
    }

    public function createService(stdClass $body): Ingresso
    {
        $ingresso = $this->buildIngresso($body->ingresso);
        // Ingresso novo comeca com o estoque cheio
        $ingresso->setQuantidadeDisponivel($ingresso->getQuantidadeTotal());

        $this->checkEventoExiste($ingresso->getIdEvento());
        $this->checkTipoUnico($ingresso->getTipo(), $ingresso->getIdEvento());

        return $this->ingressoDAO->create($ingresso);
    }

    public function findAllService(): array
    {
        return $this->ingressoDAO->findAll();
    }

    public function findByIdService(int $id): Ingresso
    {
        $ingresso = $this->ingressoDAO->findById($id);
        if (!$ingresso) {
            throw new ErrorResponse(404, "Ingresso nao encontrado", [
                "message" => "Nao existe ingresso com id {$id}"
            ]);
        }
        return $ingresso;
    }

    public function updateService(int $id, stdClass $body): Ingresso
    {
        $atual = $this->findByIdService($id);
        $ingresso = $this->buildIngresso($body->ingresso);
        $ingresso->setIdIngresso($id);

        $this->checkEventoExiste($ingresso->getIdEvento());
        $this->checkTipoUnico($ingresso->getTipo(), $ingresso->getIdEvento(), $id);

        // Ajusta o disponivel pela diferenca do total (vendidos nao mudam)
        $vendidos = $atual->getQuantidadeTotal() - $atual->getQuantidadeDisponivel();
        if ($ingresso->getQuantidadeTotal() < $vendidos) {
            throw new ErrorResponse(400, "Total abaixo do vendido", [
                "message" => "Ja foram vendidos {$vendidos} ingressos deste tipo"
            ]);
        }
        $ingresso->setQuantidadeDisponivel($ingresso->getQuantidadeTotal() - $vendidos);

        $this->ingressoDAO->update($ingresso);
        return $this->findByIdService($id);
    }

    public function deleteService(int $id): void
    {
        $this->findByIdService($id);

        if ($this->compraDAO->countByIngresso($id) > 0) {
            throw new ErrorResponse(400, "Ingresso possui compras vinculadas", [
                "message" => "Exclua as compras deste ingresso antes"
            ]);
        }

        $this->ingressoDAO->delete($id);
    }

    public function countService(): int
    {
        return $this->ingressoDAO->count();
    }

    private function buildIngresso($dados): Ingresso
    {
        try {
            $ingresso = new Ingresso();
            $ingresso->setTipo((string) $dados->tipo);
            $ingresso->setPreco((float) $dados->preco);
            $ingresso->setQuantidadeTotal((int) $dados->quantidade_total);
            $ingresso->setIdEvento((int) $dados->id_evento);
            return $ingresso;
        } catch (Throwable $e) {
            throw new ErrorResponse(400, "Dados invalidos", ["message" => $e->getMessage()]);
        }
    }

    private function checkEventoExiste(int $idEvento): void
    {
        if (!$this->eventoDAO->findById($idEvento)) {
            throw new ErrorResponse(404, "Evento nao encontrado", [
                "message" => "Nao existe evento com id {$idEvento}"
            ]);
        }
    }

    private function checkTipoUnico(string $tipo, int $idEvento, int $ignorarId = 0): void
    {
        if ($this->ingressoDAO->existsTipoEvento($tipo, $idEvento, $ignorarId)) {
            throw new ErrorResponse(400, "Ingresso ja existe", [
                "message" => "O tipo '{$tipo}' ja existe neste evento"
            ]);
        }
    }
}
