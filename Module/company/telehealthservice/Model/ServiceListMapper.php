<?php

namespace Company\Telehealthservice\Model;

use Company\Exception\BadRequestException;
use Company\Exception\NotFoundException;
use Company\File\Model\FileMapper;
use Company\Site\Model\SiteMapper;
use Company\SQL\AnyMapper;
use Company\SQL\Mapper;

/**
 * @property  string $thumbnail
 */

class ServiceListMapper extends Mapper {

    protected $_autoloadDirs = false;
    protected $autoLoadSite;

    function tableName()
    {
        return 'teleclinic_service_list';
    }

    function tableAlias()
    {
        return 'tsl';
    }

    function autoloadDirs() {
        $this->_autoloadDirs = true;
        return $this;
    }

    function autoLoadNameSite() {
        $this->autoLoadSite = true;
        return $this;
    }

    function makeEntity($rawData)
    {
        /**
         * @var ServiceListMapper $entity
         */
        $entity = parent::makeEntity($rawData);
        $entity->thumbnail = FileMapper::makeInstance()->filterContext(FileMapper::CONTEXT_SERVICE_LIST . '::' . $entity->id)->getEntity()->resource ?? '';
        if ($this->_autoloadDirs) {
            $entity->serviceDirs = ServiceDirMapper::makeInstance()->filterID($entity->dirID)->getEntity();
        }
        if ($this->autoLoadSite) {
            $entity->siteInfo = SiteMapper::makeInstance()->filterID($entity->siteID)->getEntity();
        }
        return $entity;
    }

    function updateServiceList($id, $data) {
        $isInsert = is_null($id);
        $keysToUpdate = ["name", "code", "dirID", "sort", "price", "img", "siteID", "description", "isDoctor"];

        $dataUpdate = array_intersect_key($data, array_flip($keysToUpdate));
        if(!$dataUpdate['name'])
            throw new BadRequestException("name cannot be null");

        if(!$dataUpdate['code'])
            throw new BadRequestException("service code cannot be null");
        if(!$dataUpdate['siteID'])
            throw new BadRequestException("site cannot be null");

        $dataUpdate["price"] = (double)arrData($data, "price");
        $dataUpdate["sort"] = (int)arrData($data, "sort");
        $dirID = arrData($data, "dirID");
        if ($dirID)
            $this->checkDirID($dirID);

        $this->startTrans();

        if ($isInsert) {
            if ($dirID === null)
                throw new BadRequestException("ServiceDir must exist");
            $res = $this->insert($dataUpdate);
            $id = $this->db->PO_Insert_ID($this->tableName());
        } else {
            $this->filterID($id)->existsOrFail(new NotFoundException("Could not found ServiceList by id: $id"));
            $res = $this->filterID($id)->update($dataUpdate) != false;
        }
        $this->completeTransOrFail();

        return $id;
    }

    function checkDirID($dirID) {
        ServiceDirMapper::makeInstance()->filterID($dirID)->existsOrFail(new NotFoundException("ServiceDir does not exist"));
    }

    function filterName($name) {
        $this->where('name LIKE ?', __FUNCTION__)->setParamWhere("%$name%", __FUNCTION__);
        return $this;
    }

    function filterDir($dirID) {
        if($dirID)
            $this->where('dirID=?', __FUNCTION__)->setParamWhere($dirID, __FUNCTION__);

        return $this;
    }

    function filterSite($siteID) {
        $this->where('siteID=?', __FUNCTION__)->setParamWhere($siteID, __FUNCTION__);
        return $this;
    }

    /**
     * @param bool $is_deleted false: Chưa xóa; true: dã xóa
     * @return $this
     */
    function filterDeleted(bool  $is_deleted = false) {
        if($is_deleted)
            $this->where('deletedAt IS not NULL', __FUNCTION__);
        else
            $this->where('deletedAt is NULL', __FUNCTION__);
        return $this;
    }
    function filterIsDeleted()
    {
        $this->where('tsl.deletedAt is not null', __FUNCTION__);
        return $this;
    }

    function deleteService($id) {
        $this->startTrans();
        $updateData['deletedAt'] = \DateTimeEx::create()->toIsoString();

        $this->filterID($id)->update($updateData);
        $this->completeTransOrFail();
    }
}