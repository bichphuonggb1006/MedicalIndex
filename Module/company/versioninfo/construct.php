<?php

namespace Company\VersionInfo;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;

$versionInfoCtrl = "\\Company\\VersionInfo\\Controller\\VersionInfoCtrl";

if (R::getInstance()->requestUriHas('versionInfo')) {
// route versionInfo
    R::getInstance()->addRoute(new MVC('/:siteID/rest/versionInfo', 'GET', $versionInfoCtrl, "getVersionInfo"));
}
