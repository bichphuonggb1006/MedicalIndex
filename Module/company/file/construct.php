<?php
use Company\MVC\Router as R;
use Company\MVC\MvcContext as MVC;

$r = R::getInstance();

$ctrl = "\\" . \Company\File\FileController::class;
$r->addRoute(new MVC('/rest/upload', 'POST', $ctrl, 'upload'));
$r->addRoute(new MVC('/rest/upload/show', 'GET', $ctrl, 'show'));
