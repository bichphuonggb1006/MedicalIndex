<?php

namespace Company\MVC;

class Router {

    protected $routes = [];
    static protected $instance;

    /**
     * @return Router
     */
    static function getInstance() {
        if (!static::$instance) {
            static::$instance = new static;
        }

        return static::$instance;
    }


    /**
     * Đăng ký route
     * @param \Company\MVC\MvcContext $route
     * @param string $filterUriHasText vd: truyền license thì chỉ các đường dẫn có chữ license mới được thêm
     * @return $this
     */
    function addRoute(MvcContext $route) {
        if (php_sapi_name() === 'cli')
            return $this; //ignore routes on cli

        //filter wrong method
        if ($route->method != '*' && strpos($route->method, $_SERVER['REQUEST_METHOD']) === false) {
            return $this;
        }

        $this->routes[$route->getId()] = $route;
        return $this;
    }
    
    function requestUriHas($str) {
        if (php_sapi_name() === 'cli')
            return false;

        return strpos($_SERVER['REQUEST_URI'], $str) !== false;
    }

    function getRoute($name) {
        
    }

    function getRoutes() {
        return $this->routes;
    }

}
