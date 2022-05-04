<?php

namespace Todolist\Todolist;

class UiLoader implements \Company\MVC\UiLoadable {

    public function load(\Company\MVC\Layout $layout) {
        $module = \Company\MVC\Module::getInstance('todolist/todolist');
        $layout->addJS($module->getBabelURL('autoload.json'));
    }

}
