<?php

namespace Company\Zone\Model;

use Company\Exception\BadRequestException;
use Company\SQL\Mapper;

class ZoneMapper extends Mapper{

    static protected $masterZoneID;

    /**
     * @param mixed $masterZoneID
     */
    public static function setMasterZoneID($masterZoneID): void
    {
        self::$masterZoneID = $masterZoneID;
    }

    /**
     * @return mixed
     */
    public static function getMasterZoneID()
    {
        return self::$masterZoneID;
    }

    function tableName()
    {
        return 'system_zone';
    }

    function tableAlias()
    {
        return 'zone';
    }

    function filterName($name) {
        $this->where('`name`=?', __FUNCTION__)->setParamWhere($name, __FUNCTION__);
        return $this;
    }

    function updateZone($id, $data) {
        if(!arrData($data, 'name')) {
            throw new BadRequestException("Name cannot be null");
        }

        if($id == '0') {
            $id = uid();
            //name cannot be duplicated
            $this->makeInstance()->filterName($data['name'])->existsThenFail();
            $this->startTrans();
            $this->insert([
                'id' => $id,
                'name' => $data['name'],
                'masterNode' => $data["masterNode"]
            ]);
            $this->completeTransOrFail();
        } else {
            $this->filterID($id)->existsOrFail();
            $this->startTrans();
            $this->filterID($id)->update([
                'name' => $data['name'],
                'masterNode' => $data["masterNode"]
            ]);
            $this->completeTransOrFail();
        }

        return $this->makeInstance()->filterID($id)->getEntity();
    }

    function deleteZone($id) {
        $this->startTrans();
        $this->filterID($id)->delete();
        $this->completeTransOrFail();
    }

    /**
     * @param string $zoneID
     * @param string $key
     * @param $value
     * @throws \Exception
     */
    function setAttr($zoneID, $key, $value) {
        $zone = $this->filterID($zoneID)->getRow();
        $attrs = arrData($zone, "attrs");

        if ($attrs)
            $attrs = json_decode($attrs, true);

        $attrs[$key] = $value;
        $this->filterID($zoneID)->update(["attrs" => json_encode($attrs)]);
    }

    /**
     * @param string $zoneID
     * @param string $key
     * @return array|mixed|null
     */
    function getAttr($zoneID, $key) {
        $zone = $this->filterID($zoneID)->getRow();
        $attrs = arrData($zone, "attrs");

        if ($attrs) {
            $attrs = json_decode($attrs, true);
            return arrData($attrs, $key);
        }

        return null;
    }
}