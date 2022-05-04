<?php


namespace Teleclinic\Teleclinic\Controller;


use Company\Auth\Auth;
use Company\MVC\Layout;
use Company\MVC\Module;
use Company\Site\Model\SiteMapper;
use Teleclinic\Teleclinic\Model\ScheduleMapper;


class UICtrl extends \Company\MVC\Controller {
    /**
     * @var Layout
     */
    protected $layout;

    function init() {
        parent::init();
        $module = Module::getInstance('teleclinic/teleclinic');

        $this->layout = Layout::getLayout('admin');
        $this->layout->addCSS($module->getPublicURL() . '/style.css');
    }

    function ServiceDir($siteID) {
        $module = Module::getInstance('teleclinic/teleclinic');
        $this->layout
            ->setSiteID($siteID)
            ->setTitle("Thư mục dịch vụ")
            ->renderReact('TeleclinicServiceDir');
    }

    function ServiceList($siteID) {
        $module = Module::getInstance('teleclinic/teleclinic');

        $this->layout
            ->setSiteID($siteID)
            ->setTitle("Dịch vụ")
            ->renderReact('TeleclinicServiceList');
    }

    function Vclinic($siteID) {
        Auth::getInstance()->requireLogin();
        $module = Module::getInstance('teleclinic/teleclinic');
        $this->layout
            ->setSiteID($siteID)
            ->setTitle("Phòng khám ảo")
            ->renderReact('VclinicList');
    }

    function Scheduled($siteID) {
        Auth::getInstance()->requireLogin();
        $module = Module::getInstance('teleclinic/teleclinic');
//        echo "<pre>";
//        print_r("Hello");
//        die(123);
        $this->layout
            ->setSiteID($siteID)
            ->setTitle("Đã xếp lịch")
            ->renderReact('Scheduled');
    }

    function getList($siteID) {
        Auth::getInstance()->requireLogin();
        $module = Module::getInstance('teleclinic/teleclinic');
        $this->layout
            ->setSiteID($siteID)
            ->setTitle("Danh sách việc")
            ->renderReact('ToDo');
    }
    function Unscheduled($siteID) {

        Auth::getInstance()->requireLogin();
        $module = Module::getInstance('teleclinic/teleclinic');
//        echo "<pre>";
//        print_r($_REQUEST);
//        die(123);
        $sites = SiteMapper::makeInstance()->getEntities()->toArray();
        $this->layout
            ->setSiteID($siteID)
            ->setTitle("Chưa xếp lịch")
            ->renderReact('Unscheduled', ['sites'=> $sites]);
    }

    function home() {
        Auth::getInstance()->requireLogin();

        $this->resp->redirect(url('/teleclinic/scheduled'));
    }

    function report($siteID) {
        Auth::getInstance()->requireLogin();

        $this->layout
            ->setSiteID($siteID)
            ->setTitle("Báo cáo")
            ->renderReact('ClinicReport');
    }

    function vclinicSchedule($siteID) {
        Auth::getInstance()->requireLogin();
        $this->layout
            ->setSiteID($siteID)
            ->renderReact('ClinicSchedule');
    }

    function medicalRecord($siteID){
        Auth::getInstance()->requireLogin();
//        echo "<pre>";
//        print_r($_REQUEST);
//        die(123);
        $this->layout
            ->setSiteID($siteID)
            ->renderReact('MedicalRecord');
    }

    function  medicalRecordDetail($siteID,$phone){
        Auth::getInstance()->requireLogin();

        $result = ScheduleMapper::makeInstance()->getPatientData($phone);

        $this->layout
            ->setSiteID($siteID)
            ->renderReact('MedicalRecordDetail',['patient'=>$result['patientInformation'],
                                                                'schedules' =>$result['schedules']
                                                               ]);

    }

    //yersin
    function  medicalFlow(){
//        Auth::getInstance()->requireLogin();
        $this->layout
            ->setSiteID('master')
            ->renderReact('MedicalFlow');

    }

}
