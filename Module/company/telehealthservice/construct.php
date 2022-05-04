<?php

namespace Company\Telehealthservice;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
$r = R::getInstance();

/**
 * User router
 */

if ($r->requestUriHas('rest/serviceDir')) {
    $ctrl = "\\Company\\Telehealthservice\\Controller\\ServiceCtrl";
    $r->addRoute(new MVC('/rest/serviceDir(/:id)', 'POST,PUT', $ctrl, "updateDir"));
    $r->addRoute(new MVC('/rest/serviceDir/:id', 'GET', $ctrl, "getDir"));
    $r->addRoute(new MVC('/:siteID/rest/serviceDir', 'GET', $ctrl, "getDirs"));
    $r->addRoute(new MVC('/rest/serviceDir/:id', 'DELETE', $ctrl, "deleteDir"));
}

if ($r->requestUriHas('rest/serviceList')) {
    $ctrl = "\\Company\\Telehealthservice\\Controller\\ServiceCtrl";
    $r->addRoute(new MVC('/rest/serviceList(/:id)', 'POST,PUT', $ctrl, "updateServiceList"));
    $r->addRoute(new MVC('/rest/serviceList/:id', 'GET', $ctrl, "getServiceList"));
    $r->addRoute(new MVC('/:siteID/rest/serviceList', 'GET', $ctrl, "getServicesList"));
    $r->addRoute(new MVC('/rest/serviceList/:id', 'DELETE', $ctrl, "deleteServiceList"));
    $r->addRoute(new MVC('/rest/serviceList/:id/checkTime', 'GET', $ctrl, "checkTimeAvailable"));
}

$ctrl = "\\Company\\Telehealthservice\\Controller\\ServiceCtrl";
$r->addRoute(new MVC('/:siteID/rest/sites', 'GET', $ctrl, "getSites"));