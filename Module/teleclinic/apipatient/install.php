<?php
use Company\MVC\Module;
use Company\User\Model\PrivilegeMapper;
use Company\SQL\DB;

$module = new Module("teleclinic/apiPatient");
$module->initDatabase();

$db = DB::getInstance();
$privMapper = PrivilegeMapper::makeInstance();

$privMapper->startTrans();
$privGroup = ['id' => 'teleclinic', 'name' => 'Khám bệnh nhân từ xa'];
$privMapper->createGroupIfNotExists($privGroup['id'], $privGroup['name']);

require __DIR__ . '/sql/privileges.data.php'; //lấy danh sách privilege

//insert priv
foreach ($exports as $priv) {
    $privMapper->createPrivilegeIfNotExists($priv['id'], $privGroup['id'], $priv['name'], arrData($priv, 'desc'));
}
$privMapper->completeTrans();