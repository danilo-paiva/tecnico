<?php

declare(strict_types=1);

namespace Api\Services;

use Api\Dao\LocalDAO;
use Api\Http\ErrorResponse;
use Api\Models\Local;
use InvalidArgumentException;

class LocalService
{
    private LocalDAO $localDAO;

    public function __construct(LocalDAO $localDAO)
    {
        $this->localDAO = $localDAO;
    }

    /**
     * @return Local[]
     */
    public function findAll(): array
    {
        return $this->localDAO->getAll();
    }

    public function findById(int $id): Local
    {
        if ($id <= 0) {
            throw new ErrorResponse('ID do local inválido.', 400);
        }
        $local = $this->localDAO->getById($id);
        if ($local === null) {
            throw new ErrorResponse('Local não encontrado.', 404);
        }
        return $local;
    }

    public function create(array $data): Local
    {
        try {
            $local = Local::fromArray($data);
        } catch (InvalidArgumentException $e) {
            throw new ErrorResponse($e->getMessage(), 400);
        }

        $this->assertNomeUnico($local->getNome());

        $id = $this->localDAO->create($local);
        $criado = $this->localDAO->getById($id);
        if ($criado === null) {
            throw new ErrorResponse('Falha ao criar local.', 400);
        }
        return $criado;
    }

    public function update(int $id, array $data): Local
    {
        $existente = $this->findById($id);

        $merged = array_merge($existente->toArray(), $data);
        $merged['idLocal'] = $id;

        try {
            $local = Local::fromArray($merged);
        } catch (InvalidArgumentException $e) {
            throw new ErrorResponse($e->getMessage(), 400);
        }

        $this->assertNomeUnico($local->getNome(), $id);

        $this->localDAO->update($local);
        return $this->findById($id);
    }

    public function delete(int $id): void
    {
        $this->findById($id);
        $ok = $this->localDAO->delete($id);
        if (!$ok) {
            throw new ErrorResponse('Falha ao deletar local.', 400);
        }
    }

    public function count(): int
    {
        return $this->localDAO->count();
    }

    /**
     * Busca locais por nome (wrapper do DAO com validacao).
     * @return Local[]
     */
    public function getByNome(string $nome): array
    {
        $nome = trim($nome);
        if ($nome === '') {
            throw new ErrorResponse('Nome para busca é obrigatório.', 400);
        }
        return $this->localDAO->findByNome($nome);
    }

    /**
     * Valida nome duplicado via findByNome (comparacao case-insensitive exata).
     */
    private function assertNomeUnico(string $nome, ?int $ignoreId = null): void
    {
        $resultados = $this->localDAO->findByNome($nome);
        $normalizado = mb_strtolower(trim($nome));
        foreach ($resultados as $local) {
            if (mb_strtolower(trim($local->getNome())) === $normalizado) {
                if ($ignoreId !== null && $local->getIdLocal() === $ignoreId) {
                    continue;
                }
                throw new ErrorResponse('Já existe um local com esse nome.', 409);
            }
        }
    }
}
