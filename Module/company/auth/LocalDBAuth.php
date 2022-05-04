<?php

namespace Company\Auth;

use Company\Cache\CacheDriver;
use Company\Exception as Ex;
use Company\User\Model\UserMapper;

class LocalDBAuth implements AuthMethodInterface {

    public static function getName() {
        return 'localdb';
    }

    public function handleLoginUpdate($userID, $type, $account, $passwd) {
        \Company\SQL\AnyMapper::makeInstance()
                ->from('user_login')
                ->insert([
                    'userID' => $userID,
                    'type' => $type,
                    'account' => $account,
                    'passwd' => password_hash($passwd, PASSWORD_BCRYPT)
        ]);
    }

    public function auth($account, $password) {
        if (!$account || !$password) {
            throw new Ex\BadRequestException("please input all required field");
        }
        $cache = CacheDriver::getInstance(CacheDriver::PRIVATE_MEMORY_CACHE);
        $user = unserialize($cache->get("user/$account"));

        if (!$user) {
            $user = UserMapper::makeInstance()
                ->setLoadPrivileges()
                ->setLoadRoles()
                ->setLoadLogin()
                ->filterLogin($this->getName(), $account)
                ->filterActive()
                ->getEntityOrFail();

            $dbPass = $user->login[$this->getName()]->passwd;
            $verify = password_verify($password, $dbPass) || md5($password) == $dbPass;
            if ($verify)
                $cache->set("user/$account", serialize($user), 60);
            else
                throw new \Company\Exception\NotFoundException();
        }

        return $user;
    }

}
