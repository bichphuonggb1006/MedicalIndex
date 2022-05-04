<?php

namespace CompanyUI\User;

use Company\MVC\MvcContext;
use Company\MVC\Router;

if (app()->isRest() == false) {
    $ctrl = "CompanyUI\\Dict\\DictCtrl";
//đăng ký url mới
    Router::getInstance()
            ->addRoute(new MvcContext('/:siteID/dict/collection', 'GET', $ctrl, 'DictCollectionList'));
    Router::getInstance()
            ->addRoute(new MvcContext('/:siteID/dict/item', 'GET', $ctrl, 'DictItemList'));
}

