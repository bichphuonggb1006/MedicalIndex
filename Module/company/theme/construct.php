<?php

namespace Company\Theme;

//sử dụng class của namespace khác phải dùng "use"
use Company\MVC\MvcContext;
use Company\MVC\Router;

//đăng ký url mới
//đăng ký url mới
if (Router::getInstance()->requestUriHas('modules')) {
    Router::getInstance()
            ->addRoute(new MvcContext('/modules/:vendor/:module/public/babelLoader', 'GET', "Company\\Theme\\ThemeCtrl", 'babelLoader'));
    Router::getInstance()
            ->addRoute(new MvcContext('/modules/:vendor/:module/public/:filepath+', 'GET', "Company\\Theme\\ThemeCtrl", 'moduleContent'));
    Router::getInstance()
            ->addRoute(new MvcContext('/modules/:vendor/:module/langs/:lang', 'GET', "Company\\Theme\\ThemeCtrl", 'lang'));
}
