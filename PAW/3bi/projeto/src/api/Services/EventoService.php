<?php

declare(strict_types=1);

namespace Api\Services;

use Api\Dao\EventoDAO;
use Api\Dao\LocalDAO;
use Api\Http\ErrorResponse;
use Api\Models\Evento;
use InvalidArgumentException;

class EventoService
{
    private EventoDAO $eventoDAO;
    private LocalDAO $localDAO;

    public function __construct(EventoDAO $eventoDAO, LocalDAO $localDAO)
    {
        $this->eventoDAO = $eventoDAO;
        $this->localDAO = $localDAO;
    }

    /**
     * @return Evento[]
     */
    public function findAll(): array
    {
        return $this->eventoDAO->getAll();
    }

    public function findById(int $id): Evento
    {
        if ($id <= 0) {
            throw new ErrorResponse('ID do evento inválido.', 400);
        }
        $evento = $this->eventoDAO->getById($id);
        if ($evento === null) {
            throw new ErrorResponse('Evento não encontrado.', 404);
        }
        return $evento;
    }

    public function create(array $data): Evento
    {
        try {
            $evento = Evento::fromArray($data);
        } catch (InvalidArgumentException $e) {
            throw new ErrorResponse($e->getMessage(), 400);
        }

        $this->assertLocalExiste($evento->getIdLocal());
        $this->assertTituloUnicoNaData($evento->getTitulo(), $evento->getDataEvento());

        $id = $this->eventoDAO->create($evento);
        $criado = $this->eventoDAO->getById($id);
        if ($criado === null) {
            throw new ErrorResponse('Falha ao criar evento.', 400);
        }
        return $criado;
    }

    public function update(int $id, array $data): Evento
    {
        $existente = $this->findById($id);

        $merged = array_merge($existente->toArray(), $data);
        $merged['idEvento'] = $id;

        try {
            $evento = Evento::fromArray($merged);
        } catch (InvalidArgumentException $e) {
            throw new ErrorResponse($e->getMessage(), 400);
        }

        $this->assertLocalExiste($evento->getIdLocal());
        $this->assertTituloUnicoNaData($evento->getTitulo(), $evento->getDataEvento(), $id);

        $this->eventoDAO->update($evento);
        return $this->findById($id);
    }

    public function delete(int $id): void
    {
        $this->findById($id);
        $ok = $this->eventoDAO->delete($id);
        if (!$ok) {
            throw new ErrorResponse('Falha ao deletar evento.', 400);
        }
    }

    public function count(): int
    {
        return $this->eventoDAO->count();
    }

    /**
     * @return Evento[]
     */
    public function getByLocal(int $idLocal): array
    {
        if ($idLocal <= 0) {
            throw new ErrorResponse('ID do local inválido.', 400);
        }
        $local = $this->localDAO->getById($idLocal);
        if ($local === null) {
            throw new ErrorResponse('Local não encontrado.', 404);
        }
        return $this->eventoDAO->getByLocal($idLocal);
    }

    private function assertLocalExiste(int $idLocal): void
    {
        if ($idLocal <= 0) {
            throw new ErrorResponse('ID do local é obrigatório.', 400);
        }
        $local = $this->localDAO->getById($idLocal);
        if ($local === null) {
            throw new ErrorResponse('Local informado não existe.', 404);
        }
    }

    /**
     * Valida titulo duplicado na mesma data (comparacao case-insensitive exata + mesma data DATE).
     */
    private function assertTituloUnicoNaData(string $titulo, string $dataEvento, ?int $ignoreId = null): void
    {
        $candidatos = $this->eventoDAO->findByTituloData($titulo, $dataEvento);
        $normTitulo = mb_strtolower(trim($titulo));
        $normData = $this->normalizeDate($dataEvento);

        foreach ($candidatos as $evento) {
            if ($ignoreId !== null && $evento->getIdEvento() === $ignoreId) {
                continue;
            }
            $tituloIgual = mb_strtolower(trim($evento->getTitulo())) === $normTitulo;
            $dataIgual = $this->normalizeDate($evento->getDataEvento()) === $normData;
            if ($tituloIgual && $dataIgual) {
                throw new ErrorResponse('Já existe um evento com esse título na mesma data.', 409);
            }
        }
    }

    private function normalizeDate(string $data): string
    {
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $data);
        if ($dt && $dt->format('Y-m-d H:i:s') === $data) {
            return $dt->format('Y-m-d');
        }
        $dt = \DateTime::createFromFormat('Y-m-d', $data);
        if ($dt && $dt->format('Y-m-d') === $data) {
            return $dt->format('Y-m-d');
        }
        // fallback: tenta parse generico e pega Y-m-d
        try {
            $generic = new \DateTime($data);
            return $generic->format('Y-m-d');
        } catch (\Exception) {
            return trim($data);
        }
    }
}
