<?php

namespace Company\Service;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;

/**
 * User router
 */
$serviceCtrl = "\\Company\\Service\\Controller\\ServiceCtrl";
if (R::getInstance()->requestUriHas('service')) {
    R::getInstance()->addRoute(new MVC('/master/rest/service(/:serviceID)', 'POST,PUT', $serviceCtrl, "updateService"));
    R::getInstance()->addRoute(new MVC('/master/rest/service(/:serviceID)', 'GET', $serviceCtrl, "getService"));
    R::getInstance()->addRoute(new MVC('/master/rest/service/:serviceID', 'DELETE', $serviceCtrl, "deleteService"));
}

if (R::getInstance()->requestUriHas('process')) {
    R::getInstance()->addRoute(new MVC('/master/rest/processes(/:serviceID)', 'GET', $serviceCtrl, "getProcesses"));
    R::getInstance()->addRoute(new MVC('/master/rest/process/handle', 'GET', $serviceCtrl, "handleProcess"));
}


