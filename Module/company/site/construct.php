<?php

namespace Company\Site;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;

/**
 * User router
 */
$siteCtrl = "\\Company\\Site\\Controller\\SiteCtrl";
if (R::getInstance()->requestUriHas('rest/sites')) {
    R::getInstance()->addRoute(new MVC('/:siteID/master/rest/sites(/:id)', 'POST,PUT', $siteCtrl, "updateSite"));
    R::getInstance()->addRoute(new MVC('/:siteID/master/rest/sites/:id', 'GET', $siteCtrl, "getSite"));
    R::getInstance()->addRoute(new MVC('/:siteID/master/rest/sites', 'GET', $siteCtrl, "getSites"));
    R::getInstance()->addRoute(new MVC('/:siteID/master/rest/sites/:id', 'DELETE', $siteCtrl, "deleteSite"));
    R::getInstance()->addRoute(new MVC('/:siteID/master/rest/sites/tags', 'GET', $siteCtrl, "getTags"));
}


