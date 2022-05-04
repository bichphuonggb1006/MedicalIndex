<?php

namespace CompanyUI\User;

use Company\MVC\MvcContext;
use Company\MVC\MvcContext as MVC;
use Company\MVC\Router;
use Company\MVC\Router as R;

if (app()->isRest() == false) {
    $userCtrl = "CompanyUI\\User\\UserCtrl";
//đăng ký url mới
    Router::getInstance()
            ->addRoute(new MvcContext('/:siteID/users', 'GET', $userCtrl, 'userList'));
    Router::getInstance()
            ->addRoute(new MvcContext('/:siteID/roles', 'GET', $userCtrl, 'roleList'));

    Router::getInstance()
            ->addRoute(new MvcContext('/auth/login', 'GET', $userCtrl, 'login'));

    Router::getInstance()
            ->addRoute(new MvcContext('/auth/logout', 'GET', $userCtrl, 'logout'));

    // ghép tài khoản
    Router::getInstance()
        ->addRoute(new MvcContext('/:siteID/users/sites/merge', 'GET', $userCtrl, 'mergeSite'));
}

