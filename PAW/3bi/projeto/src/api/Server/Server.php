<?php

declare(strict_types=1);

namespace Api\Server;

use Slim\App;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Api\Http\ErrorResponse;
use Api\Middlewares\AuthMiddleware;
use Api\Routes\AuthRouter;
use Api\Routes\LocalRouter;
use Api\Routes\EventoRouter;
use Api\Routes\ParticipanteRouter;
use Api\Routes\IngressoRouter;
use Api\Routes\CompraRouter;

/**
 * Server
 * Centraliza configuração do Slim 4: middlewares globais, rotas e tratamento de erros.
 * Domínio: Gestão de Eventos & Ingressos (novo domínio, distinto do RH do 2º bi).
 */
class Server
{
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->setupMiddlewares();
        $this->setupRoutes();
        $this->setupErrorHandling();
    }

    private function setupMiddlewares(): void
    {
        // Body parsing (JSON, form)
        $this->app->addBodyParsingMiddleware();

        // CORS
        $this->app->add(function (Request $request, RequestHandler $handler): Response {
            $response = $handler->handle($request);
            return $response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH')
                ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
                ->withHeader('Access-Control-Expose-Headers', 'Authorization');
        });

        // JWT Auth — protege todos os recursos exceto rotas públicas definidas no middleware
        $this->app->add(AuthMiddleware::class);

        // Routing middleware é obrigatório no Slim 4
        $this->app->addRoutingMiddleware();
    }

    private function setupRoutes(): void
    {
        // Healthcheck / info (público)
        $this->app->get('/', function (Request $request, Response $response) {
            $payload = json_encode([
                'success' => true,
                'message' => 'API Eventos — Gestão de Eventos & Ingressos (3BI JWT)',
                'version' => '3.0.0',
                'auth' => '/auth/login (POST email, senha -> token)',
                'endpoints' => [
                    'locais' => '/locais',
                    'eventos' => '/eventos',
                    'participantes' => '/participantes',
                    'ingressos' => '/ingressos',
                    'compras' => '/compras',
                ],
                'frontend' => '/login.html -> /dashboard.html',
                'docs' => '/docs/banco.sql',
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $response->getBody()->write($payload !== false ? $payload : '{}');
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        });

        // Preflight CORS
        $this->app->options('/{routes:.+}', function (Request $request, Response $response) {
            return $response->withStatus(200);
        });

        // Auth (público)
        AuthRouter::routes($this->app);

        // Entidades (protegidas por JWT)
        LocalRouter::routes($this->app);
        EventoRouter::routes($this->app);
        ParticipanteRouter::routes($this->app);
        IngressoRouter::routes($this->app);
        CompraRouter::routes($this->app);
    }

    private function setupErrorHandling(): void
    {
        $errorMiddleware = $this->app->addErrorMiddleware(true, true, true);

        $errorMiddleware->setDefaultErrorHandler(
            function (Request $request, \Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails) {
                $response = new \Slim\Psr7\Response();
                $status = 500;
                $errorPayload = null;

                if ($exception instanceof ErrorResponse) {
                    $status = $exception->getHttpCode();
                    $payload = [
                        'success' => false,
                        'message' => $exception->getMessage(),
                        'error'   => $exception->getError() ?? new \stdClass(),
                    ];
                } elseif ($exception instanceof HttpNotFoundException) {
                    $status = 404;
                    $payload = [
                        'success' => false,
                        'message' => 'Rota não encontrada.',
                        'error'   => ['path' => $request->getUri()->getPath()],
                    ];
                } else {
                    $payload = [
                        'success' => false,
                        'message' => $exception->getMessage(),
                        'error'   => [
                            'code' => $exception->getCode(),
                            'file' => $exception->getFile(),
                            'line' => $exception->getLine(),
                        ],
                    ];
                    error_log('[Server] Unhandled: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());
                }

                $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
            }
        );
    }

    public function run(): void
    {
        $this->app->run();
    }

    public function getApp(): App
    {
        return $this->app;
    }
}
