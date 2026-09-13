<?php

namespace Api\Server;

use Slim\App;
use Psr\Http\Message\ServerRequestInterface;
use Api\Http\ErrorResponse;
use Api\Routes\AuthRouter;
use Api\Routes\LocalRouter;
use Api\Routes\EventoRouter;
use Api\Routes\IngressoRouter;
use Api\Routes\ParticipanteRouter;
use Api\Routes\CompraRouter;

// Liga os middlewares, as rotas e o tratamento de erros da API.
// Rotas publicas: POST /login, POST /participantes/login e GET /.
// Todo o resto exige "Authorization: Bearer <token>".
class Server
{
    private App $app;
    private AuthRouter $authRouter;
    private LocalRouter $localRouter;
    private EventoRouter $eventoRouter;
    private IngressoRouter $ingressoRouter;
    private ParticipanteRouter $participanteRouter;
    private CompraRouter $compraRouter;

    public function __construct(
        App $app,
        AuthRouter $authRouter,
        LocalRouter $localRouter,
        EventoRouter $eventoRouter,
        IngressoRouter $ingressoRouter,
        ParticipanteRouter $participanteRouter,
        CompraRouter $compraRouter
    ) {
        $this->app = $app;
        $this->authRouter = $authRouter;
        $this->localRouter = $localRouter;
        $this->eventoRouter = $eventoRouter;
        $this->ingressoRouter = $ingressoRouter;
        $this->participanteRouter = $participanteRouter;
        $this->compraRouter = $compraRouter;

        $this->setupMiddlewares();
        $this->setupRoutes();
        $this->setupErrorHandling();
    }

    private function setupMiddlewares(): void
    {
        // Converte JSON do body em array automaticamente
        $this->app->addBodyParsingMiddleware();

        // Libera a API para front-ends de outras origens (CORS)
        $this->app->add(function ($request, $handler) {
            // Preflight do navegador: responde 204 sem passar pelas rotas
            if (strtoupper($request->getMethod()) === 'OPTIONS') {
                $response = new \Slim\Psr7\Response();
                return $response
                    ->withHeader('Access-Control-Allow-Origin', '*')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                    ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
                    ->withStatus(204);
            }
            $response = $handler->handle($request);
            return $response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        });
    }

    private function setupRoutes(): void
    {
        $this->authRouter->setupRoutes();
        $this->localRouter->setupRoutes();
        $this->eventoRouter->setupRoutes();
        $this->ingressoRouter->setupRoutes();
        $this->participanteRouter->setupRoutes();
        $this->compraRouter->setupRoutes();

        $this->app->get('/', function ($request, $response) {
            $payload = [
                'success' => true,
                'message' => 'API de gestao de eventos e ingressos',
                'data' => [
                    'recursos' => ['locais', 'eventos', 'ingressos', 'participantes', 'compras'],
                    'auth' => 'POST /login com {"participante": {"email", "senha"}}'
                ]
            ];
            $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        });
    }

    private function setupErrorHandling(): void
    {
        $errorMiddleware = $this->app->addErrorMiddleware(true, true, true);
        $errorMiddleware->setDefaultErrorHandler(
            function (ServerRequestInterface $request, \Throwable $exception) {
                $response = new \Slim\Psr7\Response();

                if ($exception instanceof ErrorResponse) {
                    $status = $exception->getHttpCode();
                    $payload = [
                        'success' => false,
                        'message' => $exception->getMessage(),
                        'error' => $exception->getError() ?? (object) []
                    ];
                } elseif ($exception instanceof \InvalidArgumentException) {
                    $status = 400;
                    $payload = [
                        'success' => false,
                        'message' => 'Dados invalidos',
                        'error' => ['message' => $exception->getMessage()]
                    ];
                } else {
                    $status = 500;
                    $payload = [
                        'success' => false,
                        'message' => $exception->getMessage(),
                        'error' => ['code' => $exception->getCode()]
                    ];
                }

                $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE));
                return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
            }
        );
    }

    public function run(): void
    {
        $this->app->run();
    }
}
