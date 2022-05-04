<?php

namespace Teleclinic\ApiPatient\Response;

class SiteListResponse extends AbstractResponse
{
    public function __construct($data)
    {

        $rows = [];
        foreach ($data['rows'] as $datum) {
                $rows[] = (new SiteResponse((array)$datum))->toArray();
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