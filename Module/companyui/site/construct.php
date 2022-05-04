<?php

namespace CompanyUI\User;

use Company\MVC\MvcContext;
use Company\MVC\Router;

if (app()->isRest() == false) {
    $ctrl = "CompanyUI\\Site\\SiteCtrl";
//đăng ký url mới
    Router::getInstance()
            ->addRoute(new MvcContext('/:siteID/sites', 'GET', $ctrl, 'siteList'));

// lấy danh sách các site được phân quyền
    Router::getInstance()
            ->addRoute(new MvcContext('/:siteID/users/sites', 'GET', $ctrl, 'userSiteList'));


}
