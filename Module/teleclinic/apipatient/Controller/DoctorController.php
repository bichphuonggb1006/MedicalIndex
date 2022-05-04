<?php

namespace Teleclinic\ApiPatient\Controller;


use Company\Exception\NotFoundException;
use Company\MVC\Controller;
use Company\MVC\MvcContext;
use Teleclinic\Api\Auth\Auth;
use Teleclinic\ApiPatient\Model\ApiPatientDoctorMapper;
use Teleclinic\ApiPatient\Response\DoctorListResponse;
use Teleclinic\ApiPatient\Response\DoctorResponse;

class DoctorController extends Controller
{
    public function all(){
        $rs = new \Result();
        $pageNo = $this->req->get('pageNo', 1);
        $pageNo= $pageNo>0 ? $pageNo : 1;
        $pageSize = $this->req->get('pageSize', 30);
        $pageSize = $pageSize>0 ? $pageSize : 30;
        $doctors = ApiPatientDoctorMapper::makeInstance()
            ->filterLikeName($this->req->get('fullname'))
            ->filterActive(ApiPatientDoctorMapper::ACTIVED)
            ->filterDeleted()
            ->filterOnlyDocter()
            ->setLoadSite()
            ->setLoadDep()
            ->setLoadSchedele()
            ->setPage($pageNo, $pageSize)
            ->getPage();
        $doctors = new DoctorListResponse($doctors);
        $rs->setSuccess($doctors->toArray());
        return $this->outputJSON($rs->toArray());
    }

    public function getDoctor($id){
        $rs = new \Result();
        $doctor = ApiPatientDoctorMapper::makeInstance()
            ->filterID($id)
            ->setLoadSite()
            ->setLoadDep()
            ->setLoadSchedele()
            ->getEntityOrFail();
        $doctor = new DoctorResponse($doctor);
        $rs->setSuccess($doctor->toArray());
        return $this->outputJSON($rs->toArray());
    }
}