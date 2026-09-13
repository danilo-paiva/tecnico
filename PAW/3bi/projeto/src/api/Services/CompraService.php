<?php

namespace Api\Services;

use Api\Models\Compra;
use Api\DAO\CompraDAO;
use Api\DAO\ParticipanteDAO;
use Api\DAO\IngressoDAO;
use Api\Http\ErrorResponse;
use stdClass;
use Throwable;

// Regras da compra: participante e ingresso precisam existir;
// quantidade positiva e com estoque; valor calculado pela API.
// A troca de participante/ingresso numa compra pronta nao e permitida.
class CompraService
{
    private CompraDAO $compraDAO;
    private ParticipanteDAO $participanteDAO;
    private IngressoDAO $ingressoDAO;

    public function __construct(CompraDAO $compraDAO, ParticipanteDAO $participanteDAO, IngressoDAO $ingressoDAO)
    {
        $this->compraDAO = $compraDAO;
        $this->participanteDAO = $participanteDAO;
        $this->ingressoDAO = $ingressoDAO;
    }

    public function createService(stdClass $body): Compra
    {
        $compra = $this->buildCompra($body->compra);
        $this->checkParticipanteExiste($compra->getIdParticipante());
        $this->checkIngressoExiste($compra->getIdIngresso());

        try {
            return $this->compraDAO->create($compra);
        } catch (Throwable $e) {
            throw new ErrorResponse(400, "Compra nao realizada", ["message" => $e->getMessage()]);
        }
    }

    public function findAllService(): array
    {
        return $this->compraDAO->findAll();
    }

    public function findByIdService(int $id): Compra
    {
        $compra = $this->compraDAO->findById($id);
        if (!$compra) {
            throw new ErrorResponse(404, "Compra nao encontrada", [
                "message" => "Nao existe compra com id {$id}"
            ]);
        }
        return $compra;
    }

    public function updateService(int $id, stdClass $body): Compra
    {
        $atual = $this->findByIdService($id);
        $compra = $this->buildCompra($body->compra);
        $compra->setIdCompra($id);

        if ($compra->getIdParticipante() !== $atual->getIdParticipante()
            || $compra->getIdIngresso() !== $atual->getIdIngresso()
        ) {
            throw new ErrorResponse(400, "Compra nao pode ser transferida", [
                "message" => "Cancele esta compra e faca uma nova"
            ]);
        }

        try {
            return $this->compraDAO->updateQuantidade($compra);
        } catch (Throwable $e) {
            throw new ErrorResponse(400, "Compra nao atualizada", ["message" => $e->getMessage()]);
        }
    }

    public function deleteService(int $id): void
    {
        $this->findByIdService($id);

        try {
            $this->compraDAO->delete($id);
        } catch (Throwable $e) {
            throw new ErrorResponse(400, "Compra nao excluida", ["message" => $e->getMessage()]);
        }
    }

    public function countService(): int
    {
        return $this->compraDAO->count();
    }

    private function buildCompra($dados): Compra
    {
        try {
            $compra = new Compra();
            $compra->setIdParticipante((int) $dados->id_participante);
            $compra->setIdIngresso((int) $dados->id_ingresso);
            $compra->setQuantidade((int) $dados->quantidade);
            return $compra;
        } catch (Throwable $e) {
            throw new ErrorResponse(400, "Dados invalidos", ["message" => $e->getMessage()]);
        }
    }

    private function checkParticipanteExiste(int $id): void
    {
        if (!$this->participanteDAO->findById($id)) {
            throw new ErrorResponse(404, "Participante nao encontrado", [
                "message" => "Nao existe participante com id {$id}"
            ]);
        }
    }

    private function checkIngressoExiste(int $id): void
    {
        if (!$this->ingressoDAO->findById($id)) {
            throw new ErrorResponse(404, "Ingresso nao encontrado", [
                "message" => "Nao existe ingresso com id {$id}"
            ]);
        }
    }
}
