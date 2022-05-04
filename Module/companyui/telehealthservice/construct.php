<?php

namespace CompanyUI\Telehealthservice;

use Company\MVC\MvcContext;
use Company\MVC\MvcContext as MVC;
use Company\MVC\Router;
use Company\MVC\Router as R;

if (app()->isRest() == false) {
    $ctrl = "CompanyUI\\Telehealthservice\\ServiceCtrl";
    Router::getInstance()
        ->addRoute(new MvcContext('/:siteID/serviceList', 'GET', $ctrl, 'ServiceList'));
    Router::getInstance()
        ->addRoute(new MvcContext('/:siteID/serviceDir', 'GET', $ctrl, 'ServiceDir'));

}
