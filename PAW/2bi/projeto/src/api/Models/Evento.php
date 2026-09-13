<?php

namespace Api\Models;

use DateTime;
use InvalidArgumentException;
use JsonSerializable;

// Representa um registro da tabela eventos
class Evento implements JsonSerializable
{
    private const STATUS_VALIDOS = ['planejado', 'confirmado', 'cancelado', 'realizado'];

    private ?int $id_evento = null;
    private string $titulo = "";
    private ?string $descricao = null;
    private string $data_evento = "";
    private string $status = "planejado";
    private int $id_local = 0;

    public function getIdEvento(): ?int
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

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function setTitulo(string $value): void
    {
        $value = trim($value);
        if (mb_strlen($value) < 3) {
            throw new InvalidArgumentException("titulo deve ter pelo menos 3 caracteres.");
        }
        if (mb_strlen($value) > 150) {
            throw new InvalidArgumentException("titulo deve ter no maximo 150 caracteres.");
        }
        $this->titulo = $value;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(?string $value): void
    {
        $this->descricao = $value === null ? null : trim($value);
    }

    public function getDataEvento(): string
    {
        return $this->data_evento;
    }

    public function setDataEvento(string $value): void
    {
        $value = trim($value);
        $data = DateTime::createFromFormat('Y-m-d H:i:s', $value);
        if ($data === false) {
            $data = DateTime::createFromFormat('Y-m-d', $value);
        }
        if ($data === false) {
            throw new InvalidArgumentException("data_evento invalida. Use AAAA-MM-DD ou AAAA-MM-DD HH:MM:SS.");
        }
        $this->data_evento = $data->format('Y-m-d H:i:s');
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $value): void
    {
        $value = trim($value);
        if (!in_array($value, self::STATUS_VALIDOS, true)) {
            throw new InvalidArgumentException("status deve ser: " . implode(', ', self::STATUS_VALIDOS) . ".");
        }
        $this->status = $value;
    }

    public function getIdLocal(): int
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

    public function jsonSerialize(): array
    {
        return [
            'id_evento' => $this->getIdEvento(),
            'titulo' => $this->getTitulo(),
            'descricao' => $this->getDescricao(),
            'data_evento' => $this->getDataEvento(),
            'status' => $this->getStatus(),
            'id_local' => $this->getIdLocal()
        ];
    }
}
