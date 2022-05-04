<?php
namespace Teleclinic\ApiPatient\Model\Common;

use Company\Dvhc\DvhcModel;
use Company\SQL\Mapper;

class ApiPatientDvhcMapper extends DvhcModel {

    function tableName()
    {
        return 'system_dvhc';
    }

    function tableAlias()
    {
        return 'dvhc';
    }

    function __construct()
    {
        parent::__construct();
        $this->orderBy('id');
    }

    function filterParentID($parentID) {
        if($parentID == null)
            $parentID = '0';
        $this->where('parentID=?', __FUNCTION__)->setParamWhere($parentID, __FUNCTION__);
        return $this;
    }

    function filterLevel($level) {
        if($level)
            $this->where('level=?', __FUNCTION__)->setParamWhere($level, __FUNCTION__);
        return $this;
    }
}