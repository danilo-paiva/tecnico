<?php

declare(strict_types=1);

namespace Api\Models;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Participante
 * Representa a pessoa que compra ingressos para eventos.
 */
class Participante implements JsonSerializable
{
    private ?int $idParticipante;
    private string $nome;
    private string $email;
    private string $cpf;
    private string $telefone;
    private string $senha;

    public function __construct(
        ?int $idParticipante = null,
        string $nome = '',
        string $email = '',
        string $cpf = '',
        string $telefone = '',
        string $senha = ''
    ) {
        $this->idParticipante = null;
        $this->nome = '';
        $this->email = '';
        $this->cpf = '';
        $this->telefone = '';
        $this->senha = '';

        if ($idParticipante !== null) {
            $this->setIdParticipante($idParticipante);
        }
        if ($nome !== '') {
            $this->setNome($nome);
        }
        if ($email !== '') {
            $this->setEmail($email);
        }
        if ($cpf !== '') {
            $this->setCpf($cpf);
        }
        if ($telefone !== '') {
            $this->setTelefone($telefone);
        }
        if ($senha !== '') {
            $this->setSenha($senha);
        }
    }

    public function getIdParticipante(): ?int
    {
        return $this->idParticipante;
    }

    public function setIdParticipante(?int $idParticipante): void
    {
        if ($idParticipante !== null && $idParticipante <= 0) {
            throw new InvalidArgumentException('ID do participante deve ser um número positivo.');
        }
        $this->idParticipante = $idParticipante;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): void
    {
        $nome = trim($nome);
        if ($nome === '') {
            throw new InvalidArgumentException('Nome do participante é obrigatório.');
        }
        if (mb_strlen($nome) < 3) {
            throw new InvalidArgumentException('Nome deve ter pelo menos 3 caracteres.');
        }
        if (mb_strlen($nome) > 150) {
            throw new InvalidArgumentException('Nome deve ter no máximo 150 caracteres.');
        }
        if (!preg_match('/^[\p{L}\s\'\-]+$/u', $nome)) {
            throw new InvalidArgumentException('Nome contém caracteres inválidos.');
        }
        $this->nome = $nome;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $email = trim(mb_strtolower($email));
        if ($email === '') {
            throw new InvalidArgumentException('E-mail é obrigatório.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('E-mail em formato inválido.');
        }
        if (mb_strlen($email) > 255) {
            throw new InvalidArgumentException('E-mail deve ter no máximo 255 caracteres.');
        }
        $this->email = $email;
    }

    public function getCpf(): string
    {
        return $this->cpf;
    }

    public function setCpf(string $cpf): void
    {
        $numeros = preg_replace('/\D/', '', $cpf);
        if ($numeros === '' || $numeros === null) {
            throw new InvalidArgumentException('CPF é obrigatório.');
        }
        if (mb_strlen($numeros) !== 11) {
            throw new InvalidArgumentException('CPF deve conter 11 dígitos.');
        }
        if (preg_match('/^(\d)\1{10}$/', $numeros)) {
            throw new InvalidArgumentException('CPF inválido.');
        }
        // Nota: validação de dígitos verificadores desativada para compatibilidade com dados de demonstração.
        // Se quiser rigor, descomente o bloco abaixo:
        // for ($t = 9; $t < 11; $t++) { $soma=0; for($i=0;$i<$t;$i++) $soma+=(int)$numeros[$i]*(($t+1)-$i); $digito=((10*$soma)%11)%10; if((int)$numeros[$t]!==$digito) throw new InvalidArgumentException('CPF inválido.'); }
        $this->cpf = $numeros;
    }

    public function getTelefone(): string
    {
        return $this->telefone;
    }

    public function setTelefone(string $telefone): void
    {
        $telefone = trim($telefone);
        if ($telefone === '') {
            throw new InvalidArgumentException('Telefone é obrigatório.');
        }
        $numeros = preg_replace('/\D/', '', $telefone);
        $tam = mb_strlen($numeros);
        if ($tam < 10 || $tam > 11) {
            throw new InvalidArgumentException('Telefone deve conter 10 ou 11 dígitos (com DDD).');
        }
        $this->telefone = $telefone;
    }

    public function getSenha(): string
    {
        return $this->senha;
    }

    /**
     * Define a senha. Espera texto puro; armazena hash com password_hash.
     * Se o valor já parecer um hash bcrypt/argon, mantém como está.
     */
    public function setSenha(string $senha): void
    {
        if (trim($senha) === '') {
            throw new InvalidArgumentException('Senha é obrigatória.');
        }
        if (mb_strlen($senha) < 6) {
            throw new InvalidArgumentException('Senha deve ter pelo menos 6 caracteres.');
        }
        if (mb_strlen($senha) > 255) {
            throw new InvalidArgumentException('Senha deve ter no máximo 255 caracteres.');
        }
        // Detecta se já é hash (evita re-hash ao hidratar do banco)
        $info = password_get_info($senha);
        if ($info['algo'] !== 0 && $info['algo'] !== null) {
            $this->senha = $senha;
        } else {
            $this->senha = password_hash($senha, PASSWORD_DEFAULT);
        }
    }

    /**
     * Define senha já hasheada vinda do banco sem re-hashear.
     * Útil para hidratação direta.
     */
    public function setSenhaHash(string $hash): void
    {
        if (trim($hash) === '') {
            throw new InvalidArgumentException('Hash da senha é obrigatório.');
        }
        $this->senha = $hash;
    }

    public static function fromArray(array $dados): self
    {
        $instancia = new self();
        if (isset($dados['idParticipante']) && $dados['idParticipante'] !== null && $dados['idParticipante'] !== '') {
            $instancia->setIdParticipante((int) $dados['idParticipante']);
        }
        if (isset($dados['nome'])) {
            $instancia->setNome((string) $dados['nome']);
        }
        if (isset($dados['email'])) {
            $instancia->setEmail((string) $dados['email']);
        }
        if (isset($dados['cpf'])) {
            $instancia->setCpf((string) $dados['cpf']);
        }
        if (isset($dados['telefone'])) {
            $instancia->setTelefone((string) $dados['telefone']);
        }
        if (isset($dados['senha'])) {
            // Dado vindo do banco já é hash
            $instancia->setSenhaHash((string) $dados['senha']);
        }
        return $instancia;
    }

    public function toArray(bool $exporSenha = false): array
    {
        $dados = [
            'idParticipante' => $this->idParticipante,
            'nome'           => $this->nome,
            'email'          => $this->email,
            'cpf'            => $this->cpf,
            'telefone'       => $this->telefone,
        ];
        if ($exporSenha) {
            $dados['senha'] = $this->senha;
        }
        return $dados;
    }

    public function jsonSerialize(): mixed
    {
        // Nunca expõe hash de senha no JSON padrão
        return $this->toArray(false);
    }
}
