<?php

declare(strict_types=1);

namespace Api\Services;

use Api\Dao\ParticipanteDAO;
use Api\Http\ErrorResponse;
use Api\Models\Participante;
use InvalidArgumentException;

class ParticipanteService
{
    private ParticipanteDAO $participanteDAO;

    public function __construct(ParticipanteDAO $participanteDAO)
    {
        $this->participanteDAO = $participanteDAO;
    }

    /**
     * @return Participante[]
     */
    public function findAll(): array
    {
        return $this->participanteDAO->getAll();
    }

    public function findById(int $id): Participante
    {
        if ($id <= 0) {
            throw new ErrorResponse('ID do participante inválido.', 400);
        }
        $p = $this->participanteDAO->getById($id);
        if ($p === null) {
            throw new ErrorResponse('Participante não encontrado.', 404);
        }
        return $p;
    }

    public function create(array $data): Participante
    {
        if (empty($data['senha'])) {
            throw new ErrorResponse('Senha é obrigatória.', 400);
        }

        // Validacao de unicidade antes de instanciar (para mensagens 409 claras)
        if (!empty($data['email'])) {
            $this->assertEmailUnico((string) $data['email']);
        }
        if (!empty($data['cpf'])) {
            $this->assertCpfUnico((string) $data['cpf']);
        }

        // Hasheia senha com password_hash se ainda não for hash
        $data['senha'] = $this->hashSenhaIfNeeded((string) $data['senha']);

        try {
            $participante = Participante::fromArray($data);
            // Garante que a senha armazenada seja o hash (fromArray usa setSenhaHash, então re-aplica hash se necessario)
            // Se fromArray usou setSenhaHash, precisamos garantir que nao salvou texto puro sem hash
            $info = password_get_info($participante->getSenha());
            if ($info['algo'] === 0 || $info['algo'] === null) {
                $participante->setSenha($participante->getSenha());
            }
        } catch (InvalidArgumentException $e) {
            throw new ErrorResponse($e->getMessage(), 400);
        }

        // Revalida após normalização (email lower, cpf apenas numeros)
        $this->assertEmailUnico($participante->getEmail());
        $this->assertCpfUnico($participante->getCpf());

        $id = $this->participanteDAO->create($participante);
        $criado = $this->participanteDAO->getById($id);
        if ($criado === null) {
            throw new ErrorResponse('Falha ao criar participante.', 400);
        }
        return $criado;
    }

    public function update(int $id, array $data): Participante
    {
        $existente = $this->findById($id);

        // Se senha foi enviada, hasheia
        if (isset($data['senha']) && $data['senha'] !== '') {
            $data['senha'] = $this->hashSenhaIfNeeded((string) $data['senha']);
        } else {
            // Mantem senha atual se nao enviada
            unset($data['senha']);
        }

        $merged = array_merge($existente->toArray(true), $data);
        // toArray(true) nao existe sem param? Participante::toArray(bool) - precisamos garantir hash
        // Se toArray sem senha, adiciona senha atual
        if (!isset($merged['senha'])) {
            $merged['senha'] = $existente->getSenha();
        }
        $merged['idParticipante'] = $id;

        try {
            $participante = Participante::fromArray($merged);
            // Se senha veio como texto puro hasheado já, fromArray usou setSenhaHash; garante consistencia
            $info = password_get_info($participante->getSenha());
            if ($info['algo'] === 0 || $info['algo'] === null) {
                // texto puro que passou por fromArray como hash - corrige
                $participante->setSenhaHash(password_hash($merged['senha'], PASSWORD_DEFAULT));
            }
        } catch (InvalidArgumentException $e) {
            throw new ErrorResponse($e->getMessage(), 400);
        }

        // Valida unicidade ignorando o proprio registro
        $this->assertEmailUnico($participante->getEmail(), $id);
        $this->assertCpfUnico($participante->getCpf(), $id);

        $this->participanteDAO->update($participante);
        return $this->findById($id);
    }

    public function delete(int $id): void
    {
        $this->findById($id);
        $ok = $this->participanteDAO->delete($id);
        if (!$ok) {
            throw new ErrorResponse('Falha ao deletar participante.', 400);
        }
    }

    public function count(): int
    {
        return $this->participanteDAO->count();
    }

    public function getByEmail(string $email): Participante
    {
        $email = trim($email);
        if ($email === '') {
            throw new ErrorResponse('E-mail para busca é obrigatório.', 400);
        }
        $p = $this->participanteDAO->findByEmail($email);
        if ($p === null) {
            throw new ErrorResponse('Participante não encontrado.', 404);
        }
        return $p;
    }

    public function getByCpf(string $cpf): Participante
    {
        $cpf = trim($cpf);
        if ($cpf === '') {
            throw new ErrorResponse('CPF para busca é obrigatório.', 400);
        }
        $p = $this->participanteDAO->findByCpf($cpf);
        if ($p === null) {
            throw new ErrorResponse('Participante não encontrado.', 404);
        }
        return $p;
    }

    private function hashSenhaIfNeeded(string $senha): string
    {
        $info = password_get_info($senha);
        if ($info['algo'] !== 0 && $info['algo'] !== null) {
            return $senha;
        }
        return password_hash($senha, PASSWORD_DEFAULT);
    }

    private function assertEmailUnico(string $email, ?int $ignoreId = null): void
    {
        $existente = $this->participanteDAO->findByEmail($email);
        if ($existente !== null) {
            if ($ignoreId !== null && $existente->getIdParticipante() === $ignoreId) {
                return;
            }
            throw new ErrorResponse('E-mail já cadastrado.', 409);
        }
    }

    private function assertCpfUnico(string $cpf, ?int $ignoreId = null): void
    {
        $existente = $this->participanteDAO->findByCpf($cpf);
        if ($existente !== null) {
            if ($ignoreId !== null && $existente->getIdParticipante() === $ignoreId) {
                return;
            }
            throw new ErrorResponse('CPF já cadastrado.', 409);
        }
    }
}
