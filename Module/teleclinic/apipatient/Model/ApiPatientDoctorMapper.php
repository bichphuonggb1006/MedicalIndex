<?php

namespace Teleclinic\ApiPatient\Model;

use Company\User\Model\UserMapper;

class ApiPatientDoctorMapper extends UserMapper
{
    public function filterLikeName($name = null){
        if(!empty($name))
            $this->where('fullname like ?', __FUNCTION__)->setParamWhere("%$name%", __FUNCTION__);
        return $this;
    }
}