<?php

use Company\MVC\Router as R;
use Company\MVC\MvcContext as MVC;
use Payment\BASE\Controller\UICtrl;
use Payment\BASE\Controller\PaymentCtrl;

$r = R::getInstance();
$UICtrl = "\\" . UICtrl::class;
$PaymentCtrl = "\\" . PaymentCtrl::class;

$r->addRoute(new MVC('/rest/payment/:provider/uniqID', 'GET', $PaymentCtrl, 'generateUniqID'));
$r->addRoute(new MVC('/rest/payment/payments', 'GET', $PaymentCtrl, 'getPayments'));
$r->addRoute(new MVC('/payment/cart', 'GET', $UICtrl, "cart"));
