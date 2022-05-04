<?php

namespace Teleclinic\ApiPatient\Response;

class SiteResponse extends AbstractResponse
{
    public function __construct($data)
    {
        $data = gettype($data) == 'object' ? (array)$data : $data;
        $this->data = [
            "id" => $data['id'],
            "name" => $data['name'],
//            "shortName" => $data['shortName'],
            "active" => $data['active'],
//            "createdDate" => $data['createdDate'],
//            "willDeleteAt" => $data['willDeleteAt'],
            "address" => "{$data['address']}, {$data['ward_name']}, {$data['district_name']}, {$data['province_name']}",
            "phone" => $data['phone'],
//            "tags" => $data['tags'],
            "description" => $data['description']
        ];
        return $this;
    }
}