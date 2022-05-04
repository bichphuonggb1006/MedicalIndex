<?php

namespace Company\Setting;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;

/**
 * Form route
 */
$formCtrl = "\\Company\\Setting\\Controller\\FormCtrl";
$fieldCtrl = "\\Company\\Setting\\Controller\\FieldCtrl";
if (R::getInstance()->requestUriHas('setting')) {
    //R::getInstance()->addRoute(new MVC('/rest/setting/forms/:id', 'POST,PUT', $formCtrl, "updateForm"));
//R::getInstance()->addRoute(new MVC('/rest/setting/forms/:id', 'GET', $formCtrl, "getForm"));
    R::getInstance()->addRoute(new MVC('/rest/setting/forms', 'GET', $formCtrl, "getForms"));

    R::getInstance()->addRoute(new MVC('/:siteID/rest/settings/updateIntegrate', 'POST,PUT', $formCtrl, "updateSettingIntegrate"));

    /**
     * Field route
     */
    R::getInstance()->addRoute(new MVC('/:siteID/rest/settings/forms/fields', 'POST,PUT', $fieldCtrl, "updateValueFields"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/settings/forms/:id/fields', 'GET', $fieldCtrl, "getFieldsFormId"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/settings/getData', 'GET', $fieldCtrl, "getDataSetting"));
}





