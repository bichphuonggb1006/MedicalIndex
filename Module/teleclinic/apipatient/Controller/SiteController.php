<?php

namespace Teleclinic\ApiPatient\Controller;


use Company\Exception\NotFoundException;
use Company\MVC\Controller;
use Company\MVC\MvcContext;
use Teleclinic\Api\Auth\Auth;
use Teleclinic\ApiPatient\Model\ApiPatientSiteMapper;
use Teleclinic\ApiPatient\Response\SiteListResponse;
use Teleclinic\ApiPatient\Response\SiteResponse;

class SiteController extends Controller
{
    public function all(){
        $response = new \Result();
        $name = $this->req->get('name');
        $provinceID = $this->req->get('provinceID');
        $pageNo = $this->req->get('pageNo', 1);
        $pageNo= $pageNo>0 ? $pageNo : 1;
        $pageSize = $this->req->get('pageSize', 20);
        $pageSize = $pageSize>0 ? $pageSize : 20;
        $sites = ApiPatientSiteMapper::makeInstance()->filterActive(ApiPatientSiteMapper::ACTIVED)->filterLikeKeyWord($name)->filterProvince($provinceID)->setPage($pageNo, $pageSize)->getPage();
        $sites = new SiteListResponse($sites);
        $response->setSuccess($sites->toArray());
        return $this->outputJSON($response->toArray());
    }

    public function getSite($id){
        $response = new \Result();
        $site = ApiPatientSiteMapper::makeInstance()->filterID($id)->getEntityOrFail();
        $site = new SiteResponse($site);
        $response->setSuccess($site->toArray());
        return $this->outputJSON($response->toArray());
    }
}