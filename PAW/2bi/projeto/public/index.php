<?php

require __DIR__ . '/../vendor/autoload.php';

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Api\Database\MysqlDatabase;
use Api\Server\Server;

$builder = new ContainerBuilder();
$builder->useAutowiring(true);

$mysqlDatabase = new MysqlDatabase([
    'host' => 'localhost',
    'user' => 'root',
    'password' => '',
    'database' => 'rh_db',
]);

$container = $builder->build();
$container->set(MysqlDatabase::class, $mysqlDatabase);

AppFactory::setContainer($container);
$app = AppFactory::create();
$container->set(\Slim\App::class, $app);

$server = $container->get(Server::class);
$server->run();
