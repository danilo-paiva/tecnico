<?php

namespace Api\Services;

use Api\Models\Participante;
use Api\DAO\ParticipanteDAO;
use Api\DAO\CompraDAO;
use Api\Http\ErrorResponse;
use stdClass;
use Throwable;

// Regras do participante: email e cpf unicos;
// nao excluir quem tem compra vinculada.
class ParticipanteService
{
    private ParticipanteDAO $participanteDAO;
    private CompraDAO $compraDAO;

    public function __construct(ParticipanteDAO $participanteDAO, CompraDAO $compraDAO)
    {
        $this->participanteDAO = $participanteDAO;
        $this->compraDAO = $compraDAO;
    }

    public function createService(stdClass $body): Participante
    {
        $participante = $this->buildParticipante($body->participante);
        $this->checkEmailUnico($participante->getEmail());
        $this->checkCpfUnico($participante->getCpf());
        return $this->participanteDAO->create($participante);
    }

    public function findAllService(): array
    {
        return $this->participanteDAO->findAll();
    }

    public function findByIdService(int $id): Participante
    {
        $participante = $this->participanteDAO->findById($id);
        if (!$participante) {
            throw new ErrorResponse(404, "Participante nao encontrado", [
                "message" => "Nao existe participante com id {$id}"
            ]);
        }
        return $participante;
    }

    public function updateService(int $id, stdClass $body): Participante
    {
        $this->findByIdService($id);
        $participante = $this->buildParticipante($body->participante);
        $participante->setIdParticipante($id);

        $this->checkEmailUnico($participante->getEmail(), $id);
        $this->checkCpfUnico($participante->getCpf(), $id);

        $this->participanteDAO->update($participante);
        return $this->findByIdService($id);
    }

    public function deleteService(int $id): void
    {
        $this->findByIdService($id);

        if ($this->compraDAO->countByParticipante($id) > 0) {
            throw new ErrorResponse(400, "Participante possui compras vinculadas", [
                "message" => "Exclua as compras deste participante antes"
            ]);
        }

        $this->participanteDAO->delete($id);
    }

    public function countService(): int
    {
        return $this->participanteDAO->count();
    }

    private function buildParticipante($dados): Participante
    {
        try {
            $participante = new Participante();
            $participante->setNome((string) $dados->nome);
            $participante->setEmail((string) $dados->email);
            $participante->setCpf((string) $dados->cpf);
            $participante->setTelefone(isset($dados->telefone) ? (string) $dados->telefone : null);
            $participante->setSenha((string) $dados->senha);
            return $participante;
        } catch (Throwable $e) {
            throw new ErrorResponse(400, "Dados invalidos", ["message" => $e->getMessage()]);
        }
    }

    private function checkEmailUnico(string $email, int $ignorarId = 0): void
    {
        $outro = $this->participanteDAO->findByEmail($email);
        if ($outro && $outro->getIdParticipante() !== $ignorarId) {
            throw new ErrorResponse(400, "Email ja cadastrado", [
                "message" => "O email '{$email}' ja esta em uso"
            ]);
        }
    }

    private function checkCpfUnico(string $cpf, int $ignorarId = 0): void
    {
        $outro = $this->participanteDAO->findByCpf($cpf);
        if ($outro && $outro->getIdParticipante() !== $ignorarId) {
            throw new ErrorResponse(400, "CPF ja cadastrado", [
                "message" => "O cpf '{$cpf}' ja esta em uso"
            ]);
        }
    }
}
