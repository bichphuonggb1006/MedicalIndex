<?php

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;

$ctrl = "\\Company\\Zone\\Controller\\ZoneCtrl";
if (R::getInstance()->requestUriHas('rest/zone')) {
    R::getInstance()->addRoute(new MVC('/master/rest/zone/:id', 'POST,PUT', $ctrl, "updateZone"));
    R::getInstance()->addRoute(new MVC('/master/rest/zone', 'GET', $ctrl, "getZones"));
    R::getInstance()->addRoute(new MVC('/master/rest/zone/:id', 'GET', $ctrl, "getZone"));
    R::getInstance()->addRoute(new MVC('/master/rest/zone/:id', 'DELETE', $ctrl, "deleteZone"));
}

if (R::getInstance()->requestUriHas('rest/contactPoint')) {
    R::getInstance()->addRoute(new MVC('/master/rest/contactPoint', 'POST,PUT', $ctrl, "insertContactPoint"));
    R::getInstance()->addRoute(new MVC('/master/rest/contactPoint', 'GET', $ctrl, "getContactPoints"));
    R::getInstance()->addRoute(new MVC('/master/rest/contactPoint/:id', 'GET', $ctrl, "getContactPoint"));
    R::getInstance()->addRoute(new MVC('/master/rest/contactPoint/:id', 'DELETE', $ctrl, "deleteContactPoint"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/contactPoint/detect', 'GET', $ctrl, "detectContactPoint"));
}

R::getInstance()->addRoute(new MVC('/rest/hostname', 'GET', $ctrl, "getHostName"));