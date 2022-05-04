<?php

namespace Company\Country;

use Company\SQL\Mapper;

class CountryModel extends Mapper
{

    function tableName()
    {
        return 'system_countries';
    }

    function tableAlias()
    {
        return 's_c';
    }

    public function filterCode($code){
        $this->where('id=?',__FUNCTION__)->setParamWhere($code,__FUNCTION__);
        return $this;
    }
}