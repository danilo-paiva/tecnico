<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Api\Models\Local;
use Api\Models\Evento;
use Api\Models\Ingresso;
use Api\Models\Participante;
use Api\Models\Compra;

// Testes das validacoes dos Models (regras que a API usa no cadastro).
class ModelsTest extends TestCase
{
    public function testLocalValido(): void
    {
        $l = new Local();
        $l->setNome('Ginasio Central');
        $l->setEndereco('Rua A, 100');
        $l->setCapacidade(800);
        $this->assertEquals('Ginasio Central', $l->getNome());
        $this->assertEquals(800, $l->getCapacidade());
    }

    public function testLocalInvalido(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Local())->setNome('AB'); // menos de 3 letras
    }

    public function testLocalCapacidadeZeroInvalida(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Local())->setCapacidade(0);
    }

    public function testEventoValido(): void
    {
        $e = new Evento();
        $e->setTitulo('Show de Rock');
        $e->setDataEvento('2026-12-10 20:00:00');
        $e->setStatus('confirmado');
        $e->setIdLocal(2);
        $this->assertEquals('confirmado', $e->getStatus());
        $this->assertEquals('2026-12-10 20:00:00', $e->getDataEvento());
    }

    public function testEventoStatusInvalido(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Evento())->setStatus('adiado-para-sempre');
    }

    public function testEventoDataInvalida(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Evento())->setDataEvento('10/12/2026');
    }

    public function testIngressoValido(): void
    {
        $i = new Ingresso();
        $i->setTipo('Inteira');
        $i->setPreco(80.0);
        $i->setQuantidadeTotal(200);
        $i->setIdEvento(1);
        $this->assertEquals(80.0, $i->getPreco());
    }

    public function testIngressoPrecoNegativoInvalido(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Ingresso())->setPreco(-1);
    }

    public function testParticipanteValido(): void
    {
        $p = new Participante();
        $p->setNome('Diego');
        $p->setEmail('diego@email.com');
        $p->setCpf('444.555.666-77');
        $p->setSenha('secreta123');
        $this->assertEquals('diego@email.com', $p->getEmail());
    }

    public function testParticipanteEmailInvalido(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Participante())->setEmail('nao-e-email');
    }

    public function testParticipanteCpfInvalido(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Participante())->setCpf('123');
    }

    public function testParticipanteSenhaCurtaInvalida(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Participante())->setSenha('123');
    }

    public function testParticipanteJsonNaoVazaSenha(): void
    {
        $p = new Participante();
        $p->setNome('Diego Souza');
        $p->setEmail('diego@email.com');
        $p->setCpf('444.555.666-77');
        $p->setSenha('secreta123');
        $json = $p->jsonSerialize();
        $this->assertArrayNotHasKey('senha', $json);
        $this->assertEquals('Diego Souza', $json['nome']);
        $this->assertEquals('comum', $json['perfil']); // perfil padrao
    }

    public function testCompraValida(): void
    {
        $c = new Compra();
        $c->setQuantidade(2);
        $c->setValorTotal(160.0);
        $c->setIdParticipante(1);
        $c->setIdIngresso(1);
        $this->assertEquals(2, $c->getQuantidade());
    }

    public function testCompraQuantidadeZeroInvalida(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Compra())->setQuantidade(0);
    }

    public function testParticipantePerfilAdministrador(): void
    {
        $p = new Participante();
        $p->setPerfil('administrador');
        $this->assertEquals('administrador', $p->getPerfil());
        $this->assertEquals('administrador', $p->jsonSerialize()['perfil']);
    }

    public function testParticipantePerfilInvalido(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Participante())->setPerfil('dono');
    }
}
