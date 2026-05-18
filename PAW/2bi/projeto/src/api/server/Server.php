<?php
namespace Api\Server;

use Slim\App;
use Psr\Http\Message\ServerRequestInterface;
use Api\Http\ErrorResponse;
use Api\Routes\CargoRouter;
use Api\Routes\FuncionarioRouter;
use Api\Routes\DepartamentoRouter;
use Api\Routes\DependenteRouter;
use Api\Routes\FolhaPagamentoRouter;

class Server
{
    private App $app;
    private CargoRouter $cargoRouter;
    private FuncionarioRouter $funcionarioRouter;
    private DepartamentoRouter $departamentoRouter;
    private DependenteRouter $dependenteRouter;
    private FolhaPagamentoRouter $folhaPagamentoRouter;

    public function __construct(
        App $app,
        CargoRouter $cargoRouter,
        FuncionarioRouter $funcionarioRouter,
        DepartamentoRouter $departamentoRouter,
        DependenteRouter $dependenteRouter,
        FolhaPagamentoRouter $folhaPagamentoRouter
    ) {
        $this->app = $app;
        $this->cargoRouter = $cargoRouter;
        $this->funcionarioRouter = $funcionarioRouter;
        $this->departamentoRouter = $departamentoRouter;
        $this->dependenteRouter = $dependenteRouter;
        $this->folhaPagamentoRouter = $folhaPagamentoRouter;

        $this->setupMiddlewares();
        $this->setupRoutes();
        $this->setupErrorHandling();
    }

    private function setupMiddlewares(): void
    {
        $this->app->addBodyParsingMiddleware();
        $this->app->add(function ($request, $handler) {
            $response = $handler->handle($request);
            return $response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        });
    }

    private function setupRoutes(): void
    {
        $this->departamentoRouter->routes($this->app);
        $this->cargoRouter->setupRoutes();
        $this->funcionarioRouter->setupRoutes();
        $this->dependenteRouter->routes($this->app);
        $this->folhaPagamentoRouter->routes($this->app);

        $this->app->get('/', function ($request, $response) {
            return $response->withHeader('Location', '/login.html')->withStatus(302);
        });
    }

    private function setupErrorHandling(): void
    {
        $errorMiddleware = $this->app->addErrorMiddleware(true, true, true);
        $errorMiddleware->setDefaultErrorHandler(
            function (ServerRequestInterface $request, \Throwable $exception)  {
                $response = new \Slim\Psr7\Response();
                $status = 500;
                if ($exception instanceof ErrorResponse) {
                    $payload = [
                        'success' => false,
                        'message' => $exception->getMessage(),
                        'error' => $exception->getError() ?? (object) [],
                    ];
                    $status = $exception->getHttpCode();
                } else {
                    $payload = [
                        'success' => false,
                        'message' => $exception->getMessage(),
                        'error' => [
                            'code' => $exception->getCode(),
                            'file' => $exception->getFile(),
                            'line' => $exception->getLine(),
                        ],
                    ];
                }
                $response->getBody()->write(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
            }
        );
    }

    public function run(): void
    {
        $this->app->run();
    }
}
