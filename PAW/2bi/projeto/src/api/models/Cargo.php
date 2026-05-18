<?php
namespace Api\Models;
use InvalidArgumentException;
use \JsonSerializable;

class Cargo implements JsonSerializable
{
    private int $idCargo;
    private string $nomeCargo = "";
    private int $idDepartamento;

    public function __construct() {}

    public function getIdCargo(): ?int { return $this->idCargo; }

    public function setIdCargo(int $value): void {
        if ($value <= 0) throw new InvalidArgumentException("idCargo deve ser maior que zero.");
        $this->idCargo = $value;
    }

    public function getNomeCargo(): ?string { return $this->nomeCargo; }

    public function setNomeCargo(string $value): void {
        $nome = trim($value);
        if ($nome === '') throw new InvalidArgumentException("nomeCargo não pode ser vazio.");
        $len = mb_strlen($nome);
        if ($len < 3 || $len > 64) throw new InvalidArgumentException("nomeCargo deve ter entre 3 e 64 caracteres.");
        $this->nomeCargo = $nome;
    }

    public function getIdDepartamento(): ?int { return $this->idDepartamento; }

    public function setIdDepartamento(int $value): void {
        if ($value <= 0) throw new InvalidArgumentException("idDepartamento deve ser maior que zero.");
        $this->idDepartamento = $value;
    }

    public function jsonSerialize(): array {
        return [
            'idCargo' => $this->getIdCargo(),
            'nomeCargo' => $this->getNomeCargo(),
            'idDepartamento' => $this->getIdDepartamento()
        ];
    }
}
