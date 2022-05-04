<?php

use Company\MVC\Router as R;
use Company\MVC\MvcContext as MVC;
$r = R::getInstance();

if ($r->requestUriHas('rest/teleclinic/serviceDir')) {
    $ctrl = "\\" . Teleclinic\Teleclinic\Controller\ServiceCtrl::class;
    $r->addRoute(new MVC('/rest/teleclinic/serviceDir(/:id)', 'POST,PUT', $ctrl, "updateDir"));
    $r->addRoute(new MVC('/rest/teleclinic/serviceDir/:id', 'GET', $ctrl, "getDir"));
    $r->addRoute(new MVC('/:siteID/rest/teleclinic/serviceDir', 'GET', $ctrl, "getDirs"));
    $r->addRoute(new MVC('/rest/teleclinic/serviceDir/:id', 'DELETE', $ctrl, "deleteDir"));
}

if ($r->requestUriHas('rest/teleclinic/vclinic')) {
    $ctrl = "\\" . Teleclinic\Teleclinic\Controller\VclinicCtrl::class;
    $r->addRoute(new MVC('/rest/teleclinic/vclinic(/:id)', 'POST,PUT', $ctrl, "updateClinic"));
    $r->addRoute(new MVC('/rest/teleclinic/vclinic/:id', 'GET', $ctrl, "getClinic"));
    $r->addRoute(new MVC('/rest/teleclinic/vclinic', 'GET', $ctrl, "getClinics"));
    $r->addRoute(new MVC('/rest/teleclinic/vclinic/:serviceID/service', 'GET', $ctrl, "getServiceClinics"));

    $r->addRoute(new MVC('/rest/teleclinic/vclinic/:id', 'DELETE', $ctrl, "deleteClinic"));

    $r->addRoute(new MVC('/rest/teleclinic/vclinic/:id/schedule', 'POST', $ctrl, "updateSchedule"));
    $r->addRoute(new MVC('/rest/teleclinic/vclinic/schedule', 'GET', $ctrl, "getSchedules"));
}

if ($r->requestUriHas('rest/teleclinic/serviceList')) {
    $ctrl = "\\" . Teleclinic\Teleclinic\Controller\ServiceCtrl::class;
    $r->addRoute(new MVC('/rest/teleclinic/serviceList(/:id)', 'POST,PUT', $ctrl, "updateServiceList"));
    $r->addRoute(new MVC('/rest/teleclinic/serviceList/:id', 'GET', $ctrl, "getServiceList"));
    $r->addRoute(new MVC('/:siteID/rest/teleclinic/serviceList', 'GET', $ctrl, "getServicesList"));
    $r->addRoute(new MVC('/rest/teleclinic/serviceList/:id', 'DELETE', $ctrl, "deleteServiceList"));
    $r->addRoute(new MVC('/rest/teleclinic/serviceList/:id/checkTime', 'GET', $ctrl, "checkTimeAvailable"));
}

if($r->requestUriHas('rest/teleclinic/schedule')) {
    $ctrl = "\\" . Teleclinic\Teleclinic\Controller\ScheduleCtrl::class;
    $r->addRoute(new MVC('/rest/teleclinic/schedule/:id', 'GET', $ctrl, "getSchedule"));
    $r->addRoute(new MVC('/rest/teleclinic/schedule', 'GET', $ctrl, "getSchedules"));
    $r->addRoute(new MVC('/rest/teleclinic/schedule/request', 'POST', $ctrl, "newRequest"));
    $r->addRoute(new MVC('/rest/teleclinic/schedule/request', 'PUT', $ctrl, "updateRequest"));
    $r->addRoute(new MVC('/rest/teleclinic/schedule/:id/schedule', 'POST,PUT', $ctrl, "confirmSchedule"));
    $r->addRoute(new MVC('/rest/teleclinic/schedule/:id/diagnosis', 'POST,PUT', $ctrl, "diagnosis"));
    $r->addRoute(new MVC('/rest/teleclinic/schedule/:id', 'DELETE', $ctrl, "cancel"));

    $r->addRoute(new MVC('/:siteID/rest/teleclinic/schedule/sendNotification', 'POST', $ctrl, "sendNotification"));
    $r->addRoute(new MVC('/rest/teleclinic/schedule/getScheduleHistory', 'POST', $ctrl, "getScheduleHistory"));
    $r->addRoute(new MVC('/rest/teleclinic/schedule/file/:id', 'GET', $ctrl, "getFile"));
    $r->addRoute(new MVC('/rest/teleclinic/schedule/getClinicScheduleSummaries', 'GET', $ctrl, "getClinicScheduleSummaries"));
    $r->addRoute(new MVC('/rest/teleclinic/schedule/:id/paymentStatus', 'POST', $ctrl, "updatePaymentStatus"));
}

