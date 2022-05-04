<?php

namespace Teleclinic\ApiPatient\Model;

use Company\Site\Model\SiteMapper;

class ApiPatientSiteMapper extends SiteMapper
{
    public function filterLikeKeyWord($term = null){
        if(!empty($term))
            $this->where('name like ?', __FUNCTION__)->setParamWhere("%$term%", __FUNCTION__);
        return $this;
    }
    public function filterProvince($term = null){
        if(!empty($term))
            $this->where('province = ?', __FUNCTION__)->setParamWhere("$term", __FUNCTION__);
        return $this;
    }
}