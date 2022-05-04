<?php

namespace Company\User;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
use Company\User\Controller\PrivilegeCtrl;

/**
 * User router
 */
if (R::getInstance()->requestUriHas('/rest/users')) {
    $userCtrl = "\\Company\\User\\Controller\\UserCtrl";
    R::getInstance()->addRoute(new MVC('/:siteID/rest/users(/:id)', 'POST,PUT', $userCtrl, "updateUser"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/users/changePassword', 'POST', $userCtrl, "changePassword"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/users/:id', 'GET', $userCtrl, "getUser"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/users', 'GET', $userCtrl, "getUsers"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/users/:id', 'DELETE', $userCtrl, "deleteUser"));
}

if (R::getInstance()->requestUriHas('/rest/departments')) {
    /**
     * Department route
     */
    $depCtrl = "\\Company\\User\\Controller\\DepartmentCtrl";
    R::getInstance()->addRoute(new MVC('/:siteID/rest/departments/:id/active', 'POST,PUT', $depCtrl, "updateStatusActive"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/departments(/:id)', 'POST,PUT', $depCtrl, "updateDepartment"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/departments/:id', 'GET', $depCtrl, "getDep"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/departments/:id', 'DELETE', $depCtrl, "deleteDep"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/departments', 'GET', $depCtrl, "getDeps"));
}

if (R::getInstance()->requestUriHas('/rest/roles')) {
    /**
     * Role router
     */
    $roleCtrl = "\\Company\\User\\Controller\\RoleCtrl";
    R::getInstance()->addRoute(new MVC('/:siteID/rest/roles(/:id)', 'POST,PUT', $roleCtrl, "updateRole"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/roles', 'GET', $roleCtrl, "getRoles"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/roles/:id', 'GET', $roleCtrl, "getRole"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/roles/:id', 'DELETE', $roleCtrl, "deleteRole"));
    R::getInstance()->addRoute(new MVC('/:siteID/rest/roles/:id/users', 'GET', $roleCtrl, "getRoleUser"));

    R::getInstance()->addRoute(new MVC('/:siteID/rest/roles/setUserRoleDefault/:id', 'POST,PUT', $roleCtrl, "setUserRoleDefault"));
}

if(R::getInstance()->requestUriHas('privilege')) {
    $controller = PrivilegeCtrl::class;
    R::getInstance()->addRoute(new MVC('/rest/privileges/all', 'GET', $controller, "getAllPrivs"));
}

if (R::getInstance()->requestUriHas('user')) {
    $userCtrl = "\\Company\\User\\Controller\\UserCtrl";
    // lấy danh sách các site user được phân quyền
    R::getInstance()->addRoute(new MVC('/:siteID/rest/users/:userID/sites', 'GET', $userCtrl, "getUserSites"));
// ghép tài khoản lại với nhau
    R::getInstance()->addRoute(new MVC('/:siteID/rest/user/merge', 'POST,PUT', $userCtrl, "updateMergeSite"));
}


$roleCtrl = "\\Company\\User\\Controller\\RoleCtrl";

R::getInstance()->addRoute(new MVC('/:siteID/test1', 'GET', "\\Company\\User\\Controller\\UserCtrl", "test"));

R::getInstance()->addRoute(new MVC('/:siteID/rest/listCustomDisplay', 'GET', $roleCtrl, "getListCustomDisplay"));
R::getInstance()->addRoute(new MVC('/:siteID/rest/listDiagConfig', 'GET', $roleCtrl, "getListDiagConfig"));
