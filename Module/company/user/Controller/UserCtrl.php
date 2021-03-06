<?php

namespace Company\User\Controller;

use Company\Auth\Auth;
use Company\Exception\BadRequestException;
use Company\Site\Model\SiteMapper;
use Company\User\Model as M;
use Company\User\Model\UserMapper;
use Company\User\User;

class UserCtrl extends \Company\MVC\Controller {

    /** @var M\UserMapper */
    protected $userMapper;
    protected $auth;

    function init() {
        parent::init();
        $this->userMapper = M\UserMapper::makeInstance();
        $this->auth = Auth::getInstance();
    }

    function updateUser($siteID, $id = null) {
        $this->auth->requireAdmin();
        $this->auth->setSiteID($siteID);
        $this->auth->checkSiteID($siteID);
        $this->auth->requirePrivilege('manageUser');

        $data = $this->input();
        $data['siteFK'] = $siteID;
        $result = $this->userMapper->updateUser($id, $data);

        $this->resp->setBody(json_encode($result));
    }

    function changePassword($siteID) {
        $this->auth->requireLogin();
        $this->auth->checkSiteID($siteID);

        $data = $this->input();
        $account = $data["account"];
        $oldPassword = $data["oldPassword"];
        $newPassword = $data["newPassword"];
        $type = $data["type"];

        if (!UserMapper::checkPasswordStrength($newPassword))
            throw new BadRequestException("New passwod invalid!");

        $user = UserMapper::makeInstance()
            ->setLoadLogin()
            ->filterLogin($type, $account)
            ->getEntityOrFail();

        $dbPass = $user->login[$type]->passwd;

        $verify = password_verify($oldPassword, $dbPass);

        if (!$verify)
            throw new \Company\Exception\BadRequestException("Old password not match");

        M\UserLoginMapper::makeInstance()
            ->where('`type`=? AND account=?', __FUNCTION__)
            ->setParamWhere($type, __FUNCTION__ . 1)
            ->setParamWhere($account, __FUNCTION__ . 2)
            ->update([
                "passwd" => password_hash($newPassword, PASSWORD_BCRYPT)
            ]);

        $this->resp->setBody(json_encode(result(true)));
    }

    function getUser($siteID, $id) {
        $this->auth->requireLogin();
        $user = $this->userMapper->makeInstance()
                ->filterSiteFK($siteID)
                ->filterID($id)
                ->setLoadLogin()
                ->setLoadPrivileges()
                ->setLoadRoles()
                ->setLoadDep()
                ->getEntity();

        $this->resp->setBody(json_encode($user));
    }

    function getUsers($siteID) {
        $mapper = $this->userMapper->makeInstance()
                ->filterDepFK($this->req->get('parentID'))
                ->filterName($this->req->get('fullname'))
                ->filterActive($this->req->get('active'))
                ->filterDeleted()
                ->setLoadPrivileges()
                ->filterSiteFK($siteID);

        if ($this->req->get('loadDep')) {
            $mapper->setLoadDep();
        }

        $users = $mapper->getEntities();

        $this->resp->setBody(json_encode($users->toArray()));
    }

    function deleteUser($siteID, $id) {
        $this->auth->requireAdmin();
        $this->auth->setSiteID($siteID);
        $this->auth->checkSiteID($siteID);
        $this->auth->requirePrivilege('manageUser');

        $this->userMapper->deleteUser($siteID, $id);
        $this->resp->setBody(json_encode(result(true)));
    }


    // l???y danh s??ch site ????? ch???n
    function getUserSites($siteID, $userID) {
        $this->auth->requireLogin();
        $pageSize = $this->req->get('pageSize', 10);
        $pageNo = $this->req->get('pageNo', 1);

        $privs = $this->auth->getUser()->privileges;

        // l?? admin t???ng s??? hi???n h???t t???t c??? c??c site
        if (in_array('fullcontrol', $privs)) {
            $sites = SiteMapper::makeInstance()
                ->filterName($this->req->get('name'))
                ->setPage($pageNo, $pageSize)
                ->getPage();
        } else {
            // l???y user ??ang ????ng nh???p
            $user = $this->auth->getUser();
            if ($user->id != $userID) {
                throw new E\BadRequestException("Error userID: " . $userID);
            }
            $arrSiteID = \Company\User\Model\UserMapper::makeInstance()->getSitesID($userID);
            // l???y danh s??ch c??c site thu???c user ????
            $sites = SiteMapper::makeInstance()
                ->filterID($arrSiteID)
                ->filterName($this->req->get('name'))
                ->setPage($pageNo, $pageSize)
                ->getPage();
        }

        $this->resp->setBody(json_encode($sites));
    }

    // ?????ng b??? t??i kho???n
    function updateMergeSite($siteID) {
        $this->auth->requireLogin();

        $accountMerge = $this->input('mainAccount');
        if(!$accountMerge)
            throw new BadRequestException("mainAccount must not empty");
        $accountConnect = $this->input('account');
        if(!$accountConnect)
            throw new BadRequestException("account must not empty");
        $passwordConnect = $this->input('password');

        $user = $this->auth->getUser();
        // t??i kho???n ??ang ????ng nh???p v??o h??? th???ng
        $accountCurrent = $user->login['localdb']->account;
        // check t??i kho???n k???t n???i v?? t??i kho???n ??ang ????ng nh???p
        if ($accountCurrent == $accountConnect) {
            $result = result(false, "Account is login don't connect");
        } else {
            $AccConnect = Auth::getInstance()->authAll(
                $accountConnect
                , $passwordConnect);
            // ki???m tra xem c?? k???t n???i th??nh c??ng ?????n t??i kho???n kh??ng?
            if ($AccConnect) {
                //check quy???n c???a site k???t n???i ?????n
                $privs = $AccConnect->privileges;

                // check site c???a t??i kho???n k???t n???i ?????n
                $arrSiteID = \Company\User\Model\UserMapper::makeInstance()->getSitesID($AccConnect->id);
                if (in_array($siteID, $arrSiteID)) {
                    $result = result(false, 'Account connect is same site');
                } else {
                    if ($accountConnect == $accountMerge) {
                        $this->userMapper->updateMergeSite($user, $AccConnect);
                    } else {
                        $this->userMapper->updateMergeSite($AccConnect, $user);
                    }
                    $result = result(true);
                }
            } else {
                $result = result(false, 'Account or password is incorrect');
            }
        }

        $this->resp->setBody(json_encode($result));
    }

    function test($siteID) {
        Auth::getInstance()->requireLogin();
        Auth::getInstance()->setSiteID($siteID);
        var_dump(Auth::getInstance()->hasPrivilege('manageRole'));
    }

}
