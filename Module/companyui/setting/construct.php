<?php

namespace CompanyUI\User;

use Company\MVC\MvcContext;
use Company\MVC\Router;

if (app()->isRest() == false) {
    $ctrl = "CompanyUI\\Setting\\SettingCtrl";
//đăng ký url mới
// setting
    Router::getInstance()
            ->addRoute(new MvcContext('/:siteID/setting', 'GET', $ctrl, 'SettingList'));
// setting integrate
    Router::getInstance()
            ->addRoute(new MvcContext('/:siteID/settingIntegrate', 'GET', $ctrl, 'SettingIntegrateList'));
}