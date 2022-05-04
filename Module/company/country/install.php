<?php

namespace Company\Country;

use Company\Country\CountryModel;
use Company\SQL\DB;

$module = new \Company\MVC\Module("company/country");

//check if db inited
CountryModel::makeInstance()->limit(1)->count($records);
$module->initDatabase();
if ($records == 0) {
    //insert records
    $xml = simplexml_load_file(__DIR__ . '/sql/contry_data.xml');
    $insert = [];
    $bulkInsertSize = 100;
    foreach ($xml->children() as $xmlItem) {
        $insert[] = [
            'id' => (string)$xmlItem->code,
            'continent' => (string)$xmlItem->continent,
            'name_en' => (string)$xmlItem->name_en ?? null,
            'name' => (string)$xmlItem
        ];
        if (count($insert) == $bulkInsertSize) {
            CountryModel::makeInstance()->insert($insert);
            $insert = []; //reset
        }
    }
    if (count($insert)) { //if anything left
        CountryModel::makeInstance()->insert($insert);
    }

}
