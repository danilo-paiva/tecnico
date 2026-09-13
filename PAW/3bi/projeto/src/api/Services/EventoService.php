<?php

namespace Api\Services;

use Api\Models\Evento;
use Api\DAO\EventoDAO;
use Api\DAO\LocalDAO;
use Api\DAO\IngressoDAO;
use Api\Http\ErrorResponse;
use stdClass;
use Throwable;

// Regras do evento: local precisa existir; titulo unico por data;
// nao excluir quem tem ingresso vinculado.
class EventoService
{
    private EventoDAO $eventoDAO;
    private LocalDAO $localDAO;
    private IngressoDAO $ingressoDAO;

    public function __construct(EventoDAO $eventoDAO, LocalDAO $localDAO, IngressoDAO $ingressoDAO)
    {
        $this->eventoDAO = $eventoDAO;
        $this->localDAO = $localDAO;
        $this->ingressoDAO = $ingressoDAO;
    }

    public function createService(stdClass $body): Evento
    {
        $evento = $this->buildEvento($body->evento);
        $this->checkLocalExiste($evento->getIdLocal());
        $this->checkTituloUnico($evento->getTitulo(), $evento->getDataEvento());
        return $this->eventoDAO->create($evento);
    }

    public function findAllService(): array
    {
        return $this->eventoDAO->findAll();
    }

    public function findByIdService(int $id): Evento
    {
        $evento = $this->eventoDAO->findById($id);
        if (!$evento) {
            throw new ErrorResponse(404, "Evento nao encontrado", [
                "message" => "Nao existe evento com id {$id}"
            ]);
        }
        return $evento;
    }

    public function updateService(int $id, stdClass $body): Evento
    {
        $this->findByIdService($id);
        $evento = $this->buildEvento($body->evento);
        $evento->setIdEvento($id);

        $this->checkLocalExiste($evento->getIdLocal());
        $this->checkTituloUnico($evento->getTitulo(), $evento->getDataEvento(), $id);

        $this->eventoDAO->update($evento);
        return $this->findByIdService($id);
    }

    public function deleteService(int $id): void
    {
        $this->findByIdService($id);

        if ($this->ingressoDAO->countByEvento($id) > 0) {
            throw new ErrorResponse(400, "Evento possui ingressos vinculados", [
                "message" => "Exclua os ingressos deste evento antes"
            ]);
        }

        $this->eventoDAO->delete($id);
    }

    public function countService(): int
    {
        return $this->eventoDAO->count();
    }

    private function buildEvento($dados): Evento
    {
        try {
            $evento = new Evento();
            $evento->setTitulo((string) $dados->titulo);
            $evento->setDescricao(isset($dados->descricao) ? (string) $dados->descricao : null);
            $evento->setDataEvento((string) $dados->data_evento);
            $evento->setStatus(isset($dados->status) ? (string) $dados->status : 'planejado');
            $evento->setIdLocal((int) $dados->id_local);
            return $evento;
        } catch (Throwable $e) {
            throw new ErrorResponse(400, "Dados invalidos", ["message" => $e->getMessage()]);
        }
    }

    private function checkLocalExiste(int $idLocal): void
    {
        if (!$this->localDAO->findById($idLocal)) {
            throw new ErrorResponse(404, "Local nao encontrado", [
                "message" => "Nao existe local com id {$idLocal}"
            ]);
        }
    }

    private function checkTituloUnico(string $titulo, string $data, int $ignorarId = 0): void
    {
        if ($this->eventoDAO->existsTituloData($titulo, $data, $ignorarId)) {
            throw new ErrorResponse(400, "Evento ja existe", [
                "message" => "Ja existe '{$titulo}' nesta data"
            ]);
        }
    }
}
