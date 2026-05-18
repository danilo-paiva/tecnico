<?php
namespace Api\Models;
use JsonSerializable;

class FolhaPagamento implements JsonSerializable
{
    private int $idFolha;
    private string $dataPagamento;
    private float $valorLiquido;
    private int $idFuncionario;

    public function __construct() {}

    public function getIdFolha(): int { return $this->idFolha; }

    public function setIdFolha(int $value): void {
        if ($value <= 0) throw new \Exception("idFolha deve ser positivo.");
        $this->idFolha = $value;
    }

    public function getDataPagamento(): string { return $this->dataPagamento; }

    public function setDataPagamento(string $data): void {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) throw new \Exception("dataPagamento deve estar no formato YYYY-MM-DD.");
        $this->dataPagamento = $data;
    }

    public function getValorLiquido(): float { return $this->valorLiquido; }

    public function setValorLiquido(float $valor): void {
        if ($valor < 0) throw new \Exception("valorLiquido não pode ser negativo.");
        $this->valorLiquido = $valor;
    }

    public function getIdFuncionario(): int { return $this->idFuncionario; }

    public function setIdFuncionario(int $value): void {
        if ($value <= 0) throw new \Exception("idFuncionario deve ser positivo.");
        $this->idFuncionario = $value;
    }

    public function jsonSerialize(): array {
        return [
            'idFolha' => $this->getIdFolha(),
            'dataPagamento' => $this->getDataPagamento(),
            'valorLiquido' => $this->getValorLiquido(),
            'idFuncionario' => $this->getIdFuncionario()
        ];
    }
}
