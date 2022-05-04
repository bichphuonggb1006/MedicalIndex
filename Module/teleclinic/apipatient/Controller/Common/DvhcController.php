<?php

namespace Teleclinic\ApiPatient\Controller\Common;

use Company\Dvhc\DvhcModel;
use Teleclinic\ApiPatient\Model\Common\ApiPatientDvhcMapper;

class DvhcController extends \Company\MVC\Controller
{

    /**
     * @return void
     */
    function getAll()
    {
        $rs = new \Result();
        $rows = ApiPatientDvhcMapper::makeInstance()
            ->filterLevel($this->req->get('level',null))
            ->filterParentID($this->req->get('parentID',null))
            ->getEntities();
        $this->outputJSON($rs->setSuccess($rows)->toArray());
    }
}