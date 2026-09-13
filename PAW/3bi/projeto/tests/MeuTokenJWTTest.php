<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Api\Http\MeuTokenJWT;

// Testes do JWT (aula paw03x01): gerar, validar, Bearer, expirado e adulterado.
class MeuTokenJWTTest extends TestCase
{
    private function claims(): \stdClass
    {
        $c = new \stdClass();
        $c->id_participante = 1;
        $c->nome = 'Ana Souza';
        $c->email = 'ana@email.com';
        return $c;
    }

    public function testGerarTokenTemTresPartes(): void
    {
        $token = (new MeuTokenJWT())->gerarToken($this->claims());
        $this->assertEquals(3, count(explode('.', $token)));
    }

    public function testTokenValidoPassa(): void
    {
        $jwt = new MeuTokenJWT();
        $token = $jwt->gerarToken($this->claims());
        $this->assertTrue($jwt->validateToken($token));
        $payload = $jwt->getPayload();
        $this->assertNotNull($payload);
        $this->assertEquals('ana@email.com', $payload->participante->email);
        $this->assertEquals(1, $payload->participante->id_participante);
    }

    public function testAceitaPrefixoBearer(): void
    {
        $jwt = new MeuTokenJWT();
        $token = $jwt->gerarToken($this->claims());
        $this->assertTrue($jwt->validateToken('Bearer ' . $token));
    }

    public function testTokenVazioOuLixoFalha(): void
    {
        $jwt = new MeuTokenJWT();
        $this->assertFalse($jwt->validateToken(''));
        $this->assertFalse($jwt->validateToken('nao-e-um-token'));
        $this->assertFalse($jwt->validateToken('a.b.c'));
    }

    public function testTokenAdulteradoFalha(): void
    {
        $jwt = new MeuTokenJWT();
        $token = $jwt->gerarToken($this->claims());
        [$h, $p, $s] = explode('.', $token);
        // Troca o payload por outro texto valido em base64url (assinatura quebra)
        $adulterado = $h . '.' . rtrim(strtr(base64_encode('{"x":1}'), '+/', '-_'), '=') . '.' . $s;
        $this->assertFalse($jwt->validateToken($adulterado));
    }

    public function testTokenExpiradoFalha(): void
    {
        $jwt = new MeuTokenJWT();
        $jwt->setDuration(-10); // ja nasceu expirado
        $token = $jwt->gerarToken($this->claims());
        $this->assertFalse((new MeuTokenJWT())->validateToken($token));
    }

    public function testEmissorDiferenteFalha(): void
    {
        $outro = (new MeuTokenJWT())->setIss('http://outro-servidor');
        $token = $outro->gerarToken($this->claims());
        $this->assertFalse((new MeuTokenJWT())->validateToken($token));
    }
}
