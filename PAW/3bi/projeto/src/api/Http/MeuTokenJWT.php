<?php

namespace Api\Http;

use stdClass;
use DomainException;
use Exception;
use InvalidArgumentException;
use UnexpectedValueException;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\SignatureInvalidException;

// Gera e valida tokens JWT (aula paw03x01).
// O token NAO e criptografado, e apenas ASSINADO com o segredo abaixo:
// so quem conhece o segredo consegue criar um token valido.
class MeuTokenJWT
{
    // Segredo usado para assinar e validar os tokens.
    // Em producao, use variavel de ambiente.
    private const KEY = 'x9S4q0v+V0IjvHkG20uAxaHx1ijj+q1HWjHKv+ohxp/oK+77qyXkVj/l4QYHHTF3';

    private const ALGORITHM = 'HS256';

    private const TYPE = 'JWT';

    // Payload guardado apos uma validacao com sucesso
    private ?stdClass $payload;

    private string $iss;
    private string $aud;
    private string $sub;
    private int $duration;

    public function __construct()
    {
        $this->payload = null;
        $this->iss = 'http://localhost';
        $this->aud = 'http://localhost';
        $this->sub = 'acesso_sistema';
        // 30 dias
        $this->duration = 3600 * 24 * 30;
    }

    // Gera um token JWT a partir dos dados do participante logado.
    // Espera: $claims->id_participante, $claims->nome, $claims->email, $claims->perfil
    public function gerarToken(stdClass $claims): string
    {
        $headers = [
            'alg' => self::ALGORITHM,
            'typ' => self::TYPE
        ];

        $payload = [
            'iss' => $this->iss, // quem emitiu
            'aud' => $this->aud, // quem deve consumir
            'sub' => $this->sub, // finalidade do token
            'iat' => time(), // emitido em
            'nbf' => time(), // nao vale antes de
            'exp' => time() + $this->duration, // expiracao
            'jti' => bin2hex(random_bytes(16)), // id unico
            // Dados publicos do usuario (LEMBRETE: JWT nao criptografa!)
            'participante' => [
                'id_participante' => $claims->id_participante ?? null,
                'nome' => $claims->nome ?? null,
                'email' => $claims->email ?? null,
                'perfil' => $claims->perfil ?? 'comum',
            ],
        ];

        return JWT::encode($payload, self::KEY, self::ALGORITHM, null, $headers);
    }

    // Valida um token ("Bearer xxx" ou so o "xxx").
    // Devolve true/false e guarda o payload para getPayload().
    public function validateToken(string $stringToken): bool
    {
        if (empty($stringToken)) {
            return false;
        }

        $token = trim($stringToken);

        if (str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
        }

        // Formato basico: header.payload.signature
        $padrao = '/^[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+$/';
        if (preg_match($padrao, $token) !== 1) {
            return false;
        }

        try {
            // Confere assinatura, algoritmo, expiracao e nbf
            $payloadValido = JWT::decode($token, new Key(self::KEY, self::ALGORITHM));

            if (!isset($payloadValido->iss) || $payloadValido->iss !== $this->iss) {
                return false;
            }
            if (!isset($payloadValido->aud) || $payloadValido->aud !== $this->aud) {
                return false;
            }
            if (!isset($payloadValido->sub) || $payloadValido->sub !== $this->sub) {
                return false;
            }

            $this->payload = $payloadValido;
            return true;
        } catch (
            SignatureInvalidException |
            BeforeValidException |
            ExpiredException |
            InvalidArgumentException |
            DomainException |
            UnexpectedValueException |
            Exception $e
        ) {
            return false;
        }
    }

    public function getPayload(): ?stdClass
    {
        return $this->payload;
    }

    public function setPayload(?stdClass $payload): self
    {
        $this->payload = $payload;
        return $this;
    }

    public function limparPayload(): self
    {
        $this->payload = null;
        return $this;
    }

    public function getIss(): string
    {
        return $this->iss;
    }

    public function setIss(string $iss): self
    {
        $this->iss = $iss;
        return $this;
    }

    public function getAud(): string
    {
        return $this->aud;
    }

    public function setAud(string $aud): self
    {
        $this->aud = $aud;
        return $this;
    }

    public function getSub(): string
    {
        return $this->sub;
    }

    public function setSub(string $sub): self
    {
        $this->sub = $sub;
        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;
        return $this;
    }
}
