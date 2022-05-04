<?php

namespace Teleclinic\Teleclinic;

class UiLoader implements \Company\MVC\UiLoadable {

    public function load(\Company\MVC\Layout $layout) {
        $module = \Company\MVC\Module::getInstance('teleclinic/teleclinic');
        $layout->addJS($module->getBabelURL('autoload.json'));

    }

}
