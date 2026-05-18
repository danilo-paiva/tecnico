<?php
namespace Api\Models;
use InvalidArgumentException;
use JsonSerializable;

class Departamento implements JsonSerializable
{
    private int $idDepartamento;
    private string $nomeDepartamento = "";

    public function __construct() {}

    public function getIdDepartamento(): ?int { return $this->idDepartamento; }

    public function setIdDepartamento(int $value): void {
        if ($value <= 0) throw new InvalidArgumentException("idDepartamento deve ser maior que zero.");
        $this->idDepartamento = $value;
    }

    public function getNomeDepartamento(): ?string { return $this->nomeDepartamento; }

    public function setNomeDepartamento(string $value): void {
        $nome = trim($value);
        if ($nome === '') throw new InvalidArgumentException("nomeDepartamento não pode ser vazio.");
        if (mb_strlen($nome) < 3 || mb_strlen($nome) > 100) throw new InvalidArgumentException("nomeDepartamento deve ter entre 3 e 100 caracteres.");
        $this->nomeDepartamento = $nome;
    }

    public function jsonSerialize(): array {
        return [
            'idDepartamento' => $this->getIdDepartamento(),
            'nomeDepartamento' => $this->getNomeDepartamento()
        ];
    }
}
