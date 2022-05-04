<?php

namespace Company\Country;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
if (R::getInstance()->requestUriHas('countries')) {
    R::getInstance()->addRoute(new MVC('/rest/countries', 'GET', CountryCtrl::class, "getAll"));
    R::getInstance()->addRoute(new MVC('/rest/countries/init', 'GET', CountryCtrl::class, "initDB"));
}
