<?php

namespace CompanyUI\Telehealthservice;

class UiLoader implements \Company\MVC\UiLoadable
{

    public function load(\Company\MVC\Layout $layout)
    {
        $module = \Company\MVC\Module::getInstance('companyui/telehealthservice');
        $layout->addJS($module->getBabelURL('autoload.json'));
    }

}