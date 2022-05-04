<?php

use Company\MVC\Module;

$module = new Module("company/zone");

$module->initDatabase();

try {
    \Company\Zone\Model\ZoneMapper::makeInstance()->updateZone(null, [
        "id" => "local",
        "name" => "local"
    ]);
} catch (\Exception $e) {
    // existed
}
