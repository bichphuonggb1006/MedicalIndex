<?php

use Company\MVC\Router as R;
use Company\MVC\MvcContext as MVC;

$r = R::getInstance();
$r->addRoute(new MVC('/thuctap/backend/01', 'GET', "\\" . Todolist\Todolist\Controller\UiCtrl::class, "index"));