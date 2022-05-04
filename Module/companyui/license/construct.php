<?php

namespace CompanyUI\User;

use Company\MVC\MvcContext;
use Company\MVC\Router;

if (app()->isRest() == false) {
    $ctrl = "CompanyUI\\License\\LicenseCtrl";

    //đăng ký url mới
    Router::getInstance()
            ->addRoute(new MvcContext('/:siteID/license', 'GET', $ctrl, 'licenseList'));
}


