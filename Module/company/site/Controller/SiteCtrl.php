<?php

namespace Company\Site\Controller;

use Company\Auth\Auth;
use Company\File\Model\FileMapper;
use Company\Site\Model as M;
use Company\Telehealthservice\Model\ServiceListMapper;

class SiteCtrl extends \Company\MVC\Controller {

    /** @var M\UserMapper */
    protected $siteMapper;
    protected $auth;

    function init() {
        parent::init();
        $this->siteMapper = M\SiteMapper::makeInstance();
        $this->auth = Auth::getInstance();
    }

    function updateSite($siteID, $id = null) {
        $this->auth->setSiteID($siteID);
        $this->auth->requireAdmin();
        $this->auth->checkSiteID($siteID);
        $this->auth->requirePrivilege('manageSite');
        $siteID = $this->siteMapper->updateSite($id, $this->input());
//        $serviceListID = ServiceListMapper::makeInstance()->updateServiceList($id, $this->input());
        $selectedFile = $this->input('thumbnail');
        if ($selectedFile) {
            FileMapper::storeOrUpdateThumbnailSite($siteID, $selectedFile);
        }else{
            FileMapper::deleteThumbnailSite($siteID);
        }

        $this->resp->setBody(json_encode(result(true)));
    }

    function getSite($siteID, $id) {
        $this->auth->requireLogin();
        $site = $this->siteMapper->makeInstance()
                ->filterID($id)
                ->autoloadTags()
                ->getEntityOrFail();
        $this->resp->setBody(json_encode($site));
    }

    function getSites($siteID) {
        $this->auth->requireLogin();

        $pageSize = $this->req->get('pageSize', 10);
        $pageNo = $this->req->get('pageNo', 1);
        $tags = $this->req->get('tags');
        $name = $this->req->get('name');
        $sites = $this->siteMapper->makeInstance()
                ->setPage($pageNo, $pageSize)
                ->filterName($name)
                ->filterTags($tags)
                ->filterActive($this->input('active'))
                ->autoloadTags()
                ->getPage();

        $this->resp->setBody(json_encode($sites));
    }

    function getTags() {
        $this->auth->requireLogin();
        $siteID = $this->req->get("siteID");
        $tags = $this->siteMapper->makeInstance()->getAllTags();
        $this->resp->setBody(json_encode($tags));
    }



    function deleteSite($siteID, $id) {
        $this->auth->requireAdmin();
        $this->auth->setSiteID($siteID);
        $this->auth->checkSiteID($siteID);

        $this->auth->requirePrivilege('manageSite');
        $this->siteMapper->deleteSite($id);
        $this->resp->setBody(json_encode(result(true)));
    }


}
