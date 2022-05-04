<?php

namespace Teleclinic\ApiPatient\Response;

class ServicesResponse extends AbstractResponse
{
    public function __construct($data)
    {
        $data = gettype($data) == 'object' ? (array)$data : $data;
        $this->data = [
            "id" => $data['id'],
            "price" => $data['price'],
            "description" => '',
            "health_facilities" => [
                "name" => $data['siteInfo']->name
            ],
            "services_dirs" => ["id"=>$data['serviceDirs']->id,
                "name"=>$data['serviceDirs']->name]
        ];
        return $this;
    }
}