<?php

declare(strict_types=1);

namespace Api\Services;

use Api\Dao\EventoDAO;
use Api\Dao\IngressoDAO;
use Api\Http\ErrorResponse;
use Api\Models\Ingresso;
use InvalidArgumentException;

class IngressoService
{
    private IngressoDAO $ingressoDAO;
    private EventoDAO $eventoDAO;

    public function __construct(IngressoDAO $ingressoDAO, EventoDAO $eventoDAO)
    {
        $this->ingressoDAO = $ingressoDAO;
        $this->eventoDAO = $eventoDAO;
    }

    /**
     * @return Ingresso[]
     */
    public function findAll(): array
    {
        return $this->ingressoDAO->getAll();
    }

    public function findById(int $id): Ingresso
    {
        if ($id <= 0) {
            throw new ErrorResponse('ID do ingresso inválido.', 400);
        }
        $ingresso = $this->ingressoDAO->getById($id);
        if ($ingresso === null) {
            throw new ErrorResponse('Ingresso não encontrado.', 404);
        }
        return $ingresso;
    }

    public function create(array $data): Ingresso
    {
        // Se quantidadeDisponivel nao informada, assume total (comportamento comum)
        if (!isset($data['quantidadeDisponivel']) && isset($data['quantidadeTotal'])) {
            $data['quantidadeDisponivel'] = $data['quantidadeTotal'];
        }

        try {
            $ingresso = Ingresso::fromArray($data);
        } catch (InvalidArgumentException $e) {
            throw new ErrorResponse($e->getMessage(), 400);
        }

        $this->assertEventoExiste($ingresso->getIdEvento());
        $this->assertTipoUnicoPorEvento($ingresso->getTipo(), $ingresso->getIdEvento());
        $this->assertDisponivelMenorIgualTotal($ingresso->getQuantidadeDisponivel(), $ingresso->getQuantidadeTotal());

        $id = $this->ingressoDAO->create($ingresso);
        $criado = $this->ingressoDAO->getById($id);
        if ($criado === null) {
            throw new ErrorResponse('Falha ao criar ingresso.', 400);
        }
        return $criado;
    }

    public function update(int $id, array $data): Ingresso
    {
        $existente = $this->findById($id);

        $merged = array_merge($existente->toArray(), $data);
        $merged['idIngresso'] = $id;

        try {
            $ingresso = Ingresso::fromArray($merged);
        } catch (InvalidArgumentException $e) {
            throw new ErrorResponse($e->getMessage(), 400);
        }

        $this->assertEventoExiste($ingresso->getIdEvento());
        $this->assertTipoUnicoPorEvento($ingresso->getTipo(), $ingresso->getIdEvento(), $id);
        $this->assertDisponivelMenorIgualTotal($ingresso->getQuantidadeDisponivel(), $ingresso->getQuantidadeTotal());

        $this->ingressoDAO->update($ingresso);
        return $this->findById($id);
    }

    public function delete(int $id): void
    {
        $this->findById($id);
        $ok = $this->ingressoDAO->delete($id);
        if (!$ok) {
            throw new ErrorResponse('Falha ao deletar ingresso.', 400);
        }
    }

    public function count(): int
    {
        return $this->ingressoDAO->count();
    }

    /**
     * @return Ingresso[]
     */
    public function getByEvento(int $idEvento): array
    {
        if ($idEvento <= 0) {
            throw new ErrorResponse('ID do evento inválido.', 400);
        }
        $this->assertEventoExiste($idEvento);
        return $this->ingressoDAO->getByEvento($idEvento);
    }

    public function getByTipoEvento(string $tipo, int $idEvento): Ingresso
    {
        $tipo = trim($tipo);
        if ($tipo === '') {
            throw new ErrorResponse('Tipo do ingresso é obrigatório.', 400);
        }
        if ($idEvento <= 0) {
            throw new ErrorResponse('ID do evento inválido.', 400);
        }
        $ingresso = $this->ingressoDAO->findByTipoEvento($tipo, $idEvento);
        if ($ingresso === null) {
            throw new ErrorResponse('Ingresso não encontrado para esse tipo e evento.', 404);
        }
        return $ingresso;
    }

    private function assertEventoExiste(int $idEvento): void
    {
        if ($idEvento <= 0) {
            throw new ErrorResponse('ID do evento é obrigatório.', 400);
        }
        $evento = $this->eventoDAO->getById($idEvento);
        if ($evento === null) {
            throw new ErrorResponse('Evento informado não existe.', 404);
        }
    }

    private function assertTipoUnicoPorEvento(string $tipo, int $idEvento, ?int $ignoreId = null): void
    {
        $existente = $this->ingressoDAO->findByTipoEvento($tipo, $idEvento);
        if ($existente !== null) {
            if ($ignoreId !== null && $existente->getIdIngresso() === $ignoreId) {
                return;
            }
            throw new ErrorResponse('Já existe ingresso desse tipo para o evento informado.', 409);
        }
    }

    private function assertDisponivelMenorIgualTotal(int $disponivel, int $total): void
    {
        if ($disponivel > $total) {
            throw new ErrorResponse('Quantidade disponível não pode ser maior que a quantidade total.', 400);
        }
    }
}
