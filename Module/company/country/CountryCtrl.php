<?php

namespace Company\Country;

use Company\Country\CountryModel;
use Company\MVC\Controller;

class CountryCtrl extends Controller
{
    public function initDB()
    {
        $xml = simplexml_load_file(__DIR__ . '/sql/country_data.xml');
        $insert = [];
        $bulkInsertSize = 100;
        foreach ($xml->children() as $xmlItem) {
            $insert[] = [
                'id' => (string)$xmlItem->attributes()->code,
                'continent' => (string)$xmlItem->attributes()->continent,
                'name_en' => (string)$xmlItem,
                'name' => null,
            ];
            if (count($insert) == $bulkInsertSize) {
                CountryModel::makeInstance()->insert($insert);
                $insert = []; //reset
            }
        }
        if (count($insert)) { //if anything left
            CountryModel::makeInstance()->insert($insert);
        }
        $this->outputJSON(['message' => 'Thành công']);
    }

    function getAll()
    {
        $response =  new \Result();
        $rows = CountryModel::makeInstance()->getEntities();
        $response->setSuccess($rows);
        $this->outputJSON($response->toArray());
    }
}