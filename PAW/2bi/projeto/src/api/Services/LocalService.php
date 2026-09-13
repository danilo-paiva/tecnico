<?php

namespace Api\Services;

use Api\Models\Local;
use Api\DAO\LocalDAO;
use Api\DAO\EventoDAO;
use Api\Http\ErrorResponse;
use stdClass;
use Throwable;

// Regras do local: nome unico; nao excluir quem tem evento vinculado.
class LocalService
{
    private LocalDAO $localDAO;
    private EventoDAO $eventoDAO;

    public function __construct(LocalDAO $localDAO, EventoDAO $eventoDAO)
    {
        $this->localDAO = $localDAO;
        $this->eventoDAO = $eventoDAO;
    }

    public function createService(stdClass $body): Local
    {
        $local = $this->buildLocal($body->local);

        if ($this->localDAO->findByNome($local->getNome())) {
            throw new ErrorResponse(400, "Local ja existe", [
                "message" => "O nome '{$local->getNome()}' ja esta cadastrado"
            ]);
        }

        return $this->localDAO->create($local);
    }

    public function findAllService(): array
    {
        return $this->localDAO->findAll();
    }

    public function findByIdService(int $id): Local
    {
        $local = $this->localDAO->findById($id);
        if (!$local) {
            throw new ErrorResponse(404, "Local nao encontrado", [
                "message" => "Nao existe local com id {$id}"
            ]);
        }
        return $local;
    }

    public function updateService(int $id, stdClass $body): Local
    {
        $this->findByIdService($id);
        $local = $this->buildLocal($body->local);
        $local->setIdLocal($id);

        $outro = $this->localDAO->findByNome($local->getNome());
        if ($outro && $outro->getIdLocal() !== $id) {
            throw new ErrorResponse(400, "Local ja existe", [
                "message" => "O nome '{$local->getNome()}' ja esta cadastrado"
            ]);
        }

        $this->localDAO->update($local);
        return $this->findByIdService($id);
    }

    public function deleteService(int $id): void
    {
        $this->findByIdService($id);

        if ($this->eventoDAO->countByLocal($id) > 0) {
            throw new ErrorResponse(400, "Local possui eventos vinculados", [
                "message" => "Exclua ou transfira os eventos deste local antes"
            ]);
        }

        $this->localDAO->delete($id);
    }

    public function countService(): int
    {
        return $this->localDAO->count();
    }

    private function buildLocal($dados): Local
    {
        try {
            $local = new Local();
            $local->setNome((string) $dados->nome);
            $local->setEndereco((string) $dados->endereco);
            $local->setCapacidade((int) $dados->capacidade);
            return $local;
        } catch (Throwable $e) {
            throw new ErrorResponse(400, "Dados invalidos", ["message" => $e->getMessage()]);
        }
    }
}
