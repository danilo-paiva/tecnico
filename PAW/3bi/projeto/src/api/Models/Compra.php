<?php

declare(strict_types=1);

namespace Api\Models;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Compra
 * Representa a compra de ingressos feita por um participante.
 */
class Compra implements JsonSerializable
{
    private ?int $idCompra;
    private string $dataCompra;
    private int $quantidade;
    private float $valorTotal;
    private int $idParticipante;
    private int $idIngresso;

    public function __construct(
        ?int $idCompra = null,
        string $dataCompra = '',
        int $quantidade = 0,
        float $valorTotal = 0.0,
        int $idParticipante = 0,
        int $idIngresso = 0
    ) {
        $this->idCompra = null;
        $this->dataCompra = '';
        $this->quantidade = 0;
        $this->valorTotal = 0.0;
        $this->idParticipante = 0;
        $this->idIngresso = 0;

        if ($idCompra !== null) {
            $this->setIdCompra($idCompra);
        }
        if ($dataCompra !== '') {
            $this->setDataCompra($dataCompra);
        }
        if ($quantidade !== 0) {
            $this->setQuantidade($quantidade);
        }
        if (func_num_args() >= 4) {
            $this->setValorTotal($valorTotal);
        }
        if ($idParticipante !== 0) {
            $this->setIdParticipante($idParticipante);
        }
        if ($idIngresso !== 0) {
            $this->setIdIngresso($idIngresso);
        }
    }

    public function getIdCompra(): ?int
    {
        return $this->idCompra;
    }

    public function setIdCompra(?int $idCompra): void
    {
        if ($idCompra !== null && $idCompra <= 0) {
            throw new InvalidArgumentException('ID da compra deve ser um número positivo.');
        }
        $this->idCompra = $idCompra;
    }

    public function getDataCompra(): string
    {
        return $this->dataCompra;
    }

    public function setDataCompra(string $dataCompra): void
    {
        $dataCompra = trim($dataCompra);
        if ($dataCompra === '') {
            throw new InvalidArgumentException('Data da compra é obrigatória.');
        }
        $formatos = ['Y-m-d H:i:s', 'Y-m-d'];
        $valido = false;
        foreach ($formatos as $formato) {
            $dt = \DateTime::createFromFormat($formato, $dataCompra);
            if ($dt && $dt->format($formato) === $dataCompra) {
                $valido = true;
                break;
            }
        }
        if (!$valido) {
            throw new InvalidArgumentException('Data da compra inválida. Use o formato YYYY-MM-DD ou YYYY-MM-DD HH:MM:SS.');
        }
        // Não permite data futura
        $dataObj = new \DateTime($dataCompra);
        $agora = new \DateTime();
        if ($dataObj > $agora) {
            throw new InvalidArgumentException('Data da compra não pode ser no futuro.');
        }
        $this->dataCompra = $dataCompra;
    }

    public function getQuantidade(): int
    {
        return $this->quantidade;
    }

    public function setQuantidade(int $quantidade): void
    {
        if ($quantidade <= 0) {
            throw new InvalidArgumentException('Quantidade deve ser maior que zero.');
        }
        if ($quantidade > 1000) {
            throw new InvalidArgumentException('Quantidade excede o limite permitido por compra.');
        }
        $this->quantidade = $quantidade;
    }

    public function getValorTotal(): float
    {
        return $this->valorTotal;
    }

    public function setValorTotal(float $valorTotal): void
    {
        if ($valorTotal < 0) {
            throw new InvalidArgumentException('Valor total não pode ser negativo.');
        }
        if ($valorTotal > 1000000) {
            throw new InvalidArgumentException('Valor total excede o limite permitido.');
        }
        $this->valorTotal = round($valorTotal, 2);
    }

    public function getIdParticipante(): int
    {
        return $this->idParticipante;
    }

    public function setIdParticipante(int $idParticipante): void
    {
        if ($idParticipante <= 0) {
            throw new InvalidArgumentException('ID do participante deve ser um número positivo.');
        }
        $this->idParticipante = $idParticipante;
    }

    public function getIdIngresso(): int
    {
        return $this->idIngresso;
    }

    public function setIdIngresso(int $idIngresso): void
    {
        if ($idIngresso <= 0) {
            throw new InvalidArgumentException('ID do ingresso deve ser um número positivo.');
        }
        $this->idIngresso = $idIngresso;
    }

    public static function fromArray(array $dados): self
    {
        $instancia = new self();
        if (isset($dados['idCompra']) && $dados['idCompra'] !== null && $dados['idCompra'] !== '') {
            $instancia->setIdCompra((int) $dados['idCompra']);
        }
        if (isset($dados['dataCompra'])) {
            $instancia->setDataCompra((string) $dados['dataCompra']);
        }
        if (isset($dados['quantidade'])) {
            $instancia->setQuantidade((int) $dados['quantidade']);
        }
        if (isset($dados['valorTotal'])) {
            $instancia->setValorTotal((float) $dados['valorTotal']);
        }
        if (isset($dados['idParticipante'])) {
            $instancia->setIdParticipante((int) $dados['idParticipante']);
        }
        if (isset($dados['idIngresso'])) {
            $instancia->setIdIngresso((int) $dados['idIngresso']);
        }
        return $instancia;
    }

    public function toArray(): array
    {
        return [
            'idCompra'       => $this->idCompra,
            'dataCompra'     => $this->dataCompra,
            'quantidade'     => $this->quantidade,
            'valorTotal'     => $this->valorTotal,
            'idParticipante' => $this->idParticipante,
            'idIngresso'     => $this->idIngresso,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
