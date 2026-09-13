<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Api\Services\ParticipanteService;
use Api\DAO\ParticipanteDAO;
use Api\DAO\CompraDAO;
use Api\Models\Participante;
use Api\Http\MeuTokenJWT;
use Api\Http\ErrorResponse;

// Testa o login SEM banco: o DAO e simulado (mock) com senha "123456".
class ParticipanteServiceLoginTest extends TestCase
{
    private function daoComUsuario(string $email = 'ana@email.com', string $senha = '123456'): ParticipanteDAO
    {
        $dao = $this->createMock(ParticipanteDAO::class);
        $dao->method('verificarLogin')->willReturnCallback(
            function (Participante $tentativa) use ($email, $senha) {
                if ($tentativa->getEmail() === $email && $tentativa->getSenha() === $senha) {
                    $p = new Participante();
                    $p->setIdParticipante(1);
                    $p->setNome('Ana Souza');
                    $p->setEmail($email);
                    $p->setCpf('111.222.333-44');
                    $p->setPerfil('administrador');
                    return $p;
                }
                return null;
            }
        );
        return $dao;
    }

    public function testLoginCertoDevolveParticipanteETokenValido(): void
    {
        $service = new ParticipanteService(
            $this->daoComUsuario(),
            $this->createMock(CompraDAO::class)
        );

        $resultado = $service->loginService(['email' => 'ana@email.com', 'senha' => '123456']);

        $this->assertArrayHasKey('participante', $resultado);
        $this->assertArrayHasKey('token', $resultado);
        $this->assertEquals('ana@email.com', $resultado['participante']->getEmail());

        // O token devolvido precisa passar na validacao oficial
        $jwt = new MeuTokenJWT();
        $this->assertTrue($jwt->validateToken($resultado['token']));
        $this->assertEquals(1, $jwt->getPayload()->participante->id_participante);
        $this->assertEquals('administrador', $jwt->getPayload()->participante->perfil);
    }

    public function testSenhaErradaDa401(): void
    {
        $this->expectException(ErrorResponse::class);
        $service = new ParticipanteService(
            $this->daoComUsuario(),
            $this->createMock(CompraDAO::class)
        );
        try {
            $service->loginService(['email' => 'ana@email.com', 'senha' => 'errada']);
        } catch (ErrorResponse $e) {
            $this->assertEquals(401, $e->getHttpCode());
            throw $e;
        }
    }

    public function testEmailDesconhecidoDa401(): void
    {
        $this->expectException(ErrorResponse::class);
        $service = new ParticipanteService(
            $this->daoComUsuario(),
            $this->createMock(CompraDAO::class)
        );
        try {
            $service->loginService(['email' => 'ninguem@email.com', 'senha' => '123456']);
        } catch (ErrorResponse $e) {
            $this->assertEquals(401, $e->getHttpCode());
            throw $e;
        }
    }
}
