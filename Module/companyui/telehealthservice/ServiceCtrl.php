<?php

namespace CompanyUI\Telehealthservice;

use Company\Auth\Auth;
use Company\MVC\Layout;
use Company\MVC\Module;

class ServiceCtrl extends \Company\MVC\Controller
{
    protected $layout;

    function init() {
        parent::init();
        $module = Module::getInstance('companyui/telehealthservice');
        $this->layout = Layout::getLayout('admin');
        $this->layout->addCSS($module->getPublicURL() . '/css/style.css');
    }

    function ServiceDir($siteID) {
        $module = Module::getInstance('companyui/telehealthservice');
        $this->layout
            ->setSiteID($siteID)
            ->setTitle("Nhóm dịch vụ")
            ->renderReact('TeleclinicServiceDir');
    }

    function ServiceList($siteID) {
        $module = Module::getInstance('companyui/telehealthservice');

        $this->layout
            ->setSiteID($siteID)
            ->setTitle("Dịch vụ")
            ->renderReact('TeleclinicServiceList');
    }
}