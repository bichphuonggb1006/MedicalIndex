<?php
namespace Todolist\Todolist\Controller;

use Company\Auth\Auth;
use Company\MVC\Layout;
use Company\MVC\Module;

class UiCtrl extends \Company\MVC\Controller
{
    protected $layout;

    function init() {
        parent::init();
        $module = Module::getInstance('todolist/todolist');

        $this->layout = Layout::getLayout('admin');
        $this->layout->addCSS($module->getPublicURL() . '/style.css');
    }
    function index()
    {
        Auth::getInstance()->requireLogin();
//        echo "<pre>";
//        print_r($_REQUEST);
//        die(123);
        $this->layout
            ->setSiteID('master')
            ->renderReact('MedicalRecord');
    }
}