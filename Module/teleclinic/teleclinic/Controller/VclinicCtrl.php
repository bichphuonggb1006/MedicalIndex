<?php

namespace Teleclinic\Teleclinic\Controller;

use Company\Auth\Auth;
use Company\Exception\BadRequestException;
use Company\MVC\Controller;
use Company\MVC\Json;
use Company\User\Model\DepartmentMapper;
use Teleclinic\Teleclinic\Model\ClinicScheduleModel;
use Teleclinic\Teleclinic\Model\VclinicMapper;

class VclinicCtrl extends Controller {
    /**
     * @var Auth
     */
    protected $auth;

    protected function init() {
        parent::init();
        $this->auth = Auth::getInstance();
    }

    function updateClinic($id = 0) {
        $this->auth->requireLogin();
        VclinicMapper::makeInstance()->updateClinic($id, $this->input());
        $this->resp->setBody(json_encode(result(true)));
    }

    function getClinic($id) {
        $this->auth->requireLogin();
        $row = VclinicMapper::makeInstance()
            ->autoloadDep()
            ->autoloadService()
            ->filterID($id)
            ->filterNotDeleted()
            ->getEntity();

        $this->resp->setBody(json_encode($row));
    }

    function getClinics() {
        $this->auth->requireLogin();
        if(!$this->req->get('siteID'))
            throw new BadRequestException("siteID is required");

        $mapper = VclinicMapper::makeInstance()
            ->autoloadDep()
            ->autoloadService()
            ->autoloadUser()
            ->filterSiteID($this->req->get('siteID'))
            ->filterDepID($this->req->get('depID'))
            ->filterNotDeleted();
        $this->auth->setSiteID($this->req->get('siteID'));


        if(!$this->auth->isAdmin() && !$this->auth->isFullControl() && !$this->auth->hasPrivilege('manageDepartment')) {
            //filter theo phòng
            $mapper->filterUserId($this->auth->getUser()->id);
        }
        $rows = $mapper->getEntities()->toArray();

        if($this->req->get('groupBy') == 'depID' && count($rows)) {
            //get all depID
            $depIDs = [];
            foreach($rows as $row)
                $depIDs[$row->depID] = $row->depID;
            $deps = DepartmentMapper::makeInstance()->filterID($depIDs)->getEntities();
            foreach($deps as $dep) {
                $dep->clinics = [];
                foreach($rows as $row) {
                    if($row->depID == $dep->id)
                        $dep->clinics[] = $row;
                }
            }
            $this->resp->setBody(Json::encode($deps));
        } else {
            //not group by
            $this->resp->setBody(Json::encode($rows));
        }
    }

    function updateSchedule($clinicID) {
        Auth::getInstance()->requireLogin();

        ClinicScheduleModel::makeInstance()->updateSchedule($clinicID, $this->input('date'), $this->input('schedule'));
        $this->outputJSON(result(true));
    }

    function deleteClinic($id) {
        $this->auth->requireLogin();
        VclinicMapper::makeInstance()->filterID($id)->update([
            'deletedAt' => \DateTimeEx::create()->toIsoString()
        ]);
    }

    function getServiceClinics($serviceID){
        $clinics = VclinicMapper::makeInstance()->filterLinkedService($serviceID)->getAll()->toArray();
        $this->resp->setBody(Json::encode($clinics));
    }

}
