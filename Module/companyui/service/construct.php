<?php

namespace CompanyUI\Service;

use Company\MVC\MvcContext;
use Company\MVC\Router;

if (app()->isRest() == false) {
    $ctrl = "CompanyUI\\Service\\ServiceCtrl";

    Router::getInstance()
            ->addRoute(new MvcContext('/:siteID/services', 'GET', $ctrl, 'ServiceList'));

    Router::getInstance()
            ->addRoute(new MvcContext('/:siteID/services/:id/processes', 'GET', $ctrl, 'ProcessList'));
}