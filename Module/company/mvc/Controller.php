<?php

namespace Company\MVC;

abstract class Controller extends \Macroable
{

    /** @var MvcContext */
    protected $context;

    /** @var \Slim\Http\Request */
    protected $req;

    /** @var \Slim\Http\Response */
    protected $resp;

    protected $_input = false;

    function __construct(MvcContext $context)
    {
        $this->context = $context;
        $this->req = $context->app->slim->request;
        $this->resp = $context->app->slim->response;
        //nếu là Restful
        if ($this->isRest()) {
            $this->resp->header('Content-type', 'application/json');
        }
        $this->init();
    }

    /** run after __construct, tobe overrided */
    protected function init()
    {

    }

    protected function escape($str)
    {
        $str = stripslashes($str);
        $arr_search = array('&', '<', '>', '"', "'");
        $arr_replace = array('&amp;', '&lt;', '&gt;', '&#34;', '&#39;');
        $str = str_replace($arr_search, $arr_replace, $str);

        return $str;
    }

    protected function getCookie($name)
    {
        return call_user_func(array($this->context->app->slim, 'getCookie'), $name);
    }

    protected function setCookie($name, $value)
    {
        return call_user_func(array($this->context->app->slim, 'setCookie'), $name, $value);
    }

    function input($key = null, $default = null)
    {
        if ($this->_input === false) {
            $this->_input = json_decode(file_get_contents('php://input'), true);
        }
        if ($key === null) {
            return $this->_input;
        }

        return $this->_input[$key] ?? $default;
    }

    /**
     * Kiểm tra xem request có phải là rest không
     */
    protected function isRest()
    {
        if (strtolower($this->req->headers('content-type')) == 'application/json') {
            return true;
        }
        $input = trim(file_get_contents('php://input'));
        if ($input) {
            if (in_array($input[0], ['{', '[']) && in_array($input[strlen($input) - 1], ['}', ']'])) {
                return true;
            }
        }
        return false;
    }


    protected function outputJSON($data)
    {
        $this->resp->header('Content-Type', 'application/json');
        if ($data instanceof \Result) {
            return $this->resp->setBody(Json::encode($data->toArray()));
        }

        $this->resp->setBody(Json::encode($data));
    }

}
