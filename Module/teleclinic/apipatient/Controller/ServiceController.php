<?php

namespace Teleclinic\ApiPatient\Controller;


use Company\Exception\NotFoundException;
use Company\MVC\Controller;
use Company\MVC\MvcContext;
use Teleclinic\Api\Auth\Auth;
use Teleclinic\ApiPatient\Model\ApiPatientServiceMapper;
use Teleclinic\ApiPatient\Response\ServicesListResponse;
use Teleclinic\ApiPatient\Response\ServicesResponse;

class ServiceController extends Controller
{
    public function all(){
        $response = new \Result();
        $pageNo = $this->req->get('pageNo', 1);
        $pageNo= $pageNo>0 ? $pageNo : 1;
        $pageSize = $this->req->get('pageSize', 20);
        $pageSize = $pageSize>0 ? $pageSize : 20;
        $isDeleted = (bool)$this->req->get('isDeleted', false);
        $services = ApiPatientServiceMapper::makeInstance()
            ->autoloadDirs()
            ->filterDeleted($isDeleted)
            ->autoLoadNameSite()
            ->setPage($pageNo, $pageSize)
            ->getPage();
        $services = new ServicesListResponse($services);
        $response->setSuccess($services->toArray());
        return $this->outputJSON($response->toArray());
    }

    public function getService($id){
        $response = new \Result();
        $isDeleted = (bool)$this->req->get('isDeleted', false);
        $service = ApiPatientServiceMapper::makeInstance()
            ->autoloadDirs()
            ->filterDeleted($isDeleted)
            ->autoLoadNameSite()
            ->filterID($id)
            ->getEntityOrFail();
        $service = new ServicesResponse($service);
        $response->setSuccess($service->toArray());
        return $this->outputJSON($response->toArray());
    }

}