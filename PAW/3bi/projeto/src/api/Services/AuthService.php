<?php

declare(strict_types=1);

namespace Api\Services;

use Api\Dao\ParticipanteDAO;
use Api\Http\ErrorResponse;
use Api\Config\JwtConfig;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService
{
    private ParticipanteDAO $participanteDAO;

    public function __construct(ParticipanteDAO $participanteDAO)
    {
        $this->participanteDAO = $participanteDAO;
    }

    /**
     * Autentica por email/senha e retorna token JWT + dados do usuário
     */
    public function login(string $email, string $senha): array
    {
        $email = trim(mb_strtolower($email));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ErrorResponse('E-mail inválido.', 400);
        }
        if (trim($senha) === '') {
            throw new ErrorResponse('Senha é obrigatória.', 400);
        }

        $participante = $this->participanteDAO->findByEmail($email);
        if ($participante === null) {
            throw new ErrorResponse('Credenciais inválidas.', 401);
        }

        $hash = $participante->getSenha();
        // O DAO retorna hash via getSenha (armazenado), mas Model esconde no toArray; getSenha retorna hash
        if (!password_verify($senha, $hash)) {
            // tenta também verificar se senha está em texto puro no banco (dados antigos de exemplo)
            // para dados de exemplo antigos o hash é falso ($2y$10$exemplo...), então permite login com senha fixa?
            // Para demo, se hash não verifica, verifica se senha é "123456" ou "Senha@123" para contas de exemplo?
            // Melhor: se for hash de exemplo (contém "exemplo"), aceita se senha for "123456" ou a original?
            // Vamos manter rigor: se não verificar, lança 401
            throw new ErrorResponse('Credenciais inválidas.', 401);
        }

        $now = time();
        $payload = [
            'iss' => JwtConfig::ISSUER,
            'iat' => $now,
            'exp' => $now + JwtConfig::EXPIRATION_SECONDS,
            'sub' => $participante->getIdParticipante(),
            'email' => $participante->getEmail(),
            'nome' => $participante->getNome(),
        ];

        $token = JWT::encode($payload, JwtConfig::SECRET, JwtConfig::ALGO);

        return [
            'token' => $token,
            'expiresIn' => JwtConfig::EXPIRATION_SECONDS,
            'usuario' => [
                'idParticipante' => $participante->getIdParticipante(),
                'nome' => $participante->getNome(),
                'email' => $participante->getEmail(),
            ],
        ];
    }

    /**
     * Valida token e retorna payload
     */
    public function validateToken(string $token): object
    {
        try {
            $decoded = JWT::decode($token, new Key(JwtConfig::SECRET, JwtConfig::ALGO));
            return $decoded;
        } catch (\Firebase\JWT\ExpiredException $e) {
            throw new ErrorResponse('Token expirado. Faça login novamente.', 401);
        } catch (\Throwable $e) {
            throw new ErrorResponse('Token inválido.', 401);
        }
    }

    /**
     * Registra novo participante (para permitir cadastro via tela de registro se desejar)
     */
    public function register(array $data): array
    {
        // Reusa ParticipanteService lógica? Simplifica aqui
        // Espera: nome, email, cpf, telefone, senha
        $required = ['nome','email','cpf','telefone','senha'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
                throw new ErrorResponse("Campo '$field' é obrigatório.", 400);
            }
        }
        // Verifica duplicidade
        if ($this->participanteDAO->findByEmail(trim(mb_strtolower($data['email']))) !== null) {
            throw new ErrorResponse('E-mail já cadastrado.', 409);
        }
        // Cpf normalizado
        $cpfNumeros = preg_replace('/\D/', '', $data['cpf']);
        if ($this->participanteDAO->findByCpf($cpfNumeros) !== null) {
            // findByCpf faz busca por numeros; se já existe retorna
            throw new ErrorResponse('CPF já cadastrado.', 409);
        }

        // Cria via Model
        $participante = \Api\Models\Participante::fromArray([
            'nome' => $data['nome'],
            'email' => $data['email'],
            'cpf' => $data['cpf'],
            'telefone' => $data['telefone'],
            'senha' => $data['senha'],
        ]);
        // fromArray usa setSenhaHash para senha, então precisamos setar senha corretamente com hash
        // fromArray trata senha como hash, então precisamos criar manualmente
        // Vamos criar novo objeto e usar setSenha que faz hash
        $p = new \Api\Models\Participante();
        $p->setNome($data['nome']);
        $p->setEmail($data['email']);
        $p->setCpf($data['cpf']);
        $p->setTelefone($data['telefone']);
        $p->setSenha($data['senha']); // hasheia

        $id = $this->participanteDAO->create($p);
        $criado = $this->participanteDAO->getById($id);
        if ($criado === null) {
            throw new ErrorResponse('Falha ao registrar usuário.', 500);
        }
        return $criado->toArray();
    }
}
