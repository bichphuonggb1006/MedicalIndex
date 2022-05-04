<?php

namespace Company\MVC;

class MvcContext {

    public $config;

    /** @var Bootstrap */
    public $app;
    public $path;
    public $method;
    public $controller;
    public $action;
    public $rewriteBase;
    public $id;
    public $cacheLifetime = false;

    /**
     * @param String|Array $path
     * @param type $method
     * @param type $controller
     * @param type $action
     */
    function __construct($path, $method, $controller, $action) {
        $this->path = $path;
        $this->method = $method;
        if($controller[0] !== "\\")
            $controller = "\\" . $controller;
        $this->controller = $controller;
        $this->action = $action;
    }

    function getId($withMethod = true) {
        return $withMethod ? "{$this->method}:$this->path" : "$this->path";
    }

}
