<?php

namespace Company\Dvhc;

use Company\SQL\Mapper;

class DvhcModel extends Mapper
{

    const LEVEL_PROVINCE = 'TINH';
    const LEVEL_DISTRICT = 'HUYEN';
    const LEVEL_WARD = 'XA';

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

    function filterParentID($parentID)
    {
        if ($parentID == null)
            $parentID = '0';
        $this->where('parentID=?', __FUNCTION__)->setParamWhere($parentID, __FUNCTION__);
        return $this;
    }

    function filterID($ID)
    {
        if ($ID == null)
            $parentID = '0';
        $this->where('ID=?', __FUNCTION__)->setParamWhere($ID, __FUNCTION__);
        return $this;
    }

    function filterLevel($level)
    {
        if ($level)
            $this->where('level=?', __FUNCTION__)->setParamWhere($level, __FUNCTION__);
        return $this;
    }
}