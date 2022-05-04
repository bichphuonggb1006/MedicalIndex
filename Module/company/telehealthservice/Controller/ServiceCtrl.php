<?php

namespace Company\Telehealthservice\Controller;

use Company\Auth\Auth;
use Company\Exception\BadRequestException;
use Company\Exception\NotFoundException;
use Company\File\Model\FileMapper;
use Company\MVC\Controller;
use Company\MVC\Json;
use Company\MVC\MvcContext;
use Company\Telehealthservice\Model\ServiceDirMapper;
use Company\Telehealthservice\Model\ServiceListMapper;
use Company\Site\Model\SiteMapper;
use function React\Promise\map;

class ServiceCtrl extends Controller
{

    /**
     * @var Auth
     */
    protected $auth;

    protected function init()
    {
        parent::init();
        $this->auth = Auth::getInstance();
    }

    function updateDir($id = 0)
    {

        $this->auth->requireAdmin();

        $serviceDirID = ServiceDirMapper::makeInstance()->updateDir($id, $this->input());
        $selectedFile = $this->input('thumbnail');
        if ($selectedFile) {
            FileMapper::storeOrUpdateThumbnailServiceDir($serviceDirID, $selectedFile);
        }else{
            FileMapper::deleteThumbnailServiceDir($serviceDirID);
        }

        $this->resp->setBody(json_encode(result(true)));
    }

    function getDir($id)
    {
        $this->auth->requireLogin();

        $dir = ServiceDirMapper::makeInstance()->filterID($id)->getEntity();
        if (!$dir || !$dir->id) throw new NotFoundException("Service Dir not found");

        $siteID = $dir->siteID;
        $this->auth->checkSiteID($siteID);

        $this->resp->setBody(json_encode($dir));
    }

    function getSites()
    {
        $this->auth->requireLogin();
        $sites = SiteMapper::makeInstance()
            ->filterActive(SiteMapper::ACTIVED)
            ->getEntities();
        $this->resp->setBody(Json::encode($sites));
    }

    function getDirs($siteID)
    {
//        $parentID = $this->req->get(ServiceDirMapper::PARENTID);
        $deleted = $this->req->get('deleted', false);

        $dirs = ServiceDirMapper::makeInstance()->filterDeleted($deleted)->orderBy("path")->getEntities();

        $this->resp->setBody(Json::encode($dirs));
    }

    function deleteDir($id)
    {
        $this->auth->requireLogin();

        /* Đánh dấu xoá các thư mục con bên trong nếu có */
        $dir = ServiceDirMapper::makeInstance()->autoloadChildDir()->filterID($id)->getEntity();
        if (!$dir || $dir->deleted) {
            $this->resp->setBody(json_encode(result(true)));
            return;
        }

        /* TH danh sau xoa thu muc cha thi cung danh dau xoa thu muc con */
        foreach ($dir->children as $childDir) {
            /* Danh dau xoa dich vu di kem*/
            $services = ServiceListMapper::makeInstance()->filterDir($childDir->id)->getEntities();

            foreach ($services as $srv) {
                ServiceListMapper::makeInstance()->deleteService($srv->id);
            }
            ServiceDirMapper::makeInstance()->deleteDir($childDir->id);
        }

        /* Danh dau xoa thu muc hien tai*/
        $services = ServiceListMapper::makeInstance()->filterDir($id)->getEntities();

        foreach ($services as $srv) {
            ServiceListMapper::makeInstance()->deleteService($srv->id);
        }
        ServiceDirMapper::makeInstance()->deleteDir($id);

        $this->resp->setBody(json_encode(result(true)));
    }

    function dirs($siteID)
    {

        $parentID = $this->req->get('parentID');
    }

    /**
     * SERVICE
     */

    function updateServiceList($id = null)
    {
        $this->auth->requireAdmin();

        $serviceListID = ServiceListMapper::makeInstance()->updateServiceList($id, $this->input());
        $selectedFile = $this->input('thumbnail');
        if ($selectedFile) {
            FileMapper::storeOrUpdateThumbnailServiceList($serviceListID, $selectedFile);
        }else{
            FileMapper::deleteThumbnailServiceList($serviceListID);
        }
        $this->resp->setBody(json_encode(result(true)));
    }

    function getServiceList($id)
    {
        $this->auth->requireLogin();

        $serviceList = ServiceListMapper::makeInstance()->filterID($id)->getEntityOrFail(new NotFoundException("ServiceList not found"));

        $siteID = $serviceList->siteID;
        $this->auth->checkSiteID($siteID);

        $this->resp->setBody($serviceList->toJson());
    }

    function deleteServiceList($id)
    {
        $this->auth->requireAdmin();

        ServiceListMapper::makeInstance()->deleteService($id);

        $this->resp->setBody(json_encode(result(true)));
    }

    function getServicesList()
    {
        $name = trim($this->req->get('name'));
        $dirID = trim($this->req->get('dirID'));
        $siteID = trim($this->req->get('$siteID'));
        $groupBy = $this->req->get('groupBy');
        $isDeleted = (bool)$this->req->get('isDeleted', false);

        $mapper = ServiceListMapper::makeInstance()->autoloadDirs()->filterDeleted($isDeleted);

        if (!empty($name)) $mapper->filterName($name);
        if (!empty($dirID)) $mapper->filterDir($dirID);
        if (!empty($siteID)) $mapper->filterSite($siteID);

        if ($groupBy == 'serviceDir') {
            //get root
            $res = ServiceDirMapper::makeInstance()->autoloadServices()->autoloadChildDir()->filterParent(0)->filterDeleted($isDeleted)->getEntities();
        } else {
            $res = $mapper->getEntities();
        }

        $this->resp->setBody($res->toJson());

    }

}