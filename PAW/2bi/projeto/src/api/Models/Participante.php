<?php

namespace Api\Models;

use InvalidArgumentException;
use JsonSerializable;

// Representa um registro da tabela participantes.
// A senha nunca aparece no JSON de resposta.
class Participante implements JsonSerializable
{
    private ?int $id_participante = null;
    private string $nome = "";
    private string $email = "";
    private string $cpf = "";
    private ?string $telefone = null;
    private string $senha = "";

    public function getIdParticipante(): ?int
    {
        return $this->id_participante;
    }

    public function setIdParticipante(int $value): void
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("id_participante deve ser maior que zero.");
        }
        $this->id_participante = $value;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $value): void
    {
        $value = trim($value);
        if (mb_strlen($value) < 3) {
            throw new InvalidArgumentException("nome deve ter pelo menos 3 caracteres.");
        }
        if (mb_strlen($value) > 150) {
            throw new InvalidArgumentException("nome deve ter no maximo 150 caracteres.");
        }
        $this->nome = $value;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $value): void
    {
        $value = trim($value);
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("email em formato invalido.");
        }
        $this->email = $value;
    }

    public function getCpf(): string
    {
        return $this->cpf;
    }

    public function setCpf(string $value): void
    {
        $digitos = preg_replace('/\D/', '', $value);
        if (strlen($digitos) !== 11) {
            throw new InvalidArgumentException("cpf deve conter 11 digitos.");
        }
        $this->cpf = trim($value);
    }

    public function getTelefone(): ?string
    {
        return $this->telefone;
    }

    public function setTelefone(?string $value): void
    {
        $this->telefone = $value === null ? null : trim($value);
    }

    public function getSenha(): string
    {
        return $this->senha;
    }

    public function setSenha(string $value): void
    {
        if (mb_strlen(trim($value)) < 6) {
            throw new InvalidArgumentException("senha deve ter pelo menos 6 caracteres.");
        }
        $this->senha = $value;
    }

    public function jsonSerialize(): array
    {
        return [
            'id_participante' => $this->getIdParticipante(),
            'nome' => $this->getNome(),
            'email' => $this->getEmail(),
            'cpf' => $this->getCpf(),
            'telefone' => $this->getTelefone()
        ];
    }
}
