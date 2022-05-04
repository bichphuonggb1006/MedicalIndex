<?php

namespace Company\Auth;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;

$app = \Company\MVC\Bootstrap::getInstance();
if (isset($app->config['session']) && isset($app->config['session']['updateInterval'])) {
    Auth::config($app->config['session']['updateInterval']);
}

Auth::registerAuthMethod(new LocalDBAuth);

$router = R::getInstance();
if ($router->requestUriHas('auth')) {
    $ctrl = "\\Company\\Auth\\AuthCtrl";
    $router->addRoute(new MVC("/rest/auth", 'POST', $ctrl, "auth"));
    $router->addRoute(new MVC("/rest/auth", 'GET', $ctrl, "renewSession"));
    $router->addRoute(new MVC("/rest/auth", 'DELETE', $ctrl, "logout"));


    //test
    $testCtrl = "\\Company\\Auth\\TestCtrl";
    $router->addRoute(new MVC("/rest/auth/test", 'GET', $testCtrl, "test"));
    $router->addRoute(new MVC("/rest/auth/test2", 'GET', $testCtrl, "test2"));
}

