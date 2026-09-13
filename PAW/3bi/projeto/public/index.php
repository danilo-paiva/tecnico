<?php

// Servidor embutido do PHP: entrega arquivos estaticos sem passar pelo Slim
if (PHP_SAPI === 'cli-server') {
    $arquivo = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($arquivo !== __DIR__ && is_file($arquivo)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Api\Database\MysqlDatabase;
use Api\Server\Server;

// Container de injecao de dependencia (resolve as classes sozinho)
$builder = new ContainerBuilder();
$builder->useAutowiring(true);

// Banco configurado a parte porque precisa de host/usuario/senha
$mysqlDatabase = new MysqlDatabase([
    'host' => 'localhost',
    'user' => 'root',
    'password' => '',
    'database' => 'eventos_db'
]);

$container = $builder->build();
$container->set(MysqlDatabase::class, $mysqlDatabase);

AppFactory::setContainer($container);
$app = AppFactory::create();

// A mesma instancia do Slim deve ser usada nos roteadores
$container->set(\Slim\App::class, $app);

$server = $container->get(Server::class);
$server->run();
