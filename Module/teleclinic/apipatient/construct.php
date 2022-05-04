<?php

use Company\MVC\Router as R;
use Company\MVC\MvcContext as MVC;

$r = R::getInstance();

//===== api(api) app ======
$ctrl = "\\" . Teleclinic\ApiPatient\Controller\PatientController::class;
$r->addRoute(new MVC('/api-patient/user/register', 'POST', $ctrl, 'register'));
$r->addRoute(new MVC('/api-patient/user/login', 'POST', $ctrl, 'login'));

$r->addRoute(new MVC('/api-patient/user/change-password', 'POST', $ctrl, 'changePassword'));
$r->addRoute(new MVC('/api-patient/user', 'GET', $ctrl, 'getProfile'));
$r->addRoute(new MVC('/api-patient/user', 'POST', $ctrl, 'postProfile'));

//Forgot password
$r->addRoute(new MVC('/api-patient/user/forgot-password', 'POST', $ctrl, 'forgotPassword'));
$r->addRoute(new MVC('/api-patient/user/post-new-password', 'POST', $ctrl, 'postNewPassword'));


$ctrl = "\\" . Teleclinic\ApiPatient\Controller\SiteController::class;
$r->addRoute(new MVC('/api-patient/user/healt-facilities', 'GET', $ctrl, 'all'));
$r->addRoute(new MVC('/api-patient/user/healt-facilities/:id', 'GET', $ctrl, 'getSite'));

$ctrl = "\\" . Teleclinic\ApiPatient\Controller\DoctorController::class;
$r->addRoute(new MVC('/api-patient/doctors', 'GET', $ctrl, 'all'));
$r->addRoute(new MVC('/api-patient/doctors/:id', 'GET', $ctrl, 'getDoctor'));

$ctrl = "\\" . Teleclinic\ApiPatient\Controller\ServiceController::class;
$r->addRoute(new MVC('/api-patient/services', 'GET', $ctrl, 'all'));
$r->addRoute(new MVC('/api-patient/services/:id', 'GET', $ctrl, 'getService'));

#Common
$ctrl = "\\" . Teleclinic\ApiPatient\Controller\Common\DvhcController::class;
$r->addRoute(new MVC('/api-patient/dvhc', 'GET', $ctrl, 'getAll'));
$ctrl = "\\" . Teleclinic\ApiPatient\Controller\Common\CountryController::class;
$r->addRoute(new MVC('/api-patient/countries', 'GET', $ctrl, 'getAll'));


$ctrl = "\\" . Teleclinic\ApiPatient\Controller\Common\FileController::class;
$r->addRoute(new MVC('/api-patient/upload', 'POST', $ctrl, 'upload'));
$r->addRoute(new MVC('/api-patient/upload/show', 'GET', $ctrl, 'show'));

#OTP
$ctrl = "\\" . Teleclinic\ApiPatient\Controller\Common\OtpController::class;
$r->addRoute(new MVC('/api-patient/get-otp', 'POST', $ctrl, 'getOpt'));
$r->addRoute(new MVC('/api-patient/verify-otp', 'POST', $ctrl, 'verifyOtp'));


//$r->addRoute(new MVC('api-patient/user/check-phone', 'POST', $ctrl, 'checkPhoneBeforeRegister'));


//$r->addRoute(new MVC('api-patient/user/verify-ot', 'POST', $ctrl, 'verifyOtp'));
//$r->addRoute(new MVC('api-patient/user/get-otp', 'POST', $ctrl, 'getOpt'));
//$r->addRoute(new MVC('api-patient/user/logout', 'GET', $ctrl, 'logOut'));

////Forgot password
//$r->addRoute(new MVC('api-patient/user/forgot-password', 'POST', $ctrl, 'forgotPassword'));
//$r->addRoute(new MVC('api-patient/user/change-password-by-token', 'POST', $ctrl, 'changePasswordByToken'));


//$r->addRoute(new MVC('/:siteID/teleclinic/patient/show', 'GET', $ctrl, 'getMe'));
//$r->addRoute(new MVC('/:siteID/teleclinic/patient/medical-record', 'GET', $ctrl, 'getMedicalRecord'));
//$r->addRoute(new MVC('/:siteID/teleclinic/patient/update', 'POST', $ctrl, 'update'));


//$ctrl = "\\" . Teleclinic\ApiPatient\Controller\ScheduleCtrl::class;
//$r->addRoute(new MVC('api-patient/teleclinic/schedule/:id', 'GET', $ctrl, "getSchedule"));
//$r->addRoute(new MVC('api-patient/teleclinic/schedule', 'GET', $ctrl, "getSchedules"));
//$r->addRoute(new MVC('api-patient/teleclinic/schedule/request', 'POST', $ctrl, "newRequest"));
//$r->addRoute(new MVC('api-patient/teleclinic/schedule/request', 'PUT', $ctrl, "updateRequest"));
//$r->addRoute(new MVC('api-patient/teleclinic/schedule/:id/schedule', 'POST,PUT', $ctrl, "confirmSchedule"));
//$r->addRoute(new MVC('api-patient/teleclinic/schedule/:id/diagnosis', 'POST,PUT', $ctrl, "diagnosis"));
//$r->addRoute(new MVC('api-patient/teleclinic/schedule/:id', 'DELETE', $ctrl, "cancel"));
//
//$r->addRoute(new MVC('/:siteID/rest/teleclinic/schedule/sendNotification', 'POST', $ctrl, "sendNotification"));
//$r->addRoute(new MVC('api-patient/teleclinic/schedule/getScheduleHistory', 'POST', $ctrl, "getScheduleHistory"));
//$r->addRoute(new MVC('api-patient/teleclinic/schedule/file/:id', 'GET', $ctrl, "getFile"));
//$r->addRoute(new MVC('api-patient/teleclinic/schedule/getClinicScheduleSummaries', 'GET', $ctrl, "getClinicScheduleSummaries"));
//$r->addRoute(new MVC('api-patient/teleclinic/schedule/:id/paymentStatus', 'POST', $ctrl, "updatePaymentStatus"));
//
//$fieldCtrl = "\\" . Teleclinic\ApiPatient\Controller\FieldCtrl::class;
//$r->addRoute(new MVC('api-patient/settings/forms/:id/fields', 'GET', $fieldCtrl, "getFieldsFormId"));