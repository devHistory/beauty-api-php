<?php

/**
 * @name    /ROOT/app/config/routes.php
 * @see     /ROOT/docs/examples/routes.php
 * @link    https://docs.phalconphp.com/zh/3.3/routing
 */

use Phalcon\Mvc\Router;


$router = new Router(false);
$router->removeExtraSlashes(true);

$router->notFound(['controller' => 'default', 'action' => 'notFound']);
$router->add('/', ['controller' => 'default', 'action' => 'index']);

$router->add('/keys/public', ['controller' => 'keys', 'action' => 'public']);
$router->add('/keys/secrets', ['controller' => 'keys', 'action' => 'secrets']);
$router->add('/login/([a-z]{2,10})', ['controller' => 'login', 'action' => 'platform', 'type' => 1]);
$router->add('/login/device', ['controller' => 'login', 'action' => 'device']);
$router->add('/login', ['controller' => 'login', 'action' => 'account']);
$router->add('/register', ['controller' => 'register', 'action' => 'account']);

$router->setDefaultModule('v1');
$router->setDefaultNamespace('App\Http\Controllers');
$router->setDefaultController('index');
$router->setDefaultAction('index');

return $router;
