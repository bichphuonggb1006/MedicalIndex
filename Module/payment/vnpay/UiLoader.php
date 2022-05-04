<?php

namespace Payment\VNPAY;

class UiLoader implements \Company\MVC\UiLoadable {

    public function load(\Company\MVC\Layout $layout) {
        $module = \Company\MVC\Module::getInstance('payment/vnpay');
        $layout->addJS($module->getBabelURL('autoload.json'));
        $layout->addCSS($module->getPublicURL() . '/style.css');
    }

}
