<?php

namespace Teleclinic\ApiPatient\Response;

use Company\MVC\Json;

abstract class AbstractResponse
{
    protected $data;

    public function toArray()
    {
        return $this->data;
    }

    public function toJson()
    {

    }


}