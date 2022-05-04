<?php

namespace Company\Auth;

class AuthCtrl extends \Company\MVC\Controller {

    protected $auth;

    function init() {
        $this->auth = Auth::getInstance();
    }

    function auth() {
        $user = Auth::getInstance()->getUser();
        $result = Auth::getInstance()->auth(
                $this->input('type'),
                $this->input('account'),
                $this->input('password'),
                $this->input("captcha")
        );

        $this->resp->setBody(json_encode($result));
    }

    function renewSession() {
        Auth::getInstance()->requireLogin();
        $user = Auth::getInstance()->getUser();
        $this->resp->setBody(json_encode($user));
    }

    function logout() {
        Auth::getInstance()->logout();
        $this->resp->setBody(json_encode(true));
    }

}
