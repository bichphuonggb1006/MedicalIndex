<?php

namespace Company\Dvhc;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
if (R::getInstance()->requestUriHas('dvhc')) {
    R::getInstance()->addRoute(new MVC('/rest/dvhc', 'GET', DvhcCtrl::class, "getAll"));
}
