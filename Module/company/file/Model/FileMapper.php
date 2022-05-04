<?php

namespace Company\File\Model;

use Company\Exception\BadRequestException;
use Company\SQL\Mapper;

/**
 * @property  int $id
 * @property  string $resource
 * @property  int $size
 * @property  string $context
 */
class FileMapper extends Mapper
{

    const CONTEXT_SERVICE_DIR = 'serviceDir';
    const CONTEXT_PATIENT_AVATAR = 'avatar';
    const CONTEXT_SERVICE_LIST = 'ServiceList';
    const CONTEXT_SITE = 'Site';

    public static $allType = [
        self::CONTEXT_PATIENT_AVATAR => 'Ảnh đại diện cho tài khoản người dùng',
        self::CONTEXT_SERVICE_DIR => 'Ảnh banner nhóm dịch vụ',
        self::CONTEXT_SERVICE_LIST => 'Ảnh banner dịch vụ',
        self::CONTEXT_SITE => 'Ảnh banner cơ sở y tế',
    ];

    function tableName()
    {
        return 'system_file';
    }

    function tableAlias()
    {
        return 'sfile';
    }

    function makeEntity($rawData)
    {
        return new FileEntity($rawData);
    }

    function updateFile($id, $input)
    {
        if (isset($input['b64']))
            $input['b64Size'] = strlen($input['b64']);
        if (!$id) {
            //insert
            $requiredFields = ['b64', 'siteID', 'context', 'mime', 'name'];
            foreach ($requiredFields as $field) {
                if (!arrData($input, $field))
                    throw new BadRequestException("missing required field: $field");
            }
            $allowedFields = array_merge($requiredFields, ['b64Size']);
            $insert = ['id' => uid(), 'createdDate' => \DateTimeEx::create()->toIsoString()];
            foreach ($allowedFields as $field) {
                $insert[$field] = $input[$field];
            }
            $this->insert($insert);
        } else {
            //update
            $allowedFields = ['b64', 'siteID', 'context', 'mime', 'b64Size', 'name'];
            $update = [];
            foreach ($allowedFields as $field) {
                if (isset($input[$field]))
                    $update[$field] = $input[$field];
            }
            if (empty($update))
                throw new BadRequestException("input parametaer must not be empty");
            $this->filterID($id)->update($update);
        }
    }

    function filterContext($context)
    {
        $this->where('context=?', __FUNCTION__)->setParamWhere($context, __FUNCTION__);
        return $this;
    }

    function filterResource($term)
    {
        $this->where('resource=?', __FUNCTION__)->setParamWhere($term, __FUNCTION__);
        return $this;
    }

    function selectForList()
    {
        $this->select('id, createdDate, siteID, b64Size, context, mime, `name`');
        return $this;
    }


    /**
     * Cập nhật hoặc thêm mới ảnh banner cho nhóm dịch vụ
     * @param $serviceDirID
     * @param $serviceListID
     * @param string $resource_file
     * @return bool|int
     * @throws \Exception
     */
    //upload ảnh cho serviceDir
    public static function storeOrUpdateThumbnailServiceDir($serviceDirID, string $resource_file)
    {
        $exists = FileMapper::makeInstance()->filterContext(FileMapper::CONTEXT_SERVICE_DIR . '::' . $serviceDirID)->filterResource($resource_file)->isExists();
        if ($exists)
            return true;
        FileMapper::makeInstance()->filterContext(FileMapper::CONTEXT_SERVICE_DIR . '::' . $serviceDirID)->delete();
        return FileMapper::makeInstance()->filterResource($resource_file)->update([
            'context' => FileMapper::CONTEXT_SERVICE_DIR . '::' . $serviceDirID
        ]);
    }

    public static function deleteThumbnailServiceDir($serviceDirID){
        FileMapper::makeInstance()->filterContext(FileMapper::CONTEXT_SERVICE_DIR . '::' . $serviceDirID)->delete();
    }

    //upload ảnh cho serviceList
    public static function storeOrUpdateThumbnailServiceList($serviceListID, string $resource_file)
    {
        $exists = FileMapper::makeInstance()->filterContext(FileMapper::CONTEXT_SERVICE_LIST . '::' . $serviceListID)->filterResource($resource_file)->isExists();
        if ($exists)
            return true;
        FileMapper::makeInstance()->filterContext(FileMapper::CONTEXT_SERVICE_LIST . '::' . $serviceListID)->delete();
        return FileMapper::makeInstance()->filterResource($resource_file)->update([
            'context' => FileMapper::CONTEXT_SERVICE_LIST . '::' . $serviceListID
        ]);
    }

    public static function deleteThumbnailServiceList($serviceListID){
        FileMapper::makeInstance()->filterContext(FileMapper::CONTEXT_SERVICE_LIST . '::' . $serviceListID)->delete();
    }

    //upload ảnh cho Site
    public static function storeOrUpdateThumbnailSite($siteID, string $resource_file)
    {
        $exists = FileMapper::makeInstance()->filterContext(FileMapper::CONTEXT_SITE . '::' . $siteID)->filterResource($resource_file)->isExists();
        if ($exists)
            return true;
        FileMapper::makeInstance()->filterContext(FileMapper::CONTEXT_SITE . '::' . $siteID)->delete();
        return FileMapper::makeInstance()->filterResource($resource_file)->update([
            'context' => FileMapper::CONTEXT_SITE . '::' . $siteID
        ]);
    }

    public static function deleteThumbnailSite($siteID){
        FileMapper::makeInstance()->filterContext(FileMapper::CONTEXT_SITE . '::' . $siteID)->delete();
    }

}