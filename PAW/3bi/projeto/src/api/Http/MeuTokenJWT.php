<?php

declare(strict_types=1);

namespace Api\Http;

use Api\Config\JwtConfig;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use DomainException;
use InvalidArgumentException;
use UnexpectedValueException;
use stdClass;
use Exception;

/**
 * MeuTokenJWT
 * Padrão da Aula 1 (paw03x01 slides 38-41): header.payload.signature (HS256).
 * Centraliza geração e validação do JWT. Não alterar sem necessidade.
 */
class MeuTokenJWT
{
    private ?stdClass $payload;
    private string $iss;
    private string $aud;
    private string $sub;
    private int $duration;

    public function __construct()
    {
        $this->payload = null;
        $this->iss = JwtConfig::ISSUER;
        $this->aud = JwtConfig::AUDIENCE;
        $this->sub = JwtConfig::SUBJECT;
        $this->duration = JwtConfig::EXPIRATION_SECONDS;
    }

    /**
     * Gera token a partir das claims do usuário autenticado.
     * Mantém claims registradas (iss/aud/sub/iat/nbf/exp/jti) + bloco
     * `participante` (padrão da aula: funcionario{name,email,role,id}).
     */
    public function gerarToken(stdClass $claims): string
    {
        $headers = [
            'alg' => JwtConfig::ALGO,
            'typ' => JwtConfig::TYPE,
        ];

        $payload = [
            'iss' => $this->iss,
            'aud' => $this->aud,
            'sub' => $this->sub,
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + $this->duration,
            'jti' => bin2hex(random_bytes(16)),
            // Claims planas (compat: front lê email/nome/sub numérico via /auth/me)
            'idParticipante' => $claims->idParticipante ?? null,
            'name' => $claims->name ?? null,
            'email' => $claims->email ?? null,
            'role' => $claims->role ?? 'participante',
            // Bloco aninhado no padrão da aula (funcionario -> participante)
            'participante' => [
                'name' => $claims->name ?? null,
                'email' => $claims->email ?? null,
                'role' => $claims->role ?? 'participante',
                'idParticipante' => $claims->idParticipante ?? null,
            ],
        ];

        return JWT::encode($payload, JwtConfig::SECRET, JwtConfig::ALGO, null, $headers);
    }

    /**
     * Valida token (aceita com ou sem prefixo "Bearer ").
     * Confere formato header.payload.signature + iss/aud/sub.
     */
    public function validateToken(string $stringToken): bool
    {
        if (empty($stringToken)) {
            return false;
        }
        $token = trim($stringToken);
        if (str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
        }
        $padrao = '/^[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+$/';
        if (preg_match($padrao, $token) !== 1) {
            return false;
        }
        try {
            $payloadValido = JWT::decode($token, new Key(JwtConfig::SECRET, JwtConfig::ALGO));
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
        } catch (SignatureInvalidException | BeforeValidException | ExpiredException | InvalidArgumentException | DomainException | UnexpectedValueException | Exception $e) {
            return false;
        }
    }

    public function getPayload(): ?stdClass
    {
        return $this->payload;
    }
}
