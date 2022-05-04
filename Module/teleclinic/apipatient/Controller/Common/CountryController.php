<?php

namespace Teleclinic\ApiPatient\Controller\Common;

use Teleclinic\ApiPatient\Model\ApiPatientCountryMapper;
use Teleclinic\ApiPatient\Model\Common\ApiPatientDvhcMapper;

class CountryController extends \Company\MVC\Controller
{
    /**
     * @return void
     */
    function getAll()
    {
        $rs = new \Result();
        $rows = ApiPatientCountryMapper::makeInstance()
            ->getEntities();
        $this->outputJSON($rs->setSuccess($rows)->toArray());
    }
}