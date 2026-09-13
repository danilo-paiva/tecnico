<?php

namespace Api\Models;

use InvalidArgumentException;
use JsonSerializable;

// Representa um registro da tabela compras.
// O valor_total e calculado pela API (preco do ingresso x quantidade).
class Compra implements JsonSerializable
{
    private ?int $id_compra = null;
    private ?string $data_compra = null;
    private int $quantidade = 0;
    private float $valor_total = 0;
    private int $id_participante = 0;
    private int $id_ingresso = 0;

    public function getIdCompra(): ?int
    {
        return $this->id_compra;
    }

    public function setIdCompra(int $value): void
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("id_compra deve ser maior que zero.");
        }
        $this->id_compra = $value;
    }

    public function getDataCompra(): ?string
    {
        return $this->data_compra;
    }

    public function setDataCompra(?string $value): void
    {
        $this->data_compra = $value;
    }

    public function getQuantidade(): int
    {
        return $this->quantidade;
    }

    public function setQuantidade(int $value): void
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("quantidade deve ser maior que zero.");
        }
        $this->quantidade = $value;
    }

    public function getValorTotal(): float
    {
        return $this->valor_total;
    }

    public function setValorTotal(float $value): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException("valor_total nao pode ser negativo.");
        }
        $this->valor_total = $value;
    }

    public function getIdParticipante(): int
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

    public function getIdIngresso(): int
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

    public function jsonSerialize(): array
    {
        return [
            'id_compra' => $this->getIdCompra(),
            'data_compra' => $this->getDataCompra(),
            'quantidade' => $this->getQuantidade(),
            'valor_total' => $this->getValorTotal(),
            'id_participante' => $this->getIdParticipante(),
            'id_ingresso' => $this->getIdIngresso()
        ];
    }
}
