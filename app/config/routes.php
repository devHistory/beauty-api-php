<?php

/**
 * @name    /ROOT/app/config/routes.php
 * @see     /ROOT/docs/examples/routes.php
 * @link    https://docs.phalconphp.com/zh/3.3/routing
 *
 * Add RESTFUL Resource
 * $resource = new Providers\System\Route($router);
 * $resource->addResource('/products', 'V1\Products');
 * $resource->addResource('/news', 'V1\News', '{id:[0-9]{1,10}}')->only('show');
 */

use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Group;
use App\Providers;


$router = new Router(false);
$router->removeExtraSlashes(true);

$router->notFound(['controller' => 'default', 'action' => 'notFound']);
$router->add('/', ['controller' => 'default', 'action' => 'index']);

$router->add('/access', ['controller' => 'V1\Access', 'action' => 'session']);
$router->add('/init/update', ['controller' => 'V1\Init', 'action' => 'update']);
$router->add('/login/([a-z]{2,10})', ['controller' => 'V1\Login', 'action' => 'platform', 'type' => 1]);
$router->add('/login/device', ['controller' => 'V1\Login', 'action' => 'device']);
$router->add('/login', ['controller' => 'V1\Login', 'action' => 'login']);
$router->add('/register', ['controller' => 'V1\Login', 'action' => 'register']);

/**
 * Resources
 * Allow Action: 'index', 'store', 'show', 'update', 'destroy'
 */
$resource = new Providers\System\Route($router);
$resource->setIdFormat('{id:[a-f0-9]{24}}');
$resource->addResource('/friends', 'V1\Friends')->only('index', 'store', 'destroy');
$resource->addResource('/relation', 'V1\Relation')->only('index', 'store', 'destroy');
$resource->addResource('/posts', 'V1\Posts')->only('show', 'store', 'destroy');
$resource->addResource('/comments', 'V1\Comments')->only('store', 'destroy');
$resource->addResource('/favorites', 'V1\Favorites')->only('index', 'store', 'destroy');

$router->addPost('/report', ['controller' => 'V1\Report', 'action' => 'report']);
$router->addPost('/report/feedback', ['controller' => 'V1\Report', 'action' => 'feedback']);
$router->addPost('/setting/name', ['controller' => 'V1\Setting', 'action' => 'name']);
$router->addPost('/setting/password', ['controller' => 'V1\Setting', 'action' => 'password']);
$router->addPost('/setting/attribute', ['controller' => 'V1\Setting', 'action' => 'attribute']);
$router->addPost('/files/access', ['controller' => 'V1\Files', 'action' => 'access']);
$router->addPost('/zone/nearby', ['controller' => 'V1\Zone', 'action' => 'nearby']);

$router->setDefaultModule('m1');
$router->setDefaultNamespace('App\Http\Controllers');
$router->setDefaultController('index');
$router->setDefaultAction('index');

return $router;
