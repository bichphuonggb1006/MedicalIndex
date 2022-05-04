<?php

namespace Company\Site\Model;

use Company\Dvhc\DvhcModel;
use Company\Exception as E;
use Company\File\Model\FileMapper;
use Company\MVC\Module;
use Company\MVC\Trigger;
use Company\User\Model\UserMapper;

class SiteMapper extends \Company\SQL\Mapper {

    const ACTIVED=1;
    const INACTIVE=0;

    protected $dbVersion;
    protected $autoloadRoles;
    protected $autholoadPrivs;
    protected $autoloadDep;
    protected $autoloadLogin;
    protected $autoloadTags;

    public function tableAlias() {
        return 'ss';
    }

    public function tableName() {
        return 'system_site';
    }

    function __construct() {
        parent::__construct();
        $this->orderBy('ss.createdDate DESC');
        $this->dbVersion = Module::getInstance('company/site')->getMeta()->version;
    }

    function autoloadTags($auto = true){
        $this->autoloadTags = $auto;
        return $this;
    }

    function makeEntity($rawData) {
        $entity = parent::makeEntity($rawData);
        if ($this->autoloadTags)
        {
            $entity->tags = $this->db->getCol("select tag from system_site_tag where site_id = ?", [$entity->id]);
        }
        $entity->thumbnail = FileMapper::makeInstance()->filterContext(FileMapper::CONTEXT_SITE . '::' . $entity->id)->getEntity()->resource ?? '';
        return $entity;
    }

    function updateSite($id, $input) {
        $isInsert = $this->makeInstance()->filterID($id)->isExists() ? false : true;

        //validate required
        $required = ['name','province','district','ward','address','location'];
        foreach ($required as $field) {
            if (!strlen(trim($input[$field]))) {
                throw new E\BadRequestException("Missing required field: " . $field);
            }
        }

        //fields to update
        $updateFields = ['name', 'active', 'shortName', 'willDeleteAt'];
        $updateData = [];
        $attrs = [];
        foreach($input as $field => $val) {
            if(in_array($field, $updateFields))
                $updateData[$field] = $val;
            else
                $attrs[$field] = $val;
        }
        //format du lieu
        $updateData['active'] = $updateData['active'] ? 1 : 0;
        $updateData['province'] = $input['province'];
        $updateData['district'] = $input['district'];
        $updateData['ward'] = $input['ward'];
        $updateData['address'] = $input['address'];
        $updateData['location'] = $input['location'];


        //Filable address to attribute
        $attrs['ward_name']=DvhcModel::makeInstance()->filterID($input['ward'])->select('name')->getOne();
        $attrs['province_name']=DvhcModel::makeInstance()->filterID($input['province'])->select('name')->getOne();
        $attrs['district_name']=DvhcModel::makeInstance()->filterID($input['district'])->select('name')->getOne();

        $site = $this->makeInstance()->filterName($input['name'], false)->getEntity();
        //check tên site trên hệ thống
        if ($site->id && $site->id != $id) {
            throw new E\BadRequestException("site name is exits");
        }
        if ($isInsert) {
            $id = $updateData['id'] = $attrs["id"] = arrData($input, 'id') ?: uniqid();
            $updateData += [
                'createdDate' => \DateTimeEx::create()->toIsoString()
            ];
        }

        if ($updateData['willDeleteAt'] == null) {
            $updateData['willDeleteAt'] = \DateTimeEx::createDateDefault()->toIsoString();
        }

        //update
        $this->startTrans();
        if ($isInsert) {
            $this->insert($updateData);
            $id = $this->db->PO_Insert_ID($this->tableName());
        } else {
            $this->filterID($id)->update($updateData);
        }
        if(!empty($attrs)) {
            static::makeInstance()->filterID($id)->updateJson('attrs', $attrs);
        }
        $tags = arrData($input, 'tags');
        $this->db->delete("system_site_tag", "site_id=?", [$id]);


        if (is_array($tags))
        {
            foreach($tags as $tag){
                $this->db->insert("system_site_tag", [
                   'site_id' => $id,
                   'tag' => $tag
                ]);
            }
        }
        $this->completeTransOrFail();

        // <Doanh>: trigger
        $triggerParams = [
            'id' => $id,
            'isInsert' => $isInsert,
            'input' => $input,
            'updateData' => $updateData,
            'attrs' => $attrs
        ];
        // <Doanh>: thực hiện trigger
        Trigger::execute('user/site::afterUpdate', $triggerParams);

        return $id;
    }

    function getAllTags()
    {
        return $this->db->getCol("select distinct (tag) from system_site_tag");
    }



    function deleteSite($id, $force = false) {
        if ($id == 'master') {
            throw new E\BadRequestException('Don`t delete id is master');
        }
        $this->startTrans();
        $data = [
            'willDeleteAt' => \DateTimeEx::create()->addDay(30)->toIsoString(),
            'active' => 0
        ];
        $this->makeInstance()
                ->filterID($id)
                ->update($data);

        $this->completeTransOrFail();
    }

    function filterName($name, $filterLike = true) {
        if ($name) {
            if ($filterLike) {
                $this->where('ss.name LIKE ?', __FUNCTION__)
                        ->setParamWhere("%$name%", __FUNCTION__);
            } else {
                $this->where('ss.name LIKE ?', __FUNCTION__)
                        ->setParamWhere("$name", __FUNCTION__);
            }
        }
        return $this;
    }

    function filterTags($tags) {
        if(empty($tags)) {
            return $this;
        }
        $where = [];
        foreach($tags as $tag) {
            $where[] = '?';
        }
        $where = implode(",", $where);
        $siteIDs = $this->db->getCol("select site_id from system_site_tag where tag in($where)", $tags);
        if(empty($siteIDs)) {
            $siteIDs = ['not found any site'];
        }
        $this->filterId($siteIDs);
        return $this;
    }

    function filterActive($active = 1) {
        if ($active === null || $active == 'null') {
            unset($this->where[__FUNCTION__]);
            unset($this->paramsWhere[__FUNCTION__]);
        } else if ($active) {
            $this->where('ss.active=?', __FUNCTION__)
                    ->setParamWhere(1, __FUNCTION__);
        } else {
            $this->where('ss.active=?', __FUNCTION__)
                    ->setParamWhere(0, __FUNCTION__);
        }
        return $this;
    }



}
