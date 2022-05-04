<?php

namespace Company\License;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;

/**
 * license router
 */
$licenseCtrl = "\\Company\\License\\Controller\\LicenseCtrl";

// route license
if (R::getInstance()->requestUriHas('license')) {
    R::getInstance()->addRoute(new MVC('/:siteID/rest/licenses', 'GET', $licenseCtrl, "getLicenses"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/license(/:id)', 'GET', $licenseCtrl, "getLicense"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/licenses/register', 'POST', $licenseCtrl, "register"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/licenses/upload', 'POST,PUT', $licenseCtrl, "uploadLicenseFile"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/license/:id', 'POST,PUT', $licenseCtrl, "refreshLicense"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/license/downloadHardWareID', 'POST,PUT', $licenseCtrl, "downloadHardWareID"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/license/:id', 'DELETE', $licenseCtrl, "returnLicense"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/licenses/autoCheckLicense', 'GET', $licenseCtrl, "autoCheckLicense"));
}
