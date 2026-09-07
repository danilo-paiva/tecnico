<?php
namespace Api\Models;
use JsonSerializable;

/**
 * Modelo de Dependente
 * Representa pessoas vinculadas a um funcionário para fins de benefícios.
 */
class Dependente implements JsonSerializable
{
    private int $idDependente;
    private string $nomeDependente;
    private string $parentesco;
    private int $idFuncionario;

    public function __construct() {}

    public function getIdDependente(): int { return $this->idDependente; }

    /**
     * Define o ID do dependente. Deve ser positivo.
     */
    public function setIdDependente(int $value): void {
        if ($value <= 0) throw new \Exception("idDependente deve ser positivo.");
        $this->idDependente = $value;
    }

    public function getNomeDependente(): string { return $this->nomeDependente; }

    public function setNomeDependente(string $nome): void {
        $nome = trim($nome);
        if (strlen($nome) < 3) throw new \Exception("nomeDependente deve ter ao menos 3 caracteres.");
        $this->nomeDependente = $nome;
    }

    public function getParentesco(): string { return $this->parentesco; }

    public function setParentesco(string $parentesco): void {
        $this->parentesco = trim($parentesco);
    }

    public function getIdFuncionario(): int { return $this->idFuncionario; }

    /**
     * Define o ID do funcionário responsável por este dependente.
     */
    public function setIdFuncionario(int $value): void {
        if ($value <= 0) throw new \Exception("idFuncionario deve ser positivo.");
        $this->idFuncionario = $value;
    }

    /**
     * Define a representação JSON do objeto para respostas da API.
     */
    public function jsonSerialize(): array {
        return [
            'idDependente' => $this->getIdDependente(),
            'nomeDependente' => $this->getNomeDependente(),
            'parentesco' => $this->getParentesco(),
            'idFuncionario' => $this->getIdFuncionario()
        ];
    }
}
