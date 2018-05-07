<?php

/**
 * @name    /ROOT/app/config/routes.php
 * @see     /ROOT/docs/examples/routes.php
 * @link    https://docs.phalconphp.com/zh/3.3/routing
 */

use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Group;


$router = new Router(false);
$router->removeExtraSlashes(true);

$router->notFound(['controller' => 'default', 'action' => 'notFound']);
$router->add('/', ['controller' => 'default', 'action' => 'index']);

$router->add('/keys/public', ['controller' => 'keys', 'action' => 'public']);
$router->add('/keys/secrets', ['controller' => 'keys', 'action' => 'secrets']);
$router->add('/login/([a-z]{2,10})', ['controller' => 'login', 'action' => 'platform', 'type' => 1]);
$router->add('/login/device', ['controller' => 'login', 'action' => 'device']);
$router->add('/login', ['controller' => 'login', 'action' => 'login']);
$router->add('/register', ['controller' => 'login', 'action' => 'register']);

// relation
$relation = new Group(['controller' => 'relation']);
$relation->setPrefix('/relation');
$relation->addGet('/friends', ['action' => 'getFriends']);
$relation->addPost('/friends', ['action' => 'addFriends']);
$relation->addDelete('/friends', ['action' => 'delFriends']);
$relation->addPost('/follow', ['action' => 'addFollow']);
$relation->addDelete('/follow', ['action' => 'delFollow']);
$relation->addGet('/following', ['action' => 'following']);
$relation->addGet('/followers', ['action' => 'followers']);
$router->mount($relation);

$router->setDefaultModule('v1');
$router->setDefaultNamespace('App\Http\Controllers');
$router->setDefaultController('index');
$router->setDefaultAction('index');

return $router;
