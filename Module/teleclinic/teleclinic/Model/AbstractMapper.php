<?php

namespace Teleclinic\Teleclinic\Model;

use Company\SQL\Mapper;

abstract class AbstractMapper extends Mapper
{


    /**
     * @param $siteID
     * @return $this
     */
    public function filterSiteID($siteID)
    {
        //Todo: chưa xử lý nếu siteID thuộc vào site dùng chung dữ liệu
        $this->where('siteID=?', __FUNCTION__)
            ->setParamWhere($siteID, __FUNCTION__);
        return $this;
    }
}