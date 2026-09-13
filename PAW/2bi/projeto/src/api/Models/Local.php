<?php

namespace Api\Models;

use InvalidArgumentException;
use JsonSerializable;

// Representa um registro da tabela locais
class Local implements JsonSerializable
{
    private ?int $id_local = null;
    private string $nome = "";
    private string $endereco = "";
    private int $capacidade = 0;

    public function getIdLocal(): ?int
    {
        return $this->id_local;
    }

    public function setIdLocal(int $value): void
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("id_local deve ser maior que zero.");
        }
        $this->id_local = $value;
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
        if (mb_strlen($value) > 120) {
            throw new InvalidArgumentException("nome deve ter no maximo 120 caracteres.");
        }
        $this->nome = $value;
    }

    public function getEndereco(): string
    {
        return $this->endereco;
    }

    public function setEndereco(string $value): void
    {
        $value = trim($value);
        if (mb_strlen($value) < 3) {
            throw new InvalidArgumentException("endereco deve ter pelo menos 3 caracteres.");
        }
        if (mb_strlen($value) > 255) {
            throw new InvalidArgumentException("endereco deve ter no maximo 255 caracteres.");
        }
        $this->endereco = $value;
    }

    public function getCapacidade(): int
    {
        return $this->capacidade;
    }

    public function setCapacidade(int $value): void
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("capacidade deve ser maior que zero.");
        }
        $this->capacidade = $value;
    }

    public function jsonSerialize(): array
    {
        return [
            'id_local' => $this->getIdLocal(),
            'nome' => $this->getNome(),
            'endereco' => $this->getEndereco(),
            'capacidade' => $this->getCapacidade()
        ];
    }
}
