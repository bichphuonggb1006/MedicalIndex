<?php

namespace Teleclinic\ApiPatient\Response;

class DoctorResponse extends AbstractResponse
{
    public function __construct($data)
    {
        $data = gettype($data) == 'object' ? (array)$data : $data;
        $this->data = [
            "id" => $data['id'],
            "price" => '',
            "description" => $data['desc'],
            "health_facilities" => [
                    "name" => $data['siteInfo']->name
                ],
            "type" => [
                "code" => $data['department']->code,
                "name" => $data['department']->name
            ],
            "schedules" => [
                "date" => 'Chưa làm',
                "time" => '',
                "status" => ''
            ],
        ];
        return $this;
    }
}