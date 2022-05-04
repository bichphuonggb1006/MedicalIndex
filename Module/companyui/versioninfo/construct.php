<?php

namespace CompanyUI\VersionInfo;

use Company\MVC\MvcContext;
use Company\MVC\Router;

if (app()->isRest() == false) {
    $ctrl = "CompanyUI\\VersionInfo\\VersionInfoCtrl";
//đăng ký url mới
    Router::getInstance()
            ->addRoute(new MvcContext('/:siteID/versionInfo', 'GET', $ctrl, 'versionInfo'));
}