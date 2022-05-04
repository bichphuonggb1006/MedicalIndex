<?php

namespace Company\Telehealthservice\Model;

use Company\Exception\BadRequestException;
use Company\File\Model\FileMapper;
use Company\SQL\Mapper;

/**
 * @property  string $thumbnail
 * @property  array $services
 */
class ServiceDirMapper extends Mapper
{

    const PARENTID = 0;

    protected $autoloadChildDir;
    protected $autoloadService;

    function __construct()
    {
        parent::__construct();

        $this->orderBy($this->tableAlias() . '.sort');
    }

    function tableName()
    {
        return 'teleclinic_service_dir';
    }

    function tableAlias()
    {
        return 'tsd';
    }

    function autoloadChildDir()
    {
        $this->autoloadChildDir = true;
        return $this;
    }

    function autoloadServices()
    {
        $this->autoloadService = true;
        return $this;
    }

    function makeEntity($rawData)
    {
        /**
         * @var ServiceDirMapper $entity
         */
        $entity = parent::makeEntity($rawData);
        $entity->services = ServiceListMapper::makeInstance()->filterDir($entity->id)->getEntities()->toArray();
        $entity->thumbnail = FileMapper::makeInstance()->filterContext(FileMapper::CONTEXT_SERVICE_DIR . '::' . $entity->id)->getEntity()->resource ?? '';
        return $entity;
    }

    function filterSite($siteID)
    {
        $this->where('siteID=?', __FUNCTION__)->setParamWhere($siteID, __FUNCTION__);
        return $this;
    }

    function filterParent($parentID)
    {
        if ($parentID !== null)
            $this->where('parentID=?', __FUNCTION__)->setParamWhere($parentID, __FUNCTION__);
        return $this;
    }

    /**
     * @param bool $is_deleted false: Chưa xóa; true: dã xóa
     * @return $this
     */
    function filterDeleted(bool $is_deleted = false)
    {
        if ($is_deleted)
            $this->where('deletedAt IS not NULL', __FUNCTION__);
        else
            $this->where('deletedAt is NULL', __FUNCTION__);

        return $this;
    }

    function filterParentRoot()
    {
        $this->where('parentID=?', __FUNCTION__)->setParamWhere(0, __FUNCTION__);
        return $this;
    }

    function getEntity($callback = null)
    {
        $entity = parent::getEntity($callback);
        $entity->parentID = (int)$entity->parentID;
        $entity->sort = (int)$entity->sort;
        return $entity;
    }

    function updateDir($id, $data)
    {
        $update = [
            'name' => arrData($data, 'name'),
            'parentID' => (int)arrData($data, 'parentID'),
            'sort' => (int)arrData($data, 'sort', -1),
            'siteID' => null,
            'deletedAt' => null
        ];

        if (!$update['name'])
            throw new BadRequestException("name cannot be null");

        //auto sort
        if ($update['sort'] == -1) {
            $maxSort = static::makeInstance()->filterSite($update['siteID'])->select('MAX(sort)')->getOne();
            $update['sort'] = (int)$maxSort + 1;
        }

        $this->startTrans();
        $ok = true;
        if ($id == 0) {
            $ok = $this->insert($update);
            $id = $this->db->PO_Insert_ID($this->tableName());
        } else {
            $this->filterID($id)->update($update);
        }
        $this->completeTransOrFail();

        if ($ok)
            $this->rebuildPath("parentID", "path", "sort", 0);
        return $id;
    }

    function deleteDir($id)
    {
        $this->startTrans();
        $updateData['deletedAt'] = \DateTimeEx::create()->toIsoString();
        $this->filterID($id)->update($updateData);
        $this->completeTransOrFail();
    }

}