<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Api\Http\MeuTokenJWT;
use Api\Http\ErrorResponse;
use Api\Middlewares\Participante\ValidateParticipanteToken;
use Api\Middlewares\Participante\ValidateParticipanteLoginBody;

// Handler falso: so devolve 200 (ou o atributo jwtPayload, para conferir).
class EchoHandler implements Handler
{
    public ?Request $recebida = null;

    public function handle(Request $request): ResponseInterface
    {
        $this->recebida = $request;
        $response = new Response();
        $payload = $request->getAttribute('jwtPayload');
        $response->getBody()->write(json_encode(['ok' => true, 'temPayload' => $payload !== null]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}

class MiddlewaresTest extends TestCase
{
    private function pedido(string $metodo = 'GET', string $body = '', array $headers = []): Request
    {
        $f = new ServerRequestFactory();
        $req = $f->createServerRequest($metodo, 'http://localhost/teste');
        if ($body !== '') {
            $stream = (new StreamFactory())->createStream($body);
            $req = $req->withBody($stream);
        }
        foreach ($headers as $nome => $valor) {
            $req = $req->withHeader($nome, $valor);
        }
        return $req;
    }

    private function tokenValido(): string
    {
        $claims = new \stdClass();
        $claims->id_participante = 7;
        $claims->nome = 'Teste';
        $claims->email = 'teste@email.com';
        return (new MeuTokenJWT())->gerarToken($claims);
    }

    // ---- ValidateParticipanteToken ----

    public function testTokenValidoLiberaEPassaPayload(): void
    {
        $mw = new ValidateParticipanteToken();
        $handler = new EchoHandler();
        $req = $this->pedido('GET', '', ['Authorization' => 'Bearer ' . $this->tokenValido()]);
        $resposta = $mw->process($req, $handler);
        $this->assertEquals(200, $resposta->getStatusCode());
        $this->assertNotNull($handler->recebida->getAttribute('jwtPayload'));
    }

    public function testSemTokenDa401(): void
    {
        $this->expectException(ErrorResponse::class);
        try {
            (new ValidateParticipanteToken())->process($this->pedido(), new EchoHandler());
        } catch (ErrorResponse $e) {
            $this->assertEquals(401, $e->getHttpCode());
            throw $e;
        }
    }

    public function testFormatoErradoDa401(): void
    {
        $this->expectException(ErrorResponse::class);
        try {
            $req = $this->pedido('GET', '', ['Authorization' => $this->tokenValido()]);
            (new ValidateParticipanteToken())->process($req, new EchoHandler());
        } catch (ErrorResponse $e) {
            $this->assertEquals(401, $e->getHttpCode());
            throw $e;
        }
    }

    public function testTokenFalsoDa401(): void
    {
        $this->expectException(ErrorResponse::class);
        try {
            $req = $this->pedido('GET', '', ['Authorization' => 'Bearer token.falso.aqui']);
            (new ValidateParticipanteToken())->process($req, new EchoHandler());
        } catch (ErrorResponse $e) {
            $this->assertEquals(401, $e->getHttpCode());
            throw $e;
        }
    }

    // ---- ValidateParticipanteLoginBody ----

    public function testLoginBodyValidoPassa(): void
    {
        $mw = new ValidateParticipanteLoginBody();
        $body = json_encode(['participante' => ['email' => 'ana@email.com', 'senha' => '123456']]);
        $resposta = $mw->process($this->pedido('POST', $body), new EchoHandler());
        $this->assertEquals(200, $resposta->getStatusCode());
    }

    public function testLoginSemEmailDa400(): void
    {
        $this->expectException(ErrorResponse::class);
        try {
            $mw = new ValidateParticipanteLoginBody();
            $body = json_encode(['participante' => ['senha' => '123456']]);
            $mw->process($this->pedido('POST', $body), new EchoHandler());
        } catch (ErrorResponse $e) {
            $this->assertEquals(400, $e->getHttpCode());
            throw $e;
        }
    }

    public function testLoginEmailInvalidoDa400(): void
    {
        $this->expectException(ErrorResponse::class);
        try {
            $mw = new ValidateParticipanteLoginBody();
            $body = json_encode(['participante' => ['email' => 'x', 'senha' => '123456']]);
            $mw->process($this->pedido('POST', $body), new EchoHandler());
        } catch (ErrorResponse $e) {
            $this->assertEquals(400, $e->getHttpCode());
            throw $e;
        }
    }

    public function testLoginSemSenhaDa400(): void
    {
        $this->expectException(ErrorResponse::class);
        try {
            $mw = new ValidateParticipanteLoginBody();
            $body = json_encode(['participante' => ['email' => 'ana@email.com']]);
            $mw->process($this->pedido('POST', $body), new EchoHandler());
        } catch (ErrorResponse $e) {
            $this->assertEquals(400, $e->getHttpCode());
            throw $e;
        }
    }
}
