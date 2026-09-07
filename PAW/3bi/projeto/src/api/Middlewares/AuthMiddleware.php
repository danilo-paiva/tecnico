<?php

declare(strict_types=1);

namespace Api\Middlewares;

use Api\Http\ErrorResponse;
use Api\Config\JwtConfig;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{
    // Rotas públicas que não exigem token
    private const PUBLIC_PATHS = [
        '/',
        '/auth/login',
        '/auth/register',
        '/login.html',
        '/register.html',
        '/index.html',
        '/css/style.css',
        '/js/api.js',
        '/js/auth.js',
    ];

    private const PUBLIC_PREFIXES = [
        '/css/',
        '/js/',
        '/img/',
        '/assets/',
    ];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $method = strtoupper($request->getMethod());

        // Libera OPTIONS (preflight CORS)
        if ($method === 'OPTIONS') {
            return $handler->handle($request);
        }

        // Normaliza path: remove trailing slash exceto root
        $normalized = rtrim($path, '/');
        if ($normalized === '') $normalized = '/';

        // Verifica se é rota pública exata
        if (in_array($path, self::PUBLIC_PATHS, true) || in_array($normalized, self::PUBLIC_PATHS, true)) {
            return $handler->handle($request);
        }

        // Verifica prefixos públicos
        foreach (self::PUBLIC_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $handler->handle($request);
            }
        }

        // Libera arquivos estáticos por extensão
        if (preg_match('/\.(html|css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|map)$/i', $path)) {
            return $handler->handle($request);
        }

        // /auth/me é protegido (não libera genérico)
        // apenas /auth/login e /auth/register já estão em PUBLIC_PATHS

        // Exige token
        $authHeader = $request->getHeaderLine('Authorization');
        if ($authHeader === '') {
            $authHeader = $request->getHeaderLine('authorization');
        }

        if ($authHeader === '' || !str_starts_with($authHeader, 'Bearer ')) {
            throw new ErrorResponse('Token JWT não fornecido. Faça login.', 401, ['header' => 'Authorization: Bearer <token> requerido']);
        }

        $token = trim(substr($authHeader, 7));
        if ($token === '') {
            throw new ErrorResponse('Token JWT vazio.', 401);
        }

        try {
            $decoded = JWT::decode($token, new Key(JwtConfig::SECRET, JwtConfig::ALGO));
            // Injeta payload no request para uso nos controllers
            $request = $request->withAttribute('jwt', $decoded);
            $request = $request->withAttribute('usuario', [
                'id' => $decoded->sub ?? null,
                'email' => $decoded->email ?? null,
                'nome' => $decoded->nome ?? null,
            ]);
        } catch (\Firebase\JWT\ExpiredException $e) {
            throw new ErrorResponse('Token expirado. Faça login novamente.', 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            throw new ErrorResponse('Assinatura do token inválida.', 401);
        } catch (\Throwable $e) {
            throw new ErrorResponse('Token inválido: ' . $e->getMessage(), 401);
        }

        return $handler->handle($request);
    }

    // Para compatibilidade com ->add(AuthMiddleware::class) que pode chamar __invoke
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->process($request, $handler);
    }
}
