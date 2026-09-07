<?php

declare(strict_types=1);

// Para php -S: serve arquivos estáticos diretamente (css, js, html, img)
if (PHP_SAPI === 'cli-server') {
    $urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $filePath = __DIR__ . $urlPath;
    if ($urlPath !== '/' && is_file($filePath)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Api\Database\MysqlDatabase;
use Api\Server\Server;

// Container com autowiring
$builder = new ContainerBuilder();
$builder->useAutowiring(true);
$builder->useAttributes(false);

// Config do banco — ajuste se necessário (XAMPP padrão)
$mysqlDatabase = new MysqlDatabase([
    'host'     => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'user'     => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? '',
    'database' => $_ENV['DB_NAME'] ?? 'eventos_db',
    'port'     => (int)($_ENV['DB_PORT'] ?? 3306),
]);

$container = $builder->build();
$container->set(MysqlDatabase::class, $mysqlDatabase);

AppFactory::setContainer($container);
$app = AppFactory::create();
$container->set(Slim\App::class, $app);

$server = $container->get(Server::class);
$server->run();
