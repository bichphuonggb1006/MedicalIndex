<?php

namespace CompanyUI\User;

use Company\MVC\MvcContext;
use Company\MVC\Router;

if (app()->isRest() == false) {
    $ctrl = "CompanyUI\\Module\\ModuleCtrl";
//đăng ký url mới
    Router::getInstance()
            ->addRoute(new MvcContext('/:siteID/modules', 'GET', $ctrl, 'moduleList'));
}


