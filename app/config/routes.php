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

$router->add('/keys/public', ['controller' => 'V1\Keys', 'action' => 'public']);
$router->add('/keys/secrets', ['controller' => 'V1\Keys', 'action' => 'secrets']);
$router->add('/login/([a-z]{2,10})', ['controller' => 'V1\Login', 'action' => 'platform', 'type' => 1]);
$router->add('/login/device', ['controller' => 'V1\Login', 'action' => 'device']);
$router->add('/login', ['controller' => 'V1\Login', 'action' => 'login']);
$router->add('/register', ['controller' => 'V1\Login', 'action' => 'register']);
$router->add('/init', ['controller' => 'V1\Init', 'action' => 'sync']);

// relation
$relation = new Group(['controller' => 'V1\Relation']);
$relation->setPrefix('/relation');
$relation->addGet('/friends', ['action' => 'getFriends']);
$relation->addPost('/friends', ['action' => 'addFriends']);
$relation->addDelete('/friends', ['action' => 'delFriends']);
$relation->addPost('/follow', ['action' => 'addFollow']);
$relation->addDelete('/follow', ['action' => 'delFollow']);
$relation->addGet('/following', ['action' => 'following']);
$relation->addGet('/followers', ['action' => 'followers']);
$router->mount($relation);

$router->addGet('/posts/([a-f0-9]{24})', ['controller' => 'V1\Posts', 'action' => 'get', 'postId' => 1]);
$router->addPost('/posts', ['controller' => 'V1\Posts', 'action' => 'add']);
$router->addDelete('/posts/([a-f0-9]{24})', ['controller' => 'V1\Posts', 'action' => 'del', 'postId' => 1]);
$router->addPost('/comments', ['controller' => 'V1\Comments', 'action' => 'add']);
$router->addDelete('/comments/([a-f0-9]{24})', ['controller' => 'V1\Comments', 'action' => 'del', 'commentId' => 1]);
$router->addGet('/favorites', ['controller' => 'V1\Favorites', 'action' => 'get']);
$router->addPost('/favorites', ['controller' => 'V1\Favorites', 'action' => 'add']);
$router->addDelete('/favorites/([a-f0-9]{24})', ['controller' => 'V1\Favorites', 'action' => 'del', 'id' => 1]);
$router->addPost('/report', ['controller' => 'V1\Report', 'action' => 'report']);
$router->addPost('/report/feedback', ['controller' => 'V1\Report', 'action' => 'feedback']);
$router->addPost('/setting/name', ['controller' => 'V1\Setting', 'action' => 'name']);
$router->addPost('/setting/password', ['controller' => 'V1\Setting', 'action' => 'password']);
$router->addPost('/setting/attribute', ['controller' => 'V1\Setting', 'action' => 'attribute']);

$router->setDefaultModule('m1');
$router->setDefaultNamespace('App\Http\Controllers');
$router->setDefaultController('index');
$router->setDefaultAction('index');

return $router;
