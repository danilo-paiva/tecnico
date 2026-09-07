<?php
namespace Api\Models;
use JsonSerializable;

/**
 * Modelo de Funcionário
 * Representa a entidade principal de RH, vinculando pessoa ao cargo.
 */
class Funcionario implements JsonSerializable
{
    private int $idFuncionario;
    private Cargo $cargo;
    private string $nomeFuncionario;
    private string $email;
    private string $senha;
    private int $recebeValeTransporte;

    public function __construct() {
        $this->cargo = new Cargo();
    }

    public function getIdFuncionario(): int { return $this->idFuncionario; }

    /**
     * Define o ID do funcionário.
     * Valida se o valor é um número inteiro positivo.
     */
    public function setIdFuncionario($valor): void {
        if (!is_numeric($valor) || intval($valor) != $valor || $valor <= 0) {
            throw new \Exception("idFuncionario deve ser um número inteiro positivo.");
        }
        $this->idFuncionario = intval($valor);
    }

    public function getCargo(): Cargo { return $this->cargo; }

    /**
     * Vincula um objeto do tipo Cargo ao funcionário.
     */
    public function setCargo($cargo): void {
        if (!($cargo instanceof Cargo)) throw new \Exception("cargo deve ser uma instância de Cargo.");
        $this->cargo = $cargo;
    }

    public function getNomeFuncionario(): string { return $this->nomeFuncionario; }

    public function setNomeFuncionario(string $nome): void {
        $nome = trim($nome);
        if (strlen($nome) < 3) throw new \Exception("nomeFuncionario deve ter pelo menos 3 caracteres.");
        $this->nomeFuncionario = $nome;
    }

    public function getEmail(): string { return $this->email; }

    /**
     * Valida e define o e-mail do funcionário.
     */
    public function setEmail(string $email): void {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new \Exception("email em formato inválido.");
        $this->email = $email;
    }

    public function getSenha(): string { return $this->senha; }

    /**
     * Define a senha do funcionário com regras de complexidade:
     * Mínimo 6 caracteres, 1 Letra Maiúscula, 1 Número e 1 Caractere Especial.
     */
    public function setSenha(string $senha): void {
        $senha = trim($senha);
        if (strlen($senha) < 6) throw new \Exception("senha deve ter pelo menos 6 caracteres.");
        if (!preg_match("/[A-Z]/", $senha) || !preg_match("/[0-9]/", $senha) || !preg_match("/[!@#$%^&*(),.?\":{}|<>]/", $senha)) {
            throw new \Exception("senha deve conter letra maiúscula, número e caractere especial.");
        }
        $this->senha = $senha;
    }

    public function getRecebeValeTransporte(): int { return $this->recebeValeTransporte; }

    /**
     * Define se o funcionário recebe vale transporte (booleano simulado por 0 ou 1).
     */
    public function setRecebeValeTransporte(int $valor): void {
        if ($valor !== 0 && $valor !== 1) throw new \Exception("recebeValeTransporte deve ser 0 ou 1.");
        $this->recebeValeTransporte = $valor;
    }

    /**
     * Define a representação JSON do objeto para respostas da API.
     * Note que a senha não é incluída por razões de segurança.
     */
    public function jsonSerialize(): array {
        return [
            'idFuncionario' => $this->getIdFuncionario(),
            'cargo' => $this->getCargo(),
            'nomeFuncionario' => $this->getNomeFuncionario(),
            'email' => $this->getEmail(),
            'recebeValeTransporte' => $this->getRecebeValeTransporte()
        ];
    }
}
