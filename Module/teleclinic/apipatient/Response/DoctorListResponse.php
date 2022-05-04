<?php

namespace Teleclinic\ApiPatient\Response;

class DoctorListResponse extends AbstractResponse
{
    public function __construct($data)
    {
        $rows = [];
        foreach ($data['rows'] as $datum) {
            $row = (new DoctorResponse((array)$datum))->toArray();
            unset($row['schedules']);
            $rows[]=$row;
        }
        $this->data = [
            'rows' => $rows,
            "pageNo" => $data['pageNo'],
            "pageSize" => $data['pageSize'],
            "recordCount" => $data['recordCount'],
            "pageCount" => $data['pageCount'],
        ];
        return $this;
    }
}