<?php

declare(strict_types=1);

namespace Api\Models;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Ingresso
 * Representa um lote/tipo de ingresso vinculado a um evento.
 */
class Ingresso implements JsonSerializable
{
    // Tipos livres — aceita qualquer string 2..80 (mais flexível que enum rígido para demo)

    private ?int $idIngresso;
    private string $tipo;
    private float $preco;
    private int $quantidadeTotal;
    private int $quantidadeDisponivel;
    private int $idEvento;

    public function __construct(
        ?int $idIngresso = null,
        string $tipo = '',
        float $preco = 0.0,
        int $quantidadeTotal = 0,
        int $quantidadeDisponivel = 0,
        int $idEvento = 0
    ) {
        $this->idIngresso = null;
        $this->tipo = '';
        $this->preco = 0.0;
        $this->quantidadeTotal = 0;
        $this->quantidadeDisponivel = 0;
        $this->idEvento = 0;

        if ($idIngresso !== null) {
            $this->setIdIngresso($idIngresso);
        }
        if ($tipo !== '') {
            $this->setTipo($tipo);
        }
        // preco pode ser 0 (cortesia), então verifica se foi informado
        if (func_num_args() >= 3) {
            $this->setPreco($preco);
        }
        if ($quantidadeTotal !== 0 || func_num_args() >= 4) {
            $this->setQuantidadeTotal($quantidadeTotal);
        }
        if (func_num_args() >= 5) {
            $this->setQuantidadeDisponivel($quantidadeDisponivel);
        }
        if ($idEvento !== 0) {
            $this->setIdEvento($idEvento);
        }
    }

    public function getIdIngresso(): ?int
    {
        return $this->idIngresso;
    }

    public function setIdIngresso(?int $idIngresso): void
    {
        if ($idIngresso !== null && $idIngresso <= 0) {
            throw new InvalidArgumentException('ID do ingresso deve ser um número positivo.');
        }
        $this->idIngresso = $idIngresso;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): void
    {
        $tipo = trim($tipo);
        if ($tipo === '') {
            throw new InvalidArgumentException('Tipo do ingresso é obrigatório.');
        }
        if (mb_strlen($tipo) < 2) {
            throw new InvalidArgumentException('Tipo deve ter pelo menos 2 caracteres.');
        }
        if (mb_strlen($tipo) > 80) {
            throw new InvalidArgumentException('Tipo deve ter no máximo 80 caracteres.');
        }
        $this->tipo = mb_strtolower($tipo);
    }

    public function getPreco(): float
    {
        return $this->preco;
    }

    public function setPreco(float $preco): void
    {
        if ($preco < 0) {
            throw new InvalidArgumentException('Preço não pode ser negativo.');
        }
        if ($preco > 100000) {
            throw new InvalidArgumentException('Preço excede o limite permitido.');
        }
        $this->preco = round($preco, 2);
    }

    public function getQuantidadeTotal(): int
    {
        return $this->quantidadeTotal;
    }

    public function setQuantidadeTotal(int $quantidadeTotal): void
    {
        if ($quantidadeTotal <= 0) {
            throw new InvalidArgumentException('Quantidade total deve ser maior que zero.');
        }
        $this->quantidadeTotal = $quantidadeTotal;

        // Ajuste de consistência: se disponível ainda não foi definido, assume total
        if ($this->quantidadeDisponivel === 0 && $quantidadeTotal > 0) {
            // não força, apenas mantém 0 até setQuantidadeDisponivel ser chamado
        }
        if ($this->quantidadeDisponivel > $this->quantidadeTotal) {
            throw new InvalidArgumentException('Quantidade disponível não pode ser maior que a quantidade total.');
        }
    }

    public function getQuantidadeDisponivel(): int
    {
        return $this->quantidadeDisponivel;
    }

    public function setQuantidadeDisponivel(int $quantidadeDisponivel): void
    {
        if ($quantidadeDisponivel < 0) {
            throw new InvalidArgumentException('Quantidade disponível não pode ser negativa.');
        }
        if ($this->quantidadeTotal > 0 && $quantidadeDisponivel > $this->quantidadeTotal) {
            throw new InvalidArgumentException('Quantidade disponível não pode ser maior que a quantidade total.');
        }
        $this->quantidadeDisponivel = $quantidadeDisponivel;
    }

    public function getIdEvento(): int
    {
        return $this->idEvento;
    }

    public function setIdEvento(int $idEvento): void
    {
        if ($idEvento <= 0) {
            throw new InvalidArgumentException('ID do evento deve ser um número positivo.');
        }
        $this->idEvento = $idEvento;
    }

    public static function fromArray(array $dados): self
    {
        $instancia = new self();
        if (isset($dados['idIngresso']) && $dados['idIngresso'] !== null && $dados['idIngresso'] !== '') {
            $instancia->setIdIngresso((int) $dados['idIngresso']);
        }
        if (isset($dados['tipo'])) {
            $instancia->setTipo((string) $dados['tipo']);
        }
        if (isset($dados['preco'])) {
            $instancia->setPreco((float) $dados['preco']);
        }
        if (isset($dados['quantidadeTotal'])) {
            $instancia->setQuantidadeTotal((int) $dados['quantidadeTotal']);
        }
        if (isset($dados['quantidadeDisponivel'])) {
            $instancia->setQuantidadeDisponivel((int) $dados['quantidadeDisponivel']);
        }
        if (isset($dados['idEvento'])) {
            $instancia->setIdEvento((int) $dados['idEvento']);
        }
        return $instancia;
    }

    public function toArray(): array
    {
        return [
            'idIngresso'           => $this->idIngresso,
            'tipo'                 => $this->tipo,
            'preco'                => $this->preco,
            'quantidadeTotal'      => $this->quantidadeTotal,
            'quantidadeDisponivel' => $this->quantidadeDisponivel,
            'idEvento'             => $this->idEvento,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
