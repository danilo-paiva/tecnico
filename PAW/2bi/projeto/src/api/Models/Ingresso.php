<?php

namespace Api\Models;

use InvalidArgumentException;
use JsonSerializable;

// Representa um registro da tabela ingressos (um tipo de ingresso de um evento)
class Ingresso implements JsonSerializable
{
    private ?int $id_ingresso = null;
    private string $tipo = "";
    private float $preco = 0;
    private int $quantidade_total = 0;
    private int $quantidade_disponivel = 0;
    private int $id_evento = 0;

    public function getIdIngresso(): ?int
    {
        return $this->id_ingresso;
    }

    public function setIdIngresso(int $value): void
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("id_ingresso deve ser maior que zero.");
        }
        $this->id_ingresso = $value;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function setTipo(string $value): void
    {
        $value = trim($value);
        if (mb_strlen($value) < 2) {
            throw new InvalidArgumentException("tipo deve ter pelo menos 2 caracteres.");
        }
        if (mb_strlen($value) > 80) {
            throw new InvalidArgumentException("tipo deve ter no maximo 80 caracteres.");
        }
        $this->tipo = $value;
    }

    public function getPreco(): float
    {
        return $this->preco;
    }

    public function setPreco(float $value): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException("preco nao pode ser negativo.");
        }
        $this->preco = $value;
    }

    public function getQuantidadeTotal(): int
    {
        return $this->quantidade_total;
    }

    public function setQuantidadeTotal(int $value): void
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("quantidade_total deve ser maior que zero.");
        }
        $this->quantidade_total = $value;
    }

    public function getQuantidadeDisponivel(): int
    {
        return $this->quantidade_disponivel;
    }

    public function setQuantidadeDisponivel(int $value): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException("quantidade_disponivel nao pode ser negativa.");
        }
        $this->quantidade_disponivel = $value;
    }

    public function getIdEvento(): int
    {
        return $this->id_evento;
    }

    public function setIdEvento(int $value): void
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("id_evento deve ser maior que zero.");
        }
        $this->id_evento = $value;
    }

    public function jsonSerialize(): array
    {
        return [
            'id_ingresso' => $this->getIdIngresso(),
            'tipo' => $this->getTipo(),
            'preco' => $this->getPreco(),
            'quantidade_total' => $this->getQuantidadeTotal(),
            'quantidade_disponivel' => $this->getQuantidadeDisponivel(),
            'id_evento' => $this->getIdEvento()
        ];
    }
}
