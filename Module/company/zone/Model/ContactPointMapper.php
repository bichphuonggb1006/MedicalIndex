<?php

namespace Company\Zone\Model;

use Company\Cache\CacheDriver;
use Company\Exception\BadRequestException;
use Company\SQL\Mapper;

class ContactPointMapper extends Mapper{

    protected $autoloadZone;

    function tableName()
    {
        return 'system_contact_point';
    }

    function tableAlias()
    {
        return 'system_contact_point';
    }

    function filterZone($zoneID) {
        $this->where('zoneID=?', __FUNCTION__)->setParamWhere($zoneID, __FUNCTION__);
        return $this;
    }

    function filterAddr($address, $exactSearch = false) {
        $param = $exactSearch ? $address : "%$address%";
        $this->where("(`lanAddress` LIKE ? OR `internetAddress` LIKE ?)")
            ->setParamWhere($param, 0)
            ->setParamWhere($param, 1);
        return $this;
    }

    function insertContactPoint($data) {
        if(!arrData($data,'lanAddress') && !arrData($data,'internetAddress')) {
            throw new BadRequestException("address cannot be null");
        }

        $regexp = '/^[a-zA-Z0-9][a-zA-Z0-9\-\_]+[a-zA-Z0-9]$/';
        foreach (["lanAddress", "internetAddress"] as $key) {
            if (false === preg_match($regexp, $data[$key])) {
                throw new BadRequestException("$key must be ip/domain name");
            }
            if(strpos($data[$key], 'http') !== false) {
                throw new BadRequestException("$key must must not have http");
            }
        }

        $zoneID = arrData($data,'zoneID');
        ZoneMapper::makeInstance()
            ->filterID($zoneID)
            ->existsOrFail(new BadRequestException("Zone not found"));

        $id = uid();

        $this->startTrans();
        $this->insert([
            'id' => $id,
            'lanAddress' => arrData($data,'lanAddress'),
            'internetAddress' => arrData($data,'internetAddress'),
            'zoneID' => $zoneID
        ]);
        $this->completeTransOrFail();

        return $this->makeInstance()->filterID($id)->getEntity();
    }

    function deleteContactPoint($id) {
        $this->startTrans();
        $this->filterID($id)->delete();
        $this->completeTransOrFail();
    }

    function autoloadZone($enable = true) {
        $this->autoloadZone = $enable;
        return $this;
    }

    function makeEntity($rawData)
    {
        $entity = parent::makeEntity($rawData);
        if($this->autoloadZone && $entity->id) {
            $entity->zone = ZoneMapper::makeInstance()
                ->filterID($entity->zoneID)
                ->getEntity();
        }
        return $entity;
    }

    /**
     * Detect contact point from client hostname
     * @return \Company\Entity\Entity Contact Point Entity with Zone
     */
    function detectContactPoint() {
        $host = arrData($_SERVER, 'HTTP_HOST');

        $cache = CacheDriver::getInstance(CacheDriver::PRIVATE_MEMORY_CACHE);
        $contactPoint = unserialize($cache->get("contactpoint/$host"));

        if (!$contactPoint) {
            $contactPoint = $this->makeInstance()
                ->autoloadZone()
                ->filterAddr($host, true)
                ->getEntityOrFail();

            $cache->set("contactpoint/$host", serialize($contactPoint), 3600);
        }

        if(!$contactPoint->id) {
            return result(false, 'Contact point not found');
        }
        if(!$contactPoint->zone->id) {
            return result(false, 'Zone not found');
        }
        return result(true, $contactPoint);
    }
}