if (app()->isRest() == false && $r->requestUriHas('teleclinic')) {
    $ctrl = "\\" . Teleclinic\Teleclinic\Controller\UICtrl::class;

    $r->addRoute(new MVC('/:siteID/teleclinic/serviceList', 'GET', $ctrl, 'ServiceList'));
    $r->addRoute(new MVC('/:siteID/teleclinic/serviceDir', 'GET', $ctrl, 'ServiceDir'));
    $r->addRoute(new MVC('/:siteID/teleclinic/vclinic', 'GET', $ctrl, 'Vclinic'));
    $r->addRoute(new MVC('/:siteID/teleclinic/scheduled', 'GET', $ctrl, 'Scheduled'));
    $r->addRoute(new MVC('/:siteID/teleclinic/schedule', 'GET', $ctrl, 'Scheduled'));
    $r->addRoute(new MVC('/:siteID/teleclinic/unscheduled', 'GET', $ctrl, 'Unscheduled'));
    $r->addRoute(new MVC('/:siteID/teleclinic/report', 'GET', $ctrl, 'report'));
    $r->addRoute(new MVC('/:siteID/teleclinic/record', 'GET', $ctrl, 'medicalRecord'));
    $r->addRoute(new MVC('/:siteID/teleclinic/record/:id', 'GET', $ctrl, 'medicalRecordDetail'));
    $r->addRoute(new MVC('/:siteID/teleclinic/todolist', 'GET', $ctrl, 'getList'));

}
//Ho so suc khoe
if ($r->requestUriHas('rest/teleclinic/medicalRecord')) {
    $ctrl = "\\" . Teleclinic\Teleclinic\Controller\ServiceCtrl::class;
    $r->addRoute(new MVC('/rest/teleclinic/medicalRecord/:siteID', 'GET', $ctrl, "getMedicalRecord"));

}
$r->addRoute(new MVC('/kham/:uid', 'GET', "\\" . Teleclinic\Teleclinic\Controller\ScheduleCtrl::class, "startVideoCall"));

$ctrl = "\\" . Teleclinic\Teleclinic\Controller\UICtrl::class;
$r->addRoute(new MVC('/', 'GET', $ctrl, 'home'));


if (app()->isRest() == false && $r->requestUriHas('theodoidieutri')) {
    $ctrl = "\\" . Teleclinic\Teleclinic\Controller\UICtrl::class;
    $r->addRoute(new MVC('/theodoidieutri', 'GET', $ctrl, 'medicalFlow'));
}

if (app()->isRest() == true && $r->requestUriHas('medical-index')) {
    $ctrl = "\\" . Teleclinic\Teleclinic\Controller\MedicalIndexCtrl::class;
    $r->addRoute(new MVC('/rest/medical-index', 'GET', $ctrl, 'getAll'));
    $r->addRoute(new MVC('/rest/medical-index/:id', 'GET', $ctrl, 'getId'));
    $r->addRoute(new MVC('/rest/medical-index/create', 'POST', $ctrl, 'create'));
    $r->addRoute(new MVC('/rest/medical-index/:id/update', 'PUT', $ctrl, 'update'));
    $r->addRoute(new MVC('/rest/medical-index/:id/delete', 'DELETE', $ctrl, 'delete'));
}