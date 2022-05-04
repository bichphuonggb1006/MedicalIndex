<?php

use Company\MVC\Router as R;
use Company\MVC\MvcContext as MVC;
use Payment\VNPAY\Controller\UICtrl;
use Payment\VNPAY\Controller\VNPayPaymentCtrl;

$r = R::getInstance();
$UICtrl = "\\" . UICtrl::class;
$VNPayPaymentCtrl = "\\" . VNPayPaymentCtrl::class;

/* vnpay payment */
$r->addRoute(new MVC('/:siteID/rest/vnpay/create', 'POST,PUT', $VNPayPaymentCtrl, "vnpCreate"));
$r->addRoute(new MVC('/vnpay/return', 'GET', $UICtrl, "vnpReturn"));
