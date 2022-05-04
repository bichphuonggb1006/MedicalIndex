<?php

namespace Teleclinic\Teleclinic\Model;

use Company\SQL\Mapper;

class MedicalIndexMapper extends Mapper
{
    function tableName()
    {
        return 'medical_index';
    }

    function tableAlias()
    {
        return 'm_i';
    }

    function filterID($id)
    {
        if ($id) $this->where('id=?', __FUNCTION__)->setParamWhere($id, __FUNCTION__);
        return $this;
    }
    function filterPatientCode($p_code)
    {
        if ($p_code) $this->where('patient_code=?', __FUNCTION__)->setParamWhere($p_code, __FUNCTION__);
        return $this;
    }
}