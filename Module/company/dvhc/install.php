<?php
namespace Company\Dvhc;

use Company\SQL\DB;

$module = new \Company\MVC\Module("company/dvhc");

//check if db inited
DvhcModel::makeInstance()->limit(1)->count($records);
$module->initDatabase();
if($records == 0) {
    //insert records
    $xml = simplexml_load_file(__DIR__ . '/sql/dvhc_data.xml');
    $insert = [];
    $bulkInsertSize = 100;
    foreach($xml->children() as $xmlItem) {
        $insert[] = [
            'id' => (string) $xmlItem->MaDVHC,
            'name' => (string) $xmlItem->Ten,
            'level' => (string) $xmlItem->Cap,
            'parentID' => (string) $xmlItem->CapTren ? (string) $xmlItem->CapTren : '0'
        ];
        if(count($insert) == $bulkInsertSize) {
            DvhcModel::makeInstance()->insert($insert);
            $insert = []; //reset
        }
    }
    if(count($insert)) { //if anything left
        DvhcModel::makeInstance()->insert($insert);
    }

}
