<?php

namespace Teleclinic\ApiPatient\Response;

class ServicesListResponse extends AbstractResponse
{
    public function __construct($data)
    {
        $rows = [];
        foreach ($data['rows'] as $datum) {
            $row = (new ServicesResponse((array)$datum))->toArray();
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