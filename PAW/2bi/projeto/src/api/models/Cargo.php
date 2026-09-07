<?php
namespace Api\Models;
use InvalidArgumentException;
use \JsonSerializable;

/**
 * Modelo de Cargo
 * Representa a função exercida por um funcionário dentro de um departamento.
 */
class Cargo implements JsonSerializable
{
    private int $idCargo;
    private string $nomeCargo = "";
    private int $idDepartamento;

    public function __construct() {}

    public function getIdCargo(): ?int { return $this->idCargo; }

    /**
     * Define o ID do cargo.
     * Deve ser um número inteiro positivo.
     */
    public function setIdCargo(int $value): void {
        if ($value <= 0) throw new InvalidArgumentException("idCargo deve ser maior que zero.");
        $this->idCargo = $value;
    }

    public function getNomeCargo(): ?string { return $this->nomeCargo; }

    /**
     * Define o nome do cargo com validações de tamanho e conteúdo.
     */
    public function setNomeCargo(string $value): void {
        $nome = trim($value);
        if ($nome === '') throw new InvalidArgumentException("nomeCargo não pode ser vazio.");
        $len = mb_strlen($nome);
        if ($len < 3 || $len > 64) throw new InvalidArgumentException("nomeCargo deve ter entre 3 e 64 caracteres.");
        $this->nomeCargo = $nome;
    }

    public function getIdDepartamento(): ?int { return $this->idDepartamento; }

    /**
     * Define o ID do departamento ao qual este cargo pertence.
     */
    public function setIdDepartamento(int $value): void {
        if ($value <= 0) throw new InvalidArgumentException("idDepartamento deve ser maior que zero.");
        $this->idDepartamento = $value;
    }

    /**
     * Define a representação JSON do objeto para respostas da API.
     */
    public function jsonSerialize(): array {
        return [
            'idCargo' => $this->getIdCargo(),
            'nomeCargo' => $this->getNomeCargo(),
            'idDepartamento' => $this->getIdDepartamento()
        ];
    }
}
