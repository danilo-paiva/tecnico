<?php

declare(strict_types=1);

namespace Api\Models;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Evento
 * Representa um evento vinculado a um local.
 */
class Evento implements JsonSerializable
{
    private const STATUS_PERMITIDOS = ['planejado', 'confirmado', 'cancelado', 'realizado', 'ativo', 'adiado', 'finalizado'];

    private ?int $idEvento;
    private string $titulo;
    private ?string $descricao;
    private string $dataEvento;
    private string $status;
    private int $idLocal;

    public function __construct(
        ?int $idEvento = null,
        string $titulo = '',
        ?string $descricao = null,
        string $dataEvento = '',
        string $status = 'ativo',
        int $idLocal = 0
    ) {
        $this->idEvento = null;
        $this->titulo = '';
        $this->descricao = null;
        $this->dataEvento = '';
        $this->status = 'ativo';
        $this->idLocal = 0;

        if ($idEvento !== null) {
            $this->setIdEvento($idEvento);
        }
        if ($titulo !== '') {
            $this->setTitulo($titulo);
        }
        if ($descricao !== null) {
            $this->setDescricao($descricao);
        }
        if ($dataEvento !== '') {
            $this->setDataEvento($dataEvento);
        }
        if ($status !== 'ativo' || func_num_args() >= 5) {
            $this->setStatus($status);
        }
        if ($idLocal !== 0) {
            $this->setIdLocal($idLocal);
        }
    }

    public function getIdEvento(): ?int
    {
        return $this->idEvento;
    }

    public function setIdEvento(?int $idEvento): void
    {
        if ($idEvento !== null && $idEvento <= 0) {
            throw new InvalidArgumentException('ID do evento deve ser um número positivo.');
        }
        $this->idEvento = $idEvento;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): void
    {
        $titulo = trim($titulo);
        if ($titulo === '') {
            throw new InvalidArgumentException('Título do evento é obrigatório.');
        }
        if (mb_strlen($titulo) < 3) {
            throw new InvalidArgumentException('Título deve ter pelo menos 3 caracteres.');
        }
        if (mb_strlen($titulo) > 200) {
            throw new InvalidArgumentException('Título deve ter no máximo 200 caracteres.');
        }
        $this->titulo = $titulo;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(?string $descricao): void
    {
        if ($descricao === null || trim($descricao) === '') {
            $this->descricao = null;
            return;
        }
        $descricao = trim($descricao);
        if (mb_strlen($descricao) > 2000) {
            throw new InvalidArgumentException('Descrição deve ter no máximo 2000 caracteres.');
        }
        $this->descricao = $descricao;
    }

    public function getDataEvento(): string
    {
        return $this->dataEvento;
    }

    public function setDataEvento(string $dataEvento): void
    {
        $dataEvento = trim($dataEvento);
        if ($dataEvento === '') {
            throw new InvalidArgumentException('Data do evento é obrigatória.');
        }
        $formatos = ['Y-m-d H:i:s', 'Y-m-d'];
        $valido = false;
        foreach ($formatos as $formato) {
            $dt = \DateTime::createFromFormat($formato, $dataEvento);
            if ($dt && $dt->format($formato) === $dataEvento) {
                $valido = true;
                break;
            }
        }
        if (!$valido) {
            throw new InvalidArgumentException('Data do evento inválida. Use o formato YYYY-MM-DD ou YYYY-MM-DD HH:MM:SS.');
        }
        $this->dataEvento = $dataEvento;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $status = mb_strtolower(trim($status));
        if (!in_array($status, self::STATUS_PERMITIDOS, true)) {
            throw new InvalidArgumentException(
                'Status inválido. Valores permitidos: ' . implode(', ', self::STATUS_PERMITIDOS) . '.'
            );
        }
        $this->status = $status;
    }

    public function getIdLocal(): int
    {
        return $this->idLocal;
    }

    public function setIdLocal(int $idLocal): void
    {
        if ($idLocal <= 0) {
            throw new InvalidArgumentException('ID do local deve ser um número positivo.');
        }
        $this->idLocal = $idLocal;
    }

    public static function fromArray(array $dados): self
    {
        $instancia = new self();
        if (isset($dados['idEvento']) && $dados['idEvento'] !== null && $dados['idEvento'] !== '') {
            $instancia->setIdEvento((int) $dados['idEvento']);
        }
        if (isset($dados['titulo'])) {
            $instancia->setTitulo((string) $dados['titulo']);
        }
        if (array_key_exists('descricao', $dados)) {
            $instancia->setDescricao($dados['descricao'] !== null ? (string) $dados['descricao'] : null);
        }
        if (isset($dados['dataEvento'])) {
            $instancia->setDataEvento((string) $dados['dataEvento']);
        }
        if (isset($dados['status'])) {
            $instancia->setStatus((string) $dados['status']);
        }
        if (isset($dados['idLocal'])) {
            $instancia->setIdLocal((int) $dados['idLocal']);
        }
        return $instancia;
    }

    public function toArray(): array
    {
        return [
            'idEvento'   => $this->idEvento,
            'titulo'     => $this->titulo,
            'descricao'  => $this->descricao,
            'dataEvento' => $this->dataEvento,
            'status'     => $this->status,
            'idLocal'    => $this->idLocal,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
